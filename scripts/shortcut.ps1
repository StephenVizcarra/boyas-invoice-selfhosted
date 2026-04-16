$repoRoot    = Split-Path $PSScriptRoot -Parent
$desktop     = [System.Environment]::GetFolderPath('Desktop')
$shortcut    = Join-Path $desktop "Boyas Invoice.lnk"
$batTarget   = Join-Path $repoRoot "Launch Boyas Invoice.bat"

$ws = New-Object -ComObject WScript.Shell
$s  = $ws.CreateShortcut($shortcut)
$s.TargetPath       = $batTarget
$s.WorkingDirectory = $repoRoot
$s.Description      = "Launch Boyas Invoice"
$s.Save()

Write-Host ""
Write-Host "  Desktop shortcut created!" -ForegroundColor Green
Write-Host "  Look for 'Boyas Invoice' on your desktop." -ForegroundColor Gray
Write-Host ""
Start-Sleep -Seconds 3
