<x-mail::message>
# Olá {{ $nomeDestinatario }},

O usuário **{{ $tramitacao->deUser->name ?? 'Sistema' }}** ({{ $tramitacao->de_cargo_label }}) acaba de encaminhar o processo **{{ $processo->protocolo }}** para a sua responsabilidade (ou para o seu grupo pastoral).

<x-mail::panel>
**Resumo do Processo:**
- **Protocolo:** {{ $processo->protocolo }}
- **Assunto:** {{ strtoupper($processo->assunto) }}
- **Solicitante:** {{ $processo->nome_solicitante }}
- **Prazo estipulado:** {{ $processo->data_limite ? $processo->data_limite->format('d/m/Y') : 'Sem prazo definido' }}

**Mensagem do Encaminhamento:**
> *{{ $tramitacao->descricao ?: 'Sem descrição adicional.' }}*
</x-mail::panel>

Para assumir o processo, visualizar todos os anexos e registrar novos andamentos, acesse o SisMatriz clicando no botão abaixo:

<x-mail::button :url="$url" color="primary">
Acessar Processo
</x-mail::button>

Atenciosamente,<br>
Equipe {{ config('app.name') }}

<x-slot:footer>
<x-mail::footer>
O SisMatriz é um serviço fornecido pela Sacratech.<br>
© {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.
</x-mail::footer>
</x-slot:footer>
</x-mail::message>
