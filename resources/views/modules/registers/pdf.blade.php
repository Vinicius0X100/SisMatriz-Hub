@extends('layouts.pdf')

@section('title', 'Relatório de Registros Gerais')

@section('content')
    <div style="text-align: center; margin-bottom: 10px;">
        <h2 style="margin: 0;">Relatório de Registros Gerais</h2>
    </div>

    <div style="text-align: left; margin-bottom: 15px; font-size: 12px; color: #555;">
        Data de Emissão: {{ date('d/m/Y') }}
    </div>

    @php
        $civilStatusMap = [
            1 => 'Solteiro(a)',
            2 => 'Casado(a)',
            3 => 'União Estável',
            4 => 'Divorciado',
            5 => 'Viuvo(a)',
            6 => 'Nao Declarado'
        ];
    @endphp

    <table>
        <thead>
            <tr>
                @if(in_array('name', $columns)) <th>Nome</th> @endif
                @if(in_array('email', $columns)) <th>Email</th> @endif
                @if(in_array('phone', $columns)) <th>Telefone</th> @endif
                @if(in_array('cpf', $columns)) <th>CPF</th> @endif
                @if(in_array('rg', $columns)) <th>RG</th> @endif
                @if(in_array('sexo', $columns)) <th>Sexo</th> @endif
                @if(in_array('civil_status', $columns)) <th>Estado Civil</th> @endif
                @if(in_array('mother_name', $columns)) <th>Mãe</th> @endif
                @if(in_array('father_name', $columns)) <th>Pai</th> @endif
                @if(in_array('born_date', $columns)) <th>Nascimento</th> @endif
                @if(in_array('address', $columns)) <th>Endereço</th> @endif
                @if(in_array('address_number', $columns)) <th>Nº</th> @endif
                @if(in_array('home_situation', $columns)) <th>Bairro</th> @endif
                @if(in_array('city', $columns)) <th>Cidade</th> @endif
                @if(in_array('state', $columns)) <th>UF</th> @endif
                @if(in_array('cep', $columns)) <th>CEP</th> @endif
                @if(in_array('status', $columns)) <th>Status</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($registers as $register)
                <tr>
                    @if(in_array('name', $columns)) <td>{{ $register->name }}</td> @endif
                    @if(in_array('email', $columns)) <td>{{ $register->email }}</td> @endif
                    @if(in_array('phone', $columns)) <td>{{ $register->phone }}</td> @endif
                    @if(in_array('cpf', $columns)) <td>{{ $register->cpf }}</td> @endif
                    @if(in_array('rg', $columns)) <td>{{ $register->rg }}</td> @endif
                    @if(in_array('sexo', $columns)) 
                        <td>{{ $register->sexo == 1 ? 'Masculino' : ($register->sexo == 2 ? 'Feminino' : 'Não informado') }}</td> 
                    @endif
                    @if(in_array('civil_status', $columns)) 
                        <td>{{ $civilStatusMap[$register->civil_status] ?? 'Não informado' }}</td> 
                    @endif
                    @if(in_array('mother_name', $columns)) <td>{{ $register->mother_name }}</td> @endif
                    @if(in_array('father_name', $columns)) <td>{{ $register->father_name }}</td> @endif
                    @if(in_array('born_date', $columns)) 
                        <td>{{ ($register->born_date && $register->born_date->format('d/m/Y') !== '01/01/0001') ? $register->born_date->format('d/m/Y') : 'Não informado' }}</td> 
                    @endif
                    @if(in_array('address', $columns)) <td>{{ $register->address }}</td> @endif
                    @if(in_array('address_number', $columns)) <td>{{ $register->address_number }}</td> @endif
                    @if(in_array('home_situation', $columns)) <td>{{ $register->home_situation }}</td> @endif
                    @if(in_array('city', $columns)) <td>{{ $register->city }}</td> @endif
                    @if(in_array('state', $columns)) <td>{{ $register->state }}</td> @endif
                    @if(in_array('cep', $columns)) <td>{{ $register->cep }}</td> @endif
                    @if(in_array('status', $columns)) 
                        <td>{{ $register->status == 0 ? 'Ativo' : 'Inativo' }}</td> 
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
