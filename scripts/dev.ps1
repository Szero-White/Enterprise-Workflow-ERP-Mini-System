[CmdletBinding()]
param(
    [string] $HostAddress = '127.0.0.1',
    [ValidateRange(1, 65535)]
    [int] $AppPort = 8000,
    [ValidateRange(1, 65535)]
    [int] $VitePort = 5173
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot

function Assert-Command {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Name
    )

    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "Required command '$Name' was not found in PATH."
    }
}

function Invoke-Checked {
    param(
        [Parameter(Mandatory = $true)]
        [scriptblock] $Command,

        [Parameter(Mandatory = $true)]
        [string] $Description
    )

    & $Command

    if ($LASTEXITCODE -ne 0) {
        throw "$Description failed with exit code $LASTEXITCODE."
    }
}

function Get-EnvValue {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Name
    )

    if (-not (Test-Path -LiteralPath '.env')) {
        return $null
    }

    $pattern = "^\s*$([regex]::Escape($Name))="
    $line = Get-Content -LiteralPath '.env' |
        Where-Object { $_ -match $pattern } |
        Select-Object -First 1

    if (-not $line) {
        return $null
    }

    $value = ($line -split '=', 2)[1].Trim()

    if (
        $value.Length -ge 2 -and
        (
            ($value.StartsWith('"') -and $value.EndsWith('"')) -or
            ($value.StartsWith("'") -and $value.EndsWith("'"))
        )
    ) {
        return $value.Substring(1, $value.Length - 2)
    }

    return $value
}

function Assert-SqliteSupport {
    $modules = & php -m

    if ($LASTEXITCODE -ne 0) {
        throw 'Unable to inspect installed PHP extensions.'
    }

    if (-not ($modules -contains 'pdo_sqlite')) {
        throw "The PHP extension 'pdo_sqlite' is required by the default local SQLite configuration."
    }
}

function Test-TcpPortInUse {
    param(
        [Parameter(Mandatory = $true)]
        [int] $Port
    )

    $listeners = [System.Net.NetworkInformation.IPGlobalProperties]::GetIPGlobalProperties().GetActiveTcpListeners()

    return $listeners.Port -contains $Port
}

function Convert-ToEncodedCommand {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Command
    )

    $bytes = [System.Text.Encoding]::Unicode.GetBytes($Command)
    return [Convert]::ToBase64String($bytes)
}

Write-Host ''
Write-Host 'Enterprise Workflow & Operations - Local Development' -ForegroundColor Cyan
Write-Host '----------------------------------------------------' -ForegroundColor DarkGray

Assert-Command 'php'
Assert-Command 'composer'
Assert-Command 'node'
Assert-Command 'npm'
Assert-Command 'npx'
Assert-Command 'powershell'

if (-not (Test-Path -LiteralPath '.env.example')) {
    throw '.env.example was not found in the project root.'
}

if (-not (Test-Path -LiteralPath 'vendor\autoload.php')) {
    Write-Host '[setup] Installing PHP dependencies...' -ForegroundColor Yellow
    Invoke-Checked { composer install --no-interaction --prefer-dist } 'Composer install'
}

$viteCommand = Test-Path -LiteralPath 'node_modules\.bin\vite.cmd'
$concurrentlyCommand = Test-Path -LiteralPath 'node_modules\.bin\concurrently.cmd'

if (-not $viteCommand -or -not $concurrentlyCommand) {
    Write-Host '[setup] Installing frontend dependencies...' -ForegroundColor Yellow
    Invoke-Checked { npm ci } 'npm ci'
}

if (-not (Test-Path -LiteralPath '.env')) {
    Write-Host '[setup] Creating .env from .env.example...' -ForegroundColor Yellow
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
}

$appKey = Get-EnvValue 'APP_KEY'

if ([string]::IsNullOrWhiteSpace($appKey)) {
    Write-Host '[setup] Generating Laravel APP_KEY...' -ForegroundColor Yellow
    Invoke-Checked { php artisan key:generate --ansi } 'APP_KEY generation'
}

$dbConnection = Get-EnvValue 'DB_CONNECTION'

if ([string]::IsNullOrWhiteSpace($dbConnection)) {
    $dbConnection = 'sqlite'
}

$createdDatabase = $false

