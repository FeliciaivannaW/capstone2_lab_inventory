@echo off
REM Quick Run Script - Start all servers without setup
REM Jalankan script ini untuk development
REM Hanya menjalankan 3 server, tidak ada database setup

echo.
echo ========================================
echo  Lab Inventory - Start Servers
echo ========================================
echo.
echo Backend akan berjalan di: http://localhost:3000
echo Frontend akan berjalan di: http://localhost:8000
echo Vite Dev Server akan berjalan di: http://localhost:5173
echo.
timeout /t 2

REM Launch 3 servers in separate terminal windows
echo [1/3] Membuka Backend server...
start "Lab Inventory - Backend" cmd /k "cd backend-node && npm run dev"

echo [2/3] Membuka Frontend Laravel server...
start "Lab Inventory - Frontend (Laravel)" cmd /k "cd frontend-laravel && php artisan serve"

echo [3/3] Membuka Vite Dev server...
start "Lab Inventory - Frontend (Vite)" cmd /k "cd frontend-laravel && npm run dev"

echo.
echo [OK] Semua server sudah dijalankan dalam terminal terpisah!
echo.
timeout /t 3

REM Try to open browser
echo [INFO] Membuka browser ke http://localhost:8000...
start http://localhost:8000

echo.
echo ========================================
echo  Servers Running
echo ========================================
echo.
echo Untuk login, gunakan:
echo   Email: admin@example.com
echo   Password: password123
echo.
echo Untuk menghentikan servers:
echo   1. Tutup masing-masing terminal, atau
echo   2. Tekan Ctrl+C di setiap terminal
echo.
pause