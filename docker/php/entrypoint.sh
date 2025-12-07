#!/bin/sh
set -e

# Ensure writable directory has correct permissions
if [ -d "/var/www/html/writable" ]; then
    chmod -R 777 /var/www/html/writable
fi

# Install dependencies if vendor directory doesn't exist
if [ ! -d "/var/www/html/vendor" ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html
    composer install --optimize-autoloader
fi

# Execute the main command
exec "$@"