if ($dbConnection -eq 'sqlite') {
    Assert-SqliteSupport

    $dbDatabase = Get-EnvValue 'DB_DATABASE'

    if ([string]::IsNullOrWhiteSpace($dbDatabase)) {
        $dbPath = Join-Path $ProjectRoot 'database\database.sqlite'
    }
    elseif ($dbDatabase -eq ':memory:') {
        $dbPath = $null
    }
    elseif ([System.IO.Path]::IsPathRooted($dbDatabase)) {
        $dbPath = $dbDatabase
    }
    else {
        $dbPath = Join-Path $ProjectRoot $dbDatabase
    }

    if ($dbPath -and -not (Test-Path -LiteralPath $dbPath)) {
        Write-Host '[setup] Creating SQLite database...' -ForegroundColor Yellow

        $dbDirectory = Split-Path -Parent $dbPath

        if ($dbDirectory -and -not (Test-Path -LiteralPath $dbDirectory)) {
            New-Item -ItemType Directory -Path $dbDirectory -Force | Out-Null
        }

        New-Item -ItemType File -Path $dbPath -Force | Out-Null
        $createdDatabase = $true
    }
}

Write-Host '[database] Applying pending migrations...' -ForegroundColor Yellow
Invoke-Checked { php artisan migrate --no-interaction } 'Database migration'

if ($createdDatabase) {
    Write-Host '[database] Seeding demo data for the new local database...' -ForegroundColor Yellow
    Invoke-Checked { php artisan db:seed --no-interaction } 'Database seeding'
}

if (Test-TcpPortInUse -Port $AppPort) {
    throw "Application port $AppPort is already in use. Stop the previous Laravel development process and run this script again."
}

if (Test-TcpPortInUse -Port $VitePort) {
    throw "Vite port $VitePort is already in use. Stop the previous Vite/Node development process and run this script again."
}

$appUrl = "http://${HostAddress}:$AppPort"
$escapedProjectRoot = $ProjectRoot.Replace("'", "''")

$backendCommand = @"
`$Host.UI.RawUI.WindowTitle = 'Enterprise Workflow - Backend'
Set-Location -LiteralPath '$escapedProjectRoot'
`$env:PHP_CLI_SERVER_WORKERS = '1'
`$env:APP_URL = '$appUrl'
`$backendArgs = @(
    'concurrently',
    '-c',
    '#93c5fd,#75b1ad',
    'php artisan serve --host=$HostAddress --port=$AppPort',
    'php artisan queue:listen --tries=1',
    '--names=server,queue',
    '--kill-others-on-fail'
)
& npx @backendArgs
if (`$LASTEXITCODE -ne 0) {
    Write-Host ''
    Write-Host "Backend process group exited with code `$LASTEXITCODE." -ForegroundColor Red
}
"@

$frontendCommand = @"
`$Host.UI.RawUI.WindowTitle = 'Enterprise Workflow - Frontend'
Set-Location -LiteralPath '$escapedProjectRoot'
`$env:APP_URL = '$appUrl'
npm run dev -- --port $VitePort --strictPort
if (`$LASTEXITCODE -ne 0) {
    Write-Host ''
    Write-Host "Frontend process exited with code `$LASTEXITCODE." -ForegroundColor Red
}
"@

$backendEncoded = Convert-ToEncodedCommand -Command $backendCommand
$frontendEncoded = Convert-ToEncodedCommand -Command $frontendCommand

Write-Host ''
Write-Host 'Opening development terminals...' -ForegroundColor Green
Write-Host "Backend:  $appUrl"
Write-Host "Frontend: http://localhost:$VitePort (Vite asset server)"
Write-Host ''
Write-Host 'Two PowerShell windows will open:' -ForegroundColor Cyan
Write-Host '  1. Enterprise Workflow - Backend'
Write-Host '     Laravel server + queue listener'
Write-Host '  2. Enterprise Workflow - Frontend'
Write-Host '     Vite development server'
Write-Host ''

Start-Process powershell -ArgumentList @(
    '-NoExit',
    '-ExecutionPolicy', 'Bypass',
    '-EncodedCommand', $backendEncoded
) | Out-Null

Start-Sleep -Milliseconds 400

Start-Process powershell -ArgumentList @(
    '-NoExit',
    '-ExecutionPolicy', 'Bypass',
    '-EncodedCommand', $frontendEncoded
) | Out-Null

Write-Host 'Development terminals opened successfully.' -ForegroundColor Green
Write-Host "Open the application at: $appUrl"
Write-Host ''
Write-Host 'Close both spawned terminal windows to stop the local development environment.'
