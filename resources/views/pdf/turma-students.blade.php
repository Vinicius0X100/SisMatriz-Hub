@extends('layouts.pdf')

@section('title', 'Lista de Alunos - ' . $turma->turma)

@section('content')
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Lista de {{ $typeLabel }}</h2>
    </div>

    <div style="margin-bottom: 20px; font-size: 13px; background-color: #f8fafc; padding: 15px; border-radius: 5px; border: 1px solid #e2e8f0;">
        <p style="margin: 5px 0;"><strong>Turma:</strong> {{ $turma->turma }}</p>
        <p style="margin: 5px 0;"><strong>Catequista:</strong> {{ $turma->catequista->nome ?? 'N/A' }}</p>
        <p style="margin: 5px 0;"><strong>Período:</strong> {{ date('d/m/Y', strtotime($turma->inicio)) }} a {{ $turma->termino ? date('d/m/Y', strtotime($turma->termino)) : '?' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Telefone</th>
                <th style="text-align: center;">Batizado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
            <tr>
                <td>{{ $student['name'] }}</td>
                <td>{{ $student['phone'] }}</td>
                <td style="text-align: center;">{{ $student['batizado'] ? 'Sim' : 'Não' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; padding: 20px; color: #777;">Nenhum aluno encontrado nesta turma.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
