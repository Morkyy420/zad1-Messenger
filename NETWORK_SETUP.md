# Jak uruchomić PingMe w sieci lokalnej (WiFi)

## Krok 1: Znajdź swój adres IP w sieci lokalnej

### Windows (WSL2):
```bash
# W WSL2 terminal:
ip addr show eth0 | grep "inet\b" | awk '{print $2}' | cut -d/ -f1

# Lub w Windows PowerShell:
ipconfig
# Szukaj "IPv4 Address" w sekcji "Wireless LAN adapter Wi-Fi" lub "Ethernet adapter"
# Przykład: 192.168.1.100
```

### Linux:
```bash
hostname -I
# Lub
ip addr show | grep "inet " | grep -v 127.0.0.1
```

### MacOS:
```bash
ipconfig getifaddr en0
# Lub
ifconfig | grep "inet " | grep -v 127.0.0.1
```

**Przykładowy wynik:** `192.168.1.100` (twój adres będzie inny)

---

## Krok 2: Uruchom frontend Vite z dostępem sieciowym

### Edytuj package.json w folderze frontend:

```bash
cd /home/dmmos/projekty/my_first_project/frontend
```

Otwórz `package.json` i zmień skrypt `dev`:

```json
{
  "scripts": {
    "dev": "vite --host 0.0.0.0 --port 8080"
  }
}
```

Lub uruchom bezpośrednio:
```bash
npm run dev -- --host 0.0.0.0 --port 8080
```

**Co to robi:**
- `--host 0.0.0.0` - pozwala na dostęp z innych urządzeń w sieci
- `--port 8080` - określa port (możesz zmienić na inny)

---

## Krok 3: Skonfiguruj backend Symfony

### Edytuj plik `.env` w głównym folderze projektu:

```bash
cd /home/dmmos/projekty/my_first_project
```

Otwórz `.env` i zaktualizuj `CORS_ALLOW_ORIGIN`:

```env
# .env
CORS_ALLOW_ORIGIN=^https?://(localhost|192\.168\.1\.100|192\.168\.1\.\d+)(:[0-9]+)?$
```

**Zamień `192.168.1` na pierwsze 3 oktety twojego IP!**

### Uruchom serwer Symfony z dostępem sieciowym:

```bash
# Symfony CLI (preferowane):
symfony server:start --no-tls --port=8000 --allow-http --daemon

# Lub PHP built-in server:
php -S 0.0.0.0:8000 -t public
```

---

## Krok 4: Zaktualizuj API URL w frontendzie

### Metoda A: Zmienna środowiskowa (NAJLEPSZA)

Stwórz plik `.env` w folderze `frontend`:

```bash
cd frontend
nano .env
```

Dodaj (zamień `192.168.1.100` na TWÓJ adres IP):

```env
VITE_API_URL=http://192.168.1.100:8000/api
```

Następnie w każdym komponencie Vue zamień:
```javascript
apiUrl: 'http://localhost:8000/api'
```

Na:
```javascript
apiUrl: import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
```

### Metoda B: Ręczna zmiana (SZYBSZA dla testów)

Edytuj bezpośrednio w komponentach:
- `src/components/Login.vue`
- `src/components/Register.vue`
- `src/components/Friends.vue`
- `src/components/ChatRoom.vue`

Zmień:
```javascript
apiUrl: 'http://localhost:8000/api'
```

Na (zamień IP na swoje):
```javascript
apiUrl: 'http://192.168.1.100:8000/api'
```

---

## Krok 5: Uruchom aplikację

1. **Backend** (terminal 1):
```bash
cd /home/dmmos/projekty/my_first_project
docker compose up -d  # Uruchom bazę danych
symfony server:start --no-tls --port=8000 --allow-http
```

2. **Frontend** (terminal 2):
```bash
cd /home/dmmos/projekty/my_first_project/frontend
npm run dev -- --host 0.0.0.0 --port 8080
```

---

## Krok 6: Dostęp z urządzeń mobilnych

### Na telefonie/tablecie w tej samej sieci WiFi:

1. Otwórz przeglądarkę (Chrome, Safari, Firefox)
2. Wpisz adres (zamień `192.168.1.100` na TWÓJ IP):
   ```
   http://192.168.1.100:8080
   ```

### Troubleshooting:

#### Nie mogę się połączyć z telefonu?

1. **Sprawdź firewall na komputerze:**

   **Windows:**
   ```powershell
   # Dodaj regułę firewall dla portów 8000 i 8080:
   netsh advfirewall firewall add rule name="Vite Dev Server" dir=in action=allow protocol=TCP localport=8080
   netsh advfirewall firewall add rule name="Symfony API" dir=in action=allow protocol=TCP localport=8000
   ```

   **Linux:**
   ```bash
   sudo ufw allow 8080/tcp
   sudo ufw allow 8000/tcp
   ```

2. **Sprawdź czy telefon jest w tej samej sieci WiFi**

3. **Sprawdź czy serwery działają:**
   ```bash
   # Na komputerze:
   curl http://localhost:8080
   curl http://localhost:8000/api
   ```

4. **Sprawdź IP ponownie** - może się zmieniać przy restarcie routera

---

## Przykład kompletnej konfiguracji

### Twój IP: `192.168.1.100`

**Frontend `.env`:**
```env
VITE_API_URL=http://192.168.1.100:8000/api
```

**Backend `.env`:**
```env
CORS_ALLOW_ORIGIN=^https?://(localhost|192\.168\.1\.\d+)(:[0-9]+)?$
```

**Dostęp z komputera:**
```
http://localhost:8080
```

**Dostęp z telefonu (ta sama sieć WiFi):**
```
http://192.168.1.100:8080
```

---

## Dodatkowe wskazówki

### Stały adres IP (opcjonalnie)

Aby IP się nie zmieniało, ustaw **rezerwację DHCP** w routerze:
1. Wejdź do panelu routera (często `192.168.1.1` lub `192.168.0.1`)
2. Znajdź "DHCP Reservation" lub "Static IP"
3. Przypisz MAC address komputera do stałego IP

### QR Code dla łatwego dostępu

Wygeneruj QR code z adresem `http://192.168.1.100:8080` używając:
- https://www.qr-code-generator.com/
- Zeskanuj telefonem aby szybko otworzyć aplikację

### HTTPS (opcjonalnie, dla bardziej zaawansowanych)

Jeśli potrzebujesz HTTPS (np. dla PWA):
1. Użyj `mkcert` do wygenerowania lokalnego certyfikatu
2. Skonfiguruj Vite i Symfony z certyfikatami SSL
3. Zaufaj certyfikatowi na urządzeniu mobilnym

---

## Gotowe!

Twoja aplikacja **PingMe** powinna być teraz dostępna dla wszystkich urządzeń w twojej sieci WiFi! 🎉

Możesz teraz:
- ✅ Logować się na telefonie
- ✅ Zmieniać motywy
- ✅ Wysyłać wiadomości
- ✅ Używać wszystkich funkcji responsywnych
