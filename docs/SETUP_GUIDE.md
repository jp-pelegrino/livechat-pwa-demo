# LiveChat PWA Demo - Setup & Troubleshooting Guide

This document covers the complete setup process, issues encountered, and resolutions for the initial project scaffold.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Quick Start](#quick-start)
3. [Architecture](#architecture)
4. [Setup Issues & Resolutions](#setup-issues--resolutions)
5. [Configuration](#configuration)
6. [API Endpoints](#api-endpoints)
7. [Next Phase: Roadmap](#next-phase-roadmap)

---

## Project Overview

LiveChat PWA Demo is a real-time chat application built with:

- **Backend:** CodeIgniter 4 (PHP 8.3)
- **Database:** PostgreSQL 16
- **Pub/Sub:** Valkey 8.0 (Redis-compatible)
- **Web Server:** nginx with SSL
- **Frontend:** Progressive Web App (PWA)

---

## Quick Start

```bash
# Clone the repository
git clone https://github.com/jp-pelegrino/livechat-pwa-demo.git
cd livechat-pwa-demo

# Start all services (SSL certs are auto-generated)
docker compose up -d

# Run database migrations
docker compose exec php php spark migrate

# Access the application
# HTTPS: https://localhost:7443
# HTTP:  http://localhost:780 (redirects to HTTPS)
```

**No manual certificate generation required** - SSL certificates are automatically created when the nginx container starts for the first time.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Docker Compose                          │
├─────────────┬─────────────┬─────────────┬──────────────────┤
│   nginx     │    PHP      │  PostgreSQL │     Valkey       │
│  (reverse   │  (CI4 app)  │   (data)    │   (pub/sub)      │
│   proxy)    │             │             │                  │
│  :780/:7443 │    :9000    │    :5432    │     :6379        │
└─────────────┴─────────────┴─────────────┴──────────────────┘
```

### Services

| Service  | Image                    | Purpose                      |
|----------|--------------------------|------------------------------|
| nginx    | Custom (nginx:stable-alpine) | HTTPS termination, reverse proxy |
| php      | Custom (php:8.3-fpm-alpine)  | CodeIgniter 4 application    |
| postgres | postgres:16              | Primary database             |
| valkey   | valkey/valkey:8.0-alpine | Real-time pub/sub messaging  |

---

## Setup Issues & Resolutions

### Issue 1: Alpine Package Names

**Error:**
```
ERROR: unable to select packages:
  curl-dev (no such package)
  icu-dev (no such package)
  postgresql-dev (no such package)
```

**Cause:** Incorrect package names for Alpine Linux.

**Resolution:**
- Changed `postgresql-dev` → `libpq-dev`
- Removed `curl-dev` (already built into PHP)
- Added build dependencies: `autoconf`, `g++`, `gcc`, `make`, `musl-dev`

---

### Issue 2: DNS Transient Errors During Build

**Error:**
```
WARNING: fetching ... DNS: transient error (try again later)
ERROR: unable to select packages
```

**Cause:** Network connectivity issues during Docker build.

**Resolution:**
- Added `apk update` before package installation
- Added retry logic with 5-second delay
- If persistent, check Docker DNS settings

---

### Issue 3: Vendor Directory Missing

**Error:**
```
Failed opening required '.../vendor/codeigniter4/framework/system/Boot.php'
```

**Cause:** Volume mount (`./:/var/www/html`) overwrites the container's `vendor/` directory installed during build.

**Resolution:**
- Created entrypoint script that runs `composer install` at startup if `vendor/` doesn't exist
- Dependencies are installed automatically on first container run

---

### Issue 4: Windows Line Endings (CRLF)

**Error:**
```
exec /usr/local/bin/entrypoint.sh: no such file or directory
```

**Cause:** Git on Windows converts shell scripts to CRLF line endings.

**Resolution:**
- Added `dos2unix` in Dockerfile to convert scripts to Unix line endings
- Added `.gitattributes` to enforce LF for shell scripts:
  ```
  *.sh text eol=lf
  ```

---

### Issue 5: SSL Certificate Not Found

**Error:**
```
nginx: [emerg] cannot load certificate "/etc/ssl/certs/fullchain.pem"
```

**Cause:** SSL certificates didn't exist on first run.

**Resolution:**
- Created custom nginx Dockerfile with openssl installed
- Added entrypoint script that auto-generates self-signed certificates if they don't exist
- Certificates are persisted to `./certs/` via volume mount

---

### Issue 6: PostgreSQL Extension Missing

**Error:**
```
The required PHP extension "pgsql" is not loaded
```

**Cause:** CI4's PostgreSQL driver requires both `pdo_pgsql` and `pgsql` extensions.

**Resolution:**
- Added `pgsql` extension to PHP Dockerfile alongside `pdo_pgsql`

---

### Issue 7: Writable Directory Permissions

**Error:**
```
Unable to write to cache or logs directory
```

**Cause:** PHP process couldn't write to CI4's writable directories.

**Resolution:**
- Added `chmod -R 777 /var/www/html/writable` in entrypoint script

---

## Configuration

### Environment Variables (`.env`)

Copy `.env.example` to `.env` and customize:

```bash
cp .env.example .env
```

| Variable       | Default         | Description                    |
|----------------|-----------------|--------------------------------|
| `APP_ENV`      | `development`   | Application environment        |
| `APP_BASE_URL` | `https://localhost:7443` | Base URL for the app |
| `DB_HOST`      | `postgres`      | Database hostname              |
| `DB_PORT`      | `5432`          | Database port                  |
| `DB_NAME`      | `livechat_demo` | Database name                  |
| `DB_USER`      | `ci`            | Database username              |
| `DB_PASS`      | `secret`        | Database password              |
| `VALKEY_HOST`  | `valkey`        | Valkey hostname                |
| `VALKEY_PORT`  | `6379`          | Valkey port                    |
| `HTTP_PORT`    | `780`           | External HTTP port             |
| `HTTPS_PORT`   | `7443`          | External HTTPS port            |

### Cloudflare Tunnel Setup

For exposing the local HTTPS server via Cloudflare tunnel:

```bash
# Local: https://localhost:7443
# Tunnel: cloudflared tunnel --url https://localhost:7443
# Public: https://chat.yourdomain.com
```

---

## API Endpoints

### Messages API

| Method | Endpoint                        | Description           |
|--------|----------------------------------|----------------------|
| GET    | `/api/tickets/{id}/messages`    | List ticket messages  |
| POST   | `/api/tickets/{id}/messages`    | Create new message    |

**Example: Create Message**
```bash
curl -k -X POST https://localhost:7443/api/tickets/1/messages \
  -H "Content-Type: application/json" \
  -d '{"body": "Hello, World!"}'
```

**Response:**
```json
{
  "id": 1,
  "ticket_id": "1",
  "sender_id": 1,
  "body": "Hello, World!",
  "created_at": "2025-12-04 12:00:00"
}
```

---

## Next Phase: Roadmap

The current scaffold displays the CodeIgniter 4 welcome page. Here's the recommended next phase of development:

### Phase 2: Core Chat Functionality

1. **User Authentication**
   - Login/Registration system
   - Session management
   - JWT tokens for API authentication

2. **Ticket System**
   - Create `tickets` table migration
   - CRUD endpoints for tickets
   - Ticket status management (open, closed, pending)

3. **Real-time Messaging**
   - WebSocket integration via Valkey
   - Message broadcasting to connected clients
   - Online presence indicators

### Phase 3: Frontend Chat UI

1. **Chat Interface**
   - Message list component
   - Message input with send button
   - Ticket/conversation selector

2. **Real-time Updates**
   - WebSocket client connection
   - Live message updates without refresh
   - Typing indicators

3. **PWA Features**
   - Push notification subscriptions
   - Offline message queue
   - App install prompt

### Phase 4: Production Readiness

1. **Security**
   - Input validation & sanitization
   - CSRF protection
   - Rate limiting

2. **SSL/TLS**
   - Let's Encrypt automation
   - Certificate renewal

3. **Monitoring**
   - Error logging
   - Performance metrics
   - Health checks

### Suggested Next Steps (Immediate)

1. Create a custom homepage replacing the CI4 welcome page
2. Implement user registration/login
3. Build the tickets CRUD API
4. Create a basic chat UI (can start with vanilla JS or Alpine.js)

---

## Useful Commands

```bash
# Rebuild containers
docker compose build --no-cache

# View logs
docker compose logs -f

# View specific service logs
docker compose logs -f nginx
docker compose logs -f php

# Run migrations
docker compose exec php php spark migrate

# Rollback migrations
docker compose exec php php spark migrate:rollback

# Access PHP container shell
docker compose exec php sh

# Access PostgreSQL
docker compose exec postgres psql -U ci -d livechat_demo

# Stop all containers
docker compose down

# Stop and remove volumes
docker compose down -v
```

---

## File Structure

```
livechat-pwa-demo/
├── app/
│   ├── Config/
│   │   ├── Database.php      # DB configuration (reads env vars)
│   │   └── Routes.php        # API routes
│   ├── Controllers/
│   │   └── Api/
│   │       └── MessagesController.php
│   ├── Database/
│   │   └── Migrations/
│   │       └── 2024-01-01-000001_CreateMessages.php
│   └── Libraries/
│       └── ValkeyClient.php  # Pub/sub wrapper
├── docker/
│   ├── nginx/
│   │   ├── Dockerfile        # Custom nginx with openssl
│   │   └── entrypoint.sh     # Auto SSL generation
│   └── php/
│       ├── Dockerfile        # PHP-FPM with extensions
│       └── entrypoint.sh     # Composer install & permissions
├── nginx/
│   └── default.conf          # nginx site configuration
├── public/
│   ├── index.php             # CI4 front controller
│   ├── manifest.json         # PWA manifest
│   └── sw.js                 # Service worker
├── scripts/
│   └── generate-certs.sh     # Manual SSL cert generation
├── certs/                    # Auto-generated SSL certs (gitignored)
├── .env.example              # Environment template
├── .gitattributes            # Line ending rules
├── docker-compose.yml        # Service orchestration
└── composer.json             # PHP dependencies
```

---

## Support

For issues or questions, please open a GitHub issue in the repository.
