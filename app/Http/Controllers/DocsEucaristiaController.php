<?php

namespace App\Http\Controllers;

use App\Models\Catecando;
use App\Models\DocsEucaristia;
use App\Models\TurmaEucaristia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocsEucaristiaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $turmaId = $request->input('turma_id');
        $status = $request->input('status');

        $query = Catecando::with(['register.docsEucaristia', 'turma'])
            ->whereHas('register', function ($q) {
                $q->where('paroquia_id', Auth::user()->paroquia_id);
            });

        if ($search) {
            $query->whereHas('register', function ($qr) use ($search) {
                $qr->where('name', 'like', "%{$search}%");
            });
        }

        if ($turmaId) {
            $query->where('turma_id', $turmaId);
        }

        if ($status) {
            if ($status === 'pendente') {
                $query->where(function($q) {
                    $q->whereDoesntHave('register.docsEucaristia')
                      ->orWhereHas('register.docsEucaristia', function($d) {
                          $d->where('rg', false)
                            ->orWhere('comprovante_residencia', false)
                            ->orWhere('certidao_batismo', false);
                      });
                });
            } elseif ($status === 'obrigatoria_entregue' || $status === 'entregue') {
                $query->whereHas('register.docsEucaristia', function($d) {
                    $d->where('rg', true)
                      ->where('comprovante_residencia', true)
                      ->where('certidao_batismo', true);
                });
            }
        }

        $students = $query->paginate(10);
        $turmas = TurmaEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->get();

        if ($request->ajax()) {
            return view('modules.docs-eucaristia.partials.list', compact('students'))->render();
        }

        return view('modules.docs-eucaristia.index', compact('students', 'turmas'));
    }

    public function update(Request $request, $register_id)
    {
        // Verify permission
        $student = Catecando::where('register_id', $register_id)
            ->whereHas('register', function($q) {
                $q->where('paroquia_id', Auth::user()->paroquia_id);
            })->firstOrFail();

        $data = [
            'rg' => $request->has('rg'),
            'comprovante_residencia' => $request->has('comprovante_residencia'),
            'certidao_batismo' => $request->has('certidao_batismo'),
        ];

        DocsEucaristia::updateOrCreate(
            ['register_id' => $register_id],
            $data
        );

        $params = [
            'search' => $request->input('search'),
            'turma_id' => $request->input('turma_id'),
            'status' => $request->input('status'),
        ];

        return redirect()->route('docs-eucaristia.index', $params)->with('success', 'Documentação atualizada com sucesso!');
    }
}
