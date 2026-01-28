<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\UserPin;
use App\Models\BlockedUser;
use App\Models\UserAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $targetUserId = $request->query('user_id');
        return view('modules.chat.index', compact('targetUserId'));
    }

    public function getUsers()
    {
        $currentUser = Auth::user();
        
        // Get blocked users IDs
        $blockedUserIds = BlockedUser::where('user_id', $currentUser->id)
            ->pluck('blocked_user_id')
            ->toArray();

        // Get users from the same paroquia, excluding current user
        $users = User::where('paroquia_id', $currentUser->paroquia_id)
            ->where('id', '!=', $currentUser->id)
            ->select('id', 'name', 'avatar', 'user', 'hide_name') 
            ->get();

        // Get pinned users IDs for current user
        $pinnedUserIds = UserPin::where('user_id', $currentUser->id)
            ->pluck('pinned_user_id')
            ->toArray();

        // Optimize: Fetch all messages involving current user in one query
        $allMessages = Message::where(function($q) use ($currentUser) {
                $q->where('sender_id', $currentUser->id)
                  ->where('deleted_by_sender', false);
            })
            ->orWhere(function($q) use ($currentUser) {
                $q->where('receiver_id', $currentUser->id)
                  ->where('deleted_by_receiver', false);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by the "other" user
        $conversations = $allMessages->groupBy(function ($msg) use ($currentUser) {
            return $msg->sender_id == $currentUser->id ? $msg->receiver_id : $msg->sender_id;
        });

        // Get latest access per user
        $latestAccesses = UserAccess::whereIn('user_id', $users->pluck('id'))
            ->orderBy('access_date', 'desc')
            ->orderBy('access_time', 'desc')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        // Attach unread count, last message, pin status and online info
        $users->map(function ($user) use ($conversations, $currentUser, $pinnedUserIds, $blockedUserIds, $latestAccesses) {
            $userMessages = $conversations->get($user->id, collect());
            
            $lastMessage = $userMessages->first(); // Since it's ordered by desc, first is latest

            $unreadCount = $userMessages->where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id)
                ->where('is_read', false)
                ->count();

            $user->last_message = $lastMessage;
            $user->unread_count = $unreadCount;
            $user->is_pinned = in_array($user->id, $pinnedUserIds);
            $user->is_blocked = in_array($user->id, $blockedUserIds);
            
            // Format name based on hide_name
             if ($user->hide_name) {
                $user->display_name = 'Usuário';
            } else {
                $user->display_name = $user->name ?? $user->user;
            }
            
            // Online status based on latest access within 10 minutes
            $user->is_online = false;
            $user->status_text = 'Offline';
            $user->last_access_at = null;
            if ($latestAccesses->has($user->id)) {
                $acc = $latestAccesses->get($user->id);
                $dt = Carbon::parse(($acc->access_date ?? '') . ' ' . ($acc->access_time ?? ''));
                $user->last_access_at = $dt ? $dt->toIso8601String() : null;
                if ($dt) {
                    $diffMinutes = $dt->diffInMinutes(Carbon::now());
                    $user->is_online = $diffMinutes < 10;
                    if ($user->is_online) {
                        $user->status_text = 'Online';
                    } else {
                        $user->status_text = 'Visto há ' . ($diffMinutes < 60 ? $diffMinutes . ' min' : $dt->format('d/m H:i'));
                    }
                }
            }
            
            return $user;
        });
        
        // Sort by Pinned (desc), then last message date (desc), then name (asc)
        $users = $users->sort(function ($a, $b) {
            // 1. Pinned first
            if ($a->is_pinned && !$b->is_pinned) return -1;
            if (!$a->is_pinned && $b->is_pinned) return 1;

            // 2. Last message date (desc)
            $timeA = $a->last_message ? $a->last_message->created_at->timestamp : 0;
            $timeB = $b->last_message ? $b->last_message->created_at->timestamp : 0;
            
            if ($timeA != $timeB) {
                return $timeB <=> $timeA;
            }

            // 3. Name (asc)
            return strcasecmp($a->display_name, $b->display_name);
        })->values();

        return response()->json($users);
    }

    public function getMessages($userId)
    {
        $currentUser = Auth::user();

        // Check if user is blocked (by me or them) - optional, but let's check blocking status to return
        $isBlockedByMe = BlockedUser::where('user_id', $currentUser->id)
            ->where('blocked_user_id', $userId)
            ->exists();
        
        $isBlockedByThem = BlockedUser::where('user_id', $userId)
            ->where('blocked_user_id', $currentUser->id)
            ->exists();

        // Mark messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where(function ($q) use ($userId, $currentUser) {
                $q->where('sender_id', $userId)
                  ->where('receiver_id', $currentUser->id)
                  ->where('deleted_by_receiver', false);
            })
            ->orWhere(function ($q) use ($userId, $currentUser) {
                $q->where('sender_id', $currentUser->id)
                  ->where('receiver_id', $userId)
                  ->where('deleted_by_sender', false);
            })
            ->with(['replyTo.sender:id,name,user,hide_name']) // Eager load reply info
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
            'is_blocked_by_me' => $isBlockedByMe,
            'is_blocked_by_them' => $isBlockedByThem
        ]);
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'message' => 'required|string',
                'reply_to_id' => 'nullable|exists:messages,id',
            ]);

            $currentUser = Auth::user();
            
            // Check if blocked
            $isBlocked = BlockedUser::where(function($q) use ($currentUser, $request) {
                $q->where('user_id', $currentUser->id)->where('blocked_user_id', $request->receiver_id);
            })->orWhere(function($q) use ($currentUser, $request) {
                $q->where('user_id', $request->receiver_id)->where('blocked_user_id', $currentUser->id);
            })->exists();

            if ($isBlocked) {
                return response()->json(['error' => 'Não é possível enviar mensagem para este usuário.'], 403);
            }

            $message = Message::create([
                'sender_id' => $currentUser->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'is_read' => false,
                'toast_notify' => false,
                'reply_to_id' => $request->reply_to_id,
                'created_at' => now(),
            ]);
            
            // Reload to get relationships if needed, or just return basic
            if ($message->reply_to_id) {
                $message->load('replyTo.sender');
            }

            return response()->json($message);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao enviar mensagem: ' . $e->getMessage()], 500);
        }
    }
    
    public function blockUser(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        BlockedUser::firstOrCreate([
            'user_id' => Auth::id(),
            'blocked_user_id' => $request->user_id
        ]);
        
        return response()->json(['status' => 'blocked']);
    }
    
    public function unblockUser(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        BlockedUser::where('user_id', Auth::id())
            ->where('blocked_user_id', $request->user_id)
            ->delete();
            
        return response()->json(['status' => 'unblocked']);
    }
    
    public function clearChat(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $currentUser = Auth::user();
        $targetUserId = $request->user_id;
        
        // Update messages where I am sender -> deleted_by_sender = true
        Message::where('sender_id', $currentUser->id)
            ->where('receiver_id', $targetUserId)
            ->update(['deleted_by_sender' => true]);
            
        // Update messages where I am receiver -> deleted_by_receiver = true
        Message::where('receiver_id', $currentUser->id)
            ->where('sender_id', $targetUserId)
            ->update(['deleted_by_receiver' => true]);
            
        return response()->json(['status' => 'cleared']);
    }
    
    public function getUnreadCount()
    {
        $currentUser = Auth::user();
        
        // Total unread messages
        $totalUnread = Message::where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->count();
            
        // Get top senders with unread messages (grouped)
        $unreadBySender = Message::where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->select('sender_id', DB::raw('count(*) as count'))
            ->groupBy('sender_id')
            ->with('sender:id,name,user,avatar,hide_name')
            ->get();
            
        // Process names for display
        $unreadBySender->transform(function ($item) {
            $sender = $item->sender;
            if ($sender) {
                $item->sender_name = $sender->hide_name ? 'Usuário' : ($sender->name ?? $sender->user);
                $item->avatar_url = $sender->avatar ? asset('storage/uploads/avatars/' . $sender->avatar) : null;
            }
            return $item;
        });
            
        return response()->json([
            'total_unread' => $totalUnread,
            'details' => $unreadBySender
        ]);
    }

    public function toggleUserPin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $targetUserId = $request->user_id;

        $pin = UserPin::where('user_id', $currentUser->id)
            ->where('pinned_user_id', $targetUserId)
            ->first();

        if ($pin) {
            $pin->delete();
            return response()->json(['status' => 'unpinned']);
        } else {
            UserPin::create([
                'user_id' => $currentUser->id,
                'pinned_user_id' => $targetUserId,
            ]);
            return response()->json(['status' => 'pinned']);
        }
    }
}
