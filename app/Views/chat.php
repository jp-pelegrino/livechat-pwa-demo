<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LiveChat">
    <title>LiveChat Demo</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Username Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .modal h2 {
            margin-bottom: 1rem;
            color: #333;
        }

        .modal input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .modal input:focus {
            outline: none;
            border-color: #0d6efd;
        }

        .modal button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .modal button:hover {
            background: #0b5ed7;
        }

        .hidden {
            display: none !important;
        }

        /* Header */
        .header {
            background: #0d6efd;
            color: white;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .header .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .header .user-avatar {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .notification-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-left: 10px;
        }

        .notification-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .notification-btn.enabled {
            background: #28a745;
        }

        /* Room Selector */
        .room-selector {
            background: white;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
        }

        .room-btn {
            padding: 8px 16px;
            border: 2px solid #0d6efd;
            background: white;
            color: #0d6efd;
            border-radius: 20px;
            cursor: pointer;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .room-btn.active {
            background: #0d6efd;
            color: white;
        }

        /* Messages Container */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .message {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 16px;
            position: relative;
            word-wrap: break-word;
        }

        .message.received {
            background: white;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .message.sent {
            background: #0d6efd;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message .username {
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 4px;
            opacity: 0.8;
        }

        .message.sent .username {
            color: rgba(255,255,255,0.8);
        }

        .message .text {
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .message .time {
            font-size: 0.7rem;
            opacity: 0.6;
            margin-top: 4px;
            text-align: right;
        }

        .system-message {
            text-align: center;
            color: #666;
            font-size: 0.85rem;
            padding: 0.5rem;
        }

        /* Input Area */
        .input-area {
            background: white;
            padding: 1rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }

        .input-area textarea {
            flex: 1;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 24px;
            resize: none;
            font-size: 16px;
            font-family: inherit;
            max-height: 120px;
            line-height: 1.4;
        }

        .input-area textarea:focus {
            outline: none;
            border-color: #0d6efd;
        }

        .send-btn {
            width: 48px;
            height: 48px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .send-btn:hover {
            background: #0b5ed7;
        }

        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .send-btn svg {
            width: 24px;
            height: 24px;
        }

        /* Loading & Status */
        .status-bar {
            background: #fff3cd;
            color: #856404;
            padding: 8px;
            text-align: center;
            font-size: 0.85rem;
        }

        .status-bar.connected {
            background: #d4edda;
            color: #155724;
        }

        .status-bar.error {
            background: #f8d7da;
            color: #721c24;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            color: #666;
            padding: 2rem;
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Install prompt */
        .install-prompt {
            position: fixed;
            bottom: 80px;
            left: 1rem;
            right: 1rem;
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 100;
        }

        .install-prompt .icon {
            width: 48px;
            height: 48px;
            background: #0d6efd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .install-prompt .content {
            flex: 1;
        }

        .install-prompt h3 {
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .install-prompt p {
            font-size: 0.8rem;
            color: #666;
        }

        .install-prompt button {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .install-prompt .install-btn {
            background: #0d6efd;
            color: white;
        }

        .install-prompt .dismiss-btn {
            background: #eee;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Username Modal -->
    <div id="usernameModal" class="modal-overlay">
        <div class="modal">
            <h2>👋 Welcome to LiveChat</h2>
            <p style="margin-bottom: 1rem; color: #666;">Enter your name to start chatting</p>
            <input type="text" id="usernameInput" placeholder="Your name" maxlength="50" autocomplete="off">
            <button id="joinBtn">Join Chat</button>
        </div>
    </div>

    <!-- Install Prompt (hidden by default) -->
    <div id="installPrompt" class="install-prompt hidden">
        <div class="icon">💬</div>
        <div class="content">
            <h3>Install LiveChat</h3>
            <p>Add to home screen for the best experience</p>
        </div>
        <button class="install-btn" id="installBtn">Install</button>
        <button class="dismiss-btn" id="dismissInstall">✕</button>
    </div>

    <!-- Main Chat Interface -->
    <div id="chatInterface" class="hidden">
        <!-- Header -->
        <header class="header">
            <h1>💬 LiveChat Demo</h1>
            <div class="user-info">
                <div class="user-avatar" id="userAvatar">?</div>
                <span id="displayUsername">Guest</span>
                <button class="notification-btn" id="notificationBtn" title="Enable notifications">
                    🔔 Notifications
                </button>
            </div>
        </header>

        <!-- Room Selector -->
        <div class="room-selector">
            <button class="room-btn active" data-room="1">💬 General</button>
            <button class="room-btn" data-room="2">🆘 Support</button>
            <button class="room-btn" data-room="3">🎉 Random</button>
        </div>

        <!-- Status Bar -->
        <div id="statusBar" class="status-bar connected">Connected</div>

        <!-- Messages -->
        <div class="messages-container" id="messagesContainer">
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p>No messages yet. Be the first to say hello! 👋</p>
            </div>
        </div>

        <!-- Input Area -->
        <div class="input-area">
            <textarea id="messageInput" placeholder="Type a message..." rows="1"></textarea>
            <button class="send-btn" id="sendBtn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </div>
    </div>

    <script>
        // App State
        const state = {
            username: localStorage.getItem('chat_username') || '',
            currentRoom: 1,
            messages: [],
            lastMessageId: 0,
            polling: null,
            pushSubscription: null
        };

        // DOM Elements
        const elements = {
            modal: document.getElementById('usernameModal'),
            usernameInput: document.getElementById('usernameInput'),
            joinBtn: document.getElementById('joinBtn'),
            chatInterface: document.getElementById('chatInterface'),
            messagesContainer: document.getElementById('messagesContainer'),
            messageInput: document.getElementById('messageInput'),
            sendBtn: document.getElementById('sendBtn'),
            statusBar: document.getElementById('statusBar'),
            displayUsername: document.getElementById('displayUsername'),
            userAvatar: document.getElementById('userAvatar'),
            roomBtns: document.querySelectorAll('.room-btn'),
            notificationBtn: document.getElementById('notificationBtn'),
            installPrompt: document.getElementById('installPrompt'),
            installBtn: document.getElementById('installBtn'),
            dismissInstall: document.getElementById('dismissInstall')
        };

        // Initialize
        function init() {
            if (state.username) {
                showChat();
            }

            // Event Listeners
            elements.joinBtn.addEventListener('click', joinChat);
            elements.usernameInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') joinChat();
            });
            elements.sendBtn.addEventListener('click', sendMessage);
            elements.messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            elements.messageInput.addEventListener('input', autoResizeTextarea);
            elements.roomBtns.forEach(btn => {
                btn.addEventListener('click', () => switchRoom(parseInt(btn.dataset.room)));
            });
            elements.notificationBtn.addEventListener('click', toggleNotifications);
            elements.dismissInstall.addEventListener('click', () => {
                elements.installPrompt.classList.add('hidden');
            });

            // Register Service Worker
            registerServiceWorker();

            // PWA Install Prompt
            setupInstallPrompt();
        }

        function joinChat() {
            const username = elements.usernameInput.value.trim();
            if (!username) {
                elements.usernameInput.style.borderColor = '#dc3545';
                return;
            }
            state.username = username;
            localStorage.setItem('chat_username', username);
            showChat();
        }

        function showChat() {
            elements.modal.classList.add('hidden');
            elements.chatInterface.classList.remove('hidden');
            elements.displayUsername.textContent = state.username;
            elements.userAvatar.textContent = state.username.charAt(0).toUpperCase();
            loadMessages();
            startPolling();
        }

        async function loadMessages() {
            try {
                const response = await fetch(`/api/tickets/${state.currentRoom}/messages`);
                if (!response.ok) throw new Error('Failed to load messages');
                
                const messages = await response.json();
                state.messages = messages;
                state.lastMessageId = messages.length > 0 ? Math.max(...messages.map(m => m.id)) : 0;
                renderMessages();
                updateStatus('connected', 'Connected');
            } catch (error) {
                console.error('Error loading messages:', error);
                updateStatus('error', 'Connection error. Retrying...');
            }
        }

        async function pollForNewMessages() {
            try {
                const response = await fetch(`/api/tickets/${state.currentRoom}/messages?since=${state.lastMessageId}`);
                if (!response.ok) throw new Error('Failed to poll messages');
                
                const newMessages = await response.json();
                if (newMessages.length > 0) {
                    state.messages.push(...newMessages);
                    state.lastMessageId = Math.max(...newMessages.map(m => m.id));
                    renderMessages();
                    
                    // Show notification for messages from others
                    const fromOthers = newMessages.filter(m => m.username !== state.username);
                    if (fromOthers.length > 0 && document.hidden) {
                        showNotification(fromOthers[fromOthers.length - 1]);
                    }
                }
                updateStatus('connected', 'Connected');
            } catch (error) {
                console.error('Polling error:', error);
                updateStatus('error', 'Connection lost. Reconnecting...');
            }
        }

        function startPolling() {
            if (state.polling) clearInterval(state.polling);
            state.polling = setInterval(pollForNewMessages, 2000);
        }

        function stopPolling() {
            if (state.polling) {
                clearInterval(state.polling);
                state.polling = null;
            }
        }

        async function sendMessage() {
            const text = elements.messageInput.value.trim();
            if (!text) return;

            elements.sendBtn.disabled = true;
            elements.messageInput.value = '';
            autoResizeTextarea();

            try {
                const response = await fetch(`/api/tickets/${state.currentRoom}/messages`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        username: state.username,
                        body: text
                    })
                });

                if (!response.ok) throw new Error('Failed to send message');

                const message = await response.json();
                state.messages.push(message);
                state.lastMessageId = message.id;
                renderMessages();
            } catch (error) {
                console.error('Error sending message:', error);
                updateStatus('error', 'Failed to send message');
                elements.messageInput.value = text;
            } finally {
                elements.sendBtn.disabled = false;
                elements.messageInput.focus();
            }
        }

        function renderMessages() {
            if (state.messages.length === 0) {
                elements.messagesContainer.innerHTML = `
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p>No messages yet. Be the first to say hello! 👋</p>
                    </div>
                `;
                return;
            }

            elements.messagesContainer.innerHTML = state.messages.map(msg => {
                const isSent = msg.username === state.username;
                const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                return `
                    <div class="message ${isSent ? 'sent' : 'received'}">
                        <div class="username">${escapeHtml(msg.username || 'Anonymous')}</div>
                        <div class="text">${escapeHtml(msg.body)}</div>
                        <div class="time">${time}</div>
                    </div>
                `;
            }).join('');

            // Scroll to bottom
            elements.messagesContainer.scrollTop = elements.messagesContainer.scrollHeight;
        }

        function switchRoom(roomId) {
            state.currentRoom = roomId;
            state.messages = [];
            state.lastMessageId = 0;
            
            elements.roomBtns.forEach(btn => {
                btn.classList.toggle('active', parseInt(btn.dataset.room) === roomId);
            });
            
            loadMessages();
        }

        function updateStatus(type, message) {
            elements.statusBar.className = `status-bar ${type}`;
            elements.statusBar.textContent = message;
        }

        function autoResizeTextarea() {
            const textarea = elements.messageInput;
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Service Worker & Push Notifications
        async function registerServiceWorker() {
            if ('serviceWorker' in navigator) {
                try {
                    const registration = await navigator.serviceWorker.register('/sw.js');
                    console.log('Service Worker registered:', registration);
                    
                    // Check push subscription
                    const subscription = await registration.pushManager.getSubscription();
                    if (subscription) {
                        state.pushSubscription = subscription;
                        elements.notificationBtn.classList.add('enabled');
                        elements.notificationBtn.textContent = '🔔 On';
                    }
                } catch (error) {
                    console.error('Service Worker registration failed:', error);
                }
            }
        }

        async function toggleNotifications() {
            if (!('Notification' in window)) {
                alert('Notifications are not supported in this browser');
                return;
            }

            if (state.pushSubscription) {
                // Unsubscribe
                await state.pushSubscription.unsubscribe();
                state.pushSubscription = null;
                elements.notificationBtn.classList.remove('enabled');
                elements.notificationBtn.textContent = '🔔 Notifications';
            } else {
                // Subscribe
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    try {
                        const registration = await navigator.serviceWorker.ready;
                        // Note: In production, you'd use VAPID keys here
                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(
                                'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuBkr3qBUYIHBQFLXYp5Nksh8U'
                            )
                        });
                        state.pushSubscription = subscription;
                        elements.notificationBtn.classList.add('enabled');
                        elements.notificationBtn.textContent = '🔔 On';
                        
                        // In production, send subscription to server
                        console.log('Push subscription:', JSON.stringify(subscription));
                    } catch (error) {
                        console.error('Push subscription failed:', error);
                        // Fallback to local notifications
                        elements.notificationBtn.classList.add('enabled');
                        elements.notificationBtn.textContent = '🔔 On';
                    }
                }
            }
        }

        function showNotification(message) {
            if (Notification.permission === 'granted') {
                new Notification(`${message.username}`, {
                    body: message.body,
                    icon: '/icon-192.png',
                    badge: '/icon-192.png',
                    tag: 'chat-message',
                    renotify: true
                });
            }
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        // PWA Install Prompt
        let deferredPrompt;

        function setupInstallPrompt() {
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                
                // Show install prompt after a delay
                setTimeout(() => {
                    if (!window.matchMedia('(display-mode: standalone)').matches) {
                        elements.installPrompt.classList.remove('hidden');
                    }
                }, 3000);
            });

            elements.installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log('Install prompt outcome:', outcome);
                    deferredPrompt = null;
                }
                elements.installPrompt.classList.add('hidden');
            });
        }

        // Visibility change - pause/resume polling
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopPolling();
            } else if (state.username) {
                startPolling();
                pollForNewMessages(); // Immediate poll on return
            }
        });

        // Initialize app
        init();
    </script>
</body>
</html>
