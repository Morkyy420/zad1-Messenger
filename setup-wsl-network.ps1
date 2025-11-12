# PingMe - Automatyczna konfiguracja sieci WSL2
# Uruchom jako Administrator!

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  PingMe WSL2 Network Setup" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Sprawdź czy uruchomiono jako admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "❌ BŁĄD: Ten skrypt wymaga uprawnień Administratora!" -ForegroundColor Red
    Write-Host "Kliknij prawym na PowerShell i wybierz 'Uruchom jako administrator'" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Naciśnij Enter aby zakończyć"
    exit 1
}

# Pobierz adres IP WSL2
Write-Host "🔍 Szukam adresu IP WSL2..." -ForegroundColor Yellow
$wslIP = (wsl hostname -I 2>$null).Trim()

if ([string]::IsNullOrEmpty($wslIP)) {
    Write-Host "❌ Nie można znaleźć WSL2!" -ForegroundColor Red
    Write-Host "Upewnij się że WSL2 jest uruchomiony." -ForegroundColor Yellow
    Read-Host "Naciśnij Enter aby zakończyć"
    exit 1
}

Write-Host "✅ Znaleziono WSL2 IP: $wslIP" -ForegroundColor Green

# Pobierz adres WiFi z Windows
Write-Host ""
Write-Host "🔍 Szukam adresu WiFi Windows..." -ForegroundColor Yellow
$wifiAdapter = Get-NetAdapter | Where-Object {$_.Status -eq "Up" -and ($_.Name -like "*Wi-Fi*" -or $_.Name -like "*Wireless*")}
$wifiIP = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceIndex $wifiAdapter[0].ifIndex 2>$null).IPAddress

if ([string]::IsNullOrEmpty($wifiIP)) {
    Write-Host "⚠️  Nie znaleziono WiFi. Szukam Ethernet..." -ForegroundColor Yellow
    $ethAdapter = Get-NetAdapter | Where-Object {$_.Status -eq "Up" -and $_.Name -like "*Ethernet*" -and $_.Name -notlike "*vEthernet*"}
    $wifiIP = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceIndex $ethAdapter[0].ifIndex 2>$null).IPAddress
}

if ([string]::IsNullOrEmpty($wifiIP)) {
    Write-Host "❌ Nie można znaleźć adresu sieciowego!" -ForegroundColor Red
    Read-Host "Naciśnij Enter aby zakończyć"
    exit 1
}

Write-Host "✅ Znaleziono adres sieciowy: $wifiIP" -ForegroundColor Green

# Usuń stare przekierowania
Write-Host ""
Write-Host "🧹 Usuwam stare przekierowania portów..." -ForegroundColor Yellow
netsh interface portproxy delete v4tov4 listenport=8080 listenaddress=0.0.0.0 2>$null
netsh interface portproxy delete v4tov4 listenport=8000 listenaddress=0.0.0.0 2>$null

# Dodaj nowe przekierowania
Write-Host "➕ Dodaję nowe przekierowania portów..." -ForegroundColor Yellow
netsh interface portproxy add v4tov4 listenport=8080 listenaddress=0.0.0.0 connectport=8080 connectaddress=$wslIP
netsh interface portproxy add v4tov4 listenport=8000 listenaddress=0.0.0.0 connectport=8000 connectaddress=$wslIP

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Błąd podczas dodawania przekierowań!" -ForegroundColor Red
    Read-Host "Naciśnij Enter aby zakończyć"
    exit 1
}

Write-Host "✅ Przekierowania portów skonfigurowane!" -ForegroundColor Green

# Dodaj reguły firewall (jeśli nie istnieją)
Write-Host ""
Write-Host "🔥 Konfiguruję firewall..." -ForegroundColor Yellow

$viteRule = Get-NetFirewallRule -DisplayName "WSL Vite Server" -ErrorAction SilentlyContinue
if ($null -eq $viteRule) {
    New-NetFirewallRule -DisplayName "WSL Vite Server" -Direction Inbound -LocalPort 8080 -Protocol TCP -Action Allow | Out-Null
    Write-Host "✅ Dodano regułę firewall dla Vite (port 8080)" -ForegroundColor Green
} else {
    Write-Host "✅ Reguła firewall dla Vite już istnieje" -ForegroundColor Green
}

$symfonyRule = Get-NetFirewallRule -DisplayName "WSL Symfony API" -ErrorAction SilentlyContinue
if ($null -eq $symfonyRule) {
    New-NetFirewallRule -DisplayName "WSL Symfony API" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow | Out-Null
    Write-Host "✅ Dodano regułę firewall dla Symfony (port 8000)" -ForegroundColor Green
} else {
    Write-Host "✅ Reguła firewall dla Symfony już istnieje" -ForegroundColor Green
}

# Podsumowanie
Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  ✅ KONFIGURACJA ZAKOŃCZONA!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📊 Podsumowanie:" -ForegroundColor Cyan
Write-Host "  WSL2 IP:      $wslIP" -ForegroundColor White
Write-Host "  Windows IP:   $wifiIP" -ForegroundColor White
Write-Host ""
Write-Host "🌐 Dostęp do aplikacji:" -ForegroundColor Cyan
Write-Host "  Localhost:    http://localhost:8080" -ForegroundColor White
Write-Host "  Sieć WiFi:    http://${wifiIP}:8080" -ForegroundColor Yellow
Write-Host ""
Write-Host "📝 Następne kroki:" -ForegroundColor Cyan
Write-Host "  1. Edytuj plik frontend/.env" -ForegroundColor White
Write-Host "     VITE_API_URL=http://${wifiIP}:8000/api" -ForegroundColor Gray
Write-Host ""
Write-Host "  2. W WSL2 uruchom backend:" -ForegroundColor White
Write-Host "     cd /home/dmmos/projekty/my_first_project" -ForegroundColor Gray
Write-Host "     docker compose up -d" -ForegroundColor Gray
Write-Host "     php -S 0.0.0.0:8000 -t public" -ForegroundColor Gray
Write-Host ""
Write-Host "  3. W WSL2 uruchom frontend:" -ForegroundColor White
Write-Host "     cd frontend" -ForegroundColor Gray
Write-Host "     npm run dev -- --host 0.0.0.0 --port 8080" -ForegroundColor Gray
Write-Host ""
Write-Host "  4. Na telefonie otwórz:" -ForegroundColor White
Write-Host "     http://${wifiIP}:8080" -ForegroundColor Yellow
Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

Read-Host "Naciśnij Enter aby zakończyć"
