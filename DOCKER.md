# Firebird in Docker

Uses the official [FirebirdSQL/firebird-docker](https://github.com/FirebirdSQL/firebird-docker) image (Firebird **5.0.3**). Default port: **3050**.

---

## Access the remote Firebird container (192.168.101.239)

If Firebird is running in Docker on **192.168.101.227**, use the following.

### 1. On the remote server (192.168.101.239)

Ensure the container is running and port 3050 is published:

```bash
# From the project root (where docker-compose.yml is)
docker compose up -d

# Check that port 3050 is listening
docker compose ps
```

Ensure the host firewall allows TCP **3050** (e.g. `sudo ufw allow 3050/tcp` on Linux).

### 2. Connect from your machine

**Option A – Laravel (`.env`):**

```env
DB_CONNECTION=firebird
DB_HOST=192.168.101.227
DB_PORT=3050
DB_DATABASE=/var/lib/firebird/data/SMS.FDB
DB_USERNAME=SYSDBA
DB_PASSWORD=masterkey
```

Then run `php artisan config:clear` and use the app as usual.

**Option B – isql (Firebird client installed):**

```bash
isql 192.168.101.227/3050:/var/lib/firebird/data/SMS.FDB -u SYSDBA -p masterkey
```

**Option C – isql via Docker (no local Firebird client):**

```bash
docker run -it --rm firebirdsql/firebird:5.0.3 isql 192.168.101.227/3050:/var/lib/firebird/data/SMS.FDB -u SYSDBA -p masterkey
```

Replace `192.168.101.227` if your server has a different IP.

### 3. Run commands inside the remote container (SSH to server)

If you have SSH access to 192.168.101.239:

```bash
ssh user@192.168.101.227
docker exec -it sms-firebird isql /var/lib/firebird/data/SMS.FDB -u sysdba -p masterkey
```

Or create the database if it does not exist:

```bash
docker exec -it sms-firebird isql -u sysdba -p masterkey
# At SQL> prompt:
# CREATE DATABASE '/var/lib/firebird/data/SMS.FDB' USER 'SYSDBA' PASSWORD 'masterkey' PAGE_SIZE 8192;
# quit
```

---

## Start Firebird (local)

```bash
docker compose up -d
```

This starts Firebird 5 on port **3050** and creates a database **SMS.FDB** in the `firebird_data` volume (path inside container: `/var/lib/firebird/data/SMS.FDB`).

## Connect Laravel

In `.env` set:

```env
DB_CONNECTION=firebird
DB_HOST=127.0.0.1
DB_PORT=3050
DB_DATABASE=/var/lib/firebird/data/SMS.FDB
DB_USERNAME=SYSDBA
DB_PASSWORD=masterkey
```

Then:

```bash
php artisan config:clear
```

## Test connection with isql

To verify Firebird credentials work ([ref](https://stackoverflow.com/a/4273728)):

**From inside the container (local):**

```bash
docker exec -it sms-firebird isql /var/lib/firebird/data/SMS.FDB -u sysdba -p masterkey
```

**Remote server (from a machine with Firebird client):**

```bash
isql 192.168.101.227/3050:/var/lib/firebird/data/SMS.FDB -u sysdba -p masterkey
```

If this connects, credentials are correct. If it fails, the issue is on the Firebird server.

## Run migrations

After the container is up and `.env` points to it:

```bash
php artisan migrate --force
```

(Ensure the `migrations` table exists with an auto-increment `id` on Firebird; see project notes if you get an integrity error.)

---

## Remote server setup & troubleshooting

When deploying Firebird on a remote server (e.g. 192.168.101.239), use the same `docker-compose.yml` config. The error *"Your user name and password are not defined"* can be misleading—it often means auth/WireCrypt mismatch, not wrong credentials.

### 1. Required environment variables (remote server)

Ensure the remote Firebird container has:

```yaml
environment:
  FIREBIRD_ROOT_PASSWORD: masterkey
  FIREBIRD_DATABASE: SMS.FDB
  FIREBIRD_USE_LEGACY_AUTH: "1"
  FIREBIRD_CONF_WireCrypt: Enabled
```

Then:

```powershell
docker compose down
docker compose up -d
```

### 2. Reset SYSDBA password without recreating (keeps data)

If the container was started without `FIREBIRD_ROOT_PASSWORD`, it has a random password. Reset it using `gsec` **inside the container**:

**Step 1 – get current password:**

```powershell
docker exec -it sms-firebird cat /opt/firebird/SYSDBA.password
```

**Step 2 – set new password to masterkey:**

```powershell
docker exec -it sms-firebird gsec -user sysdba -pass "<current_password_from_step1>" -mo sysdba -pw masterkey
```

Replace `sms-firebird` with the actual container name, and `<current_password_from_step1>` with the value from step 1.

### 3. Client/server version mismatch

If using older PHP Firebird client libraries against Firebird 5, upgrade the client. Laravel/PHP should use a Firebird-compatible driver (e.g. PDO Firebird or the danidoble package).

### 4. Remote `.env` example

```env
DB_HOST=192.168.101.239
DB_PORT=3050
DB_DATABASE=/var/lib/firebird/data/SMS.FDB
DB_USERNAME=SYSDBA
DB_PASSWORD=masterkey
```

### 5. Fix I/O error 335544344 (CreateFile open) – based on [Stack Overflow](https://stackoverflow.com/questions/53561894)

**A. Find the correct path** – On the Firebird server:

```powershell
docker exec sms-firebird find / -name "SMS.FDB" 2>$null
docker exec sms-firebird ls -la /var/lib/firebird/data/
```

**B. Check databases.conf / aliases** – The error can be caused by a wrong alias. In `databases.conf` the format must be `alias = fullpath`, not `fullpath = alias`. If the friend has an alias like `SMS = /path/to/SMS.FDB`, try using `DB_DATABASE=SMS` (alias only) in `.env`.

**C. Check DatabaseAccess** – In `firebird.conf`, if `DatabaseAccess = Restrict` is set, Firebird can only open files in the listed paths. The friend must add the directory containing SMS.FDB, or set `DatabaseAccess = Full`. In Docker, add via env:
```yaml
environment:
  FIREBIRD_CONF_DatabaseAccess: Full
```

**D. Path format** – Use forward slashes. For Windows paths: `C:/SQLSMS/SMS.FDB` (not `C:\` – backslashes can be misinterpreted).

**E. If Firebird is native Windows** (not Docker): `DB_DATABASE=C:/SQLSMS/SMS.FDB`

### 6. Create SMS.FDB when missing

If you get *"I/O error during CreateFile (open)"* for SMS.FDB, the database file does not exist. On the **remote server**, run:

**Option A – one-liner (PowerShell):**

```powershell
"CREATE DATABASE '/var/lib/firebird/data/SMS.FDB' USER 'SYSDBA' PASSWORD 'masterkey' PAGE_SIZE 8192;" | docker exec -i sms-firebird isql -u sysdba -p masterkey
```

**Option B – interactive (replace container name if needed):**

```powershell
docker exec -it sms-firebird isql -u sysdba -p masterkey
```

Then at the `SQL>` prompt type:

```sql
CREATE DATABASE '/var/lib/firebird/data/SMS.FDB' USER 'SYSDBA' PASSWORD 'masterkey' PAGE_SIZE 8192;
```

Type `quit` to exit.

After the database is created, run migrations from your dev machine:

```bash
php artisan migrate --force
```

---

## Optional: custom Firebird image

To build and use the custom Dockerfile:

```bash
docker build -f Dockerfile.firebird -t sms-firebird .
```

Then in `docker-compose.yml` replace `image: firebirdsql/firebird:5` with `image: sms-firebird` and run `docker compose up -d` again.
