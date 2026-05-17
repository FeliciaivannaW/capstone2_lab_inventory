#!/bin/bash

# Quick Run Script - Start all servers without setup
# Jalankan script ini untuk development
# Hanya menjalankan 3 server, tidak ada database setup

set -e

echo ""
echo "========================================"
echo "  Lab Inventory - Start Servers"
echo "========================================"
echo ""
echo "Backend akan berjalan di: http://localhost:3000"
echo "Frontend akan berjalan di: http://localhost:8000"
echo "Vite Dev Server akan berjalan di: http://localhost:5173"
echo ""
sleep 2

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

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
    
    # Check if session already exists
    if tmux has-session -t "labventory" 2>/dev/null; then
        echo "[ERROR] Session 'labventory' sudah running!"
        echo "Untuk attach ke session:"
        echo "  tmux attach-session -t labventory"
        echo ""
        echo "Untuk kill session:"
        echo "  tmux kill-session -t labventory"
        exit 1
    fi
    
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
    echo "Untuk keluar dari tmux: Ctrl+B kemudian D"
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
    echo "[1/3] Memulai Backend server..."
    start_backend &
    BACKEND_PID=$!
    
    echo "[2/3] Memulai Laravel server..."
    start_frontend_serve &
    LARAVEL_PID=$!
    
    sleep 2
    
    echo "[3/3] Memulai Vite Dev server..."
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
echo "  Servers Stopped"
echo "========================================"
echo ""
