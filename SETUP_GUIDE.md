# Setup Guide — Boyas Invoice

This guide gets Boyas Invoice running on your Windows machine with no coding experience required.

**Time to complete:** ~10 minutes (plus a one-time 3–5 minute wait the first time the app builds itself)

**What you'll need:**
- A Windows 10 or Windows 11 computer
- An internet connection
- About 5 GB of free disk space (for Docker Desktop)

---

## Step 1 — Install Docker Desktop

Docker is the engine that runs the app in a self-contained box on your computer.
You only install this once.

1. Go to **https://www.docker.com/products/docker-desktop/**
2. Click **Download for Windows**
3. Run the installer like any normal program
4. When asked about **WSL 2**, click **Yes** (this is a Windows feature Docker needs)
5. Restart your computer if prompted

After installing, open **Docker Desktop** from your Start menu and let it finish loading
(there's a loading animation in the taskbar). You'll see a dashboard appear — that means
it's ready. You can close the window; Docker keeps running in the background.

> **Note:** If you see a message about enabling virtualization in BIOS, contact author —
> this is a one-time setting that takes about 2 minutes to enable.

---

## Step 2 — Get the app files

You don't need Git or any coding tools. Just download a ZIP:

1. Go to the GitHub page shared with you
2. Click the green **Code** button near the top right
3. Click **Download ZIP**
4. When it finishes downloading, right-click the ZIP file → **Extract All...**
5. Choose where to save it (Documents or Desktop is fine) and click **Extract**

You now have a folder called something like `Boyas_Invoice_Selfhosted`. Keep it wherever you put it — don't move it later, because the desktop shortcut you'll create in Step 3 points to this location.

---

## Step 3 — Create a desktop shortcut (do this once)

Open the folder you just extracted and double-click:

**`Add Desktop Shortcut.bat`**

A window will flash briefly and you'll see a message saying the shortcut was created.
You now have a **Boyas Invoice** icon on your desktop.

---

## That's it for setup!

---

## Starting the App

Double-click **Boyas Invoice** on your desktop (or **`Launch Boyas Invoice.bat`** inside the folder).

A terminal window will open and show you what's happening:

- **First time ever:** you'll see a message saying "building the app — grab a coffee." This takes 3–5 minutes and only happens once. The window will tell you when it's ready.
- **Every time after that:** the app starts in about 10 seconds.

Your browser will open automatically to **http://localhost:8080** when the app is ready.

---

## Stopping the App

Double-click **`Stop Boyas Invoice.bat`** (in the same folder) when you're done for the day.

You can also just leave it running — it uses very little memory in the background
and won't slow your computer down.

---

## Your Data

Everything you enter — your sender profile, saved recipients, invoice numbers — is stored
safely on your computer. It **cannot** be lost by stopping the app, restarting your computer,
or installing a new version.

---

## Getting Updates

When the author sends you a new version:

1. Download the new ZIP from GitHub and extract it **into the same folder**, replacing files when Windows asks
2. Double-click **`Launch Boyas Invoice.bat`** as usual

---

## Troubleshooting

**The app window says "Docker Desktop is not installed"**
Go back to Step 1 and install Docker Desktop.

**The app window says "Docker did not start in time"**
Open Docker Desktop from your Start menu manually, wait for the whale icon in the taskbar
to stop animating, then try launching the app again.

**The browser shows a blank page or error**
Wait 20–30 more seconds and refresh. The app may still be starting up.
If it keeps happening, stop the app and launch it again.

**Port 8080 is already in use**
Something else on your computer is using port 8080. Contact me — this is a quick fix.

**Anything else**
Take a screenshot of the error message and send me that ishhh.