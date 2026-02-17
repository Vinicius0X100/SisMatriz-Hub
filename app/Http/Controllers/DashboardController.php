<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\PinnedModule;
use App\Models\UserAccess;
use Carbon\Carbon;
use App\Models\Register;
use App\Models\User;
use App\Models\TurmaEucaristia;
use App\Models\TurmaCrisma;
use App\Models\TurmaAdultos;
use App\Models\InscricaoEucaristia;
use App\Models\InscricaoCrisma;
use App\Models\InscricaoCatequeseAdultos;
use App\Models\Oferta;
use App\Models\NotaFiscal;
use App\Models\CategoriaDoacao;
use App\Models\Entidade;
use App\Models\Protocol;
use App\Models\VinWatched;
use App\Models\DocsEucaristia;
use App\Models\DocsCrisma;
use Illuminate\Support\Facades\DB;
use App\Models\Evento;
use Illuminate\Support\Facades\Storage;

use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $paroquiaId = $user->paroquia_id; // Assuming user has this field or relation

        // Date Filter
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfMonth();
        
        // Ensure valid range
        if ($startDate->gt($endDate)) {
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        // Fetch modules based on permissions
        $modules = $user->getAccessibleModules();

        // Check Permissions
        $isSuperAdmin = $user->hasAnyRole(['1', '111']);
        $isTreasurer = $user->hasRole('11');

        $stats = null;
        $chartData = null;
        $accessChartData = null;

        // --- Quantitative Stats (Only Roles 1 and 111) ---
        if ($isSuperAdmin) {
            // Calculate Docs Stats
            $docsEucaristiaPending = DocsEucaristia::whereHas('register', function($q) use ($paroquiaId) {
                $q->where('paroquia_id', $paroquiaId);
            })->where(function($q) {
                $q->where('rg', false)
                  ->orWhere('comprovante_residencia', false)
                  ->orWhere('certidao_batismo', false);
            })->count();

            $docsCrismaPending = DocsCrisma::whereHas('register', function($q) use ($paroquiaId) {
                $q->where('paroquia_id', $paroquiaId);
            })->where(function($q) {
                $q->where('rg', false)
                  ->orWhere('comprovante_residencia', false)
                  ->orWhere('certidao_batismo', false)
                  ->orWhere('certidao_eucaristia', false)
                  ->orWhere('rg_padrinho', false)
                  ->orWhere('certidao_casamento_padrinho', false)
                  ->orWhere('certidao_crisma_padrinho', false);
            })->count();

            $docsEucaristiaDelivered = DocsEucaristia::whereHas('register', function($q) use ($paroquiaId) {
                $q->where('paroquia_id', $paroquiaId);
            })->where('rg', true)
              ->where('comprovante_residencia', true)
              ->where('certidao_batismo', true)
              ->count();

            $docsCrismaDelivered = DocsCrisma::whereHas('register', function($q) use ($paroquiaId) {
                $q->where('paroquia_id', $paroquiaId);
            })->where('rg', true)
              ->where('comprovante_residencia', true)
              ->where('certidao_batismo', true)
              ->where('certidao_eucaristia', true)
              ->where('rg_padrinho', true)
              ->where('certidao_casamento_padrinho', true)
              ->where('certidao_crisma_padrinho', true)
              ->count();

            $stats = [
                'registers' => Register::where('paroquia_id', $paroquiaId)->count(),
                'users' => User::where('paroquia_id', $paroquiaId)->count(),
                'classes' => TurmaEucaristia::where('paroquia_id', $paroquiaId)->count() +
                             TurmaCrisma::where('paroquia_id', $paroquiaId)->count() +
                             TurmaAdultos::where('paroquia_id', $paroquiaId)->count(),
                'enrollments' => InscricaoEucaristia::where('paroquia_id', $paroquiaId)->count() +
                                 InscricaoCrisma::where('paroquia_id', $paroquiaId)->count() +
                                 InscricaoCatequeseAdultos::where('paroquia_id', $paroquiaId)->count(),
                'categories' => CategoriaDoacao::where('paroquia_id', $paroquiaId)->count(),
                'communities' => Entidade::where('paroquia_id', $paroquiaId)->count(),
                'vicentinos' => VinWatched::where('paroquia_id', $paroquiaId)->count(),
                'protocols' => Protocol::where('paroquia_id', $paroquiaId)->count(),
                'docs_pending' => $docsEucaristiaPending + $docsCrismaPending,
                'docs_delivered' => $docsEucaristiaDelivered + $docsCrismaDelivered,
            ];
        }

        // --- Financial Charts Data (Last 12 months) (Roles 1, 111 AND 11) ---
        if ($isSuperAdmin || $isTreasurer) {
            // Optimized Queries: Fetch all data grouped by month/year
            $ofertasRaw = Oferta::where('paroquia_id', $paroquiaId)
                ->whereBetween('data', [$startDate, $endDate])
                ->selectRaw('YEAR(data) as year, MONTH(data) as month, kind, SUM(valor_total) as total')
                ->groupBy('year', 'month', 'kind')
                ->get();

            $notasRaw = NotaFiscal::where('paroquia_id', $paroquiaId)
                ->whereBetween('data_emissao', [$startDate, $endDate])
                ->selectRaw('YEAR(data_emissao) as year, MONTH(data_emissao) as month, SUM(valor_total) as total')
                ->groupBy('year', 'month')
                ->get();

            $accessRaw = collect();
            if ($isSuperAdmin) {
                $accessRaw = UserAccess::whereHas('user', function($q) use ($paroquiaId) {
                        $q->where('paroquia_id', $paroquiaId);
                    })
                    ->whereBetween('access_date', [$startDate, $endDate])
                    ->selectRaw('YEAR(access_date) as year, MONTH(access_date) as month, device_type, COUNT(*) as total')
                    ->groupBy('year', 'month', 'device_type')
                    ->get();
            }

            $months = [];
            $ofertasData = [];
            $dizimosData = [];
            $notasData = [];
            
            // Access Chart Data
            $webAccessData = [];
            $androidAccessData = [];
            $iosAccessData = [];

            $period = CarbonPeriod::create($startDate, '1 month', $endDate);

            foreach ($period as $date) {
                $monthLabel = $date->format('m/Y'); 
                $year = $date->year;
                $month = $date->month;
    
                $months[] = $monthLabel;
    
                // Ofertas (Kind != 1)
                $ofertasData[] = $ofertasRaw->where('year', $year)
                    ->where('month', $month)
                    ->where('kind', '!=', 1)
                    ->sum('total');
    
                // Dízimos (Kind == 1)
                $dizimosData[] = $ofertasRaw->where('year', $year)
                    ->where('month', $month)
                    ->where('kind', 1)
                    ->sum('total');
    
                // Notas Fiscais
                $notasData[] = $notasRaw->where('year', $year)
                    ->where('month', $month)
                    ->sum('total');

                // Access Data (Only for SuperAdmin)
                if ($isSuperAdmin) {
                    // Web (1)
                    $webAccessData[] = $accessRaw->where('year', $year)
                        ->where('month', $month)
                        ->where('device_type', 1)
                        ->sum('total');

                    // Android (2)
                    $androidAccessData[] = $accessRaw->where('year', $year)
                        ->where('month', $month)
                        ->where('device_type', 2)
                        ->sum('total');

                    // iOS (3)
                    $iosAccessData[] = $accessRaw->where('year', $year)
                        ->where('month', $month)
                        ->where('device_type', 3)
                        ->sum('total');
                }
            }
    
            $chartData = [
                'labels' => $months,
                'ofertas' => $ofertasData,
                'dizimos' => $dizimosData,
                'notas' => $notasData,
            ];

            if ($isSuperAdmin) {
                $accessChartData = [
                    'labels' => $months,
                    'web' => $webAccessData,
                    'android' => $androidAccessData,
                    'ios' => $iosAccessData,
                ];
            }
        }

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

        // Eventos: Hoje e Próximos
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        $todayEvents = Evento::where('paroquia_id', $paroquiaId)
            ->whereDate('date', '=', $today)
            ->where('time', '>=', $currentTime)
            ->orderBy('time', 'asc')
            ->limit(12)
            ->get()
            ->map(function ($event) {
                $photoPath = $event->photo ? 'uploads/eventos/' . $event->photo : null;
                $exists = $photoPath && Storage::disk('public')->exists($photoPath);
                $event->photo_url = $exists ? asset('storage/' . $photoPath) : null;
                return $event;
            });

        // Próximos eventos (excluir os de hoje para não duplicar a seção)
        $upcomingEvents = Evento::where('paroquia_id', $paroquiaId)
            ->whereDate('date', '>', $today)
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->limit(20)
            ->get()
            ->map(function ($event) {
                $photoPath = $event->photo ? 'uploads/eventos/' . $event->photo : null;
                $exists = $photoPath && Storage::disk('public')->exists($photoPath);
                $event->photo_url = $exists ? asset('storage/' . $photoPath) : null;
                return $event;
            });

        return view('dashboard', compact('pinnedModules', 'groupedModules', 'stats', 'chartData', 'accessChartData', 'todayEvents', 'upcomingEvents'));
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
