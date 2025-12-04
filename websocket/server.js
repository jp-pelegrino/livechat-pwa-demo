/**
 * WebSocket Server for LiveChat
 * Handles real-time messaging with Valkey pub/sub
 */

const WebSocket = require('ws');
const Redis = require('ioredis');

const WS_PORT = process.env.WS_PORT || 8080;
const VALKEY_HOST = process.env.VALKEY_HOST || 'valkey';
const VALKEY_PORT = process.env.VALKEY_PORT || 6379;

// WebSocket Server
const wss = new WebSocket.Server({ port: WS_PORT });

// Redis clients for pub/sub
const subscriber = new Redis({ host: VALKEY_HOST, port: VALKEY_PORT });
const publisher = new Redis({ host: VALKEY_HOST, port: VALKEY_PORT });

// Track clients by room
const rooms = new Map(); // roomId -> Set of WebSocket clients
const clientRooms = new Map(); // WebSocket -> Set of roomIds

console.log(`WebSocket server starting on port ${WS_PORT}...`);

// Subscribe to all room messages
subscriber.psubscribe('room:*', (err, count) => {
    if (err) {
        console.error('Failed to subscribe to Valkey:', err);
        return;
    }
    console.log(`Subscribed to ${count} Valkey patterns`);
});

// Handle messages from Valkey
subscriber.on('pmessage', (pattern, channel, message) => {
    console.log(`Valkey message on ${channel}:`, message);
    
    // Extract room ID from channel (room:123)
    // Validate the channel format before processing
    if (!channel.startsWith('room:') || !channel.includes(':')) {
        console.warn(`Ignoring message on unexpected channel format: ${channel}`);
        return;
    }
    const roomId = channel.split(':')[1];
    if (!roomId) {
        console.warn(`Invalid room ID in channel: ${channel}`);
        return;
    }
    
    // Broadcast to all clients in this room
    const roomClients = rooms.get(roomId) || new Set();
    const payload = {
        type: 'message',
        data: JSON.parse(message)
    };
    
    roomClients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify(payload));
        }
    });
});

// Handle WebSocket connections
wss.on('connection', (ws, req) => {
    console.log('New WebSocket connection');
    
    // Initialize client state
    clientRooms.set(ws, new Set());
    
    // Send welcome message
    ws.send(JSON.stringify({
        type: 'connected',
        data: { message: 'Connected to LiveChat WebSocket server' }
    }));
    
    // Handle incoming messages
    ws.on('message', async (data) => {
        try {
            const message = JSON.parse(data.toString());
            console.log('Received:', message);
            
            switch (message.type) {
                case 'join':
                    handleJoin(ws, message.roomId, message.username);
                    break;
                    
                case 'leave':
                    handleLeave(ws, message.roomId);
                    break;
                    
                case 'message':
                    handleMessage(ws, message);
                    break;
                    
                case 'typing':
                    handleTyping(ws, message);
                    break;
                    
                case 'ping':
                    ws.send(JSON.stringify({ type: 'pong' }));
                    break;
                    
                default:
                    console.log('Unknown message type:', message.type);
            }
        } catch (err) {
            console.error('Error handling message:', err);
            ws.send(JSON.stringify({
                type: 'error',
                data: { message: 'Invalid message format' }
            }));
        }
    });
    
    // Handle disconnection
    ws.on('close', () => {
        console.log('Client disconnected');
        
        // Remove from all rooms
        const rooms_joined = clientRooms.get(ws) || new Set();
        rooms_joined.forEach(roomId => {
            const roomClients = rooms.get(roomId);
            if (roomClients) {
                roomClients.delete(ws);
                if (roomClients.size === 0) {
                    rooms.delete(roomId);
                }
            }
        });
        
        clientRooms.delete(ws);
    });
    
    // Handle errors
    ws.on('error', (err) => {
        console.error('WebSocket error:', err);
    });
});

// Join a room
function handleJoin(ws, roomId, username) {
    if (!roomId) {
        ws.send(JSON.stringify({
            type: 'error',
            data: { message: 'Room ID is required' }
        }));
        return;
    }
    
    // Add to room
    if (!rooms.has(roomId)) {
        rooms.set(roomId, new Set());
    }
    rooms.get(roomId).add(ws);
    clientRooms.get(ws).add(roomId);
    
    // Store username on the socket
    ws.username = username;
    ws.currentRoom = roomId;
    
    // Notify client
    ws.send(JSON.stringify({
        type: 'joined',
        data: {
            roomId,
            members: rooms.get(roomId).size
        }
    }));
    
    // Broadcast join to room
    broadcastToRoom(roomId, {
        type: 'user_joined',
        data: {
            username,
            roomId,
            members: rooms.get(roomId).size
        }
    }, ws);
    
    console.log(`User ${username} joined room ${roomId}, ${rooms.get(roomId).size} members`);
}

// Leave a room
function handleLeave(ws, roomId) {
    const roomClients = rooms.get(roomId);
    if (roomClients) {
        roomClients.delete(ws);
        
        if (roomClients.size === 0) {
            rooms.delete(roomId);
        } else {
            // Broadcast leave to room
            broadcastToRoom(roomId, {
                type: 'user_left',
                data: {
                    username: ws.username,
                    roomId,
                    members: roomClients.size
                }
            });
        }
    }
    
    const clientRoomSet = clientRooms.get(ws);
    if (clientRoomSet) {
        clientRoomSet.delete(roomId);
    }
    
    ws.send(JSON.stringify({
        type: 'left',
        data: { roomId }
    }));
    
    console.log(`User ${ws.username} left room ${roomId}`);
}

// Handle chat message (publish to Valkey for all subscribers including this server)
async function handleMessage(ws, message) {
    const { roomId, body, username } = message;
    
    if (!roomId || !body) {
        ws.send(JSON.stringify({
            type: 'error',
            data: { message: 'Room ID and message body are required' }
        }));
        return;
    }
    
    // Generate a unique temporary ID using timestamp + random component
    const tempId = `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    
    const payload = {
        id: tempId, // Temporary ID, will be replaced by DB ID
        roomId,
        username: username || ws.username || 'Anonymous',
        body,
        created_at: new Date().toISOString(),
        temp: true // Mark as temporary until saved to DB
    };
    
    // Publish to Valkey (will be received by pmessage handler)
    await publisher.publish(`room:${roomId}`, JSON.stringify(payload));
    
    console.log(`Message in room ${roomId}: ${body}`);
}

// Handle typing indicator
function handleTyping(ws, message) {
    const { roomId, isTyping } = message;
    
    broadcastToRoom(roomId, {
        type: 'typing',
        data: {
            username: ws.username,
            isTyping
        }
    }, ws);
}

// Broadcast message to all clients in a room except sender
function broadcastToRoom(roomId, message, excludeWs = null) {
    const roomClients = rooms.get(roomId);
    if (!roomClients) return;
    
    const payload = JSON.stringify(message);
    roomClients.forEach(client => {
        if (client !== excludeWs && client.readyState === WebSocket.OPEN) {
            client.send(payload);
        }
    });
}

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('Shutting down...');
    subscriber.quit();
    publisher.quit();
    wss.close(() => {
        console.log('WebSocket server closed');
        process.exit(0);
    });
});

console.log(`WebSocket server running on ws://0.0.0.0:${WS_PORT}`);
