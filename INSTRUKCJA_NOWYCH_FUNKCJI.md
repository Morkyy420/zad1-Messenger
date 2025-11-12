# Instrukcja nowych funkcji

## Co zostało dodane?

### 1. System wydarzeń (Events) i lista zadań (Todos)

#### Backend:
- ✅ Utworzono Entity: `Event` i `Todo`
- ✅ Utworzono Repository: `EventRepository` i `TodoRepository`
- ✅ Utworzono Controllers: `EventController` i `TodoController`

#### Endpointy API:

**Wydarzenia:**
- `GET /api/events` - pobierz wszystkie wydarzenia
- `POST /api/events` - dodaj nowe wydarzenie
- `DELETE /api/events/{id}` - usuń wydarzenie

**Todos:**
- `GET /api/todos/{userId}` - pobierz todos użytkownika
- `POST /api/todos` - dodaj nowe todo
- `PATCH /api/todos/{id}` - zaktualizuj todo (np. oznacz jako ukończone)
- `DELETE /api/todos/{id}` - usuń todo

#### Frontend:
- ✅ Komponent `Events.vue` już obsługuje wyświetlanie wydarzeń w kalendarzu
- ✅ Lista "rzeczy do zrobienia" jest wyświetlana po prawej stronie

### 2. Wysyłanie mediów w czacie

#### Backend:
- ✅ Rozszerzono `Message` entity o pola:
  - `attachmentType` - typ załącznika (image, video, gif)
  - `attachmentUrl` - URL do załącznika
  - `attachmentName` - nazwa pliku
- ✅ Dodano endpoint `POST /api/conversations/{conversationId}/messages/upload` do przesyłania plików
- ✅ Walidacja plików: max 10MB, tylko obrazy i wideo

#### Frontend (ChatRoom.vue):
- ✅ **Przycisk załącznika (📎)** - po lewej stronie pola tekstowego
  - Kliknij, aby wyświetlić menu z opcjami
  - Wybierz "Obraz/Wideo" lub "GIF"

- ✅ **Drag & Drop** - przeciągnij plik z pulpitu na obszar czatu
  - Obsługiwane formaty: obrazy (JPEG, PNG, GIF, WebP) i wideo (MP4, WebM, OGG)
  - Pokazuje podgląd przed wysłaniem
  - Możliwość anulowania (×)

- ✅ **Biblioteka GIFów** - 12 popularnych GIFów do szybkiego wysłania
  - Kliknij przycisk załącznika → wybierz "GIF"
  - Wybierz GIF z siatki i zostanie automatycznie wysłany

- ✅ **Wyświetlanie mediów w wiadomościach:**
  - Obrazy - kliknij, aby zobaczyć w pełnym rozmiarze
  - Wideo - odtwarzacz z kontrolkami
  - GIFy - animowane

## Jak uruchomić?

### Krok 1: Migracja bazy danych

Musisz utworzyć nowe tabele w bazie danych. W pliku `.env` jest już ustawione SQLite.

**WAŻNE:** Zainstaluj wymagane rozszerzenie PHP:
```bash
# Na Ubuntu/Debian:
sudo apt-get install php-sqlite3

# Na innych systemach sprawdź dokumentację
```

Następnie uruchom migrację:
```bash
# Utwórz bazę danych (jeśli nie istnieje)
php bin/console doctrine:database:create

# Utwórz migrację
php bin/console doctrine:migrations:diff

# Wykonaj migrację
php bin/console doctrine:migrations:migrate
```

### Krok 2: Uprawnienia do katalogu uploads

Upewnij się, że katalog na przesyłane pliki istnieje i ma odpowiednie uprawnienia:
```bash
mkdir -p public/uploads/chat
chmod 755 public/uploads/chat
```

### Krok 3: Uruchom backend Symfony

```bash
symfony server:start
# lub
php -S localhost:8000 -t public
```

### Krok 4: Uruchom frontend Vue

```bash
cd frontend
npm install  # jeśli nie zainstalowano
npm run serve
```

## Testowanie nowych funkcji

### Wydarzenia:
1. Przejdź do zakładki "Wydarzenia" w aplikacji
2. Kliknij "➕ Dodaj wydarzenie"
3. Wypełnij formularz (tytuł, opis, data, godzina)
4. Wydarzenie pojawi się w kalendarzu na odpowiedniej dacie

### Todos:
1. W tej samej zakładce "Wydarzenia" po prawej stronie znajduje się lista "Rzeczy do zrobienia"
2. Kliknij przycisk "+" aby dodać nowe zadanie
3. Możesz oznaczyć jako ukończone lub usunąć

### Wysyłanie mediów w czacie:
1. Otwórz czat ze znajomym
2. Kliknij przycisk 📎 (agrafka)
3. Wybierz opcję:
   - **Obraz/Wideo** - wybierz plik z dysku
   - **GIF** - wybierz z biblioteki
4. Lub po prostu **przeciągnij plik** z pulpitu na obszar czatu
5. Wyślij wiadomość!

## Uwagi

- Maksymalny rozmiar pliku: **10MB**
- Obsługiwane formaty obrazów: JPEG, JPG, PNG, GIF, WebP
- Obsługiwane formaty wideo: MP4, WebM, OGG
- Pliki są przechowywane w katalogu `public/uploads/chat`
- GIFy są pobierane z serwisu Giphy (wymagane połączenie internetowe)

## Rozwiązywanie problemów

### Problem: "could not find driver"
**Rozwiązanie:** Zainstaluj PHP SQLite extension (patrz Krok 1)

### Problem: "Failed to upload file"
**Rozwiązanie:** Sprawdź uprawnienia do katalogu `public/uploads/chat` (patrz Krok 2)

### Problem: Wydarzenia nie są widoczne
**Rozwiązanie:** Sprawdź, czy migracja została wykonana poprawnie i tabela `event` istnieje w bazie danych

### Problem: Nie można wysłać załącznika
**Rozwiązanie:** Sprawdź konsolę przeglądarki i logi Symfony, upewnij się że backend działa poprawnie

## Podsumowanie

✅ System wydarzeń działa - możesz dodawać, wyświetlać i usuwać wydarzenia
✅ Lista todos działa - możesz zarządzać zadaniami
✅ Czat obsługuje obrazy, wideo i GIFy
✅ Drag & drop działa
✅ Biblioteka GIFów jest dostępna

Miłego korzystania! 🎉
