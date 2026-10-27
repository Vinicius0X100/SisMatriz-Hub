/**
 * SisMatriz - Utilitário de Auditoria de Interações Relevantes de Interface
 *
 * Registra de forma assíncrona e discreta cliques em botões críticos, abertura de modais
 * e confirmações administrativas marcadas com o atributo data-audit-action.
 */
(function() {
    'use strict';

    function sendInteraction(action, module, description) {
        const tokenMeta = document.head.querySelector('meta[name="csrf-token"]');
        const csrfToken = tokenMeta ? tokenMeta.content : '';

        if (!csrfToken || !action) return;

        const payload = JSON.stringify({
            action: action,
            module: module || 'Interface',
            description: description || 'Interação na interface'
        });

        const url = '/activity-logs/interaction';

        // Usa fetch com keepalive para garantir entrega mesmo se a página navegar
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload,
            keepalive: true
        }).catch(function(err) {
            // Falha silenciosa: nunca quebra a experiência do usuário
            console.debug('[ActivityLogger] Não foi possível registrar interação:', err);
        });
    }

    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-audit-action]');
        if (!target) return;

        const action = target.getAttribute('data-audit-action');
        const module = target.getAttribute('data-audit-module');
        const desc = target.getAttribute('data-audit-desc') || target.getAttribute('title') || target.innerText.trim();

        sendInteraction(action, module, desc);
    }, { passive: true });

    // Expõe globalmente caso algum script precise disparar manualmente
    window.logUiInteraction = sendInteraction;
})();
