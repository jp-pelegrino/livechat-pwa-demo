FROM node:20-alpine

WORKDIR /app

# Copy package files
COPY websocket/package*.json ./

# Install dependencies
RUN npm install --production

# Copy server code
COPY websocket/server.js ./

# Expose WebSocket port
EXPOSE 8080

# Start server
CMD ["node", "server.js"]
