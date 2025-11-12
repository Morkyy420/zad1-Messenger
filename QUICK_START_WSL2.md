# 🚀 PingMe - Szybki Start (WSL2)

## ⚡ Super szybkie uruchomienie

### Krok 1: Konfiguracja Windows (JEDNORAZOWO)

Otwórz **PowerShell jako Administrator** i uruchom:

```powershell
cd \path\to\my_first_project
.\setup-wsl-network.ps1
```

Ten skrypt automatycznie:
- ✅ Znajdzie adres IP WSL2
- ✅ Znajdzie adres IP WiFi Windows
- ✅ Skonfiguruje przekierowanie portów
- ✅ Doda reguły firewall

---

### Krok 2: Edytuj frontend/.env

W **WSL2**:

```bash
cd /home/dmmos/projekty/my_first_project/frontend
nano .env
```

Zamień IP na adres z poprzedniego kroku:

```env
VITE_API_URL=http://TWÓJ_WINDOWS_IP:8000/api
```

Przykład: `VITE_API_URL=http://192.168.1.100:8000/api`

---

### Krok 3: Uruchom serwery

#### Terminal 1 - Backend:

```bash
cd /home/dmmos/projekty/my_first_project
docker compose up -d
php -S 0.0.0.0:8000 -t public
```

#### Terminal 2 - Frontend:

```bash
cd /home/dmmos/projekty/my_first_project/frontend
npm run dev -- --host 0.0.0.0 --port 8080
```

---

### Krok 4: Otwórz w przeglądarce

**Na komputerze:**
```
http://localhost:8080
```

**Na telefonie (ta sama sieć WiFi):**
```
http://TWÓJ_WINDOWS_IP:8080
```

---

## 🔧 Przydatne komendy

### Restart przekierowań portów:

W **PowerShell jako Admin**:

```powershell
.\setup-wsl-network.ps1
```

### Zobacz aktualne przekierowania:

```powershell
netsh interface portproxy show all
```

### Usuń wszystkie przekierowania:

```powershell
netsh interface portproxy reset
```

### Restart WSL2:

```powershell
wsl --shutdown
```

---

## 📋 Checklist przed uruchomieniem

- [ ] Uruchomiłem `setup-wsl-network.ps1` w PowerShell jako Admin
- [ ] Edytowałem `frontend/.env` z poprawnym IP
- [ ] Uruchomiłem Docker Compose
- [ ] Backend działa na `0.0.0.0:8000`
- [ ] Frontend działa na `0.0.0.0:8080`
- [ ] Telefon jest w tej samej sieci WiFi co komputer

---

## ⚠️ Najczęstsze problemy

### "Cannot connect" z telefonu

1. Sprawdź firewall:
   ```powershell
   Get-NetFirewallRule | Where-Object {$_.DisplayName -like "*WSL*"}
   ```

2. Uruchom ponownie skrypt:
   ```powershell
   .\setup-wsl-network.ps1
   ```

### "CORS error" w przeglądarce

Sprawdź `.env` w backend:
```env
CORS_ALLOW_ORIGIN=^https?://(localhost|192\.168\.1\.\d+)(:[0-9]+)?$
```

### WSL2 IP się zmienia

Uruchom `setup-wsl-network.ps1` ponownie.

---

## 📚 Więcej informacji

Zobacz szczegółowy przewodnik:
- `WSL2_NETWORK_GUIDE.md` - pełna dokumentacja
- `NETWORK_SETUP.md` - ogólne informacje

---

## ✅ Gotowe!

Aplikacja **PingMe** powinna działać! 🎉
