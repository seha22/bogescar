# Deployment Docker (Hetzner VPS)

Panduan ini untuk deploy production memakai file:
- `docker-compose.prod.yml`
- `Dockerfile.prod`
- `deploy/nginx/default.conf`

## 1) Prasyarat VPS

- Ubuntu 22.04/24.04
- Docker Engine + Docker Compose plugin
- Domain sudah diarahkan ke IP VPS
- Port terbuka: `22`, `80`, `443`

Install Docker cepat:

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker
docker version
docker compose version
```

## 2) Clone project di server

```bash
mkdir -p /var/www
cd /var/www
git clone https://github.com/seha22/bogescar.git bc-admin
cd bc-admin
```

## 3) Siapkan `.env` production

```bash
cp .env.example .env
```

Minimal, sesuaikan nilai ini:

```env
APP_NAME=BogesCar
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=bcadmin
DB_USERNAME=bcadmin_user
DB_PASSWORD=strong_password
DB_ROOT_PASSWORD=strong_root_password

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

Catatan:
- `docker-compose.prod.yml` membaca `.env`.
- Jangan commit `.env`.

## 4) Build dan up container

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

Inisialisasi app:

```bash
docker compose -f docker-compose.prod.yml exec -T app php artisan key:generate
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan storage:link
docker compose -f docker-compose.prod.yml exec -T app php artisan optimize
```

Verifikasi:

```bash
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs --tail=100 nginx
docker compose -f docker-compose.prod.yml logs --tail=100 app
```

## 5) TLS/HTTPS

Disarankan terminasi SSL di reverse proxy host (Nginx/Caddy/Traefik) yang forward ke container `nginx:80`.

## 6) Update release manual

```bash
cd /var/www/bc-admin
git pull origin main
docker compose -f docker-compose.prod.yml up -d --build --remove-orphans
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan optimize
```

## 7) Auto deploy via GitHub Actions

Workflow ada di:
- `.github/workflows/deploy-production.yml`

Isi GitHub repository secrets:
- `VPS_HOST`: IP/domain VPS
- `VPS_USER`: user SSH (contoh `deploy`)
- `VPS_SSH_KEY`: private key SSH
- `VPS_SSH_PORT`: default `22`
- `VPS_APP_DIR`: path project di VPS (contoh `/var/www/bc-admin`)

Setelah secret terisi, setiap push ke branch `main` akan trigger deploy otomatis.
