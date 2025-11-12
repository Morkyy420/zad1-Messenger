# PingMe - Aplikacja Czatu z Kalendarzem

## 📋 Spis Treści
1. [Przegląd Projektu](#przegląd-projektu)
2. [Architektura](#architektura)
3. [Technologie](#technologie)
4. [Struktura Projektu](#struktura-projektu)
5. [Funkcje](#funkcje)
6. [Komponenty Frontend](#komponenty-frontend)
7. [API Endpoints](#api-endpoints)
8. [Baza Danych](#baza-danych)
9. [System Motywów](#system-motywów)
10. [Uruchomienie Projektu](#uruchomienie-projektu)
11. [Status Implementacji](#status-implementacji)

---

## 🎯 Przegląd Projektu

**PingMe** to nowoczesna aplikacja czatu w czasie rzeczywistym z wbudowanym kalendarzem wydarzeń i listą zadań. Projekt składa się z frontendu w Vue.js 3 i backendu w Symfony 7.

### Kluczowe Funkcje:
- ✅ System rejestracji i logowania
- ✅ Czat 1-na-1 z reakcjami emoji
- ✅ Automatyczne odświeżanie wiadomości (polling co 2s)
- ✅ System znajomych
- ✅ Kalendarz wydarzeń dla wszystkich użytkowników
- ✅ Lista zadań (Todo List)
- ✅ 11 różnych motywów kolorystycznych
- ✅ Ustawienia użytkownika (motyw, rozmiar tekstu, avatar emoji)
- ⏳ Przesyłanie zdjęć w czacie (do zrobienia)
- ⏳ Upload zdjęcia profilowego (do zrobienia)
- ⏳ Wskaźnik "pisze..." (do zrobienia)

---

## 🏗️ Architektura

```
┌─────────────────────────────────────────────────────────────┐
│                         FRONTEND                            │
│                    Vue.js 3 + Vue Router                    │
│                                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │  Login/  │  │ Dashboard│  │  Friends │  │  Events  │  │
│  │ Register │  │          │  │          │  │          │  │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘  │
│                      │                                      │
│                ┌─────┴─────┐                               │
│                │           │                               │
│          ┌─────▼────┐ ┌───▼────┐                          │
│          │ ChatRoom │ │Settings│                          │
│          └──────────┘ └────────┘                          │
└────────────────────┬────────────────────────────────────────┘
                     │ HTTP Requests (fetch API)
                     │ Polling (2-3s intervals)
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                         BACKEND                             │
│                      Symfony 7 + Doctrine                   │
│                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌─────────────────┐  │
│  │ Auth         │  │ Conversation │  │ Event/Todo      │  │
│  │ Controller   │  │ Controller   │  │ Controllers     │  │
│  └──────────────┘  └──────────────┘  └─────────────────┘  │
│         │                  │                    │           │
│         └──────────────────┴────────────────────┘           │
│                            │                                │
│                    ┌───────▼────────┐                       │
│                    │   Doctrine ORM │                       │
│                    └───────┬────────┘                       │
└────────────────────────────┼────────────────────────────────┘
                             ▼
                    ┌────────────────┐
                    │   PostgreSQL   │
                    │    Database    │
                    └────────────────┘
```

---

## 💻 Technologie

### Frontend:
- **Vue.js 3.5.13** - Framework JavaScript
- **Vue Router 4.5.0** - Routing
- **Vue CLI 5.0.8** - Build tool
- **CSS Custom Properties** - System motywów
- **Fetch API** - Komunikacja z backendem
- **ES6+** - Nowoczesny JavaScript

### Backend:
- **Symfony 7.3.4** - Framework PHP
- **Doctrine ORM** - Mapowanie obiektowo-relacyjne
- **PostgreSQL** - Baza danych
- **Serializer** - Serializacja danych do JSON
- **CORS** - Obsługa Cross-Origin Requests

### DevOps:
- **Docker** - Konteneryzacja
- **Docker Compose** - Orkiestracja kontenerów
- **Nginx** - Serwer WWW
- **Mercure** - Real-time updates (gotowe do użycia)

---

## 📁 Struktura Projektu

```
my_first_project/
│
├── frontend/                           # Aplikacja Vue.js
│   ├── public/                         # Pliki statyczne
│   ├── src/
│   │   ├── assets/                     # Zasoby (CSS, obrazy)
│   │   │   └── main.css               # Globalne style
│   │   ├── components/                 # Komponenty Vue
│   │   │   ├── Login.vue              # Strona logowania
│   │   │   ├── Register.vue           # Strona rejestracji
│   │   │   ├── Dashboard.vue          # Główny panel
│   │   │   ├── Friends.vue            # Lista znajomych
│   │   │   ├── ChatRoom.vue           # Okno czatu
│   │   │   ├── Settings.vue           # Ustawienia
│   │   │   └── Events.vue             # Kalendarz i wydarzenia
│   │   ├── router/
│   │   │   └── index.js               # Konfiguracja routingu
│   │   ├── themes.js                   # Definicje motywów
│   │   ├── App.vue                     # Główny komponent
│   │   └── main.js                     # Entry point
│   ├── vue.config.js                   # Konfiguracja Vue CLI
│   ├── package.json                    # Zależności npm
│   └── .env                            # Zmienne środowiskowe
│
├── src/                                # Backend Symfony
│   ├── Controller/
│   │   ├── AuthController.php         # Autentykacja
│   │   ├── ConversationController.php # Czat i wiadomości
│   │   ├── EventController.php        # Wydarzenia (TODO)
│   │   └── TodoController.php         # Lista zadań (TODO)
│   ├── Entity/
│   │   ├── User.php                   # Encja użytkownika
│   │   ├── Conversation.php           # Encja konwersacji
│   │   ├── Message.php                # Encja wiadomości
│   │   ├── Event.php                  # Encja wydarzenia (TODO)
│   │   └── Todo.php                   # Encja zadania (TODO)
│   └── Repository/
│       ├── UserRepository.php
│       ├── ConversationRepository.php
│       └── MessageRepository.php
│
├── docker-compose.yml                  # Konfiguracja Docker
├── .env                                # Zmienne środowiskowe Symfony
└── PROJECT_OVERVIEW.md                 # Ten plik
```

---

## 🎨 Komponenty Frontend

### 1. **App.vue**
- Główny komponent aplikacji
- Definiuje CSS variables dla systemu motywów
- Zawiera router-view

### 2. **Login.vue** & **Register.vue**
- Formularze logowania i rejestracji
- Walidacja danych
- Logo z animacją
- Responsive design
- **Endpoints:**
  - POST `/api/auth/register` - rejestracja
  - POST `/api/auth/login` - logowanie

### 3. **Dashboard.vue**
- Główny panel aplikacji
- Sidebar z nawigacją:
  - 👥 Znajomi
  - 📅 Wydarzenia
  - ⚙️ Ustawienia
- Logo PingMe
- Avatar użytkownika
- Przycisk wylogowania
- Obsługa ładowania ustawień z serwera

### 4. **Friends.vue**
- Lista znajomych użytkownika
- Przycisk "Dodaj znajomego"
- Lista wszystkich użytkowników
- Ostatnia wiadomość w konwersacji
- Status przeczytania (✓/✓✓)
- Przycisk "Czat" do otwierania rozmowy
- **Endpoints:**
  - GET `/api/auth/users` - lista użytkowników
  - GET `/api/auth/users/{id}/friends` - znajomi użytkownika
  - POST `/api/auth/users/{friendId}/friends` - dodanie znajomego
  - GET `/api/conversations/{userId}` - konwersacje użytkownika

### 5. **ChatRoom.vue**
- Okno czatu 1-na-1
- Auto-refresh co 2 sekundy
- Wysyłanie wiadomości (Enter lub przycisk)
- System reakcji emoji (rozwijane menu "+")
- Aktywne reakcje pod wiadomościami
- Nowoczesne wyświetlanie czasu:
  - "teraz" - < 1 min
  - "X min temu" - < 60 min
  - "12:45" - dzisiaj
  - "wczoraj 12:45" - wczoraj
  - "15.11 12:45" - starsze
- Automatyczne scrollowanie do dołu
- Oznaczanie jako przeczytane
- **Endpoints:**
  - GET `/api/conversations/{userId}/with/{friendId}` - pobranie/utworzenie konwersacji
  - GET `/api/conversations/{conversationId}/messages` - wiadomości
  - POST `/api/conversations/{conversationId}/messages` - wysłanie wiadomości
  - POST `/api/conversations/{conversationId}/messages/{messageId}/reaction` - reakcja
  - POST `/api/conversations/{conversationId}/messages/mark-read` - oznacz jako przeczytane

### 6. **Settings.vue**
- Wybór motywu (11 opcji)
- Rozmiar tekstu (mały, średni, duży)
- Wybór avatara emoji (24 opcje)
- Przycisk "Zapisz ustawienia"
- Podgląd na żywo
- **Endpoints:**
  - GET `/api/auth/users/{userId}/settings` - pobranie ustawień
  - PUT `/api/auth/users/{userId}/settings` - zapisanie ustawień

### 7. **Events.vue** ⭐ NOWY
- Kalendarz miesięczny z nawigacją
- Dodawanie wydarzeń (tytuł, opis, data, godzina)
- Wydarzenia wszystkich użytkowników
- Wizualne wskaźniki (kropka) na dniach z wydarzeniami
- Lista wydarzeń dla wybranego dnia
- Usuwanie wydarzeń
- Lista zadań (Todo List)
- Dodawanie/usuwanie/oznaczanie zadań jako wykonane
- Layout 3-kolumnowy (kalendarz | wydarzenia | todo)
- **Endpoints (TODO - do implementacji):**
  - GET `/api/events` - wszystkie wydarzenia
  - POST `/api/events` - dodanie wydarzenia
  - DELETE `/api/events/{id}` - usunięcie wydarzenia
  - GET `/api/todos/{userId}` - zadania użytkownika
  - POST `/api/todos` - dodanie zadania
  - PATCH `/api/todos/{id}` - zmiana statusu
  - DELETE `/api/todos/{id}` - usunięcie zadania

---

## 🔌 API Endpoints

### Autentykacja (`/api/auth/`)

| Metoda | Endpoint | Opis | Parametry |
|--------|----------|------|-----------|
| POST | `/register` | Rejestracja | username, email, password |
| POST | `/login` | Logowanie | email, password |
| GET | `/users` | Lista użytkowników | - |
| GET | `/users/{id}/friends` | Znajomi użytkownika | - |
| POST | `/users/{friendId}/friends` | Dodaj znajomego | currentUserId |
| GET | `/users/{id}/settings` | Ustawienia | - |
| PUT | `/users/{id}/settings` | Zapisz ustawienia | theme, textSize, avatar |

### Konwersacje (`/api/conversations/`)

| Metoda | Endpoint | Opis | Parametry |
|--------|----------|------|-----------|
| GET | `/{userId}` | Konwersacje użytkownika | - |
| GET | `/{userId}/with/{friendId}` | Pobrań/utwórz konwersację | - |
| GET | `/{conversationId}/messages` | Wiadomości | - |
| POST | `/{conversationId}/messages` | Wyślij wiadomość | content, authorId |
| POST | `/{conversationId}/messages/{messageId}/reaction` | Dodaj reakcję | type |
| POST | `/{conversationId}/messages/mark-read` | Oznacz jako przeczytane | userId |

### Wydarzenia (`/api/events/`) ⚠️ DO ZROBIENIA

| Metoda | Endpoint | Opis | Parametry |
|--------|----------|------|-----------|
| GET | `/` | Wszystkie wydarzenia | - |
| POST | `/` | Dodaj wydarzenie | title, description, date, time, userId |
| DELETE | `/{id}` | Usuń wydarzenie | - |

### Zadania (`/api/todos/`) ⚠️ DO ZROBIENIA

| Metoda | Endpoint | Opis | Parametry |
|--------|----------|------|-----------|
| GET | `/{userId}` | Zadania użytkownika | - |
| POST | `/` | Dodaj zadanie | text, userId |
| PATCH | `/{id}` | Zmień status | completed |
| DELETE | `/{id}` | Usuń zadanie | - |

---

## 🗄️ Baza Danych

### Schemat Tabel

#### **users**
```sql
id: SERIAL PRIMARY KEY
username: VARCHAR(255) UNIQUE NOT NULL
email: VARCHAR(255) UNIQUE NOT NULL
password: VARCHAR(255) NOT NULL -- hashed
avatar: VARCHAR(10) -- emoji
theme: VARCHAR(50) DEFAULT 'purpleDream'
text_size: VARCHAR(20) DEFAULT 'medium'
created_at: TIMESTAMP DEFAULT NOW()
```

#### **conversations**
```sql
id: SERIAL PRIMARY KEY
created_at: TIMESTAMP DEFAULT NOW()
```

#### **user_conversation** (Many-to-Many)
```sql
user_id: INTEGER REFERENCES users(id)
conversation_id: INTEGER REFERENCES conversations(id)
PRIMARY KEY (user_id, conversation_id)
```

#### **messages**
```sql
id: SERIAL PRIMARY KEY
conversation_id: INTEGER REFERENCES conversations(id)
author_id: INTEGER REFERENCES users(id)
content: TEXT NOT NULL
reactions: JSON -- { "heart": 2, "like": 1, ... }
read_at: TIMESTAMP NULL
created_at: TIMESTAMP DEFAULT NOW()
```

#### **events** ⚠️ DO ZROBIENIA
```sql
id: SERIAL PRIMARY KEY
user_id: INTEGER REFERENCES users(id)
title: VARCHAR(255) NOT NULL
description: TEXT
date: DATE NOT NULL
time: TIME NOT NULL
created_at: TIMESTAMP DEFAULT NOW()
```

#### **todos** ⚠️ DO ZROBIENIA
```sql
id: SERIAL PRIMARY KEY
user_id: INTEGER REFERENCES users(id)
text: VARCHAR(255) NOT NULL
completed: BOOLEAN DEFAULT FALSE
created_at: TIMESTAMP DEFAULT NOW()
```

---

## 🎨 System Motywów

### Dostępne Motywy (11):

1. **Purple Dream** (domyślny) - fioletowy gradient
2. **Ocean Blue** - niebieski ocean
3. **Forest Green** - zielony las
4. **Sunset Orange** - pomarańczowy zachód
5. **Cherry Blossom** - różowy kwiat wiśni
6. **Midnight Dark** - ciemny indygo
7. **Tokyo Night** ⭐ - inspirowany edytorem kodu
8. **Cyberpunk** ⭐ - neonowy zielony
9. **Nord** ⭐ - skandynawski minimalizm
10. **Monokai** ⭐ - klasyczny edytor
11. **Dracula** ⭐ - popularny ciemny motyw

### CSS Variables (używane przez motywy):
```css
--color-primary          /* Główny kolor */
--color-primaryDark      /* Ciemniejszy odcień */
--color-primaryLight     /* Jaśniejszy odcień */
--color-primaryLighter   /* Najjaśniejszy odcień */
--color-background1      /* Tło 1 */
--color-background2      /* Tło 2 */
--color-background3      /* Tło 3 */
--color-cardBg          /* Tło kart (gradient) */
--color-border          /* Obramowania */
--color-text            /* Kolor tekstu */
--color-textLight       /* Jasny tekst */
--color-success         /* Kolor sukcesu */
--color-error           /* Kolor błędu */
```

### Plik: `frontend/src/themes.js`
- Definicje wszystkich motywów
- Funkcja `applyTheme(themeName)` - aplikuje motyw
- Lista avatarów emoji (24 opcje)
- Funkcja `getTextSizeClass(size)` - rozmiar tekstu

---

## 🚀 Uruchomienie Projektu

### Wymagania:
- Docker & Docker Compose
- Node.js 18+ i npm
- PHP 8.2+
- PostgreSQL 15+

### Krok 1: Backend (Symfony)
```bash
cd /home/dmmos/projekty/my_first_project

# Uruchom kontenery Docker
docker-compose up -d

# Zainstaluj zależności
composer install

# Utwórz bazę danych
php bin/console doctrine:database:create

# Wykonaj migracje
php bin/console doctrine:migrations:migrate

# Backend dostępny na: http://localhost:8000
```

### Krok 2: Frontend (Vue.js)
```bash
cd /home/dmmos/projekty/my_first_project/frontend

# Zainstaluj zależności
npm install

# Uruchom dev server
npm run serve

# Frontend dostępny na: http://localhost:8080
```

### Zmienne Środowiskowe:

**Frontend (.env):**
```
VUE_APP_API_URL=http://localhost:8000/api
```

**Backend (.env):**
```
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$
```

---

## ✅ Status Implementacji

### ✅ Zaimplementowane (DZIAŁAJĄ):

#### Frontend:
- ✅ Logowanie i rejestracja
- ✅ Dashboard z nawigacją
- ✅ Lista znajomych
- ✅ Dodawanie znajomych
- ✅ Czat 1-na-1
- ✅ Wysyłanie wiadomości (Enter + przycisk)
- ✅ Reakcje emoji (rozwijane menu "+")
- ✅ Auto-refresh wiadomości (2s)
- ✅ Nowoczesne wyświetlanie czasu
- ✅ Oznaczanie wiadomości jako przeczytane
- ✅ Ustawienia (motyw, rozmiar tekstu, avatar)
- ✅ Zapisywanie ustawień do serwera
- ✅ 11 motywów kolorystycznych
- ✅ Kalendarz wydarzeń (UI gotowe)
- ✅ Lista Todo (UI gotowe)
- ✅ Pełna responsywność (mobile, tablet, desktop)

#### Backend:
- ✅ System autentykacji
- ✅ Zarządzanie użytkownikami
- ✅ System znajomych
- ✅ Konwersacje i wiadomości
- ✅ Reakcje na wiadomości
- ✅ Status przeczytania
- ✅ Ustawienia użytkownika
- ✅ CORS dla frontendu
- ✅ Serializacja JSON

### ⏳ Do Zrobienia (TODO):

#### Backend - Wydarzenia i Todo:
1. **EventController.php** - CRUD dla wydarzeń
2. **TodoController.php** - CRUD dla zadań
3. **Event.php** entity - encja wydarzenia
4. **Todo.php** entity - encja zadania
5. Migracje bazy danych dla nowych tabel

#### Frontend - Funkcje do dodania:
1. **Wskaźnik "pisze..."**
   - Pokazywanie gdy użytkownik pisze
   - WebSocket lub polling
   - Animacja trzech kropek

2. **Przesyłanie zdjęć w czacie**
   - Upload pliku (button + drag&drop)
   - Wyświetlanie miniatur
   - Pełny podgląd po kliknięciu
   - Backend: endpoint do uploadu

3. **Zdjęcie profilowe**
   - Upload w ustawieniach
   - Wyświetlanie w sidebarzena miejscu emoji
   - Backend: przechowywanie plików

---

## 🔍 Najważniejsze Lokalizacje

### Gdy trzeba zmienić:

**Motywy kolorystyczne:**
- `frontend/src/themes.js`

**Routing (dodać nową stronę):**
- `frontend/src/router/index.js`

**API URL:**
- `frontend/.env` (VUE_APP_API_URL)

**Główne style:**
- `frontend/src/assets/main.css`
- CSS variables w `frontend/src/App.vue`

**Backend routes:**
- `src/Controller/*Controller.php` (atrybuty #[Route])

**Baza danych:**
- `src/Entity/*.php` (definicje)
- `migrations/` (migracje)

**CORS:**
- `config/packages/nelmio_cors.yaml`

---

## 🐛 Znane Problemy

1. **Auto-refresh** - obecnie polling co 2-3s, do rozważenia WebSocket
2. **Reakcje** - backend zwiększa licznik, nie śledzi kto dodał reakcję
3. **Brak paginacji** - wszystkie wiadomości ładowane naraz
4. **Brak walidacji plików** - do implementacji z uploadem zdjęć

---

## 📝 Notatki Deweloperskie

### Konwencje:
- **Frontend**: Composition API (Vue 3 style)
- **Backend**: Controller → Service → Repository pattern (częściowo)
- **CSS**: CSS Variables + Scoped styles
- **Nazewnictwo**: camelCase (JS), snake_case (PHP/SQL)

### Dobre praktyki zastosowane:
- Wszystkie kolory przez CSS variables (łatwa zmiana motywu)
- Komponenty Vue jako Single File Components (.vue)
- Responsive design (mobile-first approach)
- Error handling (console.error + user feedback)
- Auto-scrolling w czacie
- Clean code (małe funkcje, jasne nazwy)

### Do poprawy:
- Dodać WebSocket dla real-time
- Implementować paginację
- Dodać cache dla ustawień
- Ulepszyć error handling (toasty zamiast console)
- Dodać loading states
- Unit testy

---

## 📞 Wsparcie

Aby kontynuować pracę nad projektem w Claude CLI:

1. Skopiuj zawartość tego pliku do prompta
2. Opisz co chcesz zmienić/dodać
3. Claude będzie miał pełny kontekst projektu

**Przykład:**
```
Kontynuuję pracę nad projektem PingMe. [wklej PROJECT_OVERVIEW.md]

Teraz chcę: [opisz zadanie]
```

---

**Wersja dokumentacji:** 1.0
**Ostatnia aktualizacja:** 2025-11-09
**Autor:** Claude (Anthropic)
