#!/bin/bash

# Setup Script untuk Lab Inventory - Menu Driven
# Jalankan script ini dari folder root project

set -e

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

check_mysql() {
    if ! mysql -u root -e "SELECT 1" > /dev/null 2>&1; then
        echo "[ERROR] MySQL tidak bisa diakses!"
        echo "Pastikan:"
        echo "  1. MySQL sedang running"
        echo "  2. Username/password benar (edit script ini jika perlu)"
        echo ""
        read -p "Press Enter to continue..."
        return 1
    fi
    return 0
}

do_reset_db() {
    if ! check_mysql; then
        return 1
    fi

    if mysql -u root -e "USE lab_inventory_db" > /dev/null 2>&1; then
        echo ""
        echo "========================================"
        echo "  WARNING - Database Already Exists!"
        echo "========================================"
        echo ""
        echo "Database 'lab_inventory_db' sudah ada di sistem."
        echo ""
        read -p "Lanjutkan reset database? Semua data akan dihapus! (Y/N): " choice
        
        if [[ "$choice" =~ ^[Yy]$ ]]; then
            echo "[INFO] Menghapus database lama..."
            mysql -u root -e "DROP DATABASE IF EXISTS lab_inventory_db;" > /dev/null 2>&1
            if [ $? -ne 0 ]; then
                echo "[ERROR] Gagal menghapus database!"
                return 1
            fi
            echo "[OK] Database lama berhasil dihapus"
        else
            echo "[INFO] Reset database dibatalkan."
            return 1
        fi
    fi

    echo "[INFO] Membuat database dan table..."
    mysql -u root < database/schema.sql
    echo "[OK] Database dan table berhasil dibuat"

    echo "[INFO] Memasukkan data awal..."
    mysql -u root < database/seed.sql
    echo "[OK] Data awal berhasil dimasukkan"
}

do_update_deps() {
    echo ""
    echo "[INFO] Menginstall/Update Backend Node.js dependencies..."
    cd backend-node
    if [ -d "node_modules" ]; then
        read -p "[?] Direktori node_modules ditemukan. Perbarui/Install ulang dependencies backend? (Y/N, default: N): " update_deps
        if [[ "$update_deps" =~ ^[Yy]$ ]]; then
            echo "[INFO] Menginstall ulang backend dependencies..."
            npm install
            echo "[OK] Backend dependencies berhasil diperbarui"
        else
            echo "[INFO] Melewati instalasi dependencies backend..."
        fi
    else
        npm install
        echo "[OK] Backend dependencies berhasil diinstall"
    fi
    cd ..

    echo ""
    echo "[INFO] Menginstall/Update Frontend dependencies..."
    cd frontend-laravel

    if [ ! -f .env ]; then
        echo "[INFO] File .env tidak ditemukan, membuat dari .env.example..."
        cp .env.example .env
        echo "[OK] File .env berhasil dibuat"
    fi

    if [ -d "vendor" ]; then
        read -p "[?] Direktori vendor ditemukan. Perbarui/Install ulang composer? (Y/N, default: N): " update_composer
        if [[ "$update_composer" =~ ^[Yy]$ ]]; then
            echo "[INFO] Menginstall ulang composer dependencies..."
            composer install
            echo "[OK] Composer packages berhasil diperbarui"
        else
            echo "[INFO] Melewati composer install..."
        fi
    else
        composer install
        echo "[OK] Composer packages berhasil diinstall"
    fi

    if [ -d "node_modules" ]; then
        read -p "[?] Direktori node_modules ditemukan. Perbarui/Install ulang npm frontend? (Y/N, default: N): " update_npm
        if [[ "$update_npm" =~ ^[Yy]$ ]]; then
            echo "[INFO] Menginstall ulang npm dependencies frontend..."
            npm install
            echo "[OK] Frontend npm packages berhasil diperbarui"
        else
            echo "[INFO] Melewati npm install frontend..."
        fi
    else
        npm install
        echo "[OK] Frontend npm packages berhasil diinstall"
    fi

    if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
        echo "[INFO] Generating APP_KEY..."
        php artisan key:generate > /dev/null 2>&1
        echo "[OK] APP_KEY generated"
    fi
    cd ../
}

