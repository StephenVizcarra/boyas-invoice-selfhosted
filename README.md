# Boyas Invoice

Self-hosted, single-user invoicing. Create PDF invoices, save your profile and recipients, and keep invoice numbers in sequence — all on your machine.

Laravel 12 + Vue 3. Data lives in SQLite; logos and PDFs live on disk.

- PDF invoices with logo
- Saved recipients
- Invoice numbers like `INV-YYYY-NNNN`
- Invoice history (download / delete)
- Nothing in the cloud

---

## Windows (no coding)

About 10 minutes. Needs Windows 10/11, internet, and ~5 GB free disk (for Docker).

**1. Install [Docker Desktop](https://www.docker.com/products/docker-desktop/).** Enable **WSL 2** if asked; restart if prompted. Open Docker Desktop and wait until the whale icon in the taskbar stops animating. You can close the window; Docker keeps running. If it asks you to enable virtualization in BIOS, that is a one-time PC setting.

**2. Get the app.** On GitHub: **Code** → **Download ZIP**. Right-click → **Extract All…**. Leave the folder where you put it (the desktop shortcut points here). No Git needed.

**3. Shortcut (once).** In that folder, double-click **`Add Desktop Shortcut.bat`**. You should get a **Boyas Invoice** icon on the desktop.

**Start:** desktop icon, or **`Launch Boyas Invoice.bat`**. Each launch pulls the latest image from GitHub Container Registry, then starts the app. First launch takes a few minutes; later ones are quicker if the image is already current. The browser should open **http://localhost:8080**.

**Stop:** **`Stop Boyas Invoice.bat`**. Leaving it running is fine; it uses little memory.

Your profile, recipients, invoices, and numbers stay on this computer (Docker volumes). Stopping the app or rebooting does not wipe them.

You only need a new ZIP if the launcher or `docker-compose.yml` itself changed.

---

## Docker (command line)

Needs [Docker Desktop](https://www.docker.com/products/docker-desktop/).

```bash
docker compose pull
docker compose up -d
```

Open **http://localhost:8080**. First boot creates `.env`, the app key, and the database. Data survives in Docker volumes (`boyas_storage`, `boyas_database`).

---

## Local development

Needs PHP 8.2+, Composer, Node.js 18+.

```bash
composer run setup   # deps, .env, key, migrate, build
composer run dev     # app + Vite
```

Open **http://localhost:8000**.

```bash
composer run test    # PHPUnit
./vendor/bin/pint    # code style
```

---

## Troubleshooting

| Problem | Fix |
|--------|-----|
| “Docker Desktop is not installed” | Install Docker Desktop (Windows section, step 1). |
| “Docker did not start in time” | Open Docker Desktop, wait until it is idle, launch again. |
| Blank page or error in the browser | Wait 20–30 seconds and refresh, or stop and launch again. |
| Port 8080 already in use | Stop whatever else is using 8080, or change the port. |
| Anything else | Screenshot the error and send it. |
