# 🚀 PingMe - Przewodnik uruchamiania w sieci WiFi (WSL2)

## 📋 Krok po kroku dla WSL2

### 1️⃣ Znajdź adres IP karty WiFi w Windows

Otwórz **CMD** lub **PowerShell** w Windows i wpisz:

```cmd
ipconfig
```

**Szukaj sekcji:** `Wireless LAN adapter Wi-Fi` (NIE "vEthernet WSL"!)

```
Wireless LAN adapter Wi-Fi:

   Connection-specific DNS Suffix  . :
   IPv4 Address. . . . . . . . . . . : 192.168.1.100    👈 TO JEST TWÓJ ADRES!
   Subnet Mask . . . . . . . . . . . : 255.255.255.0
   Default Gateway . . . . . . . . . : 192.168.1.1
```

**Zapisz ten adres! Będziesz go potrzebować.**

---

### 2️⃣ Znajdź adres IP WSL2

W terminalu **WSL2**:

```bash
hostname -I | awk '{print $1}'
```

Przykładowy wynik: `172.19.144.10`

**Zapisz również ten adres!**

---

### 3️⃣ Skonfiguruj przekierowanie portów (Port Forwarding)

Otwórz **PowerShell jako Administrator** (kliknij prawym na Start → PowerShell (Admin))

#### Opcja A: Automatyczny skrypt

```powershell
# Pobierz adres WSL2 automatycznie:
$wslIP = (wsl hostname -I).Trim()
Write-Host "WSL2 IP: $wslIP"

# Usuń stare przekierowania (jeśli istnieją):
netsh interface portproxy delete v4tov4 listenport=8080 listenaddress=0.0.0.0
netsh interface portproxy delete v4tov4 listenport=8000 listenaddress=0.0.0.0

# Dodaj nowe przekierowania:
netsh interface portproxy add v4tov4 listenport=8080 listenaddress=0.0.0.0 connectport=8080 connectaddress=$wslIP
netsh interface portproxy add v4tov4 listenport=8000 listenaddress=0.0.0.0 connectport=8000 connectaddress=$wslIP

# Dodaj reguły firewall:
New-NetFirewallRule -DisplayName "WSL Vite Server" -Direction Inbound -LocalPort 8080 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "WSL Symfony API" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow

Write-Host "Przekierowanie portów skonfigurowane!" -ForegroundColor Green
```

#### Opcja B: Ręczna konfiguracja

Jeśli adres WSL2 to np. `172.19.144.10`:

```powershell
netsh interface portproxy add v4tov4 listenport=8080 listenaddress=0.0.0.0 connectport=8080 connectaddress=172.19.144.10
netsh interface portproxy add v4tov4 listenport=8000 listenaddress=0.0.0.0 connectport=8000 connectaddress=172.19.144.10
```

#### Sprawdź konfigurację:

```powershell
netsh interface portproxy show all
```

Powinno pokazać:
```
Listen on ipv4:             Connect to ipv4:

Address         Port        Address         Port
--------------- ----------  --------------- ----------
0.0.0.0         8080        172.19.144.10   8080
0.0.0.0         8000        172.19.144.10   8000
```

---

### 4️⃣ Skonfiguruj plik .env w frontend

W terminalu **WSL2**:

```bash
cd /home/dmmos/projekty/my_first_project/frontend
cp .env.example .env
nano .env
```

**Wpisz** (zamień `192.168.1.100` na TWÓJ adres WiFi z kroku 1!):

```env
VITE_API_URL=http://192.168.1.100:8000/api
```

Zapisz: `Ctrl+O`, Enter, `Ctrl+X`

---

### 5️⃣ Zaktualizuj CORS w backend

Edytuj plik `.env` w głównym folderze projektu:

```bash
cd /home/dmmos/projekty/my_first_project
nano .env
```

Znajdź linię `CORS_ALLOW_ORIGIN` i zamień na (dostosuj do swojego IP):

```env
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|192\.168\.1\.100|192\.168\.1\.\d+)(:[0-9]+)?$
```

**Zamień `192.168.1` na pierwsze 3 oktety TWOJEGO IP!**

Zapisz i wyjdź.

---

### 6️⃣ Uruchom aplikację

#### Terminal 1 - WSL2 (Backend):

```bash
cd /home/dmmos/projekty/my_first_project

# Uruchom Docker (baza danych):
docker compose up -d

# Uruchom Symfony:
php -S 0.0.0.0:8000 -t public
```

#### Terminal 2 - WSL2 (Frontend):

```bash
cd /home/dmmos/projekty/my_first_project/frontend

# Uruchom Vite:
npm run dev -- --host 0.0.0.0 --port 8080
```

