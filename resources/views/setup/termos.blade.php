@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">

            {{-- Cabeçalho --}}
            <div class="text-center mb-4 fade-in-down">
                @if($user->paroquia && $user->paroquia->foto)
                    <img src="https://sismatriz.online/uploads/paroquias/{{ $user->paroquia->foto }}"
                         class="rounded-circle shadow mb-3 bg-white p-1"
                         width="80" height="80"
                         style="object-fit: cover;"
                         alt="Logo Paróquia"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                    <div class="rounded-circle shadow mb-3 bg-primary text-white align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem; display: none;">
                        <i class="bi bi-church"></i>
                    </div>
                @else
                    <div class="rounded-circle shadow mb-3 bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="bi bi-church"></i>
                    </div>
                @endif

                <h3 class="fw-bold text-dark mb-1">Termo de Consentimento e Responsabilidade</h3>
                <p class="text-muted mb-0">
                    Por favor, leia o termo abaixo com atenção antes de prosseguir.
                </p>
                <div class="mt-2">
                    <a href="#" id="btnPorqueVendo" class="text-decoration-none small text-primary fw-semibold">
                        <i class="bi bi-question-circle me-1"></i>Por que estou vendo isso?
                    </a>
                </div>
            </div>

            {{-- Card do Termo --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 px-4 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                    <span class="fw-bold">Termo de Consentimento, Uso de Dados e Responsabilidades — SisMatriz</span>
                </div>

                {{-- Conteúdo scrollável --}}
                <div class="card-body p-0">
                    <div id="termoScroll" class="termo-scroll px-4 py-4" style="max-height: 480px; overflow-y: auto;">

                        <p class="text-muted small mb-3">
                            <strong>Versão:</strong> 1.0 &nbsp;|&nbsp;
                            <strong>Data de vigência:</strong> {{ now()->format('d/m/Y') }} &nbsp;|&nbsp;
                            <strong>Responsável pelo tratamento:</strong> Sacratech Softwares
                        </p>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">1. IDENTIFICAÇÃO DO CONTROLADOR DE DADOS</h6>
                        <p class="small text-dark-50">
                            O presente Termo é firmado entre o <strong>usuário</strong> e a <strong>Sacratech Softwares</strong>, pessoa jurídica de direito privado, desenvolvedora e fornecedora da plataforma <strong>SisMatriz</strong>, e a <strong>Paróquia</strong> contratante do sistema, doravante denominadas em conjunto como "Controladores de Dados", nos termos da Lei Federal nº 13.709/2018 — Lei Geral de Proteção de Dados Pessoais (LGPD).
                        </p>
                        <p class="small text-dark-50">
                            O SisMatriz é uma plataforma de gestão administrativa paroquial destinada exclusivamente a usuários autorizados pela paróquia. O acesso ao sistema implica a plena ciência e concordância com os termos aqui estabelecidos.
                        </p>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">2. DADOS PESSOAIS COLETADOS E TRATADOS</h6>
                        <p class="small text-dark-50">Para o funcionamento do sistema, os seguintes dados pessoais poderão ser coletados, armazenados e tratados:</p>
                        <ul class="small text-dark-50">
                            <li><strong>Dados cadastrais do usuário:</strong> nome completo, endereço de e-mail, cargo/função na paróquia e identificador de acesso (login);</li>
                            <li><strong>Imagem (foto de perfil):</strong> fotografia do usuário, utilizada para identificação dentro do sistema;</li>
                            <li><strong>Dados de acesso e uso:</strong> registros de login, data e hora de acesso, endereço IP e histórico de atividades no sistema;</li>
                            <li><strong>Dados inseridos pelo próprio usuário:</strong> quaisquer informações lançadas pelo usuário no exercício de suas funções administrativas, incluindo dados de fiéis, financeiros, litúrgicos, sacramentais, pastorais e de comunicação;</li>
                            <li><strong>Dados de terceiros gerenciados pelo usuário:</strong> informações de membros da paróquia, catecúmenos, acólitos, ministros, dizimistas e demais pessoas cadastradas no sistema pelo usuário.</li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">3. FINALIDADE DO TRATAMENTO DOS DADOS</h6>
                        <p class="small text-dark-50">Os dados pessoais coletados têm as seguintes finalidades:</p>
                        <ul class="small text-dark-50">
                            <li>Autenticação e controle de acesso ao sistema;</li>
                            <li>Identificação do usuário dentro da plataforma;</li>
                            <li>Registro de auditoria e rastreabilidade das operações realizadas;</li>
                            <li>Garantia da integridade e segurança das informações gerenciadas;</li>
                            <li>Cumprimento de obrigações legais e contratuais;</li>
                            <li>Melhoria contínua dos serviços prestados pela Sacratech Softwares.</li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">4. BASE LEGAL PARA O TRATAMENTO</h6>
                        <p class="small text-dark-50">
                            O tratamento dos dados pessoais do usuário fundamenta-se nas seguintes bases legais previstas no art. 7º da LGPD:
                        </p>
                        <ul class="small text-dark-50">
                            <li><strong>Inciso I — Consentimento:</strong> ao clicar em "Aceito", o usuário manifesta seu consentimento livre, informado e inequívoco para o tratamento de seus dados pessoais para as finalidades descritas neste Termo;</li>
                            <li><strong>Inciso V — Execução de contrato:</strong> o tratamento é necessário para a prestação dos serviços contratados pela paróquia;</li>
                            <li><strong>Inciso VI — Exercício regular de direitos:</strong> quando necessário para cumprimento de obrigações legais ou regulatórias.</li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">5. RESPONSABILIDADES DO USUÁRIO</h6>
                        <p class="small text-dark-50">
                            O usuário, ao aceitar este Termo, assume plena responsabilidade civil e administrativa pelas seguintes obrigações:
                        </p>
                        <ul class="small text-dark-50">
                            <li><strong>5.1. Veracidade das informações:</strong> o usuário é integralmente responsável pela veracidade, exatidão, atualização e completude de todos os dados que inserir no sistema. A Sacratech Softwares não se responsabiliza por dados lançados de forma incorreta, incompleta ou fraudulenta por qualquer usuário;</li>
                            <li><strong>5.2. Sigilo e confidencialidade:</strong> o usuário compromete-se a manter absoluto sigilo sobre as informações às quais tiver acesso no sistema, especialmente aquelas relativas a dados pessoais de terceiros (fiéis, membros, famílias e demais pessoas cadastradas), vedando qualquer divulgação, compartilhamento ou uso fora das finalidades do sistema;</li>
                            <li><strong>5.3. Uso ético e responsável:</strong> o usuário obriga-se a utilizar o sistema exclusivamente para as finalidades para as quais foi autorizado pela paróquia, abstendo-se de realizar operações que possam prejudicar terceiros, a paróquia ou a integridade do sistema;</li>
                            <li><strong>5.4. Gestão de dados de terceiros:</strong> ao inserir, editar ou excluir dados de terceiros (fiéis, membros da comunidade, catecúmenos, ministros, entre outros), o usuário assume a responsabilidade de agir dentro dos limites de sua autorização e em conformidade com a LGPD, respeitando os direitos dos titulares;</li>
                            <li><strong>5.5. Credenciais de acesso:</strong> o usuário é exclusivamente responsável pela guarda e pelo uso de suas credenciais (login e senha). Qualquer ação realizada mediante o uso dessas credenciais será de inteira responsabilidade do usuário titular, sendo vedado o compartilhamento de acesso com terceiros;</li>
                            <li><strong>5.6. Dados consumidos para gestão:</strong> ao utilizar os dados disponíveis no sistema para fins de gestão e administração paroquial, o usuário compromete-se a tratá-los com a devida diligência, não os utilizando para finalidades diversas das administrativas e pastorais para as quais foram originalmente coletados.</li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">6. RESPONSABILIDADES DA SACRATECH SOFTWARES</h6>
                        <p class="small text-dark-50">A Sacratech Softwares compromete-se a:</p>
                        <ul class="small text-dark-50">
                            <li>Garantir a segurança técnica da plataforma mediante adoção de medidas de proteção adequadas;</li>
                            <li>Não compartilhar dados pessoais com terceiros, exceto quando necessário para a prestação do serviço ou por determinação legal;</li>
                            <li>Disponibilizar mecanismos para que o usuário exerça seus direitos enquanto titular de dados pessoais.</li>
                        </ul>
                        <p class="small text-dark-50">
                            <strong>A Sacratech Softwares não se responsabiliza, em nenhuma hipótese, por:</strong>
                        </p>
                        <ul class="small text-dark-50">
                            <li>Dados inseridos de forma incorreta, incompleta ou em desacordo com a realidade por qualquer usuário do sistema;</li>
                            <li>Danos decorrentes do uso indevido, não autorizado ou contrário às disposições deste Termo por parte dos usuários;</li>
                            <li>Informações falsas ou desatualizadas lançadas no sistema e suas consequências para terceiros;</li>
                            <li>Violações de privacidade ou da LGPD cometidas diretamente pelo usuário no exercício de suas funções.</li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">7. USO DA FOTO DE PERFIL</h6>
                        <p class="small text-dark-50">
                            O sistema poderá solicitar ao usuário o envio de uma fotografia para compor seu perfil de identificação dentro da plataforma. Ao fornecer a imagem, o usuário:
                        </p>
                        <ul class="small text-dark-50">
                            <li>Declara ser titular ou possuir os direitos sobre a imagem fornecida;</li>
                            <li>Autoriza o armazenamento e a exibição da fotografia exclusivamente dentro do ambiente do SisMatriz, para fins de identificação junto à equipe da paróquia;</li>
                            <li>Reconhece que a imagem poderá ser visualizada por outros usuários autorizados do sistema.</li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">8. DIREITOS DO TITULAR DE DADOS</h6>
                        <p class="small text-dark-50">
                            Nos termos do art. 18 da LGPD, o usuário, na qualidade de titular de dados pessoais, tem os seguintes direitos:
                        </p>
                        <ul class="small text-dark-50">
                            <li>Confirmação da existência de tratamento de seus dados pessoais;</li>
                            <li>Acesso aos dados pessoais que lhe dizem respeito;</li>
                            <li>Correção de dados incompletos, inexatos ou desatualizados;</li>
                            <li>Portabilidade dos dados, observados os segredos comerciais e industriais;</li>
                            <li>Eliminação dos dados desnecessários, excessivos ou tratados em desconformidade com a LGPD;</li>
                            <li>Revogação do consentimento a qualquer tempo, mediante requisição ao administrador do sistema ou à Sacratech Softwares.</li>
                        </ul>
                        <p class="small text-dark-50">
                            Para exercer quaisquer desses direitos, o usuário deverá entrar em contato com o administrador da paróquia ou com a Sacratech Softwares pelo canal de atendimento disponibilizado.
                        </p>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">9. RETENÇÃO E ELIMINAÇÃO DOS DADOS</h6>
                        <p class="small text-dark-50">
                            Os dados pessoais serão mantidos pelo período necessário à execução do contrato de prestação de serviços firmado entre a Sacratech Softwares e a paróquia. Após o encerramento contratual, os dados serão eliminados ou anonimizados, salvo quando sua retenção for necessária para cumprimento de obrigação legal, regulatória ou para o exercício regular de direitos.
                        </p>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">10. REVOGAÇÃO DO CONSENTIMENTO E CONSEQUÊNCIAS</h6>
                        <p class="small text-dark-50">
                            O usuário pode revogar seu consentimento a qualquer momento. A revogação, contudo, implica a <strong>impossibilidade de acesso ao sistema</strong>, uma vez que o consentimento é condição necessária para a utilização da plataforma. A revogação não afetará a licitude do tratamento realizado anteriormente.
                        </p>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">11. SANÇÕES POR DESCUMPRIMENTO</h6>
                        <p class="small text-dark-50">
                            O descumprimento de qualquer disposição deste Termo pelo usuário poderá acarretar, a critério da paróquia e/ou da Sacratech Softwares:
                        </p>
                        <ul class="small text-dark-50">
                            <li>Suspensão imediata do acesso ao sistema;</li>
                            <li>Comunicação à autoridade competente (ANPD — Autoridade Nacional de Proteção de Dados);</li>
                            <li>Adoção de medidas legais cabíveis, inclusive de natureza civil e criminal, conforme a gravidade do descumprimento.</li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-dark mt-3">12. DISPOSIÇÕES GERAIS</h6>
                        <p class="small text-dark-50">
                            Este Termo rege-se pela legislação brasileira, em especial pela Lei nº 13.709/2018 (LGPD) e pelo Código Civil Brasileiro. Quaisquer litígios decorrentes deste Termo serão submetidos ao foro da comarca da sede da Paróquia contratante, com renúncia expressa a qualquer outro, por mais privilegiado que seja. A Sacratech Softwares reserva-se o direito de atualizar este Termo periodicamente, notificando os usuários por meio do próprio sistema.
                        </p>

                        {{-- Indicador de fim do scroll --}}
                        <div id="termoFim" class="text-center py-3 mt-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle me-1"></i> Você chegou ao final do Termo
                            </span>
                        </div>

                    </div>{{-- /termo-scroll --}}
                </div>

                {{-- Indicador de scroll --}}
                <div id="scrollHint" class="px-4 py-2 bg-warning-subtle border-top border-warning-subtle d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-down-circle text-warning fs-5"></i>
                    <span class="small text-warning-emphasis fw-semibold">Role até o final para habilitar os botões abaixo.</span>
                </div>

                {{-- Footer com botões --}}
                <div class="card-footer bg-light border-top px-4 py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">

                        {{-- Recusar --}}
                        <form action="{{ route('setup.termos.recusar') }}" method="POST" id="formRecusar">
                            @csrf
                            <button type="button" id="btnRecusar"
                                    class="btn btn-outline-danger px-4"
                                    disabled
                                    onclick="confirmarRecusa()">
                                <i class="bi bi-x-circle me-2"></i>Recuso os Termos
                            </button>
                        </form>

                        {{-- Aceitar --}}
                        <form action="{{ route('setup.termos.aceitar') }}" method="POST" id="formAceitar">
                            @csrf
                            <button type="submit" id="btnAceitar"
                                    class="btn btn-success px-5"
                                    disabled>
                                <span class="spinner-border spinner-border-sm me-2 d-none" id="spinnerAceitar" role="status"></span>
                                <i class="bi bi-check-circle me-2" id="iconAceitar"></i>
                                <span id="textoAceitar">Aceito os Termos</span>
                            </button>
                        </form>

                    </div>

                    <p class="text-center text-muted small mt-3 mb-0">
                        Ao clicar em <strong>"Aceito os Termos"</strong>, você declara ter lido, compreendido e concordado integralmente com todas as cláusulas acima, em conformidade com a Lei nº 13.709/2018 (LGPD).
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal: Por que estou vendo isso? --}}
<div class="modal fade" id="modalPorque" tabindex="-1" aria-labelledby="modalPorqueLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                        <i class="bi bi-shield-check fs-6"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0" id="modalPorqueLabel">Por que estou vendo isso?</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-3 pb-4 px-4">
                <p class="text-dark mb-3">
                    Estamos exibindo este termo para <strong>todos os usuários do SisMatriz</strong> — tanto os novos quanto os que já utilizam o sistema há algum tempo — e vamos explicar o motivo de forma simples:
                </p>

                <div class="d-flex gap-3 mb-3">
                    <div class="text-primary mt-1" style="flex-shrink: 0;"><i class="bi bi-shield-lock fs-5"></i></div>
                    <div>
                        <p class="mb-1 fw-semibold text-dark">Proteção de todos os envolvidos</p>
                        <p class="small text-muted mb-0">O sistema guarda informações importantes da paróquia e dos fiéis. Para que essas informações sejam tratadas com segurança e respeito, é preciso que todos que têm acesso estejam cientes de suas responsabilidades — e que concordem com elas de forma formal.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-3">
                    <div class="text-success mt-1" style="flex-shrink: 0;"><i class="bi bi-journal-check fs-5"></i></div>
                    <div>
                        <p class="mb-1 fw-semibold text-dark">A LGPD exige isso</p>
                        <p class="small text-muted mb-0">A <strong>Lei Geral de Proteção de Dados (Lei nº 13.709/2018)</strong> determina que qualquer organização que trate dados pessoais deve obter o consentimento informado das pessoas envolvidas. Isso vale para paróquias e sistemas de gestão como o SisMatriz.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-3">
                    <div class="text-warning mt-1" style="flex-shrink: 0;"><i class="bi bi-people fs-5"></i></div>
                    <div>
                        <p class="mb-1 fw-semibold text-dark">Vale para todos, sem exceção</p>
                        <p class="small text-muted mb-0">Não importa o cargo ou o tempo de uso: administradores, coordenadores, catequistas, membros do PASCOM — todos precisam aceitar o mesmo termo. Isso garante que todo mundo esteja na mesma página quanto ao uso correto do sistema e ao respeito pelos dados dos fiéis.</p>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <div class="text-info mt-1" style="flex-shrink: 0;"><i class="bi bi-hand-thumbs-up fs-5"></i></div>
                    <div>
                        <p class="mb-1 fw-semibold text-dark">É rápido e feito uma única vez</p>
                        <p class="small text-muted mb-0">Você só precisará aceitar o termo uma vez. Depois disso, o acesso será liberado normalmente e esse passo não aparecerá novamente. É uma medida simples que faz uma grande diferença na segurança e na confiança de todos.</p>
                    </div>
                </div>

                <div class="alert alert-light border mt-4 mb-0 small text-muted rounded-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Em caso de dúvidas, entre em contato com o administrador da sua paróquia ou com o suporte da Sacratech Softwares.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Entendido!</button>
            </div>
        </div>
    </div>
