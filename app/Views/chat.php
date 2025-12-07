<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LiveChat">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <title>LiveChat Demo</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-180.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/icon-120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icon-152.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/icon-167.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icon-180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icon-512.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; height: 100vh; display: flex; flex-direction: column; }
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
        .modal { background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .modal h2 { margin-bottom: 1rem; color: #333; }
        .modal input, .modal textarea { width: 100%; padding: 12px; font-size: 16px; border: 2px solid #ddd; border-radius: 8px; margin-bottom: 1rem; font-family: inherit; -webkit-user-select: text; user-select: text; }
        .modal input:focus, .modal textarea:focus { outline: none; border-color: #0d6efd; }
        .modal input[type="text"] { -webkit-appearance: none; appearance: none; }
        .modal button { width: 100%; padding: 12px; font-size: 16px; background: #0d6efd; color: white; border: none; border-radius: 8px; cursor: pointer; -webkit-appearance: none; }
        .modal button:hover { background: #0b5ed7; }
        .modal button.secondary { background: #6c757d; margin-top: 0.5rem; }
        .hidden { display: none !important; }
        .header { background: #0d6efd; color: white; padding: 1rem; display: flex; align-items: center; justify-content: space-between; }
        .header h1 { font-size: 1.2rem; }
        .header .user-info { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; }
        .header .user-avatar { width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .notification-btn { background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; margin-left: 10px; }
        .notification-btn.enabled { background: #28a745; }
        .logout-btn { background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; margin-left: 10px; }
        .room-selector { background: white; padding: 0.75rem 1rem; border-bottom: 1px solid #eee; display: flex; gap: 0.5rem; overflow-x: auto; align-items: center; }
        .room-btn { padding: 8px 16px; border: 2px solid #0d6efd; background: white; color: #0d6efd; border-radius: 20px; cursor: pointer; white-space: nowrap; font-size: 0.9rem; }
        .room-btn.active { background: #0d6efd; color: white; }
        .room-btn .member-count { font-size: 0.7rem; margin-left: 4px; opacity: 0.8; }
        .create-room-btn { padding: 8px 16px; border: 2px dashed #0d6efd; background: white; color: #0d6efd; border-radius: 20px; cursor: pointer; white-space: nowrap; font-size: 0.9rem; }
        .messages-container { flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem; }
        .message { max-width: 80%; padding: 10px 14px; border-radius: 16px; word-wrap: break-word; }
        .message.received { background: white; align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .message.sent { background: #0d6efd; color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
        .message .username { font-size: 0.75rem; font-weight: 600; margin-bottom: 4px; opacity: 0.8; }
        .message.sent .username { color: rgba(255,255,255,0.8); }
        .message .text { font-size: 0.95rem; line-height: 1.4; }
        .message .time { font-size: 0.7rem; opacity: 0.6; margin-top: 4px; text-align: right; }
        .system-message { text-align: center; color: #666; font-size: 0.85rem; padding: 0.5rem; background: #f8f9fa; border-radius: 8px; align-self: center; }
        .typing-indicator { text-align: left; color: #666; font-size: 0.85rem; padding: 0.5rem 1rem; font-style: italic; }
        .input-area { background: white; padding: 1rem; border-top: 1px solid #eee; display: flex; gap: 0.5rem; align-items: flex-end; }
        .input-area textarea { flex: 1; padding: 12px; border: 2px solid #eee; border-radius: 24px; resize: none; font-size: 16px; font-family: inherit; max-height: 120px; line-height: 1.4; }
        .input-area textarea:focus { outline: none; border-color: #0d6efd; }
        .send-btn { width: 48px; height: 48px; background: #0d6efd; color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .send-btn:disabled { background: #ccc; cursor: not-allowed; }
        .status-bar { padding: 8px; text-align: center; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .status-bar.connected { background: #d4edda; color: #155724; }
        .status-bar.error { background: #f8d7da; color: #721c24; }
        .status-bar.connecting { background: #cce5ff; color: #004085; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .status-dot.connected { background: #28a745; }
        .status-dot.disconnected { background: #dc3545; }
        .status-dot.connecting { background: #ffc107; animation: pulse 1s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .empty-state { text-align: center; color: #666; padding: 2rem; }
        .empty-state svg { width: 64px; height: 64px; margin-bottom: 1rem; opacity: 0.5; }
        .install-prompt { position: fixed; bottom: 80px; left: 1rem; right: 1rem; background: white; padding: 1rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 1rem; z-index: 100; }
        .install-prompt .icon { width: 48px; height: 48px; background: #0d6efd; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; }
        .install-prompt .content { flex: 1; }
        .install-prompt h3 { font-size: 0.95rem; margin-bottom: 4px; }
        .install-prompt p { font-size: 0.8rem; color: #666; }
        .install-prompt button { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; }
        .install-prompt .install-btn { background: #0d6efd; color: white; }
        .install-prompt .dismiss-btn { background: #eee; color: #666; }
        .ios-install-guide { background: #fff3cd; color: #856404; padding: 0.75rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .ios-install-guide strong { display: block; margin-bottom: 4px; }
    </style>
</head>
<body>
    <!-- Username Modal -->
    <div id="usernameModal" class="modal-overlay">
        <div class="modal">
            <h2>👋 Welcome to LiveChat</h2>
            <p style="margin-bottom: 1rem; color: #666;">Enter your name to start chatting</p>
            <input type="text" id="usernameInput" placeholder="Your name" maxlength="50" autocomplete="name" autocapitalize="words" autocorrect="off" spellcheck="false" inputmode="text">
            <button type="button" id="joinBtn">Join Chat</button>
        </div>
    </div>

    <!-- Create Room Modal -->
    <div id="createRoomModal" class="modal-overlay hidden">
        <div class="modal">
            <h2>🏠 Create New Room</h2>
            <input type="text" id="roomNameInput" placeholder="Room name" maxlength="100" autocomplete="off" autocapitalize="words" autocorrect="off" spellcheck="false" inputmode="text">
            <textarea id="roomDescInput" placeholder="Description (optional)" rows="2" autocomplete="off"></textarea>
            <button type="button" id="createRoomSubmitBtn">Create Room</button>
            <button type="button" id="cancelCreateRoomBtn" class="secondary">Cancel</button>
        </div>
    </div>

    <!-- iOS Install Guide -->
    <div id="iosInstallGuide" class="modal-overlay hidden">
        <div class="modal">
            <h2>📲 Add to Home Screen</h2>
            <div class="ios-install-guide">
                <strong>On Safari:</strong>
                1. Tap the Share button (📤) at the bottom<br>
                2. Scroll down and tap "Add to Home Screen"<br>
                3. Tap "Add" in the top right corner
            </div>
            <p style="margin-bottom: 1rem; color: #666; font-size: 0.9rem;">This will install LiveChat as an app on your device for the best experience.</p>
            <button type="button" id="closeIosGuide">Got it!</button>
        </div>
    </div>

    <!-- Install Prompt -->
    <div id="installPrompt" class="install-prompt hidden">
        <div class="icon">💬</div>
        <div class="content"><h3>Install LiveChat</h3><p>Add to home screen for the best experience</p></div>
        <button class="install-btn" id="installBtn">Install</button>
        <button class="dismiss-btn" id="dismissInstall">✕</button>
    </div>

    <!-- Main Chat Interface -->
    <div id="chatInterface" class="hidden">
        <header class="header">
            <h1>💬 LiveChat Demo</h1>
            <div class="user-info">
                <div class="user-avatar" id="userAvatar">?</div>
                <span id="displayUsername">Guest</span>
                <button class="notification-btn" id="notificationBtn">🔔 Notifications</button>
                <button class="logout-btn" id="logoutBtn">🚪 Logout</button>
            </div>
        </header>

        <div class="room-selector" id="roomSelector">
            <button class="room-btn active" data-room="1">💬 General</button>
            <button class="room-btn" data-room="2">🆘 Support</button>
            <button class="room-btn" data-room="3">🎉 Random</button>
            <button class="create-room-btn" id="createRoomBtn">+ New Room</button>
        </div>

        <div id="statusBar" class="status-bar connecting"><span class="status-dot connecting"></span> Connecting...</div>

        <div class="messages-container" id="messagesContainer">
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p>No messages yet. Be the first to say hello! 👋</p>
            </div>
        </div>

        <div id="typingIndicator" class="typing-indicator hidden"></div>

        <div class="input-area">
            <textarea id="messageInput" placeholder="Type a message..." rows="1"></textarea>
            <button class="send-btn" id="sendBtn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Capacitor Core JS (auto-detects and loads only in native environment) -->
    <script src="/capacitor/capacitor.js"></script>

    <script>
        const state = {
            username: localStorage.getItem('chat_username') || '',
            currentRoom: '1',
            messages: [],
            rooms: [],
            roomMembers: {},
            lastMessageId: 0,
            ws: null,
            wsReconnectAttempts: 0,
            wsMaxReconnectAttempts: 10,
            wsReconnectDelay: 1000,
            typingUsers: new Set(),
            typingTimeout: null,
            pushSubscription: null,
            isReconnecting: false,
            notificationsEnabled: false
        };

        const $ = id => document.getElementById(id);
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        const isInStandaloneMode = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        
        // Detect if running in Capacitor native environment with error handling
        let isCapacitor = false;
        try {
            isCapacitor = window.Capacitor?.isNativePlatform?.() || false;
        } catch (e) {
            console.warn('Error detecting Capacitor environment:', e);
        }
        
        const elements = {
            modal: $('usernameModal'), usernameInput: $('usernameInput'), joinBtn: $('joinBtn'),
            chatInterface: $('chatInterface'), messagesContainer: $('messagesContainer'),
            messageInput: $('messageInput'), sendBtn: $('sendBtn'), statusBar: $('statusBar'),
            displayUsername: $('displayUsername'), userAvatar: $('userAvatar'),
            roomSelector: $('roomSelector'), notificationBtn: $('notificationBtn'),
            installPrompt: $('installPrompt'), installBtn: $('installBtn'), dismissInstall: $('dismissInstall'),
            createRoomBtn: $('createRoomBtn'), createRoomModal: $('createRoomModal'),
            roomNameInput: $('roomNameInput'), roomDescInput: $('roomDescInput'),
            createRoomSubmitBtn: $('createRoomSubmitBtn'), cancelCreateRoomBtn: $('cancelCreateRoomBtn'),
            typingIndicator: $('typingIndicator'), logoutBtn: $('logoutBtn'),
            iosInstallGuide: $('iosInstallGuide'), closeIosGuide: $('closeIosGuide')
        };

        function init() {
            if (state.username) showChat();
            else {
                // Focus input with a small delay to ensure DOM is ready
                setTimeout(() => {
                    elements.usernameInput.focus();
                    elements.usernameInput.click();
                }, 100);
            }
            
            // Use proper event listeners instead of onclick
            elements.joinBtn.addEventListener('click', function(e) { e.preventDefault(); joinChat(); });
            elements.usernameInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); joinChat(); } });
            
            elements.sendBtn.addEventListener('click', function(e) { e.preventDefault(); sendMessage(); });
            elements.messageInput.addEventListener('keydown', function(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });
            elements.messageInput.addEventListener('input', function() { autoResizeTextarea(); sendTypingIndicator(); });
            
            elements.notificationBtn.addEventListener('click', function(e) { e.preventDefault(); toggleNotifications(); });
            elements.dismissInstall.addEventListener('click', function() { elements.installPrompt.classList.add('hidden'); });
            
            elements.createRoomBtn.addEventListener('click', function(e) { 
                e.preventDefault(); 
                elements.createRoomModal.classList.remove('hidden'); 
                setTimeout(() => { elements.roomNameInput.focus(); elements.roomNameInput.click(); }, 100); 
            });
            elements.cancelCreateRoomBtn.addEventListener('click', function(e) { 
                e.preventDefault(); 
                elements.createRoomModal.classList.add('hidden'); 
                elements.roomNameInput.value = ''; 
                elements.roomDescInput.value = ''; 
            });
            elements.createRoomSubmitBtn.addEventListener('click', function(e) { e.preventDefault(); createRoom(); });
            elements.roomNameInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); createRoom(); } });
            
            elements.logoutBtn.addEventListener('click', function(e) { e.preventDefault(); logout(); });
            elements.closeIosGuide.addEventListener('click', function() { elements.iosInstallGuide.classList.add('hidden'); localStorage.setItem('ios_install_dismissed', 'true'); });
            
            registerServiceWorker();
            setupInstallPrompt();
        }

        function logout() {
            if (state.ws) { wsLeaveRoom(state.currentRoom); state.ws.close(); state.ws = null; }
            localStorage.removeItem('chat_username');
            state.username = '';
            state.messages = [];
            state.currentRoom = '1';
            state.lastMessageId = 0;
            elements.chatInterface.classList.add('hidden');
            elements.modal.classList.remove('hidden');
            elements.usernameInput.value = '';
            setTimeout(() => { elements.usernameInput.focus(); elements.usernameInput.click(); }, 100);
        }

        function joinChat() {
            const username = elements.usernameInput.value.trim();
            if (!username) { elements.usernameInput.style.borderColor = '#dc3545'; return; }
            state.username = username;
            localStorage.setItem('chat_username', username);
            showChat();
        }

        async function showChat() {
            elements.modal.classList.add('hidden');
            elements.chatInterface.classList.remove('hidden');
            elements.displayUsername.textContent = state.username;
            elements.userAvatar.textContent = state.username.charAt(0).toUpperCase();
            await loadRooms();
            connectWebSocket();
            await loadMessages();
        }

        async function loadRooms() {
            try {
                const res = await fetch('/api/rooms');
                if (res.ok) { state.rooms = await res.json(); renderRoomSelector(); }
            } catch (e) { console.error('Error loading rooms:', e); }
        }

        function renderRoomSelector() {
            const icons = { 'General': '💬', 'Support': '🆘', 'Random': '🎉' };
            const html = state.rooms.slice(0, 10).map(r => {
                const active = r.id.toString() === state.currentRoom ? 'active' : '';
                const count = state.roomMembers[r.id] || 0;
                const icon = icons[r.name] || '💭';
                return `<button class="room-btn ${active}" data-room="${r.id}">${icon} ${escapeHtml(r.name)}${count > 0 ? ` <span class="member-count">(${count})</span>` : ''}</button>`;
            }).join('') + '<button class="create-room-btn" id="createRoomBtn">+ New Room</button>';
            elements.roomSelector.innerHTML = html;
            elements.roomSelector.querySelectorAll('.room-btn').forEach(btn => btn.onclick = () => switchRoom(btn.dataset.room));
            $('createRoomBtn').onclick = () => { elements.createRoomModal.classList.remove('hidden'); elements.roomNameInput.focus(); };
        }

        async function createRoom() {
            const name = elements.roomNameInput.value.trim();
            if (!name) { elements.roomNameInput.style.borderColor = '#dc3545'; return; }
            try {
                const res = await fetch('/api/rooms', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name, description: elements.roomDescInput.value.trim(), created_by: state.username }) });
                if (res.ok) {
                    const room = await res.json();
                    state.rooms.push(room);
                    elements.createRoomModal.classList.add('hidden');
                    elements.roomNameInput.value = '';
                    elements.roomDescInput.value = '';
                    renderRoomSelector();
                    switchRoom(room.id.toString());
                }
            } catch (e) { console.error('Error creating room:', e); alert('Failed to create room'); }
        }

        function connectWebSocket() {
            if (state.ws && state.ws.readyState === WebSocket.OPEN) return;
            const protocol = location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wasReconnecting = state.isReconnecting;
            state.isReconnecting = state.wsReconnectAttempts > 0;
            updateStatus('connecting', 'Connecting...');
            try {
                state.ws = new WebSocket(`${protocol}//${location.host}/ws`);
                state.ws.onopen = async () => { 
                    state.wsReconnectAttempts = 0; 
                    state.wsReconnectDelay = 1000; 
                    updateStatus('connected', 'Connected'); 
                    wsJoinRoom(state.currentRoom);
                    // Reload messages after reconnection
                    if (state.isReconnecting) {
                        await loadMessages();
                        state.isReconnecting = false;
                    }
                };
                state.ws.onmessage = e => handleWebSocketMessage(JSON.parse(e.data));
                state.ws.onclose = () => { 
                    state.isReconnecting = true;
                    updateStatus('error', 'Disconnected. Reconnecting...'); 
                    scheduleReconnect(); 
                };
                state.ws.onerror = () => updateStatus('error', 'Connection error');
            } catch (e) { updateStatus('error', 'Connection failed'); scheduleReconnect(); }
        }

        function scheduleReconnect() {
            if (state.wsReconnectAttempts >= state.wsMaxReconnectAttempts) { updateStatus('error', 'Unable to connect. Please refresh.'); return; }
            state.wsReconnectAttempts++;
            state.isReconnecting = true;
            const delay = Math.min(state.wsReconnectDelay * Math.pow(2, state.wsReconnectAttempts - 1), 30000);
            setTimeout(connectWebSocket, delay);
        }

        function handleWebSocketMessage(msg) {
            switch (msg.type) {
                case 'joined': state.roomMembers[msg.data.roomId] = msg.data.members; renderRoomSelector(); break;
                case 'user_joined': addSystemMessage(`${msg.data.username} joined`); state.roomMembers[msg.data.roomId] = msg.data.members; renderRoomSelector(); break;
                case 'user_left': addSystemMessage(`${msg.data.username} left`); state.roomMembers[msg.data.roomId] = msg.data.members; renderRoomSelector(); break;
                case 'message': handleIncomingMessage(msg.data); break;
                case 'typing': handleTypingIndicator(msg.data); break;
            }
        }

        function wsJoinRoom(roomId) { if (state.ws?.readyState === WebSocket.OPEN) state.ws.send(JSON.stringify({ type: 'join', roomId, username: state.username })); }
        function wsLeaveRoom(roomId) { if (state.ws?.readyState === WebSocket.OPEN) state.ws.send(JSON.stringify({ type: 'leave', roomId })); }

        function sendTypingIndicator() {
            if (state.typingTimeout) clearTimeout(state.typingTimeout);
            if (state.ws?.readyState === WebSocket.OPEN) {
                state.ws.send(JSON.stringify({ type: 'typing', roomId: state.currentRoom, isTyping: true }));
                state.typingTimeout = setTimeout(() => state.ws?.readyState === WebSocket.OPEN && state.ws.send(JSON.stringify({ type: 'typing', roomId: state.currentRoom, isTyping: false })), 2000);
            }
        }

        function handleTypingIndicator(data) {
            if (data.username === state.username) return;
            data.isTyping ? state.typingUsers.add(data.username) : state.typingUsers.delete(data.username);
            if (state.typingUsers.size === 0) { elements.typingIndicator.classList.add('hidden'); return; }
            const users = [...state.typingUsers];
            elements.typingIndicator.textContent = users.length === 1 ? `${users[0]} is typing...` : `${users.length} people are typing...`;
            elements.typingIndicator.classList.remove('hidden');
        }

        function handleIncomingMessage(data) {
            if (data.roomId && data.roomId.toString() !== state.currentRoom) {
                if (data.username !== state.username) showNotification({ username: data.username, body: data.body, room: state.rooms.find(r => r.id.toString() === data.roomId.toString())?.name });
                return;
            }
            if (!state.messages.find(m => m.id === data.id || (m.temp && m.body === data.body && m.username === data.username))) {
                state.messages.push(data);
                if (data.id > state.lastMessageId) state.lastMessageId = data.id;
            }
            state.typingUsers.delete(data.username);
            handleTypingIndicator({ username: data.username, isTyping: false });
            renderMessages();
            if (document.hidden && data.username !== state.username) showNotification(data);
        }

        function addSystemMessage(text) {
            const div = document.createElement('div');
            div.className = 'system-message';
            div.textContent = text;
            elements.messagesContainer.appendChild(div);
            elements.messagesContainer.scrollTop = elements.messagesContainer.scrollHeight;
        }

        async function loadMessages() {
            try {
                const res = await fetch(`/api/rooms/${state.currentRoom}/messages`);
                if (res.ok) {
                    state.messages = await res.json();
                    state.lastMessageId = state.messages.length > 0 ? Math.max(...state.messages.map(m => m.id)) : 0;
                    renderMessages();
                }
            } catch (e) { console.error('Error loading messages:', e); }
        }

        async function sendMessage() {
            const text = elements.messageInput.value.trim();
            if (!text) return;
            elements.sendBtn.disabled = true;
            elements.messageInput.value = '';
            autoResizeTextarea();
            try {
                const res = await fetch(`/api/tickets/${state.currentRoom}/messages`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ username: state.username, body: text }) });
                if (res.ok) {
                    const msg = await res.json();
                    if (!state.messages.find(m => m.id === msg.id)) { state.messages.push(msg); state.lastMessageId = msg.id; renderMessages(); }
                }
            } catch (e) { console.error('Send error:', e); elements.messageInput.value = text; }
            elements.sendBtn.disabled = false;
            elements.messageInput.focus();
        }

        function renderMessages() {
            if (state.messages.length === 0) {
                elements.messagesContainer.innerHTML = '<div class="empty-state"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg><p>No messages yet. Be the first to say hello! 👋</p></div>';
                return;
            }
            elements.messagesContainer.innerHTML = state.messages.map(m => {
                const sent = m.username === state.username;
                const time = new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                return `<div class="message ${sent ? 'sent' : 'received'}"><div class="username">${escapeHtml(m.username || 'Anonymous')}</div><div class="text">${escapeHtml(m.body)}</div><div class="time">${time}</div></div>`;
            }).join('');
            elements.messagesContainer.scrollTop = elements.messagesContainer.scrollHeight;
        }

        function switchRoom(roomId) {
            if (roomId === state.currentRoom) return;
            wsLeaveRoom(state.currentRoom);
            state.currentRoom = roomId;
            state.messages = [];
            state.lastMessageId = 0;
            state.typingUsers.clear();
            elements.typingIndicator.classList.add('hidden');
            elements.roomSelector.querySelectorAll('.room-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.room === roomId));
            wsJoinRoom(roomId);
            loadMessages();
        }

        function updateStatus(type, message) {
            const dotClass = type === 'connected' ? 'connected' : type === 'connecting' ? 'connecting' : 'disconnected';
            elements.statusBar.className = `status-bar ${type}`;
            elements.statusBar.innerHTML = `<span class="status-dot ${dotClass}"></span> ${message}`;
        }

        function autoResizeTextarea() {
            elements.messageInput.style.height = 'auto';
            elements.messageInput.style.height = Math.min(elements.messageInput.scrollHeight, 120) + 'px';
        }

        function escapeHtml(text) { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }

        async function registerServiceWorker() {
            if ('serviceWorker' in navigator) {
                try {
                    const reg = await navigator.serviceWorker.register('/sw.js');
                    const sub = await reg.pushManager.getSubscription();
                    if (sub) { 
                        state.pushSubscription = sub; 
                        state.notificationsEnabled = true;
                        elements.notificationBtn.classList.add('enabled'); 
                        elements.notificationBtn.textContent = '🔔 On'; 
                    }
                } catch (e) { console.error('SW registration failed:', e); }
            }
        }

        async function toggleNotifications() {
            if (isCapacitor && window.Capacitor?.Plugins?.PushNotifications) {
                // Use Capacitor PushNotifications for native apps
                const PushNotifications = window.Capacitor.Plugins.PushNotifications;
                
                if (state.notificationsEnabled) {
                    // Unregister push notifications
                    state.notificationsEnabled = false;
                    state.pushSubscription = null;
                    elements.notificationBtn.classList.remove('enabled');
                    elements.notificationBtn.textContent = '🔔 Notifications';
                } else {
                    try {
                        // Request permission
                        let permissionStatus = await PushNotifications.requestPermissions();
                        
                        if (permissionStatus.receive === 'granted') {
                            // Register for push notifications
                            await PushNotifications.register();
                            state.notificationsEnabled = true;
                            elements.notificationBtn.classList.add('enabled');
                            elements.notificationBtn.textContent = '🔔 On';
                            
                            // Listen for registration
                            PushNotifications.addListener('registration', (token) => {
                                console.log('Push registration success, token: ' + token.value);
                            });
                            
                            // Listen for incoming notifications
                            PushNotifications.addListener('pushNotificationReceived', (notification) => {
                                console.log('Push notification received: ', notification);
                            });
                        }
                    } catch (e) {
                        console.error('Error setting up push notifications:', e);
                        alert('Failed to enable notifications');
                    }
                }
            } else {
                // Use web notifications for PWA
                if (!('Notification' in window)) { alert('Notifications not supported'); return; }
                if (state.notificationsEnabled) {
                    if (state.pushSubscription) {
                        await state.pushSubscription.unsubscribe();
                    }
                    state.notificationsEnabled = false;
                    state.pushSubscription = null;
                    elements.notificationBtn.classList.remove('enabled');
                    elements.notificationBtn.textContent = '🔔 Notifications';
                } else {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        state.notificationsEnabled = true;
                        elements.notificationBtn.classList.add('enabled');
                        elements.notificationBtn.textContent = '🔔 On';
                    }
                }
            }
        }

        function showNotification(msg) {
            // Use web notifications for foreground messages in both PWA and Capacitor
            // Background push notifications (when app is closed) are handled by:
            // - Service Worker for PWA (via sw.js)
            // - Native OS for Capacitor (via PushNotifications plugin)
            if (Notification.permission === 'granted') {
                new Notification(msg.room ? `${msg.username} in ${msg.room}` : msg.username, { 
                    body: msg.body, 
                    icon: '/icon-192.png', 
                    badge: '/icon-192.png', 
                    tag: 'chat-message', 
                    renotify: true 
                });
            }
        }

        let deferredPrompt;
        function setupInstallPrompt() {
            // Don't show install prompts if running in Capacitor native app
            if (isCapacitor) return;
            
            // For Chrome/Android - standard PWA install prompt
            window.addEventListener('beforeinstallprompt', e => {
                e.preventDefault();
                deferredPrompt = e;
                setTimeout(() => { if (!isInStandaloneMode) elements.installPrompt.classList.remove('hidden'); }, 3000);
            });
            elements.installBtn.addEventListener('click', async function() { 
                if (deferredPrompt) { 
                    deferredPrompt.prompt(); 
                    await deferredPrompt.userChoice; 
                    deferredPrompt = null; 
                } 
                elements.installPrompt.classList.add('hidden'); 
            });
            
            // For iOS - show manual instructions
            if (isIOS && !isInStandaloneMode && !localStorage.getItem('ios_install_dismissed')) {
                setTimeout(() => { elements.iosInstallGuide.classList.remove('hidden'); }, 5000);
            }
        }

        setInterval(() => { if (state.ws?.readyState === WebSocket.OPEN) state.ws.send(JSON.stringify({ type: 'ping' })); }, 30000);
        document.addEventListener('visibilitychange', () => { 
            if (!document.hidden && state.username && (!state.ws || state.ws.readyState !== WebSocket.OPEN)) {
                state.isReconnecting = true;
                connectWebSocket(); 
            }
        });

        init();
    </script>
</body>
</html>
