# LiveChat PWA Demo

A demonstration repository for implementing a **per-ticket live chat portal** as a Progressive Web App (PWA). This project mirrors the backend stack of [`@jp-pelegrino/wvchd-tarts`](https://github.com/jp-pelegrino/wvchd-tarts) to ensure seamless integration.

---

## Purpose

- **Demo goal:** Validate PWA-based live chat with push notifications before committing to the main helpdesk project.
- **Parity:** Backend stack, Docker deployment, and DB schema match `wvchd-tarts` for easy migration.

---

## Tech Stack

| Component       | Image / Version                |
|-----------------|--------------------------------|
| PHP             | `php:8.5.0-alpine`             |
| Framework       | CodeIgniter 4                  |
| Database        | PostgreSQL 16                  |
| Realtime        | Valkey `valkey:9.0.0-alpine`   |
| Reverse Proxy   | nginx (stable-alpine) + SSL    |
| PWA             | Service Worker + Web Push      |

---

## Architecture

```
┌────────────┐       wss://       ┌─────────────┐
│  PWA Client│◄──────────────────►│   nginx     │
└────────────┘                    │ (SSL + WS)  │
                                  └──────┬──────┘
                          ┌───────────────┼───────────────┐
                          │               │               │
                          ▼               ▼               ▼
                   ┌───────────┐   ┌───────────┐   ┌───────────┐
                   │  php-fpm  │   │  valkey   │   │ postgres  │
                   │ (CI4 API) │   │ (pubsub)  │   │   (DB)    │
                   └───────────┘   └───────────┘   └───────────┘
```

- **nginx** terminates SSL and proxies `/ws/` to Valkey; all other routes go to PHP-FPM.
- **Valkey** handles websocket pub/sub for real-time chat per ticket room (`ticket:{id}`).
- **PHP-FPM** runs CodeIgniter 4 REST API for tickets, messages, users, and push subscriptions.
- **PostgreSQL** stores persistent data (tickets, messages, users, push subscriptions).

---

## Repository Layout

```
livechat-pwa-demo/
├── docs/                   # Documentation (this folder)
│   ├── README.md           # You are here
│   ├── ARCHITECTURE.md     # Detailed architecture notes
│   ├── DOCKER.md           # Docker setup and commands
│   ├── CODEIGNITER.md      # CI4 integration guide
│   └── PWA.md              # PWA and push notification guide
├── docker/
│   └── php/
│       └── Dockerfile      # PHP 8.5 alpine image for CI4
├── nginx/
│   └── default.conf        # nginx config (SSL + websocket proxy)
├── valkey/                 # Optional Valkey config overrides
├── app/                    # CodeIgniter 4 application
├── public/                 # CI4 public dir + PWA assets
├── docker-compose.yml      # Orchestration file
└── .env.example            # Environment variable template
```

---

## Quick Start

```bash
# 1. Clone the repo
git clone https://github.com/jp-pelegrino/livechat-pwa-demo.git
cd livechat-pwa-demo

# 2. Copy environment template
cp .env.example .env
# Edit .env with your DB credentials, VAPID keys, domain, etc.

# 3. Build and start containers
docker compose up --build -d

# 4. Run database migrations (inside php container)
docker compose exec php php spark migrate

# 5. Open https://localhost (accept self-signed cert for local dev)
```

---

## Parity Checklist (with wvchd-tarts)

| Aspect              | Status | Notes                                      |
|---------------------|--------|--------------------------------------------|
| PHP version         | ✅     | `php:8.5.0-alpine`                         |
| CodeIgniter version | ✅     | Same minor version                         |
| PostgreSQL version  | ✅     | 16                                         |
| Valkey version      | ✅     | `9.0.0-alpine`                             |
| Auth scheme         | ✅     | Match JWT/session from main project        |
| REST API routes     | ✅     | Mirror `/api/tickets`, `/api/messages`     |
| DB schema           | ✅     | Identical migrations                       |
| Docker deployment   | ✅     | Same compose patterns, labels, volumes     |

---

## Key Features

1. **Websocket Chat** – Real-time messaging per ticket via Valkey pub/sub.
2. **PWA Installable** – Add-to-homescreen on mobile; works offline for cached assets.
3. **Push Notifications** – VAPID-based Web Push when app is backgrounded or closed.
4. **SSL Ready** – nginx configured for HTTPS and secure websocket (`wss://`).

---

## Environment Variables

See [`.env.example`](../.env.example) for the full list. Key variables:

| Variable              | Description                              |
|-----------------------|------------------------------------------|
| `DB_HOST`             | Postgres hostname (e.g., `postgres`)     |
| `DB_NAME`             | Database name                            |
| `DB_USER` / `DB_PASS` | Database credentials                     |
| `VALKEY_HOST`         | Valkey hostname (e.g., `valkey`)         |
| `VALKEY_PORT`         | Valkey port (default `8080`)             |
| `VAPID_PUBLIC_KEY`    | VAPID public key for Web Push            |
| `VAPID_PRIVATE_KEY`   | VAPID private key for Web Push           |
| `APP_BASE_URL`        | Public URL (e.g., `https://demo.example.com`) |

---

## Documentation Index

- **[ARCHITECTURE.md](./ARCHITECTURE.md)** – Detailed system design and data flow.
- **[DOCKER.md](./DOCKER.md)** – Docker Compose, Dockerfile, and deployment notes.
- **[CODEIGNITER.md](./CODEIGNITER.md)** – CI4 controllers, models, migrations, Valkey integration.
- **[PWA.md](./PWA.md)** – Service worker, manifest, and push notification setup.

---

## License

MIT – See [LICENSE](../LICENSE) for details.
