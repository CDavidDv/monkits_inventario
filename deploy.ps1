# deploy.ps1 - Empaquetar cambios para subir al servidor
# Uso: click derecho -> "Run with PowerShell"

$ErrorActionPreference = "Continue"
$projectRoot = $PSScriptRoot
$deployZip   = "$projectRoot\deploy_package.zip"

Write-Host "`n=== MONKITS DEPLOY ===" -ForegroundColor Cyan

# 1. Detectar si hay cambios en el frontend
$frontendChanged = (git -C $projectRoot diff --name-only HEAD 2>&1) |
    Where-Object { $_ -notmatch "^warning" -and ($_ -match "^resources/" -or $_ -eq "vite.config.js") }

if (-not $frontendChanged) {
    $frontendChanged = (git -C $projectRoot status --short 2>&1) |
        Where-Object { $_ -notmatch "^warning" -and ($_ -match "resources/" -or $_ -match "vite.config.js") }
}

if ($frontendChanged) {
    Write-Host "`n[1/3] Cambios en frontend detectados. Compilando..." -ForegroundColor Yellow
    Set-Location $projectRoot
    npm run build
    if ($LASTEXITCODE -ne 0) { Write-Host "ERROR en npm build" -ForegroundColor Red; exit 1 }
    Write-Host "     Build OK" -ForegroundColor Green
} else {
    Write-Host "`n[1/3] Sin cambios de frontend, saltando build." -ForegroundColor Gray
}

# 2. Recopilar archivos a empaquetar
Write-Host "`n[2/3] Preparando paquete de deploy..." -ForegroundColor Yellow

# Archivos PHP/config modificados (tracked por git)
# git status --short siempre tiene exactamente 3 chars de prefijo (XY + espacio)
# NO usar Trim() antes de Substring(3) porque elimina el espacio de " M archivo"
$changedFiles = (git -C $projectRoot status --short 2>&1) |
    Where-Object { $_ -notmatch "^warning" -and $_.Length -gt 3 } |
    ForEach-Object { $_ -replace '^.{3}', '' } |
    Where-Object {
        $_ -notmatch "^node_modules" -and
        $_ -notmatch "^\.git" -and
        $_ -notmatch "^deploy_package" -and
        $_ -notmatch "^public/build"
    }

# Siempre incluir el build si existe
$includeBuild = Test-Path "$projectRoot\public\build"

if ($null -eq $changedFiles -and -not $includeBuild) {
    Write-Host "No hay cambios para deployar." -ForegroundColor Gray
    exit 0
}

# 3. Crear ZIP
if (Test-Path $deployZip) { Remove-Item $deployZip -Force }

Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [System.IO.Compression.ZipFile]::Open($deployZip, 'Create')

function Add-ToZip($zip, $filePath, $entryName) {
    if (Test-Path $filePath -PathType Leaf) {
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $filePath, $entryName, 'Optimal') | Out-Null
    }
}

# Agregar archivos PHP cambiados
foreach ($f in $changedFiles) {
    $fullPath = Join-Path $projectRoot $f
    if (Test-Path $fullPath -PathType Leaf) {
        Add-ToZip $zip $fullPath $f
        Write-Host "  + $f" -ForegroundColor DarkGray
    }
}

# Agregar build completo
if ($includeBuild) {
    $buildDir = "$projectRoot\public\build"
    Get-ChildItem $buildDir -Recurse -File | ForEach-Object {
        $relative = "public/build/" + $_.FullName.Substring($buildDir.Length + 1).Replace('\', '/')
        Add-ToZip $zip $_.FullName $relative
    }
    Write-Host "  + public/build/ ($(((Get-ChildItem $buildDir -Recurse -File).Count)) archivos)" -ForegroundColor DarkGray
}

$zip.Dispose()

$size = [math]::Round((Get-Item $deployZip).Length / 1MB, 2)
Write-Host "`n[3/3] Paquete creado: deploy_package.zip ($size MB)" -ForegroundColor Green

Write-Host "`n=== PASOS SIGUIENTES ===" -ForegroundColor Cyan
Write-Host "1. Sube 'deploy_package.zip' al servidor via FTP"
Write-Host "   -> destino: public_html/inventario/deploy_package.zip"
Write-Host "2. Abre en el navegador:"
Write-Host "   -> https://monkits.com/inventario/deploy.php"
Write-Host "3. Ingresa la clave y haz clic en Deploy"
Write-Host ""