</div>

<style>
    .fade-in-down {
        animation: fadeInDown 0.7s ease-out;
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .termo-scroll {
        scroll-behavior: smooth;
    }

    /* Scrollbar customizada para o bloco do termo */
    .termo-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .termo-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .termo-scroll::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 4px;
    }

    #btnAceitar:not([disabled]),
    #btnRecusar:not([disabled]) {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    #btnAceitar:not([disabled]):hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.35);
    }
    #btnRecusar:not([disabled]):hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.25);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scroll     = document.getElementById('termoScroll');
        const btnAceitar = document.getElementById('btnAceitar');
        const btnRecusar = document.getElementById('btnRecusar');
        const scrollHint = document.getElementById('scrollHint');
        const formAceitar = document.getElementById('formAceitar');

        // ── Controle do scroll obrigatório ────────────────────────────────────
        function verificarScroll() {
            const chegouAoFim = scroll.scrollTop + scroll.clientHeight >= scroll.scrollHeight - 20;
            if (chegouAoFim) {
                btnAceitar.disabled = false;
                btnRecusar.disabled = false;
                scrollHint.style.display = 'none';
            }
        }

        scroll.addEventListener('scroll', verificarScroll);
        // Verificar imediatamente caso o conteúdo caiba na tela
        verificarScroll();

        // ── Botão "Por que estou vendo isso?" ────────────────────────────────
        document.getElementById('btnPorqueVendo').addEventListener('click', function (e) {
            e.preventDefault();
            var modal = new bootstrap.Modal(document.getElementById('modalPorque'));
            modal.show();
        });

        // ── Loading no botão Aceitar ──────────────────────────────────────────
        formAceitar.addEventListener('submit', function () {
            btnAceitar.disabled = true;
            document.getElementById('spinnerAceitar').classList.remove('d-none');
            document.getElementById('iconAceitar').classList.add('d-none');
            document.getElementById('textoAceitar').textContent = 'Aguarde...';
        });
    });

    // ── Confirmação de recusa ─────────────────────────────────────────────────
    function confirmarRecusa() {
        if (confirm('Tem certeza que deseja RECUSAR os Termos de Consentimento?\n\nAo recusar, você será desconectado e não poderá acessar o sistema. Será necessário aceitar o termo para utilizar o SisMatriz.')) {
            document.getElementById('formRecusar').submit();
        }
    }
</script>
@endsection
