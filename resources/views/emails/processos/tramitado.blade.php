<x-mail::message>
# Olá!

Você tem um novo andamento de processo no **SisMatriz**. 
O processo **{{ $processo->protocolo }}** acaba de ser encaminhado para você (ou para seu grupo pastoral).

<x-mail::panel>
**Detalhes do Processo:**
- **Protocolo:** {{ $processo->protocolo }}
- **Assunto:** {{ strtoupper($processo->assunto) }}
- **Solicitante:** {{ $processo->nome_solicitante }}
- **Prazo:** {{ $processo->data_limite ? $processo->data_limite->format('d/m/Y') : 'Não definido' }}

**Mensagem de Encaminhamento:**
> *{{ $tramitacao->descricao ?: 'Sem descrição adicional.' }}*
*(Enviado por {{ $tramitacao->deUser->name ?? 'Sistema' }})*
</x-mail::panel>

Para assumir o processo, visualizar todos os anexos e registrar novos andamentos, acesse o SisMatriz clicando no botão abaixo:

<x-mail::button :url="$url" color="primary">
Acessar Processo
</x-mail::button>

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
