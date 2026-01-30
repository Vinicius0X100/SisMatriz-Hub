<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\PinnedModule;
use App\Models\UserAccess;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $paroquiaId = $user->paroquia_id; // Assuming user has this field or relation

        // Fetch modules based on permissions
        $modules = $user->getAccessibleModules();

        // Fetch pinned modules (records)
        $pinnedRecords = PinnedModule::where('user_id', $user->id)
            ->where('paroquia_id', $paroquiaId)
            ->orderBy('order', 'asc')
            ->get()
            ->keyBy('module_slug');

        $pinnedSlugs = $pinnedRecords->keys()->toArray();

        // Process modules
        $allModules = collect($modules)->map(function ($module) use ($pinnedRecords, $pinnedSlugs) {
            $module['slug'] = Str::slug($module['name']);
            $module['is_pinned'] = in_array($module['slug'], $pinnedSlugs);
            
            if ($module['is_pinned']) {
                $record = $pinnedRecords[$module['slug']];
                $module['bg_color'] = $record->bg_color;
                $module['text_color'] = $record->text_color;
                $module['order'] = $record->order;
            }

            return $module;
        })->filter(function ($module) {
            return $module['slug'] !== 'chat';
        });

        // Pinned Modules (Sorted by order)
        $pinnedModules = $allModules->where('is_pinned', true)
            ->sortBy('order')
            ->values();


        // Grouped Modules (A-Z)
        // Sort by name
        $sortedModules = $allModules->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
        
        // Group by first letter
        $groupedModules = $sortedModules->groupBy(function ($item, $key) {
            return strtoupper(substr($item['name'], 0, 1));
        });

        return view('dashboard', compact('pinnedModules', 'groupedModules'));
    }

    public function togglePin(Request $request)
    {
        $request->validate([
            'module_slug' => 'required|string',
        ]);

        $user = Auth::user();
        $paroquiaId = $user->paroquia_id;
        $slug = $request->module_slug;

        $pinned = PinnedModule::where('user_id', $user->id)
            ->where('paroquia_id', $paroquiaId)
            ->where('module_slug', $slug)
            ->first();

        if ($pinned) {
            $pinned->delete();
            return response()->json(['status' => 'unpinned']);
        } else {
            PinnedModule::create([
                'user_id' => $user->id,
                'paroquia_id' => $paroquiaId,
                'module_slug' => $slug,
            ]);
            return response()->json(['status' => 'pinned']);
        }
    }

    public function getOnlineUsers()
    {
        $user = Auth::user();
        $currentUserId = $user->id;
        $paroquiaId = $user->paroquia_id;

        $accesses = UserAccess::with('user')
            ->where('user_id', '!=', $currentUserId) // Exclude current user
            ->whereHas('user', function ($q) use ($paroquiaId) {
                $q->where('is_visible', true)
                  ->where('paroquia_id', $paroquiaId);
            })
            ->orderBy('access_date', 'desc')
            ->orderBy('access_time', 'desc')
            ->limit(30) // Increased limit to ensure we get enough unique users
            ->get()
            ->unique('user_id')
            ->values()
            ->take(20); // Show up to 20 users

        $data = $accesses->map(function ($access) {
            $user = $access->user;
            if (!$user) return null;

            $accessDateTime = Carbon::parse($access->access_date . ' ' . $access->access_time);
            $now = Carbon::now();
            $diffInMinutes = $accessDateTime->diffInMinutes($now);
            $isOnline = $diffInMinutes < 10;

            // Handle Hide Name
            if ($user->hide_name) {
                $displayName = 'Usuário';
                // Also maybe hide avatar? Or just keep it? User said "hide name". 
                // Let's keep avatar if it's generic, but if it's a photo it reveals identity.
                // Usually "Hide Name" implies privacy. Let's force initials or generic avatar if hidden.
                // But the requirement was specific to "esconder o nome". 
                // Let's just change the name for now.
            } else {
                $displayName = Str::limit($user->name ?? $user->user, 25, '...');
            }

            if ($isOnline) {
                $statusText = 'Online';
            } elseif ($accessDateTime->isToday()) {
                $statusText = $accessDateTime->format('H:i');
            } else {
                $statusText = $accessDateTime->format('d/m/Y');
            }

            $avatarUrl = ($user->avatar && !$user->hide_name)
                ? asset('storage/uploads/avatars/' . $user->avatar)
                : null;

            $parts = explode(' ', trim($user->name ?? $user->user));
            $initials = strtoupper(substr($parts[0], 0, 1));
            if (count($parts) > 1) {
                $initials .= strtoupper(substr(end($parts), 0, 1));
            }

            return [
                'id' => $user->id,
                'name' => $displayName,
                'avatar_url' => $avatarUrl,
                'initials' => $initials,
                'is_online' => $isOnline,
                'status_text' => $statusText,
                'device_type' => $access->device_type,
            ];
        })->filter()->values();

        return response()->json($data);
    }

    public function reorderPins(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.slug' => 'required|string',
            'order.*.index' => 'required|integer',
        ]);

        $user = Auth::user();
        $paroquiaId = $user->paroquia_id;

        foreach ($request->order as $item) {
            PinnedModule::where('user_id', $user->id)
                ->where('paroquia_id', $paroquiaId)
                ->where('module_slug', $item['slug'])
                ->update(['order' => $item['index']]);
        }

        return response()->json(['success' => true]);
    }

    public function updatePinStyle(Request $request)
    {
        $request->validate([
            'module_slug' => 'required|string',
            'bg_color' => 'nullable|string',
            'text_color' => 'nullable|string',
        ]);

        $user = Auth::user();
        $paroquiaId = $user->paroquia_id;

        $updateData = [];
        if ($request->has('bg_color')) $updateData['bg_color'] = $request->bg_color;
        if ($request->has('text_color')) $updateData['text_color'] = $request->text_color;

        if (empty($updateData)) {
            return response()->json(['success' => true]);
        }

        PinnedModule::where('user_id', $user->id)
            ->where('paroquia_id', $paroquiaId)
            ->where('module_slug', $request->module_slug)
            ->update($updateData);

        return response()->json(['success' => true]);
    }
}
