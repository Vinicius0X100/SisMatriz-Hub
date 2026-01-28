@extends('layouts.app')

@section('content')
<div class="container-fluid p-0" style="height: calc(100vh - 70px);">
    <div class="row g-0 h-100">
        <!-- Sidebar: User List -->
        <div class="col-md-4 col-lg-3 border-end bg-white h-100 d-flex flex-column" id="chatSidebar">
            <!-- Header -->
            <div class="p-3 border-bottom bg-light">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0">Mensagens</h5>
                    <button class="btn btn-sm btn-outline-secondary rounded-circle" title="Nova Conversa">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </div>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="userSearch" class="form-control rounded-pill ps-5 bg-white" placeholder="Buscar pessoas...">
                </div>
            </div>
            
            <!-- User List -->
            <div class="flex-grow-1 overflow-auto" id="userListContainer">
                <div class="text-center py-5 text-muted small" id="userListLoading">
                    <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                    <div>Carregando contatos...</div>
                </div>
                <div class="list-group list-group-flush" id="userList">
                    <!-- Users will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-8 col-lg-9 h-100 d-flex flex-column bg-light" id="chatArea">
            <!-- Empty State -->
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted" id="chatEmptyState">
                <div class="bg-white p-4 rounded-circle shadow-sm mb-3">
                    <i class="bi bi-chat-dots fs-1 text-primary"></i>
                </div>
                <h5>Selecione uma conversa</h5>
                <p class="small">Escolha um contato para começar a conversar</p>
            </div>

            <!-- Chat Content (Hidden initially) -->
            <div class="d-none d-flex flex-column h-100" id="chatContent">
                <!-- Chat Header -->
                <div class="p-3 bg-white border-bottom d-flex align-items-center justify-content-between shadow-sm" style="z-index: 10;">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-link text-dark me-2 d-md-none" id="backToUsers">
                            <i class="bi bi-arrow-left fs-5"></i>
                        </button>
                        <div class="position-relative me-3">
                            <img src="" alt="" class="rounded-circle border" width="40" height="40" id="chatHeaderAvatar" style="object-fit: cover;">
                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle d-none" id="chatHeaderStatus"></span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" id="chatHeaderName">Nome do Usuário</h6>
                            <small class="text-muted" id="chatHeaderDetails">Online</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle text-muted" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#" id="actionClearChat"><i class="bi bi-trash me-2"></i>Limpar conversa</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" id="actionBlockUser"><i class="bi bi-slash-circle me-2"></i>Bloquear usuário</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-grow-1 overflow-auto p-4" id="messagesContainer" style="background-color: #f0f2f5;">
                    <!-- Messages will be injected here -->
                </div>

                <!-- Input Area -->
                <div class="bg-white border-top">
                    <!-- Reply Preview -->
                    <div id="replyPreview" class="d-none p-2 mx-3 mt-2 rounded position-relative bg-light border-start border-4 border-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="overflow-hidden">
                                <span class="fw-bold text-primary small" id="replyToName">Nome</span>
                                <div class="text-truncate text-muted small" id="replyToText">Mensagem...</div>
                            </div>
                            <button type="button" class="btn-close btn-sm ms-2" id="cancelReply"></button>
                        </div>
                    </div>

                    <div class="p-3">
                        <div id="blockedMessage" class="d-none text-center text-muted small mb-2">
                            <i class="bi bi-lock-fill"></i> Esta conversa está bloqueada.
                        </div>
                        <form id="messageForm" class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-light rounded-circle text-muted">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                            <button type="button" class="btn btn-light rounded-circle text-muted">
                                <i class="bi bi-paperclip"></i>
                            </button>
                            <input type="text" class="form-control rounded-pill bg-light border-0 py-2 px-3" id="messageInput" placeholder="Digite uma mensagem..." autocomplete="off">
                            <button type="submit" class="btn btn-primary rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-send-fill" style="margin-left: 2px;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Scrollbar */
    #userListContainer::-webkit-scrollbar,
    #messagesContainer::-webkit-scrollbar {
        width: 6px;
    }
    #userListContainer::-webkit-scrollbar-track,
    #messagesContainer::-webkit-scrollbar-track {
        background: transparent;
    }
    #userListContainer::-webkit-scrollbar-thumb,
    #messagesContainer::-webkit-scrollbar-thumb {
        background-color: rgba(0,0,0,0.1);
        border-radius: 3px;
    }

    /* Message Bubbles */
    .message-bubble {
        max-width: 75%;
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        position: relative;
        font-size: 0.95rem;
        line-height: 1.4;
        margin-bottom: 0.2rem;
        cursor: default;
    }
    .message-sent {
        background-color: #d9fdd3; /* WhatsApp Green-ish */
        color: #000;
        align-self: flex-end;
        border-bottom-right-radius: 0.2rem;
    }
    .message-received {
        background-color: #fff;
        color: #000;
        align-self: flex-start;
        border-bottom-left-radius: 0.2rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .message-time {
        font-size: 0.65rem;
        color: rgba(0,0,0,0.45);
        margin-top: 2px;
        text-align: right;
        display: block;
        line-height: 1;
    }
    .replied-message {
        background-color: rgba(0,0,0,0.05);
        border-left: 4px solid #00a884;
        border-radius: 4px;
        padding: 4px 8px;
        margin-bottom: 6px;
        font-size: 0.8rem;
        cursor: pointer;
        display: flex;
        flex-direction: column;
    }
    .message-bubble.message-sent .replied-message {
        border-left-color: #008f6f;
        background-color: rgba(0,0,0,0.1);
    }
    
    /* User List Item */
    .user-list-item {
        transition: background-color 0.2s;
        cursor: pointer;
        border-left: 3px solid transparent;
    }
    .user-list-item:hover {
        background-color: #f8f9fa;
    }
    .user-list-item.active {
        background-color: #e8f5e9;
        border-left-color: #198754;
    }
    .user-list-item.unread {
        background-color: #fff8e1; /* Light yellow highlight */
    }

    /* Mobile Responsiveness */
    @media (max-width: 767.98px) {
        #chatSidebar {
            width: 100%;
            position: absolute;
            z-index: 20;
            display: flex; /* Override d-none if set by JS logic */
        }
        #chatArea {
            width: 100%;
            position: absolute;
            z-index: 10;
        }
        .chat-open #chatSidebar {
            display: none !important;
        }
        .chat-open #chatArea {
            z-index: 30;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const AUTH_ID = parseInt(document.body.dataset.authId, 10);
        const userList = document.getElementById('userList');
        const userListLoading = document.getElementById('userListLoading');
        const chatEmptyState = document.getElementById('chatEmptyState');
        const chatContent = document.getElementById('chatContent');
        const messagesContainer = document.getElementById('messagesContainer');
        const messageForm = document.getElementById('messageForm');
        const messageInput = document.getElementById('messageInput');
        const userSearch = document.getElementById('userSearch');
        
        // New Elements
        const replyPreview = document.getElementById('replyPreview');
        const replyToName = document.getElementById('replyToName');
        const replyToText = document.getElementById('replyToText');
        const cancelReplyBtn = document.getElementById('cancelReply');
        const actionClearChat = document.getElementById('actionClearChat');
        const actionBlockUser = document.getElementById('actionBlockUser');
        const blockedMessage = document.getElementById('blockedMessage');
        
        let currentUserId = null;
        let currentUserData = null;
        let pollInterval = null;
        let allUsers = []; // Store users for search
        let currentReplyToId = null;
        let isBlockedStatus = false;

        // Fetch Users
        function fetchUsers() {
            fetch('/chat/users', { credentials: 'same-origin' })
                .then(response => {
                    if (!response.ok) throw new Error('Falha ao carregar usuários');
                    return response.json();
                })
                .then(users => {
                    userListLoading.style.display = 'none';
                    allUsers = users;
                    filterAndRenderUsers();
                    
                    // Auto-select user if passed via URL
                    const targetUserId = "{{ $targetUserId ?? '' }}";
                    if (targetUserId && !currentUserId) {
                        const targetUser = users.find(u => u.id == targetUserId);
                        if (targetUser) {
                            selectUser(targetUser);
                        }
                    }
                })
                .catch(err => {
                    console.error('Erro ao buscar usuários:', err);
                    userListLoading.innerHTML = '<div class="text-danger">Não foi possível carregar os contatos.</div>';
                });
        }

        // Search Listener
        userSearch.addEventListener('input', function() {
            filterAndRenderUsers();
        });

        function filterAndRenderUsers() {
            const term = userSearch.value.toLowerCase();
            const filtered = allUsers.filter(user => user.display_name.toLowerCase().includes(term));
            renderUserList(filtered);
        }

        function renderUserList(users) {
            userList.innerHTML = '';
            if (users.length === 0) {
                userList.innerHTML = '<div class="text-center p-3 text-muted small">Nenhum contato encontrado.</div>';
                return;
            }

            users.forEach(user => {
                const isActive = currentUserId == user.id;
                const unreadBadge = user.unread_count > 0 
                    ? `<span class="badge rounded-pill bg-success ms-auto">${user.unread_count}</span>` 
                    : '';
                
                let lastMsgHtml = '<div class="small text-muted fst-italic">Nenhuma mensagem</div>';
                let time = '';
                
                if (user.last_message) {
                    const isMyMsg = user.last_message.sender_id == AUTH_ID;
                    const msgContent = user.last_message.deleted_by_sender && isMyMsg ? '🚫 <i>Mensagem apagada</i>' : (user.last_message.message || '');
                    
                    lastMsgHtml = `<div class="small text-muted text-truncate" style="max-width: 180px;">${isMyMsg ? '<i class="bi bi-check2-all text-primary small"></i> ' : ''}${escapeHtml(msgContent)}</div>`;
                    time = new Date(user.last_message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }

                const avatarUrl = user.avatar 
                    ? `/storage/uploads/avatars/${user.avatar}` 
                    : `https://ui-avatars.com/api/?name=${user.display_name}&background=random&color=fff`;

                const pinIconClass = user.is_pinned ? 'bi-pin-angle-fill text-primary' : 'bi-pin-angle text-muted';

                const item = document.createElement('a');
                item.className = `list-group-item list-group-item-action py-3 user-list-item ${isActive ? 'active' : ''} ${user.unread_count > 0 ? 'fw-bold' : ''}`;
                item.href = '#';
                item.onclick = (e) => {
                    if (!e.target.closest('.pin-btn')) {
                        e.preventDefault();
                        selectUser(user);
                    }
                };

                item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="position-relative me-3">
                            <img src="${avatarUrl}" class="rounded-circle border" width="48" height="48" style="object-fit: cover;">
                            ${user.is_pinned ? '<div class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-light border text-primary p-1" style="font-size: 0.6rem;"><i class="bi bi-pin-angle-fill"></i></div>' : ''}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-truncate">${escapeHtml(user.display_name)}</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi ${pinIconClass} small pin-btn p-1" style="cursor: pointer;" title="${user.is_pinned ? 'Desafixar' : 'Fixar'}"></i>
                                    <small class="text-muted">${time}</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                ${lastMsgHtml}
                                ${unreadBadge}
                            </div>
                        </div>
                    </div>
                `;
                
                const pinBtn = item.querySelector('.pin-btn');
                pinBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    togglePin(user.id);
                });

                userList.appendChild(item);
            });
        }

        function togglePin(userId) {
            fetch('/chat/toggle-pin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                fetchUsers();
            })
            .catch(err => console.error('Error toggling pin:', err));
        }

        function selectUser(user) {
            currentUserId = user.id;
            currentUserData = user;
            
            cancelReply();

            document.body.classList.add('chat-open');
            chatEmptyState.classList.add('d-none');
            chatContent.classList.remove('d-none');
            
            const avatarUrl = user.avatar 
                    ? `/storage/uploads/avatars/${user.avatar}` 
                    : `https://ui-avatars.com/api/?name=${user.display_name}&background=random&color=fff`;
            
            document.getElementById('chatHeaderName').textContent = user.display_name;
            document.getElementById('chatHeaderAvatar').src = avatarUrl;
            
            fetchMessages();
            
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(fetchMessages, 3000);
            
            fetchUsers();
        }

        function fetchMessages() {
            if (!currentUserId) return;
            
            fetch(`/chat/messages/${currentUserId}`, { credentials: 'same-origin' })
                .then(response => {
                    if (!response.ok) throw new Error('Falha ao carregar mensagens');
                    return response.json();
                })
                .then(data => {
                    const messages = data.messages || []; 
                    const blockedByMe = data.is_blocked_by_me || false;
                    const blockedByThem = data.is_blocked_by_them || false;
                    
                    updateBlockStatus(blockedByMe, blockedByThem);
                    renderMessages(messages);
                })
                .catch(err => console.error('Erro ao buscar mensagens:', err));
        }
        
        function updateBlockStatus(byMe, byThem) {
            isBlockedStatus = byMe || byThem;
            
            const submitBtn = messageForm.querySelector('button[type="submit"]');
            
            if (isBlockedStatus) {
                blockedMessage.classList.remove('d-none');
                messageInput.disabled = true;
                submitBtn.disabled = true;
                
                if (byThem && !byMe) {
                     blockedMessage.innerHTML = '<i class="bi bi-lock-fill"></i> Você foi bloqueado por este usuário.';
                } else {
                     blockedMessage.innerHTML = '<i class="bi bi-lock-fill"></i> Esta conversa está bloqueada.';
                }
            } else {
                blockedMessage.classList.add('d-none');
                messageInput.disabled = false;
                submitBtn.disabled = false;
            }
            
            if (byMe) {
                actionBlockUser.innerHTML = '<i class="bi bi-unlock me-2"></i>Desbloquear usuário';
                actionBlockUser.classList.remove('text-danger');
                actionBlockUser.onclick = (e) => { e.preventDefault(); unblockUser(); };
            } else {
                actionBlockUser.innerHTML = '<i class="bi bi-slash-circle me-2"></i>Bloquear usuário';
                actionBlockUser.classList.add('text-danger');
                actionBlockUser.onclick = (e) => { e.preventDefault(); blockUser(); };
            }
            
            actionClearChat.onclick = (e) => {
                e.preventDefault();
                clearChat();
            };
        }

        function renderMessages(messages) {
            // Check if we are at bottom to auto-scroll
            const wasAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop <= messagesContainer.clientHeight + 150;
            
            messagesContainer.innerHTML = '';
            let lastDate = null;

            if (messages.length === 0) {
                 messagesContainer.innerHTML = '<div class="text-center mt-5 text-muted small">Nenhuma mensagem aqui ainda. Diga olá! 👋</div>';
                 return;
            }

            messages.forEach(msg => {
                const isSent = msg.sender_id == AUTH_ID;
                const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                const msgDate = new Date(msg.created_at).toLocaleDateString();
                if (msgDate !== lastDate) {
                    const divider = document.createElement('div');
                    divider.className = 'text-center my-3';
                    divider.innerHTML = `<span class="badge bg-light text-muted fw-normal border">${msgDate}</span>`;
                    messagesContainer.appendChild(divider);
                    lastDate = msgDate;
                }

                let replyHtml = '';
                if (msg.reply_to) { 
                    const rSender = msg.reply_to.sender;
                    const rName = rSender ? (rSender.hide_name ? 'Usuário' : (rSender.name || rSender.user)) : 'Desconhecido';
                    const rText = msg.reply_to.message;
                    replyHtml = `
                        <div class="replied-message" onclick="window.scrollToMessage(${msg.reply_to_id})">
                            <span class="fw-bold text-primary small">${escapeHtml(rName)}</span>
                            <div class="text-truncate text-muted small">${escapeHtml(rText)}</div>
                        </div>
                    `;
                }

                const bubble = document.createElement('div');
                bubble.className = `d-flex flex-column ${isSent ? 'align-items-end' : 'align-items-start'}`;
                bubble.id = `msg-${msg.id}`; 
                
                const senderName = isSent ? 'Você' : (currentUserData.display_name || 'Usuário');
                
                const bubbleContent = document.createElement('div');
                bubbleContent.className = `message-bubble ${isSent ? 'message-sent' : 'message-received'}`;
                bubbleContent.title = "Duplo clique para responder";
                
                bubbleContent.innerHTML = `
                    ${replyHtml}
                    ${escapeHtml(msg.message)}
                    <span class="message-time">
                        ${time} 
                        ${isSent ? (msg.is_read ? '<i class="bi bi-check2-all text-primary"></i>' : '<i class="bi bi-check2"></i>') : ''}
                    </span>
                `;
                
                bubbleContent.addEventListener('dblclick', () => {
                   startReply(msg.id, msg.message, senderName); 
                });

                bubble.appendChild(bubbleContent);
                messagesContainer.appendChild(bubble);
            });
            
            if (wasAtBottom) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }
        
        window.startReply = function(id, text, name) {
            currentReplyToId = id;
            replyToName.textContent = name;
            replyToText.textContent = text;
            replyPreview.classList.remove('d-none');
            messageInput.focus();
        };
        
        window.cancelReply = function() {
            currentReplyToId = null;
            replyPreview.classList.add('d-none');
            replyToName.textContent = '';
            replyToText.textContent = '';
        };
        
        cancelReplyBtn.addEventListener('click', cancelReply);

        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (!text || !currentUserId || isBlockedStatus) return;

            const payload = {
                receiver_id: currentUserId,
                message: text,
                reply_to_id: currentReplyToId
            };
            
            messageInput.value = '';
            cancelReply();

            fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 403) alert('Você não pode enviar mensagens para este usuário.');
                    throw new Error('Falha ao enviar');
                }
                return response.json();
            })
            .then(msg => {
                fetchMessages(); 
                fetchUsers(); 
            })
            .catch(err => {
                console.error('Send error:', err);
                alert('Erro ao enviar mensagem.');
            });
        });
        
        function blockUser() {
            if (!confirm('Tem certeza que deseja bloquear este usuário? Vocês não poderão mais trocar mensagens.')) return;
            
            fetch('/chat/block', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ user_id: currentUserId })
            })
            .then(() => fetchMessages());
        }
        
        function unblockUser() {
            if (!confirm('Desbloquear este usuário?')) return;
            
            fetch('/chat/unblock', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ user_id: currentUserId })
            })
            .then(() => fetchMessages());
        }
        
        function clearChat() {
            if (!confirm('Tem certeza que deseja apagar todas as mensagens desta conversa? Esta ação não pode ser desfeita.')) return;
            
            fetch('/chat/clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ user_id: currentUserId })
            })
            .then(() => {
                fetchMessages();
                fetchUsers();
            });
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        window.scrollToMessage = function(id) {
             const el = document.getElementById(`msg-${id}`);
             if (el) {
                 el.scrollIntoView({behavior: 'smooth', block: 'center'});
                 // Highlight effect
                 const bubble = el.querySelector('.message-bubble');
                 bubble.style.transition = 'background-color 0.5s';
                 const originalBg = bubble.style.backgroundColor;
                 bubble.style.backgroundColor = '#fff3cd'; // Highlight color
                 setTimeout(() => {
                     bubble.style.backgroundColor = '';
                 }, 1500);
             }
        };

        // Mobile Back Button
        document.getElementById('backToUsers').addEventListener('click', function() {
            document.body.classList.remove('chat-open');
            currentUserId = null;
            if (pollInterval) clearInterval(pollInterval);
        });

        // Initial Load
        fetchUsers();
        // Poll user list
        setInterval(fetchUsers, 10000);
    });
</script>
@endsection
