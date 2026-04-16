$Host.UI.RawUI.WindowTitle = "Boyas Invoice"

Write-Host ""
Write-Host "  Stopping Boyas Invoice..." -ForegroundColor Gray

$repoRoot = Split-Path $PSScriptRoot -Parent
Set-Location $repoRoot

docker compose down

Write-Host ""
Write-Host "  Stopped. Your data is safely saved." -ForegroundColor Green
Write-Host ""
Start-Sleep -Seconds 2
