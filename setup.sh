#!/bin/bash

# Setup Script untuk Lab Inventory - Full Automated Setup
# Jalankan script ini dari folder root project

set -e

echo ""
echo "========================================"
echo "  Lab Inventory - Full Setup"
echo "========================================"
echo ""

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Check if MySQL is accessible
if ! mysql -u root -e "SELECT 1" > /dev/null 2>&1; then
    echo "[ERROR] MySQL tidak bisa diakses!"
    echo "Pastikan:"
    echo "  1. MySQL sedang running"
    echo "  2. Username/password benar (edit script ini jika perlu)"
    echo ""
    exit 1
fi

echo "[INFO] MySQL ditemukan. Melanjutkan setup..."
echo ""

# Check if database already exists
if mysql -u root -e "USE lab_inventory_db" > /dev/null 2>&1; then
    echo ""
    echo "========================================"
    echo "  WARNING - Database Already Exists!"
    echo "========================================"
    echo ""
    echo "Database 'lab_inventory_db' sudah ada di sistem."
    echo ""
    echo "Pilihan:"
    echo "  1. Reset (Hapus & buat ulang database) - ketik: Y"
    echo "  2. Batalkan & keluar - ketik: N"
    echo ""
    read -p "Lanjutkan reset database? (Y/N): " choice
    
    if [[ "$choice" =~ ^[Yy]$ ]]; then
        echo ""
        echo "[INFO] Memulai reset database..."
        echo "[WARNING] Semua data akan dihapus!"
        echo ""
        sleep 3
        
        mysql -u root -e "DROP DATABASE IF EXISTS lab_inventory_db;" > /dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo "[ERROR] Gagal menghapus database!"
            exit 1
        fi
        echo "[OK] Database lama berhasil dihapus"
        echo ""
    else
        echo ""
        echo "[INFO] Setup dibatalkan. Database tidak berubah."
        echo ""
        exit 0
    fi
fi

# Create database and tables
echo "[1/5] Membuat database dan table..."
mysql -u root < database/schema.sql
echo "[OK] Database dan table berhasil dibuat"

echo ""

# Seed initial data
echo "[2/5] Memasukkan data awal..."
mysql -u root < database/seed.sql
echo "[OK] Data awal berhasil dimasukkan"

echo ""

# Install Backend dependencies
echo "[3/5] Menginstall Backend Node.js dependencies..."
cd backend-node
npm install > /dev/null 2>&1
echo "[OK] Backend dependencies berhasil diinstall"
cd ..

echo ""

# Install Frontend dependencies
echo "[4/5] Menginstall Frontend dependencies..."
cd frontend-laravel

# Check and create .env if doesn't exist
if [ ! -f .env ]; then
    echo "[INFO] File .env tidak ditemukan, membuat dari .env.example..."
    cp .env.example .env
    if [ $? -ne 0 ]; then
        echo "[ERROR] Gagal membuat .env!"
        cd ../..
        exit 1
    fi
    echo "[OK] File .env berhasil dibuat"
fi

composer install > /dev/null 2>&1
echo "[OK] Composer packages berhasil diinstall"

npm install > /dev/null 2>&1
echo "[OK] Frontend npm packages berhasil diinstall"

# Generate APP_KEY if not exists or empty
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "[INFO] Generating APP_KEY..."
    php artisan key:generate > /dev/null 2>&1
    echo "[OK] APP_KEY generated"
fi

cd ..

echo ""

# All setup done, now ask user if they want to start servers
echo "[5/5] Setup selesai! Database, dependencies, dan konfigurasi sudah siap."
echo ""
echo "========================================"
echo "  Selanjutnya?"
echo "========================================"
echo ""
echo "Pilihan:"
echo "  Y - Jalankan aplikasi sekarang"
echo "  N - Keluar"
echo ""
read -p "Jalankan aplikasi? (Y/N): " start_choice

if [[ "$start_choice" =~ ^[Nn]$ ]]; then
    echo ""
    echo "[INFO] Start aplikasi dibatalkan."
    echo ""
    exit 0
fi

