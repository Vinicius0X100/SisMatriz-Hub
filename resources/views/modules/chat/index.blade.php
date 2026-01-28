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
                    <div>
                        <button class="btn btn-light rounded-circle text-muted">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-grow-1 overflow-auto p-4" id="messagesContainer" style="background-color: #f0f2f5;">
                    <!-- Messages will be injected here -->
                </div>

                <!-- Input Area -->
                <div class="p-3 bg-white border-top">
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
        
        let currentUserId = null;
        let currentUserData = null;
        let pollInterval = null;
        let allUsers = []; // Store users for search

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
                            // window.history.replaceState({}, document.title, window.location.pathname);
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
                const lastMsg = user.last_message 
                    ? `<div class="small text-muted text-truncate" style="max-width: 180px;">${user.last_message.sender_id == AUTH_ID ? '<i class="bi bi-check2-all text-primary small"></i> ' : ''}${user.last_message.message}</div>` 
                    : `<div class="small text-muted fst-italic">Nenhuma mensagem</div>`;
                
                const time = user.last_message
                    ? new Date(user.last_message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                    : '';

                const avatarUrl = user.avatar 
                    ? `/storage/uploads/avatars/${user.avatar}` 
                    : `https://ui-avatars.com/api/?name=${user.display_name}&background=random&color=fff`;

                const pinIconClass = user.is_pinned ? 'bi-pin-angle-fill text-primary' : 'bi-pin-angle text-muted';

                const item = document.createElement('a');
                item.className = `list-group-item list-group-item-action py-3 user-list-item ${isActive ? 'active' : ''} ${user.unread_count > 0 ? 'fw-bold' : ''}`;
                item.href = '#';
                item.onclick = (e) => {
                    // Only select if not clicking the pin
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
                                <h6 class="mb-0 text-truncate">${user.display_name}</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi ${pinIconClass} small pin-btn p-1" style="cursor: pointer;" title="${user.is_pinned ? 'Desafixar' : 'Fixar'}"></i>
                                    <small class="text-muted">${time}</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                ${lastMsg}
                                ${unreadBadge}
                            </div>
                        </div>
                    </div>
                `;
                
                // Attach Pin Event
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
                fetchUsers(); // Refresh list
            })
            .catch(err => console.error('Error toggling pin:', err));
        }

        function selectUser(user) {
            currentUserId = user.id;
            currentUserData = user;
            
            // Update UI
            document.body.classList.add('chat-open'); // For mobile
            chatEmptyState.classList.add('d-none');
            chatContent.classList.remove('d-none');
            
            // Update Header
            const avatarUrl = user.avatar 
                    ? `/storage/uploads/avatars/${user.avatar}` 
                    : `https://ui-avatars.com/api/?name=${user.display_name}&background=random&color=fff`;
            
            document.getElementById('chatHeaderName').textContent = user.display_name;
            document.getElementById('chatHeaderAvatar').src = avatarUrl;
            
            // Fetch Messages
            fetchMessages();
            
            // Start Polling
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(fetchMessages, 3000);
            
            // Update user list to remove unread badge locally (optimistic)
            fetchUsers(); // Refresh list to clear badge
        }

        function fetchMessages() {
            if (!currentUserId) return;
            
            fetch(`/chat/messages/${currentUserId}`, { credentials: 'same-origin' })
                .then(response => {
                    if (!response.ok) throw new Error('Falha ao carregar mensagens');
                    return response.json();
                })
                .then(messages => {
                    renderMessages(messages);
                })
                .catch(err => console.error('Erro ao buscar mensagens:', err));
        }

        function renderMessages(messages) {
            messagesContainer.innerHTML = '';
            let lastDate = null;

            messages.forEach(msg => {
                const isSent = msg.sender_id == AUTH_ID;
                const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                // Date Divider
                const msgDate = new Date(msg.created_at).toLocaleDateString();
                if (msgDate !== lastDate) {
                    const divider = document.createElement('div');
                    divider.className = 'text-center my-3';
                    divider.innerHTML = `<span class="badge bg-light text-muted fw-normal border">${msgDate}</span>`;
                    messagesContainer.appendChild(divider);
                    lastDate = msgDate;
                }

                const bubble = document.createElement('div');
                bubble.className = `d-flex flex-column ${isSent ? 'align-items-end' : 'align-items-start'}`;
                bubble.innerHTML = `
                    <div class="message-bubble ${isSent ? 'message-sent' : 'message-received'}">
                        ${msg.message}
                        <span class="message-time">
                            ${time} 
                            ${isSent ? (msg.is_read ? '<i class="bi bi-check2-all text-primary"></i>' : '<i class="bi bi-check2"></i>') : ''}
                        </span>
                    </div>
                `;
                messagesContainer.appendChild(bubble);
            });
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Send Message
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (!text || !currentUserId) return;

            // Optimistic Append
            const tempMsg = {
                sender_id: AUTH_ID,
                receiver_id: currentUserId,
                message: text,
                created_at: new Date().toISOString(),
                is_read: false
            };
            // renderMessages([...currentMessages, tempMsg]); // Complex to merge, better to wait or simple append
            
            messageInput.value = '';

            fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    receiver_id: currentUserId,
                    message: text
                })
            })
            .then(response => response.json())
            .then(data => {
                fetchMessages(); // Refresh to get real ID and status
                fetchUsers(); // Refresh sidebar last message
            });
        });

        // Mobile Back Button
        document.getElementById('backToUsers').addEventListener('click', function() {
            document.body.classList.remove('chat-open');
            currentUserId = null;
            if (pollInterval) clearInterval(pollInterval);
        });

        // Initial Load
        fetchUsers();
        // Poll user list for new messages
        setInterval(fetchUsers, 10000);
    });
</script>
@endsection
