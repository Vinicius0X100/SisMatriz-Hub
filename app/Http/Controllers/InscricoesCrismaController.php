<?php

namespace App\Http\Controllers;

use App\Models\InscricaoCrisma;
use Illuminate\Http\Request;

class InscricoesCrismaController extends Controller
{
    public function index(Request $request)
    {
        $query = InscricaoCrisma::with('taxa');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%");
        }

        $records = $query->orderBy('criado_em', 'desc')->paginate(10);

        if ($request->ajax()) {
            return view('modules.inscricoes-crisma.partials.list', compact('records'))->render();
        }

        return view('modules.inscricoes-crisma.index', compact('records'));
    }

    public function show($id)
    {
        $record = InscricaoCrisma::with('taxa')->findOrFail($id);
        return response()->json($record);
    }

    public function destroy($id)
    {
        $record = InscricaoCrisma::findOrFail($id);
        // We might want to delete files too, but user didn't explicitly ask. 
        // I'll just delete the record for now to be safe, or check if I should delete files.
        // User said "uploads/certidoes/crisma" and "/comprovantes/crisma/".
        // Usually safe to keep files or soft delete, but strict delete removes them.
        // I will just delete the record.
        $record->delete();

        return redirect()->back()->with('success', 'Inscrição excluída com sucesso!');
    }
}