if ! [[ "$start_choice" =~ ^[Yy]$ ]]; then
    echo ""
    echo "[ERROR] Input tidak valid. Silakan jalankan setup kembali."
    echo ""
    exit 1
fi

echo ""
echo "========================================"
echo "  Launching Servers"
echo "========================================"
echo ""
echo "Backend akan berjalan di: http://localhost:3000"
echo "Frontend akan berjalan di: http://localhost:8000"
echo "Vite Dev Server akan berjalan di: http://localhost:5173"
echo ""
echo "Untuk menghentikan semua server:"
echo "  1. Tekan Ctrl+C di masing-masing terminal, atau"
echo "  2. Jalankan: pkill -f 'npm run dev' && pkill php"
echo ""

# Create a function to start server and log to file
start_backend() {
    cd "$SCRIPT_DIR/backend-node"
    npm run dev
}

start_frontend_serve() {
    cd "$SCRIPT_DIR/frontend-laravel"
    php artisan serve
}

start_frontend_vite() {
    cd "$SCRIPT_DIR/frontend-laravel"
    npm run dev
}

# Check if tmux is available for better terminal management
if command -v tmux &> /dev/null; then
    echo "[INFO] Menggunakan tmux untuk terminal management..."
    
    # Create a tmux session
    tmux new-session -d -s "labventory"
    
    # Start backend in first window
    tmux new-window -t labventory -n "backend"
    tmux send-keys -t labventory:backend "cd backend-node && npm run dev" C-m
    
    # Start Laravel server in second window
    tmux new-window -t labventory -n "laravel"
    tmux send-keys -t labventory:laravel "cd frontend-laravel && php artisan serve" C-m
    
    # Start Vite in third window
    tmux new-window -t labventory -n "vite"
    tmux send-keys -t labventory:vite "cd frontend-laravel && npm run dev" C-m
    
    # Attach to the session
    echo "[OK] Semua server sudah dijalankan di tmux session 'labventory'"
    echo ""
    echo "Untuk melihat output:"
    echo "  tmux attach-session -t labventory"
    echo ""
    echo "Untuk keluar tmux session: Ctrl+D atau type 'exit'"
    echo ""
    sleep 5
    echo "[INFO] Membuka browser ke http://localhost:8000..."
    
    # Try to open browser
    if command -v xdg-open &> /dev/null; then
        xdg-open "http://localhost:8000" &
    elif command -v open &> /dev/null; then
        open "http://localhost:8000" &
    fi
    
    tmux attach-session -t labventory
else
    echo "[INFO] tmux tidak ditemukan, menjalankan dengan background processes..."
    echo ""
    
    # Start all services in background
    echo "[INFO] Memulai Backend server..."
    start_backend &
    BACKEND_PID=$!
    
    echo "[INFO] Memulai Laravel server..."
    start_frontend_serve &
    LARAVEL_PID=$!
    
    sleep 2
    
    echo "[INFO] Memulai Vite Dev server..."
    start_frontend_vite &
    VITE_PID=$!
    
    echo ""
    echo "[OK] Semua server sudah dijalankan!"
    echo ""
    echo "PIDs:"
    echo "  Backend: $BACKEND_PID"
    echo "  Laravel: $LARAVEL_PID"
    echo "  Vite: $VITE_PID"
    echo ""
    echo "Untuk menghentikan semua server:"
    echo "  kill $BACKEND_PID $LARAVEL_PID $VITE_PID"
    echo ""
    echo "Atau gunakan:"
    echo "  pkill -f 'npm run dev' && pkill -f 'php artisan serve'"
    echo ""
    
    sleep 5
    
    echo "[INFO] Membuka browser ke http://localhost:8000..."
    if command -v xdg-open &> /dev/null; then
        xdg-open "http://localhost:8000" &
    elif command -v open &> /dev/null; then
        open "http://localhost:8000" &
    fi
    
    # Wait for all processes
    wait $BACKEND_PID $LARAVEL_PID $VITE_PID
fi

echo ""
echo "========================================"
echo "  Setup Selesai!"
echo "========================================"
echo ""
echo "Untuk login, gunakan:"
echo "  Email: admin@example.com"
echo "  Password: password123"
echo ""
