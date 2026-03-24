@extends('layouts.pdf')

@section('title', 'Relatório de Membros Pascom')

@section('content')
    <div style="text-align: center; margin-bottom: 10px;">
        <h2 style="margin: 0;">Relatório de Membros Pascom</h2>
    </div>

    <div style="text-align: left; margin-bottom: 15px; font-size: 12px; color: #555;">
        Data de Emissão: {{ date('d/m/Y') }}
    </div>

    @php
        $typeMap = [
            0 => 'Fotógrafo',
            1 => 'Redator',
            2 => 'Video Maker',
            3 => 'Designer',
            4 => 'Editor de Vídeo',
            5 => 'Streamer',
        ];
    @endphp

    <table>
        <thead>
            <tr>
                @if(in_array('name', $columns)) <th>Nome</th> @endif
                @if(in_array('type', $columns)) <th>Tipo</th> @endif
                @if(in_array('entidade', $columns)) <th>Comunidade</th> @endif
                @if(in_array('year_member', $columns)) <th>Ano</th> @endif
                @if(in_array('age', $columns)) <th>Idade</th> @endif
                @if(in_array('status', $columns)) <th>Status</th> @endif
                @if(in_array('register_status', $columns)) <th>Vínculo</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($records as $r)
                <tr>
                    @if(in_array('name', $columns)) <td>{{ $r->name }}</td> @endif
                    @if(in_array('type', $columns)) <td>{{ $typeMap[(int) $r->type] ?? '-' }}</td> @endif
                    @if(in_array('entidade', $columns)) <td>{{ $r->entidade->ent_name ?? '-' }}</td> @endif
                    @if(in_array('year_member', $columns)) <td>{{ $r->year_member ?? '-' }}</td> @endif
                    @if(in_array('age', $columns)) <td>{{ $r->age ?? '-' }}</td> @endif
                    @if(in_array('status', $columns)) <td>{{ (int) $r->status === 0 ? 'Ativo' : 'Inativo' }}</td> @endif
                    @if(in_array('register_status', $columns)) <td>{{ $r->register ? 'Válido' : 'Órfão' }}</td> @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
