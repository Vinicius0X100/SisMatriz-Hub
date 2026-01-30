<?php

namespace App\Http\Controllers;

use App\Models\Crismando;
use App\Models\DocsCrisma;
use App\Models\TurmaCrisma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocsCrismaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $turmaId = $request->input('turma_id');
        $status = $request->input('status');

        $query = Crismando::with(['register.docsCrisma', 'turma'])
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
                    $q->whereDoesntHave('register.docsCrisma')
                      ->orWhereHas('register.docsCrisma', function($d) {
                          $d->where('rg', false)
                            ->orWhere('comprovante_residencia', false)
                            ->orWhere('certidao_batismo', false)
                            ->orWhere('certidao_eucaristia', false)
                            ->orWhere('rg_padrinho', false)
                            ->orWhere('certidao_crisma_padrinho', false);
                      });
                });
            } elseif ($status === 'obrigatoria_entregue') {
                $query->whereHas('register.docsCrisma', function($d) {
                    $d->where('rg', true)
                      ->where('comprovante_residencia', true)
                      ->where('certidao_batismo', true)
                      ->where('certidao_eucaristia', true)
                      ->where('rg_padrinho', true)
                      ->where('certidao_crisma_padrinho', true)
                      ->where('certidao_casamento_padrinho', false);
                });
            } elseif ($status === 'entregue') {
                $query->whereHas('register.docsCrisma', function($d) {
                    $d->where('rg', true)
                      ->where('comprovante_residencia', true)
                      ->where('certidao_batismo', true)
                      ->where('certidao_eucaristia', true)
                      ->where('rg_padrinho', true)
                      ->where('certidao_crisma_padrinho', true)
                      ->where('certidao_casamento_padrinho', true);
                });
            }
        }

        $students = $query->paginate(10);
        $turmas = TurmaCrisma::where('paroquia_id', Auth::user()->paroquia_id)->get();

        if ($request->ajax()) {
            return view('modules.docs-crisma.partials.list', compact('students'))->render();
        }

        return view('modules.docs-crisma.index', compact('students', 'turmas'));
    }

    public function update(Request $request, $register_id)
    {
        // Verify permission via Crismando check
        $student = Crismando::where('register_id', $register_id)
            ->whereHas('register', function($q) {
                $q->where('paroquia_id', Auth::user()->paroquia_id);
            })->firstOrFail();

        $data = [
            'rg' => $request->has('rg'),
            'comprovante_residencia' => $request->has('comprovante_residencia'),
            'certidao_batismo' => $request->has('certidao_batismo'),
            'certidao_eucaristia' => $request->has('certidao_eucaristia'),
            'rg_padrinho' => $request->has('rg_padrinho'),
            'certidao_casamento_padrinho' => $request->has('certidao_casamento_padrinho'),
            'certidao_crisma_padrinho' => $request->has('certidao_crisma_padrinho'),
        ];

        DocsCrisma::updateOrCreate(
            ['register_id' => $register_id],
            $data
        );

        $params = [
            'search' => $request->input('search'),
            'turma_id' => $request->input('turma_id'),
            'status' => $request->input('status'),
        ];

        return redirect()->route('docs-crisma.index', $params)->with('success', 'Documentação atualizada com sucesso!');
    }
}