start_servers() {
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
    echo "  1. Tekan Ctrl+C di terminal tempat server berjalan, atau"
    echo "  2. Jalankan: pkill -f 'npm run dev' && pkill php"
    echo ""

    if command -v tmux &> /dev/null; then
        echo "[INFO] Menggunakan tmux untuk terminal management..."
        tmux new-session -d -s "labventory"
        tmux new-window -t labventory -n "backend"
        tmux send-keys -t labventory:backend "cd backend-node && npm run dev" C-m
        tmux new-window -t labventory -n "laravel"
        tmux send-keys -t labventory:laravel "cd frontend-laravel && php artisan serve" C-m
        tmux new-window -t labventory -n "vite"
        tmux send-keys -t labventory:vite "cd frontend-laravel && npm run dev" C-m
        
        echo "[OK] Semua server sudah dijalankan di tmux session 'labventory'"
        echo "Untuk melihat output: tmux attach-session -t labventory"
        
        sleep 5
        if command -v xdg-open &> /dev/null; then
            xdg-open "http://localhost:8000" &
        elif command -v open &> /dev/null; then
            open "http://localhost:8000" &
        fi
        
        tmux attach-session -t labventory
    else
        echo "[INFO] tmux tidak ditemukan, menjalankan dengan background processes..."
        
        (cd "$SCRIPT_DIR/backend-node" && npm run dev) &
        BACKEND_PID=$!
        
        (cd "$SCRIPT_DIR/frontend-laravel" && php artisan serve) &
        LARAVEL_PID=$!
        
        sleep 2
        
        (cd "$SCRIPT_DIR/frontend-laravel" && npm run dev) &
        VITE_PID=$!
        
        echo ""
        echo "[OK] Semua server sudah dijalankan!"
        echo "Untuk menghentikan semua server:"
        echo "  kill $BACKEND_PID $LARAVEL_PID $VITE_PID"
        
        sleep 5
        if command -v xdg-open &> /dev/null; then
            xdg-open "http://localhost:8000" &
        elif command -v open &> /dev/null; then
            open "http://localhost:8000" &
        fi
        
        wait $BACKEND_PID $LARAVEL_PID $VITE_PID
    fi
}

show_menu() {
    clear
    echo "========================================"
    echo "  Lab Inventory - Setup & Utility Menu"
    echo "========================================"
    echo ""
    echo "Pilihan Menu:"
    echo "  1. Full Setup (Reset Database & Install/Update Dependencies)"
    echo "  2. Update Dependencies Saja (Tanpa Reset Database)"
    echo "  3. Reset Database Saja"
    echo "  4. Jalankan Aplikasi (Start Servers)"
    echo "  5. Keluar"
    echo ""
    read -p "Pilih menu [1-5]: " menu_choice

    case $menu_choice in
        1)
            if do_reset_db; then
                do_update_deps
                echo ""
                echo "[OK] Full Setup Selesai!"
            else
                echo ""
                echo "[INFO] Operasi dibatalkan."
            fi
            read -p "Press Enter to return to menu..."
            ;;
        2)
            do_update_deps
            echo ""
            echo "[OK] Update Dependencies Selesai!"
            read -p "Press Enter to return to menu..."
            ;;
        3)
            if do_reset_db; then
                echo ""
                echo "[OK] Reset Database Selesai!"
            else
                echo ""
                echo "[INFO] Operasi dibatalkan."
            fi
            read -p "Press Enter to return to menu..."
            ;;
        4)
            start_servers
            exit 0
            ;;
        5)
            echo "Keluar."
            exit 0
            ;;
        *)
            echo "[ERROR] Pilihan tidak valid."
            sleep 2
            ;;
    esac
}

while true; do
    show_menu
done
