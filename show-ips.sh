#!/bin/bash

# PingMe - Pokaż adresy IP

echo "=================================="
echo "  📍 PingMe - Adresy IP"
echo "=================================="
echo ""

# Kolory
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Adres WSL2
WSL_IP=$(hostname -I | awk '{print $1}')
echo -e "${CYAN}WSL2 IP:${NC}       ${GREEN}$WSL_IP${NC}"

# Pobierz Windows IP (przez /mnt/c jeśli dostępne)
if command -v powershell.exe &> /dev/null; then
    WIN_IP=$(powershell.exe "(Get-NetAdapter | Where-Object {(\$_.Status -eq 'Up') -and ((\$_.Name -like '*Wi-Fi*') -or (\$_.Name -like '*Ethernet*') -and (\$_.Name -notlike '*vEthernet*'))} | Get-NetIPAddress -AddressFamily IPv4 | Select-Object -First 1).IPAddress" 2>/dev/null | tr -d '\r\n ')

    if [ -n "$WIN_IP" ]; then
        echo -e "${CYAN}Windows IP:${NC}    ${YELLOW}$WIN_IP${NC}"
    else
        echo -e "${YELLOW}Windows IP:    (uruchom 'ipconfig' w Windows)${NC}"
    fi
else
    echo -e "${YELLOW}Windows IP:    (uruchom 'ipconfig' w Windows)${NC}"
fi

echo ""
echo -e "${CYAN}🌐 Dostęp do aplikacji:${NC}"
echo -e "  Localhost:     ${GREEN}http://localhost:8080${NC}"

if [ -n "$WIN_IP" ]; then
    echo -e "  Z telefonu:    ${YELLOW}http://$WIN_IP:8080${NC}"
fi

echo ""
echo -e "${CYAN}📝 Konfiguracja frontend/.env:${NC}"
if [ -n "$WIN_IP" ]; then
    echo -e "  ${GREEN}VITE_API_URL=http://$WIN_IP:8000/api${NC}"
else
    echo -e "  ${YELLOW}VITE_API_URL=http://TWÓJ_WINDOWS_IP:8000/api${NC}"
fi

echo ""
echo "=================================="
