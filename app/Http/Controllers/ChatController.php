<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        
        // Get users from the same paroquia, excluding current user
        $users = User::where('paroquia_id', $currentUser->paroquia_id)
            ->where('id', '!=', $currentUser->id)
            ->select('id', 'name', 'avatar', 'user', 'hide_name') 
            ->get();

        // Optimize: Fetch all messages involving current user in one query
        $allMessages = Message::where('sender_id', $currentUser->id)
            ->orWhere('receiver_id', $currentUser->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by the "other" user
        $conversations = $allMessages->groupBy(function ($msg) use ($currentUser) {
            return $msg->sender_id == $currentUser->id ? $msg->receiver_id : $msg->sender_id;
        });

        // Attach unread count and last message for sorting/display
        $users->map(function ($user) use ($conversations, $currentUser) {
            $userMessages = $conversations->get($user->id, collect());
            
            $lastMessage = $userMessages->first(); // Since it's ordered by desc, first is latest

            $unreadCount = $userMessages->where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id)
                ->where('is_read', false)
                ->count();

            $user->last_message = $lastMessage;
            $user->unread_count = $unreadCount;
            
            // Format name based on hide_name
             if ($user->hide_name) {
                $user->display_name = 'Usuário';
            } else {
                $user->display_name = $user->name ?? $user->user;
            }
            
            return $user;
        });
        
        // Sort by last message date (desc) then name (asc)
        $users = $users->sortByDesc(function ($user) {
            return $user->last_message ? $user->last_message->created_at->timestamp : 0;
        })->values();

        return response()->json($users);
    }

    public function getMessages($userId)
    {
        $currentUser = Auth::user();

        // Mark messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where(function ($q) use ($userId, $currentUser) {
                $q->where('sender_id', $userId)
                  ->where('receiver_id', $currentUser->id);
            })
            ->orWhere(function ($q) use ($userId, $currentUser) {
                $q->where('sender_id', $currentUser->id)
                  ->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false,
            'toast_notify' => false,
        ]);

        return response()->json($message);
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
}
