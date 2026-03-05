$src = "c:\Users\juan.rascon\Downloads\birthday\*"
$dst = "c:\xampp\htdocs\birthday\"

if (-not (Test-Path $dst)) {
    New-Item -ItemType Directory -Path $dst -Force | Out-Null
    Write-Host "Carpeta 'birthday' creada en htdocs." -ForegroundColor Cyan
}

Copy-Item -Path $src -Destination $dst -Recurse -Force
Write-Host "✅ Sincronización REALIZADA: htdocs/birthday está actualizado." -ForegroundColor Green
