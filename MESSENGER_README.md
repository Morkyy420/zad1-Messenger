# Aplikacja Messenger - Vue.js + Symfony

Aplikacja messenger z funkcją reakcji na wiadomości, zbudowana przy użyciu Vue.js (frontend) i Symfony (backend).

## Funkcje

- Wysyłanie i odbieranie wiadomości w czasie rzeczywistym
- Reakcje na wiadomości: ❤️ Serce, 👍 Lubię to, 😂 Śmieszne, 😮 Wow
- Nowoczesny interfejs z animacjami
- API REST w Symfony
- Frontend w Vue.js 3

## Uruchomienie aplikacji

### 1. Uruchom Docker Compose (Backend + Baza danych)

```bash
docker compose up -d
```

To uruchomi:
- PostgreSQL (baza danych)
- Nginx (serwer web)
- PHP-FPM (Symfony)
- Mercure (WebSocket)

### 2. Uruchom frontend Vue.js

W nowym terminalu:

```bash
cd frontend
npm run serve
```

Frontend będzie dostępny pod adresem: **http://localhost:8080**

### 3. Backend API

Backend Symfony będzie dostępny pod adresem: **http://localhost:8000**

API endpoints:
- `GET /api/messages` - pobranie wszystkich wiadomości
- `POST /api/messages` - wysłanie nowej wiadomości
- `POST /api/messages/{id}/reaction` - dodanie reakcji do wiadomości

## Struktura projektu

```
.
├── frontend/                      # Aplikacja Vue.js
│   ├── src/
│   │   ├── components/
│   │   │   └── Messenger.vue     # Komponent messengera
│   │   ├── App.vue               # Główny komponent
│   │   └── main.js
│   └── package.json
│
├── src/                          # Backend Symfony
│   ├── Controller/
│   │   └── ChatController.php   # API endpoints
│   └── Entity/
│       └── Message.php          # Entity wiadomości
│
└── docker-compose.yml           # Konfiguracja Docker
```

## Konfiguracja

### Backend (Symfony)

Konfiguracja znajduje się w `.env`:
- `DATABASE_URL` - połączenie z bazą danych PostgreSQL
- `CORS_ALLOW_ORIGIN` - domeny dozwolone dla CORS (domyślnie localhost)

### Frontend (Vue.js)

W pliku `frontend/src/components/Messenger.vue`, linia 59:
```javascript
apiUrl: 'http://localhost:8000/api'
```

## Rozwój aplikacji

### Dodawanie nowych reakcji

1. W `src/Entity/Message.php`, zaktualizuj domyślne reakcje w konstruktorze
2. W `src/Controller/ChatController.php`, dodaj nowy typ reakcji do walidacji (linia 106)
3. W `frontend/src/components/Messenger.vue`, dodaj nowy emoji do `reactionEmojis` (linia 52)

### Stwórz nową migrację bazy danych

```bash
docker compose exec app php bin/console doctrine:migrations:diff
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

## Troubleshooting

### Backend nie działa
```bash
docker compose logs app
```

### Frontend nie działa
```bash
cd frontend
npm install
npm run serve
```

### Baza danych nie działa
```bash
docker compose restart database
docker compose exec app php bin/console doctrine:database:create --if-not-exists
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

## Porty

- **8080** - Frontend Vue.js
- **8000** - Backend Symfony API
- **5432** - PostgreSQL (tylko wewnątrz Docker)
- **9090** - Mercure Hub

## Technologie

- **Frontend**: Vue.js 3, JavaScript, CSS
- **Backend**: Symfony 6, PHP 8.1+
- **Baza danych**: PostgreSQL 16
- **Konteneryzacja**: Docker, Docker Compose
- **Real-time**: Mercure