Powinno pokazać:
```
  ➜  Local:   http://localhost:8080/
  ➜  Network: http://172.19.144.10:8080/
```

---

### 7️⃣ Testowanie

#### Na tym samym komputerze (Windows):

```
http://localhost:8080
```

#### Z telefonu/tabletu (ta sama sieć WiFi):

```
http://192.168.1.100:8080
```
*(zamień `192.168.1.100` na TWÓJ adres WiFi)*

---

## ⚠️ Troubleshooting

### Problem: "Cannot connect" z telefonu

**Sprawdź firewall:**

```powershell
# PowerShell jako Admin:
Get-NetFirewallRule | Where-Object {$_.DisplayName -like "*WSL*"}
```

Jeśli nie ma reguł, dodaj ręcznie:

```powershell
New-NetFirewallRule -DisplayName "WSL Vite" -Direction Inbound -LocalPort 8080 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "WSL Symfony" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
```

### Problem: WSL2 IP się zmienia

WSL2 może dostawać nowy IP przy każdym restarcie. Możesz:

**Opcja 1: Skrypt automatyczny (PowerShell)**

Stwórz plik `update-wsl-ports.ps1`:

```powershell
$wslIP = (wsl hostname -I).Trim()
netsh interface portproxy delete v4tov4 listenport=8080 listenaddress=0.0.0.0
netsh interface portproxy delete v4tov4 listenport=8000 listenaddress=0.0.0.0
netsh interface portproxy add v4tov4 listenport=8080 listenaddress=0.0.0.0 connectport=8080 connectaddress=$wslIP
netsh interface portproxy add v4tov4 listenport=8000 listenaddress=0.0.0.0 connectport=8000 connectaddress=$wslIP
Write-Host "Porty zaktualizowane dla WSL IP: $wslIP"
```

Uruchom przed każdym startem serwerów:
```powershell
.\update-wsl-ports.ps1
```

**Opcja 2: Stały IP dla WSL2**

Stwórz plik `.wslconfig` w `C:\Users\TwojaNazwa\`:

```ini
[wsl2]
networkingMode=mirrored
```

Zrestartuj WSL:
```powershell
wsl --shutdown
```

### Problem: "CORS error" w przeglądarce

Sprawdź plik `.env` w backend - upewnij się że `CORS_ALLOW_ORIGIN` zawiera twój IP:

```env
CORS_ALLOW_ORIGIN=^https?://(localhost|192\.168\.1\.100|192\.168\.1\.\d+)(:[0-9]+)?$
```

### Problem: API zwraca 404

Sprawdź czy backend działa:

W WSL2:
```bash
curl http://localhost:8000/api/auth/users
```

W Windows:
```powershell
curl http://localhost:8000/api/auth/users
```

Z telefonu (w przeglądarce):
```
http://192.168.1.100:8000/api/auth/users
```

---

## 🎯 Szybkie sprawdzenie

### Checklist:

- [ ] Znalazłem adres WiFi w Windows (`ipconfig`)
- [ ] Znalazłem adres WSL2 (`hostname -I`)
- [ ] Skonfigurowałem port forwarding w PowerShell
- [ ] Dodałem reguły firewall
- [ ] Stworzyłem plik `.env` w `frontend/` z VITE_API_URL
- [ ] Zaktualizowałem CORS w `backend/.env`
- [ ] Uruchomiłem Docker Compose
- [ ] Uruchomiłem backend na `0.0.0.0:8000`
- [ ] Uruchomiłem frontend na `0.0.0.0:8080`
- [ ] Telefon jest w tej samej sieci WiFi
- [ ] Mogę otworzyć `http://192.168.1.100:8080` na telefonie

---

## 📱 QR Code (opcjonalnie)

Dla łatwego dostępu z telefonu, wygeneruj QR code:

1. Wejdź na: https://www.qr-code-generator.com/
2. Wpisz: `http://192.168.1.100:8080` (twój adres)
3. Pobierz QR code
4. Zeskanuj telefonem!

---

## 🔧 Komendy pomocnicze

### Restart WSL2:
```powershell
wsl --shutdown
```

### Zobacz aktywne przekierowania:
```powershell
netsh interface portproxy show all
```

### Usuń wszystkie przekierowania:
```powershell
netsh interface portproxy reset
```

### Zobacz reguły firewall:
```powershell
Get-NetFirewallRule | Where-Object {$_.LocalPort -eq 8080 -or $_.LocalPort -eq 8000}
```

---

## ✅ Gotowe!

Aplikacja **PingMe** powinna działać na wszystkich urządzeniach w twojej sieci WiFi! 🎉

**Twój adres na telefonie:**
```
http://192.168.1.100:8080
```
*(zamień na TWÓJ adres WiFi)*
