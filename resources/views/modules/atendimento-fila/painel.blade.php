<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Painel de Atendimento — {{ $fila ? $fila->data->format('d/m/Y') : 'Sem fila ativa' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    @vite(['resources/css/app.scss'])
    <style>
        :root {
            --painel-bg: #0f172a;
            --painel-card: #1e293b;
            --painel-border: #334155;
            --painel-primary: #3b82f6;
            --painel-success: #22c55e;
            --painel-warning: #f59e0b;
            --painel-text: #f1f5f9;
            --painel-muted: #94a3b8;
        }

        body {
            background: var(--painel-bg);
            color: var(--painel-text);
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            margin: 0;
        }

        .painel-header {
            background: var(--painel-card);
            border-bottom: 1px solid var(--painel-border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .painel-header .data-badge {
            font-size: 14px;
            color: var(--painel-muted);
        }

        .painel-header .logo {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--painel-text);
        }

        .painel-content {
            padding: 24px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Em atendimento */
        .card-em-atendimento {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
            border-radius: 20px;
            padding: 32px 40px;
            margin-bottom: 24px;
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.25);
            position: relative;
            overflow: hidden;
        }

        .card-em-atendimento::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .card-em-atendimento .label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 8px;
        }

        .card-em-atendimento .nome {
            font-size: 48px;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 8px;
        }

        .card-em-atendimento .assunto {
            font-size: 20px;
            color: rgba(255,255,255,0.8);
        }

        /* Vazio — sem fila */
        .card-vazio {
            background: var(--painel-card);
            border: 2px dashed var(--painel-border);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            margin-bottom: 24px;
        }

        /* Botão chamar próximo */
        .btn-chamar {
            background: var(--painel-primary);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 16px 40px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-chamar:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-chamar:disabled {
            background: #475569;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Lista de próximos */
        .secao-titulo {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--painel-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .item-fila {
            background: var(--painel-card);
            border: 1px solid var(--painel-border);
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 10px;
            transition: border-color 0.2s;
        }

        .item-fila:hover {
            border-color: var(--painel-primary);
        }

        .item-fila .posicao {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .item-fila.agendado .posicao { background: rgba(59,130,246,0.15); color: var(--painel-primary); }
        .item-fila.walkin .posicao   { background: rgba(245,158,11,0.15); color: var(--painel-warning); }

        .item-fila .info { flex: 1; }
        .item-fila .nome-item { font-size: 17px; font-weight: 600; color: var(--painel-text); }
        .item-fila .assunto-item { font-size: 13px; color: var(--painel-muted); margin-top: 2px; }

        .item-fila .tipo-badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 100px;
            font-weight: 600;
        }

        .item-fila.agendado .tipo-badge { background: rgba(59,130,246,0.15); color: var(--painel-primary); }
        .item-fila.walkin .tipo-badge   { background: rgba(245,158,11,0.15); color: var(--painel-warning); }

        .hora-badge {
            font-size: 12px;
            color: var(--painel-muted);
        }

        /* Status de conexão */
        .status-conexao {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .status-conexao.conectado { background: var(--painel-success); }
        .status-conexao.desconectado { background: #ef4444; }

        /* Animação de loading */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .carregando { animation: pulse 1.5s infinite; }

        .total-badge {
            background: rgba(255,255,255,0.1);
            color: var(--painel-muted);
            font-size: 12px;
            padding: 3px 10px;
            border-radius: 100px;
            margin-left: 8px;
        }
    </style>
</head>
<body>

<!-- Cabeçalho do painel -->
<div class="painel-header">
    <div class="logo d-flex align-items-center">
        <!-- Logo SisMatriz -->
        <img src="{{ asset('images/logo.png') }}" alt="SisMatriz" style="height: 32px; margin-right: 12px; border-radius: 4px;" onerror="this.style.display='none'">
        
        <!-- Logo Paróquia -->
        @if($fila && $fila->paroquia && $fila->paroquia->foto)
            <img src="https://sismatriz.online/uploads/paroquias/{{ $fila->paroquia->foto }}" alt="Paróquia" style="height: 32px; margin-right: 12px; border-radius: 4px; object-fit: cover;" onerror="this.style.display='none'">
        @endif

        <i class="bi bi-people-fill me-2 text-primary"></i>Painel de Atendimento
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="data-badge" id="dataFila">
            @if($fila) {{ $fila->data->translatedFormat('d \d\e F \d\e Y') }}
            @else Sem fila ativa hoje @endif
        </span>
        <div class="data-badge d-flex align-items-center">
            <span class="status-conexao desconectado" id="statusConexao"></span>
            <span id="statusConexaoTexto">Conectando...</span>
        </div>
        <a href="{{ route('atendimento-fila.index') }}" class="btn btn-sm btn-outline-secondary" style="border-color: var(--painel-border); color: var(--painel-muted);">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>
</div>

<!-- Conteúdo principal -->
<div class="painel-content">

    @if(!$fila)
    <!-- Sem fila ativa -->
    <div class="card-vazio">
        <i class="bi bi-calendar-x" style="font-size:64px; color: var(--painel-muted); display:block; margin-bottom:16px"></i>
        <h3 style="color: var(--painel-muted)">Nenhuma fila ativa para hoje</h3>
        <p style="color: var(--painel-muted)">A secretaria precisa criar e ativar uma fila para hoje.</p>
        <a href="{{ route('atendimento-fila.create') }}" class="btn-chamar" style="margin-top:16px; text-decoration:none; display:inline-flex">
            <i class="bi bi-plus-circle"></i>Criar fila para hoje
        </a>
    </div>
    @else

    <div class="row g-4">
        <!-- Coluna principal -->
        <div class="col-lg-7">

            <!-- Em atendimento -->
            <div id="secaoEmAtendimento">
                <!-- Preenchido via JS -->
            </div>

            <!-- Botão chamar próximo -->
            <div class="text-center my-4" id="secaoBtnChamar">
                <!-- Preenchido via JS -->
            </div>

        </div>

        <!-- Coluna lateral — próximos -->
        <div class="col-lg-5">
            <div class="secao-titulo">
                <i class="bi bi-list-ol"></i>Próximos na fila
                <span class="total-badge" id="totalAguardando">0</span>
            </div>

            <!-- Agendados -->
            <div class="secao-titulo" style="font-size:11px; margin-top:16px;">
                <i class="bi bi-clock text-primary"></i>Com hora marcada
            </div>
            <div id="listaAgendados">
                <div class="item-fila carregando" style="border: 1px solid var(--painel-border);">
                    <div style="color: var(--painel-muted); font-size:14px;">Carregando...</div>
                </div>
            </div>

            <div class="secao-titulo" style="font-size:11px; margin-top:16px;">
                <i class="bi bi-person-walking text-warning"></i>Walk-in (sem hora marcada)
            </div>
            <div id="listaWalkins">
                <!-- Preenchido via JS -->
            </div>
        </div>
    </div>

    @endif
</div>

@vite(['resources/js/app.js'])

@if($fila)
<script>
const FILA_ID      = {{ $fila->id }};
const PAROQUIA_ID  = {{ $user->paroquia_id }};
const CSRF_TOKEN   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const CHAMAR_URL   = `{{ route('atendimento-fila.chamar-proximo', $fila->id) }}`;
const DADOS_URL    = `{{ route('atendimento-fila.painel.dados', $fila->id) }}`;

// -----------------------------------------------------------------------
// Renderizadores de UI
// -----------------------------------------------------------------------

function renderEmAtendimento(item) {
    const el = document.getElementById('secaoEmAtendimento');
    const btnEl = document.getElementById('secaoBtnChamar');

    if (item) {
        el.innerHTML = `
            <div class="card-em-atendimento">
                <div class="label"><i class="bi bi-person-fill me-2"></i>Em atendimento agora</div>
                <div class="nome">${item.nome}</div>
                ${item.assunto ? `<div class="assunto"><i class="bi bi-chat-text me-2"></i>${item.assunto}</div>` : ''}
                <div style="margin-top:12px; opacity:0.6; font-size:13px">${item.tipo_label} ${item.hora_agendada ? '· ' + item.hora_agendada : ''}</div>
            </div>`;
        btnEl.innerHTML = `
            <button class="btn-chamar" id="btnChamarProximo" onclick="chamarProximo()">
                <i class="bi bi-arrow-right-circle-fill"></i>Chamar próximo
            </button>`;
    } else {
        el.innerHTML = `
            <div class="card-vazio">
                <i class="bi bi-person-check" style="font-size:48px; color: var(--painel-muted); display:block; margin-bottom:12px"></i>
                <p style="color: var(--painel-muted); font-size:18px; margin:0">Nenhum atendimento em curso</p>
            </div>`;
        btnEl.innerHTML = `
            <button class="btn-chamar" id="btnChamarProximo" onclick="chamarProximo()">
                <i class="bi bi-play-circle-fill"></i>Iniciar atendimento
            </button>`;
    }
}

function renderProximos(proximos) {
    const agendados = proximos.filter(i => i.tipo === 1);
    const walkins   = proximos.filter(i => i.tipo === 0);
    const total     = document.getElementById('totalAguardando');
    if (total) total.textContent = proximos.length;

    const listaAg = document.getElementById('listaAgendados');
    const listaWk = document.getElementById('listaWalkins');

    listaAg.innerHTML = agendados.length === 0
        ? `<p style="color: var(--painel-muted); font-size:13px; padding:8px 0;">Nenhum agendado aguardando.</p>`
        : agendados.map((item, idx) => itemHtml(item, idx + 1, 'agendado')).join('');

    listaWk.innerHTML = walkins.length === 0
        ? `<p style="color: var(--painel-muted); font-size:13px; padding:8px 0;">Nenhum walk-in aguardando.</p>`
        : walkins.map((item, idx) => itemHtml(item, agendados.length + idx + 1, 'walkin')).join('');
}

function itemHtml(item, pos, classe) {
    const hora = item.hora_agendada ? `<span class="hora-badge">${item.hora_agendada}</span>` : '';
    const tipoBadge = item.tipo === 1 ? 'Agendado' : 'Walk-in';
    return `
        <div class="item-fila ${classe}">
            <div class="posicao">${pos}</div>
            <div class="info">
                <div class="nome-item">${item.nome}</div>
                ${item.assunto ? `<div class="assunto-item">${item.assunto}</div>` : ''}
            </div>
            ${hora}
            <span class="tipo-badge">${tipoBadge}</span>
        </div>`;
}

// -----------------------------------------------------------------------
// Carregar dados da fila
// -----------------------------------------------------------------------

function carregarDados() {
    fetch(DADOS_URL, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        }
    })
    .then(r => r.json())
    .then(data => {
        renderEmAtendimento(data.em_atendimento);
        renderProximos(data.proximos || []);
    })
    .catch(err => console.error('Erro ao carregar dados:', err));
}

// -----------------------------------------------------------------------
// Chamar próximo
// -----------------------------------------------------------------------

window.chamarProximo = function() {
    const btn = document.getElementById('btnChamarProximo');
    if (btn) { btn.disabled = true; btn.textContent = 'Chamando...'; }

    fetch(CHAMAR_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        }
    })
    .then(r => r.json())
    .then(data => {
        renderEmAtendimento(data.em_atendimento);
        renderProximos(data.proximos || []);
    })
    .catch(err => {
        console.error('Erro ao chamar próximo:', err);
        if (btn) { btn.disabled = false; }
    });
}

// -----------------------------------------------------------------------
// Pusher / Laravel Echo (real-time)
// -----------------------------------------------------------------------

async function inicializarEcho() {
    try {
        const pusherKey     = "{{ env('PUSHER_APP_KEY', '') }}";
        const pusherCluster = "{{ env('PUSHER_APP_CLUSTER', 'mt1') }}";

        if (!pusherKey) {
            console.warn('Pusher não configurado. Usando polling.');
            iniciarPolling();
            return;
        }

        if (typeof window.Pusher === 'undefined') {
            console.warn('Pusher.js não carregou. Usando polling.');
            iniciarPolling();
            return;
        }

        // O IIFE do laravel-echo exporta como `Echo` (var global), não window.Echo
        if (typeof Echo === 'undefined') {
            console.warn('Laravel Echo não carregou. Usando polling.');
            iniciarPolling();
            return;
        }

        // Configurar o Echo com Pusher corretamente
        window.Pusher.logToConsole = false;

        const echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            forceTLS: true,
            Pusher: window.Pusher,
        });

        echo.channel(`paroquia.${PAROQUIA_ID}.fila`)
            .listen('.fila.atualizada', (e) => {
                console.log('[Echo] Evento recebido:', e);
                if (e.fila_id === FILA_ID) {
                    carregarDados();
                }
            });

        // Status de conexão
        echo.connector.pusher.connection.bind('connected', () => {
            console.log('[Echo] Conectado ao Pusher!');
            atualizarStatusConexao(true);
        });
        echo.connector.pusher.connection.bind('disconnected', () => {
            atualizarStatusConexao(false);
            iniciarPolling(); // fallback se desconectar
        });
        echo.connector.pusher.connection.bind('error', (err) => {
            console.error('[Echo] Erro de conexão:', err);
        });

    } catch (e) {
        console.warn('Echo não disponível, usando polling:', e);
        iniciarPolling();
    }
}

let pollingInterval = null;

function iniciarPolling() {
    if (pollingInterval) return;
    pollingInterval = setInterval(carregarDados, 12000); // a cada 12s
    atualizarStatusConexao(false, 'Polling ativo');
}

function atualizarStatusConexao(conectado, texto = null) {
    const dot  = document.getElementById('statusConexao');
    const txt  = document.getElementById('statusConexaoTexto');
    dot.className = `status-conexao ${conectado ? 'conectado' : 'desconectado'}`;
    txt.textContent = texto || (conectado ? 'Ao vivo' : 'Reconectando...');
}

// -----------------------------------------------------------------------
// Inicialização
// -----------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', function () {
    carregarDados();
    inicializarEcho();
    atualizarStatusConexao(false, 'Conectando...');
});
</script>
@endif

</body>
</html>
