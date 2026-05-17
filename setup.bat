@echo off
REM Setup Script untuk Lab Inventory - Full Automated Setup
REM Jalankan script ini dari folder root project

setlocal enabledelayedexpansion

echo.
echo ========================================
echo  Lab Inventory - Full Setup
echo ========================================
echo.

REM Check if MySQL is accessible
mysql -u root -e "SELECT 1" >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] MySQL tidak bisa diakses!
    echo Pastikan:
    echo   1. Laragon MySQL sedang running
    echo   2. Password MySQL kosong (atau edit script ini)
    echo.
    pause
    exit /b 1
)

echo [INFO] MySQL ditemukan. Melanjutkan setup...
echo.

REM Check if database already exists
mysql -u root -e "USE lab_inventory_db" >nul 2>&1
if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo  WARNING - Database Already Exists!
    echo ========================================
    echo.
    echo Database 'lab_inventory_db' sudah ada di sistem.
    echo.
    echo Pilihan:
    echo   1. Reset (Hapus & buat ulang database) - ketik: Y
    echo   2. Batalkan & keluar - ketik: N
    echo.
    set /p choice="Lanjutkan reset database? (Y/N): "
    
    if /i "%choice%"=="Y" (
        echo.
        echo [INFO] Memulai reset database...
        echo [WARNING] Semua data akan dihapus!
        echo.
        timeout /t 3
        
        mysql -u root -e "DROP DATABASE IF EXISTS lab_inventory_db;" >nul 2>&1
        if %errorlevel% neq 0 (
            echo [ERROR] Gagal menghapus database!
            pause
            exit /b 1
        )
        echo [OK] Database lama berhasil dihapus
        echo.
    ) else (
        echo.
        echo [INFO] Setup dibatalkan. Database tidak berubah.
        echo.
        pause
        exit /b 0
    )
)

REM Create database and tables
echo [1/5] Membuat database dan table...
mysql -u root < database\schema.sql
if %errorlevel% neq 0 (
    echo [ERROR] Gagal membuat database!
    pause
    exit /b 1
)
echo [OK] Database dan table berhasil dibuat

echo.

REM Seed initial data
echo [2/5] Memasukkan data awal...
mysql -u root < database\seed.sql
if %errorlevel% neq 0 (
    echo [ERROR] Gagal seed data!
    pause
    exit /b 1
)
echo [OK] Data awal berhasil dimasukkan

echo.

REM Install Backend dependencies
echo [3/5] Menginstall Backend Node.js dependencies...
cd backend-node
call npm install >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Gagal install backend packages!
    cd ..
    pause
    exit /b 1
)
echo [OK] Backend dependencies berhasil diinstall
cd ..

echo.

REM Install Frontend dependencies
echo [4/5] Menginstall Frontend dependencies...
cd frontend-laravel

REM Check and create .env if doesn't exist
if not exist .env (
    echo [INFO] File .env tidak ditemukan, membuat dari .env.example...
    copy .env.example .env >nul 2>&1
    if %errorlevel% neq 0 (
        echo [ERROR] Gagal membuat .env!
        cd ..\..
        pause
        exit /b 1
    )
    echo [OK] File .env berhasil dibuat
)

call composer install >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Gagal install composer packages!
    cd ..\..
    pause
    exit /b 1
)
echo [OK] Composer packages berhasil diinstall

call npm install >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Gagal install npm packages!
    cd ..\..
    pause
    exit /b 1
)
echo [OK] Frontend npm packages berhasil diinstall

REM Generate APP_KEY if not exists or empty
for /f "tokens=*" %%i in ('findstr /C:"APP_KEY=base64:" .env 2^>nul') do (
    set "has_key=1"
)
if not defined has_key (
    echo [INFO] Generating APP_KEY...
    call php artisan key:generate >nul 2>&1
    echo [OK] APP_KEY generated
)

cd ..\..

echo.

REM All setup done, now ask user if they want to start servers
echo [5/5] Setup selesai! Database, dependencies, dan konfigurasi sudah siap.
echo.
echo ========================================
echo  Selanjutnya?
echo ========================================
echo.
echo Pilihan:
echo   Y - Jalankan aplikasi sekarang
echo   N - Keluar dulu
echo.
set /p start_choice="Jalankan aplikasi? (Y/N): "

if /i "%start_choice%"=="N" (
    echo.
    echo [INFO] Start aplikasi dibatalkan.
    echo.
    exit /b 0
)

if /i "%start_choice%"=="Y" (
    echo.
    echo ========================================
    echo  Launching Servers
    echo ========================================
    echo.
    echo Backend akan berjalan di: http://localhost:3000
    echo Frontend akan berjalan di: http://localhost:8000
    echo Vite Dev Server akan berjalan di: http://localhost:5173
    echo.
    echo Jika browser tidak terbuka otomatis, buka:
    echo http://localhost:8000
    echo.
    timeout /t 3

    REM Launch 3 servers in separate terminal windows
    echo [INFO] Membuka Backend server...
    start "Lab Inventory - Backend" cmd /k "cd backend-node && npm run dev"

    echo [INFO] Membuka Frontend Laravel server...
    start "Lab Inventory - Frontend (Laravel)" cmd /k "cd frontend-laravel && php artisan serve"

    echo [INFO] Membuka Vite Dev server...
    start "Lab Inventory - Frontend (Vite)" cmd /k "cd frontend-laravel && npm run dev"

    echo.
    echo [OK] Semua server sudah dijalankan!
    echo [INFO] Tunggu ~10 detik untuk semua server siap
    echo.
    timeout /t 10

    REM Try to open browser
    echo [INFO] Membuka browser ke http://localhost:8000...
    start http://localhost:8000

    echo.
    echo ========================================
    echo  Setup Selesai!
    echo ========================================
    echo.
    echo Untuk login, gunakan:
    echo   Email: admin@example.com
    echo   Password: password123
    echo.
    echo Tutup window ini setelah selesai menggunakan.
    echo Ketiga terminal server akan tetap berjalan.
    echo.
    pause
) else (
    echo.
    echo [ERROR] Input tidak valid. Silakan jalankan setup kembali.
    echo.
    pause
    exit /b 1
)
