$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$php = Join-Path $root '.runtime\php-8.3.33\php.exe'
$nodeDir = Join-Path $root '.runtime\node-v22.23.0-win-x64'

if (-not (Test-Path $php)) {
    throw "PHP portable tidak ditemukan di $php"
}

if (Test-Path $nodeDir) {
    $env:PATH = "$nodeDir;$env:PATH"
}

Set-Location $root
& $php artisan serve --host=127.0.0.1 --port=8000
