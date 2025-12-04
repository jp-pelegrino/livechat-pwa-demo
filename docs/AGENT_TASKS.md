# Agent Task Instructions

Concise instructions for AI agents working on the initial pull request and project scaffold.

---

## PR #1: Project Scaffold

**Goal:** Set up a runnable skeleton with Docker, CI4, Valkey, Postgres, nginx, and PWA boilerplate.

### Tasks

| #  | Task                        | Files to Create/Edit                              |
|----|-----------------------------|---------------------------------------------------|
| 1  | Docker Compose              | `docker-compose.yml`                              |
| 2  | PHP Dockerfile              | `docker/php/Dockerfile`                           |
| 3  | nginx config                | `nginx/default.conf`                              |
| 4  | Environment template        | `.env.example`                                    |
| 5  | CI4 skeleton                | `app/`, `public/index.php`, `spark`               |
| 6  | PWA manifest & SW stub      | `public/manifest.json`, `public/sw.js`            |
| 7  | DB migration (messages)     | `app/Database/Migrations/..._CreateMessages.php`  |
| 8  | Basic REST endpoints        | `app/Controllers/Api/TicketsController.php`, etc. |
| 9  | Valkey integration helper   | `app/Libraries/ValkeyClient.php`                  |
| 10 | Self-signed certs (dev)     | `certs/` (gitignored, generated via script)       |

---

## Task Details

### 1. Docker Compose (`docker-compose.yml`)

- Services: `postgres`, `php`, `valkey`, `nginx`.
- Use images: `postgres:16`, `php:8.5.0-alpine` (build), `valkey:9.0.0-alpine`, `nginx:stable-alpine`.
- Volumes: `pgdata`, mount `./` to `/var/www/html`, mount `./nginx/default.conf`, mount `./certs`.
- Networks: single default bridge is fine.
- Expose ports: `80`, `443` (nginx), `8080` optional for Valkey direct access during dev.

### 2. PHP Dockerfile (`docker/php/Dockerfile`)

- Base: `php:8.5.0-alpine`.
- Install extensions: `pdo_pgsql`, `opcache`, `intl`, `zip`, `mbstring`, `curl`.
- Install Composer.
- Set `WORKDIR /var/www/html`.
- Run `composer install --no-dev` in build.

### 3. nginx Config (`nginx/default.conf`)

- Listen `443 ssl` with cert paths `/etc/ssl/certs/fullchain.pem`, `/etc/ssl/private/privkey.pem`.
- Redirect `80 → 443`.
- Location `/ws/` proxy to `http://valkey:8080/` with websocket upgrade headers.
- Location `/` try CI4 front controller (`index.php`).
- Location `~ \.php$` fastcgi_pass to `php:9000`.

### 4. Environment Template (`.env.example`)

Include at minimum:

```
APP_ENV=development
APP_BASE_URL=https://localhost

DB_HOST=postgres
DB_PORT=5432
DB_NAME=livechat_demo
DB_USER=ci
DB_PASS=secret

VALKEY_HOST=valkey
VALKEY_PORT=8080

VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=

SSL_CERT_PATH=/etc/ssl/certs/fullchain.pem
SSL_KEY_PATH=/etc/ssl/private/privkey.pem
```

### 5. CI4 Skeleton

- Use `composer create-project codeigniter4/appstarter app` or copy minimal structure.
- Ensure `public/index.php` and `spark` CLI work.
- Configure `app/Config/Database.php` to read env vars.

### 6. PWA Manifest & Service Worker Stub

`public/manifest.json`:

```json
{
  "name": "LiveChat Demo",
  "short_name": "LiveChat",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#0d6efd",
  "icons": [
    { "src": "/icon-192.png", "sizes": "192x192", "type": "image/png" },
    { "src": "/icon-512.png", "sizes": "512x512", "type": "image/png" }
  ]
}
```

`public/sw.js`:

```js
self.addEventListener('install', e => self.skipWaiting());
self.addEventListener('activate', e => e.waitUntil(clients.claim()));
self.addEventListener('push', e => {
  const data = e.data?.json() || {};
  e.waitUntil(self.registration.showNotification(data.title || 'New Message', { body: data.body }));
});
```

### 7. DB Migration (Messages Table)

Create `app/Database/Migrations/2024-01-01-000001_CreateMessages.php`:

```php
public function up() {
    $this->forge->addField([
        'id'         => ['type' => 'SERIAL'],
        'ticket_id'  => ['type' => 'INT', 'unsigned' => true],
        'sender_id'  => ['type' => 'INT', 'unsigned' => true],
        'body'       => ['type' => 'TEXT'],
        'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
    ]);
    $this->forge->addPrimaryKey('id');
    $this->forge->createTable('messages');
}
```

### 8. Basic REST Endpoints

`app/Controllers/Api/MessagesController.php`:

- `GET /api/tickets/{id}/messages` → list messages for ticket.
- `POST /api/tickets/{id}/messages` → store new message, publish to Valkey.

Use CI4 RESTful resource pattern; authenticate via session or JWT (match main project).

### 9. Valkey Integration Helper

`app/Libraries/ValkeyClient.php`:

- Wrapper to publish messages to Valkey HTTP API (`POST /publish`).
- Read `VALKEY_HOST`, `VALKEY_PORT` from env.
- Method: `publish(string $channel, array $payload): bool`.

### 10. Self-Signed Certs Script

Add `scripts/generate-certs.sh`:

```bash
#!/bin/bash
mkdir -p certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout certs/privkey.pem -out certs/fullchain.pem \
  -subj "/CN=localhost"
```

Add `certs/` to `.gitignore`.

---

## Acceptance Criteria

- [ ] `docker compose up --build` starts all 4 services without errors.
- [ ] `docker compose exec php php spark migrate` applies migrations.
- [ ] `https://localhost` shows CI4 welcome or custom index.
- [ ] `wss://localhost/ws/` upgrades to websocket (test with `wscat`).
- [ ] `POST /api/tickets/1/messages` stores a message and returns 201.
- [ ] PWA manifest recognized in Chrome DevTools → Application tab.
- [ ] Service worker registers without console errors.

---

## Notes for Agents

- **Parity:** Keep file structure, env var names, and migration schemas identical to `wvchd-tarts` where possible.
- **Minimal scope:** Do not implement full auth or ticket CRUD yet—stubs are fine.
- **Commit style:** One commit per task or logical grouping; use conventional commits (`feat:`, `chore:`).
- **Testing:** After each task, run a quick smoke test (container up, endpoint reachable).

---

## Next PRs (out of scope for PR #1)

- Full ticket CRUD and user auth.
- Push subscription persistence and delivery.
- Frontend chat UI (Vue/Alpine/vanilla).
- Production SSL (Let's Encrypt automation).
