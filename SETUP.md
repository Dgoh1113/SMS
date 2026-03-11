# SMS Setup Guide

## Windows + Docker Firebird limitation

When running **PHP on Windows** and **Firebird in Docker** on the same machine, you may get:
`I/O error during "CreateFile (open)" operation for file "/var/lib/firebird/data/SMS.FDB"`

**Workarounds:**
1. **Use the remote server** (friend's server at 192.168.101.239) once it's set up
2. **Run Laravel in WSL2** so PHP and Firebird share the same Docker network
3. **Install Firebird natively on Windows** and use `DB_DATABASE=C:/SQLSMS/SMS.FDB`

---

## Option A: Local development (Firebird on your PC)

### 1. Start Firebird

```powershell
docker compose up -d
```

### 2. Set `.env` for local

```env
DB_CONNECTION=firebird
DB_HOST=127.0.0.1
DB_PORT=3050
DB_DATABASE=/var/lib/firebird/data/SMS.FDB
DB_USERNAME=SYSDBA
DB_PASSWORD=masterkey
```

### 3. Clear config & run migrations

```powershell
php artisan config:clear
php artisan migrate --force
```

### 4. Start Laravel

```powershell
php artisan serve
```

Open http://127.0.0.1:8000

---

## Option B: Remote (friend's server at 192.168.101.239)

### 1. Friend's server – copy `docker-compose.yml`

Your friend needs this on the server. Create `docker-compose.yml`:

```yaml
services:
  firebird:
    image: firebirdsql/firebird:5
    container_name: sms-firebird
    restart: unless-stopped
    ports:
      - "3050:3050"
    environment:
      FIREBIRD_ROOT_PASSWORD: masterkey
      FIREBIRD_DATABASE: SMS.FDB
      FIREBIRD_USE_LEGACY_AUTH: "1"
      FIREBIRD_CONF_WireCrypt: Enabled
      FIREBIRD_CONF_DatabaseAccess: Full
    volumes:
      - firebird_data:/var/lib/firebird/data

volumes:
  firebird_data:
```

### 2. Friend runs on server

```powershell
docker compose down -v
docker compose up -d
```

`-v` removes the old volume so SMS.FDB is created fresh. (Skip `-v` if they have data to keep.)

### 3. If database doesn't exist, friend creates it

```powershell
"CREATE DATABASE '/var/lib/firebird/data/SMS.FDB' USER 'SYSDBA' PASSWORD 'masterkey' PAGE_SIZE 8192;" | docker exec -i sms-firebird isql -u sysdba -p masterkey
```

### 4. Your `.env` for remote

```env
DB_CONNECTION=firebird
DB_HOST=192.168.101.239
DB_PORT=3050
DB_DATABASE=/var/lib/firebird/data/SMS.FDB
DB_USERNAME=SYSDBA
DB_PASSWORD=masterkey
```

### 5. Your machine – run migrations

```powershell
php artisan config:clear
php artisan migrate --force
php artisan serve
```

---

## Quick reference

| Setting        | Local            | Remote                 |
|----------------|------------------|------------------------|
| DB_HOST        | 127.0.0.1        | 192.168.101.239       |
| DB_PORT        | 3050             | 3050                   |
| DB_DATABASE    | /var/lib/firebird/data/SMS.FDB | same |
| DB_USERNAME    | SYSDBA           | SYSDBA                 |
| DB_PASSWORD    | masterkey        | masterkey              |

---

## Test connection

```powershell
docker exec -it sms-firebird isql /var/lib/firebird/data/SMS.FDB -u sysdba -p masterkey
```

If you see `Database: /var/lib/firebird/data/SMS.FDB, User: SYSDBA`, the connection works.
