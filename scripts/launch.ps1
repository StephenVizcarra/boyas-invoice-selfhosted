$Host.UI.RawUI.WindowTitle = "Boyas Invoice"

Write-Host ""
Write-Host "  Boyas Invoice" -ForegroundColor White
Write-Host "  --------------------------------------" -ForegroundColor DarkGray
Write-Host ""

# --- 1. Docker check ---
Write-Host "  [1/3] Checking Docker..." -NoNewline

docker info 2>&1 | Out-Null

if ($LASTEXITCODE -ne 0) {
    Write-Host " not running." -ForegroundColor Yellow
    Write-Host ""

    $dockerExe = "$env:ProgramFiles\Docker\Docker\Docker Desktop.exe"

    if (-not (Test-Path $dockerExe)) {
        Write-Host "  Docker Desktop is not installed." -ForegroundColor Red
        Write-Host "  Download it from: https://www.docker.com/products/docker-desktop/" -ForegroundColor Cyan
        Write-Host ""
        Read-Host "  Press Enter to exit"
        exit 1
    }

    Write-Host "  Starting Docker Desktop - this can take up to 60 seconds..." -ForegroundColor Yellow
    Start-Process $dockerExe

    $waited = 0
    while ($waited -lt 90) {
        Start-Sleep -Seconds 3
        $waited += 3
        docker info 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) { break }
        Write-Host "  Still waiting... $waited s" -ForegroundColor DarkGray
    }

    docker info 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        Write-Host ""
        Write-Host "  Docker did not start in time." -ForegroundColor Red
        Write-Host "  Please open Docker Desktop manually and try again." -ForegroundColor DarkGray
        Write-Host ""
        Read-Host "  Press Enter to exit"
        exit 1
    }
}

Write-Host " ready." -ForegroundColor Green

# --- 2. First-run warning ---
$hasContainer = docker ps -a --filter "name=boyas_invoice" --format "{{.Names}}" 2>&1

if (-not $hasContainer) {
    Write-Host ""
    Write-Host "  First time launch detected! I'm building the app for your dumb arse. This will take a few minutes." -ForegroundColor Yellow
    Write-Host "  Take your fat hits... you only wait this long once..." -ForegroundColor DarkGray
    Write-Host ""
}

# --- 3. Start the app ---
Write-Host "  [2/3] Starting Boyas Invoice..."

$repoRoot = Split-Path $PSScriptRoot -Parent
Set-Location $repoRoot

docker compose up -d

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "  Failed to start the app." -ForegroundColor Red
    Write-Host "  Try running the launcher again. If the problem persists, hit up gronzon." -ForegroundColor DarkGray
    Write-Host ""
    Read-Host "  Press Enter to exit"
    exit 1
}

# --- 4. Wait for health ---
Write-Host ""
Write-Host "  [3/3] Waiting for app to be ready..." -NoNewline

$ready = $false
for ($i = 0; $i -lt 40; $i++) {
    Start-Sleep -Seconds 2
    try {
        $r = Invoke-WebRequest -Uri "http://localhost:8080/up" -TimeoutSec 2 -UseBasicParsing -ErrorAction Stop
        if ($r.StatusCode -eq 200) { $ready = $true; break }
    } catch {}
    Write-Host "." -NoNewline -ForegroundColor DarkGray
}

if ($ready) {
    Write-Host " I cooked! It's ready!!" -ForegroundColor Green
} else {
    Write-Host " taking a bit longer than usual..." -ForegroundColor Yellow
    Write-Host "  Try refreshing your browser in a moment." -ForegroundColor DarkGray
}

# --- 5. Open browser ---
Write-Host ""
Write-Host "  Opening http://localhost:8080 ..." -ForegroundColor Cyan
Write-Host "  To stop the app later, double-click 'Stop Boyas Invoice.bat'" -ForegroundColor DarkGray
Write-Host ""

Start-Process "http://localhost:8080"
