#!/bin/bash
# Generate self-signed SSL certificates for local development

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CERTS_DIR="$SCRIPT_DIR/../certs"

mkdir -p "$CERTS_DIR"

echo "Generating self-signed SSL certificates for localhost..."

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout "$CERTS_DIR/privkey.pem" \
  -out "$CERTS_DIR/fullchain.pem" \
  -subj "/CN=localhost"

echo "Certificates generated successfully in $CERTS_DIR"
echo "  - $CERTS_DIR/fullchain.pem (certificate)"
echo "  - $CERTS_DIR/privkey.pem (private key)"
