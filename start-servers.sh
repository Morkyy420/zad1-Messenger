#!/bin/bash

# PingMe - Skrypt uruchamiania serwerów

echo "=================================="
echo "  🚀 PingMe Server Startup"
echo "=================================="
echo ""

# Kolory
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Sprawdź czy jesteśmy w odpowiednim katalogu
if [ ! -f "docker/compose.yaml" ]; then
    echo -e "${RED}❌ Błąd: Nie znaleziono pliku docker/compose.yaml${NC}"
    echo -e "${YELLOW}Uruchom ten skrypt z katalogu głównego projektu${NC}"
    exit 1
fi

# Pokaż adres IP WSL2
WSL_IP=$(hostname -I | awk '{print $1}')
echo -e "${CYAN}📍 Adres IP WSL2: ${GREEN}$WSL_IP${NC}"

# Sprawdź czy frontend/.env istnieje
if [ ! -f "frontend/.env" ]; then
    echo ""
    echo -e "${YELLOW}⚠️  Brak pliku frontend/.env${NC}"
    echo -e "${YELLOW}Kopiuję przykładowy plik...${NC}"
    cp frontend/.env.example frontend/.env
    echo -e "${GREEN}✅ Skopiowano frontend/.env.example -> frontend/.env${NC}"
    echo -e "${YELLOW}⚠️  WAŻNE: Edytuj frontend/.env i ustaw VITE_API_URL!${NC}"
    echo ""
    read -p "Naciśnij Enter aby kontynuować..."
fi

# Uruchom Docker Compose
echo ""
echo -e "${CYAN}🐳 Uruchamiam Docker Compose (baza danych)...${NC}"
docker compose -f docker/compose.yaml up -d

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Błąd podczas uruchamiania Docker Compose${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Docker Compose uruchomiony${NC}"

# Poczekaj na bazę danych
echo ""
echo -e "${CYAN}⏳ Czekam na bazę danych (5 sekund)...${NC}"
sleep 5

# Sprawdź czy backend server już działa
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo ""
    echo -e "${YELLOW}⚠️  Port 8000 jest już zajęty!${NC}"
    echo -e "${YELLOW}Backend prawdopodobnie już działa.${NC}"
else
    # Opcja: uruchomić backend w tle lub w osobnym terminalu
    echo ""
    echo -e "${YELLOW}📝 Aby uruchomić backend, wykonaj w NOWYM TERMINALU:${NC}"
    echo -e "${CYAN}cd $(pwd)/backend${NC}"
    echo -e "${CYAN}php -S 0.0.0.0:8000 -t public${NC}"
fi

# Sprawdź czy frontend server już działa
if lsof -Pi :8080 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo ""
    echo -e "${YELLOW}⚠️  Port 8080 jest już zajęty!${NC}"
    echo -e "${YELLOW}Frontend prawdopodobnie już działa.${NC}"
else
    echo ""
    echo -e "${YELLOW}📝 Aby uruchomić frontend, wykonaj w NOWYM TERMINALU:${NC}"
    echo -e "${CYAN}cd $(pwd)/frontend${NC}"
    echo -e "${CYAN}npm run serve -- --host 0.0.0.0 --port 8080${NC}"
fi

echo ""
echo "=================================="
echo -e "${GREEN}  ✅ Docker uruchomiony!${NC}"
echo "=================================="
echo ""
echo -e "${CYAN}📊 Status:${NC}"
echo -e "  WSL2 IP:       ${GREEN}$WSL_IP${NC}"
echo -e "  Docker:        ${GREEN}✅ Działa${NC}"
echo ""
echo -e "${CYAN}🌐 Po uruchomieniu serwerów:${NC}"
echo -e "  Localhost:     ${YELLOW}http://localhost:8080${NC}"
echo -e "  Z telefonu:    ${YELLOW}http://TWÓJ_WINDOWS_IP:8080${NC}"
echo ""
echo -e "${CYAN}💡 Wskazówki:${NC}"
echo "  • Backend: cd backend && php -S 0.0.0.0:8000 -t public"
echo "  • Frontend: cd frontend && npm run serve -- --host 0.0.0.0 --port 8080"
echo "  • Sprawdź frontend/.env (VUE_APP_API_URL)"
echo ""
echo "=================================="
echo ""
