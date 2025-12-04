#!/bin/sh
set -e

CERTS_DIR="/etc/nginx/certs"

# Generate self-signed SSL certificates if they don't exist
if [ ! -f "$CERTS_DIR/fullchain.pem" ] || [ ! -f "$CERTS_DIR/privkey.pem" ]; then
    echo "SSL certificates not found. Generating self-signed certificates..."
    mkdir -p "$CERTS_DIR"
    
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$CERTS_DIR/privkey.pem" \
        -out "$CERTS_DIR/fullchain.pem" \
        -subj "/CN=localhost" 2>/dev/null
    
    echo "Self-signed SSL certificates generated successfully."
fi

# Execute the original nginx entrypoint with all arguments
exec /docker-entrypoint.sh "$@"
