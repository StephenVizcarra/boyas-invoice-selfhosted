# Setup Guide — Boyas Invoice

This guide walks you through getting Boyas Invoice running on your computer from scratch. No prior coding experience is needed.

**Time to complete:** ~20–30 minutes

**You will need:**
- A computer running Windows 10/11 or macOS
- An internet connection
- About 500 MB of free disk space

---

## Table of Contents

1. [What is all this?](#1-what-is-all-this)
2. [Install Git](#2-install-git)
3. [Install PHP](#3-install-php)
4. [Install Composer](#4-install-composer)
5. [Install Node.js](#5-install-nodejs)
6. [Download the project](#6-download-the-project)
7. [Run the one-time setup](#7-run-the-one-time-setup)
8. [Start the app](#8-start-the-app)
9. [Using the app every day](#9-using-the-app-every-day)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. What is all this?

Boyas Invoice is a web app that runs **on your own computer**. Instead of going to a website someone else hosts, you run a small local server yourself, then open the app in your regular web browser (Chrome, Firefox, Edge, Safari — any of them work).

To do this, you need to install a few free tools that the app depends on. Think of it like installing drivers for a printer — you only do it once, and then everything just works.

Here is what you will install and why:

| Tool | What it is |
|------|-----------|
| **Git** | Downloads ("clones") the project from GitHub to your computer |
| **PHP** | The programming language the app's backend is written in |
| **Composer** | Installs the PHP libraries the app needs (like an app store for code) |
| **Node.js** | Runs the JavaScript build step that compiles the frontend |

---

## 2. Install Git

Git is how you download the project and keep it up to date.

### Windows

1. Go to **https://git-scm.com/download/win**
2. The download should start automatically. If not, click the link for the latest version.
3. Run the installer (`.exe` file). Click **Next** through all the steps — the defaults are fine.
4. When the installer finishes, search for **"Git Bash"** in your Start menu and open it. This is the terminal window you will use for all the commands in this guide.

### macOS

1. Open **Terminal** (press `Cmd + Space`, type "Terminal", press Enter).
2. Type the following and press Enter:
   ```
   git --version
   ```
3. If Git is not installed, macOS will automatically prompt you to install the **Xcode Command Line Tools**. Click **Install** and wait for it to finish (this can take a few minutes).
4. Once done, run `git --version` again — you should see a version number.

**How to check it worked:**
Open your terminal (Git Bash on Windows, Terminal on Mac) and type:
```
git --version
```
You should see something like: `git version 2.47.0`

---

## 3. Install PHP

PHP is the language the app runs on.

### Windows

The easiest way is to install **PHP for Windows** directly:

1. Go to **https://windows.php.net/download/**
2. Under the **"VS17 x64 Non Thread Safe"** section, click the **Zip** link to download.
3. Create a folder at `C:\php` (open File Explorer, go to your C: drive, right-click, New → Folder, name it `php`).
4. Extract the contents of the zip file into `C:\php`.
5. Now you need to add PHP to your system PATH so you can use it from the terminal:
   - Press `Win + S`, search for **"Environment Variables"**, click **"Edit the system environment variables"**
   - Click **"Environment Variables..."**
   - Under **"System variables"**, find and double-click **"Path"**
   - Click **New** and add: `C:\php`
   - Click OK on all windows to save
6. Close and reopen Git Bash.

> **Tip for Windows:** An easier alternative is to install **XAMPP** (https://www.apachefriends.org/), which includes PHP and adds it to your PATH automatically. During install, you only need to check "PHP" — you don't need Apache or MySQL.

### macOS

macOS comes with a very old version of PHP. Use **Homebrew** to install a current version:

1. Install Homebrew first (a package manager for Mac). In Terminal, paste this and press Enter:
   ```
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   ```
   Follow any prompts. This may take a few minutes.

2. Once Homebrew is installed, run:
   ```
   brew install php
   ```

**How to check it worked:**
```
php --version
```
You should see something like: `PHP 8.3.x` — the number needs to be 8.2 or higher.

---

## 4. Install Composer

Composer is a tool that installs the PHP packages (libraries) the app depends on. It reads a list from the project and downloads everything automatically.

### Windows & macOS

1. Go to **https://getcomposer.org/download/**
2. **Windows:** Download and run the **Composer-Setup.exe** installer. Follow the prompts — it will find your PHP installation automatically.
3. **macOS:** Follow the "Command-line installation" instructions on that page. It's four commands you paste into Terminal one at a time.

**How to check it worked:**
```
composer --version
```
You should see something like: `Composer version 2.8.x`

---

## 5. Install Node.js

Node.js is used once during setup to compile the frontend code into files the browser can use.

### Windows & macOS

1. Go to **https://nodejs.org/**
2. Click the **"LTS"** download button (LTS stands for Long Term Support — it is the most stable version).
3. Run the installer and follow the prompts. The defaults are all fine.

**How to check it worked:**
```
node --version
```
You should see something like: `v22.x.x` — the number needs to be 18 or higher.

---

## 6. Download the project

Now you will download ("clone") the project from GitHub onto your computer.

1. Open your terminal (Git Bash on Windows, Terminal on Mac).

2. Decide where you want to keep the project. A good place is your Documents folder. Navigate there by typing:
   ```
   cd ~/Documents
   ```
   (The `cd` command means "change directory" — it's like double-clicking a folder.)

3. Clone the repository (replace the URL below with the actual GitHub URL you were given):
   ```
   git clone https://github.com/YOUR-USERNAME/boyas-invoice.git
   ```
   You'll see some text scroll by as files download. When it's done, a new folder called `boyas-invoice` will appear in your Documents.

4. Move into that folder:
   ```
   cd boyas-invoice
   ```
   Your terminal prompt should now show you're inside the project folder.

---

## 7. Run the one-time setup

This is the step that installs all the project's dependencies and prepares it to run. You only need to do this **once**.

Make sure you are inside the `boyas-invoice` folder (step 6 above), then run:

```
composer run setup
```

This single command will automatically:
- Install all PHP packages the app needs
- Create a configuration file (`.env`) from the template
- Generate a security key for the app
- Initialize the local database file
- Install all JavaScript packages
- Compile the frontend

You will see a lot of text scroll by. This is normal — it is just showing you what it is doing. The whole process takes about 1–3 minutes depending on your internet connection.

**How to know it succeeded:** The last few lines should mention something about assets being compiled, with no red error messages. If you see a line that says something like `DONE` or `Build complete`, you're good.

> **If you see an error** that mentions permissions or a missing file, see the [Troubleshooting](#10-troubleshooting) section below.

---

## 8. Start the app

Every time you want to use the app, you run this one command from inside the project folder:

```
composer run dev
```

You will see four colored lines of output appear — that means the app is running. Leave this terminal window open while you use the app (closing it stops the server).

Now open your web browser and go to:

```
http://localhost:8000
```

The Boyas Invoice app should load in your browser.

---

## 9. Using the app every day

Once setup is done, your daily routine is:

1. Open your terminal (Git Bash on Windows, Terminal on Mac)
2. Navigate to the project folder:
   ```
   cd ~/Documents/boyas-invoice
   ```
3. Start the app:
   ```
   composer run dev
   ```
4. Open **http://localhost:8000** in your browser
5. When you're done, come back to the terminal and press **Ctrl + C** to stop the server

**Your data is safe.** Everything you enter — your profile, saved recipients, invoice history — is stored in files inside the project folder on your computer. It is not sent anywhere online.

---

## 10. Troubleshooting

### "composer: command not found" or "'composer' is not recognized"

Composer was not added to your PATH during installation. On Windows, try reopening Git Bash or restarting your computer. On Mac, follow the Composer download page instructions again carefully.

### "php: command not found" or "'php' is not recognized"

PHP is not on your PATH. On Windows, double-check that `C:\php` is in your Environment Variables PATH (see Step 3). On Mac, try running `brew link php`.

### The setup command fails with a permission error (macOS)

Try running:
```
sudo composer run setup
```
You will be asked for your Mac login password.

### "Could not open input file: artisan"

You are not in the right folder. Make sure you ran `cd boyas-invoice` (or whatever the folder is named) before running any commands. Type `ls` and press Enter — you should see files like `composer.json` and `artisan` listed.

### The browser shows "This site can't be reached" at localhost:8000

The server is not running. Go back to your terminal and run `composer run dev`. Keep that terminal window open.

### Something else went wrong

If you hit an error not covered here, copy the full error message from your terminal and share it — error messages always contain the information needed to diagnose the problem, even if they look intimidating.

---

*That's it! Once these tools are installed, you have everything you need to run the app anytime.*
