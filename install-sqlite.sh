#!/bin/bash
# Instala el driver PHP SQLite3 en Debian (necesario para el prototipo)

echo "Instalando php-sqlite3..."
sudo apt-get update
sudo apt-get install -y php-sqlite3

# Si usas PHP 8.4 específicamente:
# sudo apt-get install -y php8.4-sqlite3

echo ""
echo "¡Listo! Ejecuta: php artisan migrate"
echo "Y luego: php artisan serve"
