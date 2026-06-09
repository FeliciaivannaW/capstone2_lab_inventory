@echo off
REM Setup Script untuk Lab Inventory - Menu Driven
REM Jalankan script ini dari folder root project

setlocal enabledelayedexpansion

:MAIN_MENU
cls
echo.
echo ========================================
echo   Lab Inventory - Setup ^& Utility Menu
echo ========================================
echo.
echo Pilihan Menu:
echo   1. Full Setup (Reset Database ^& Install/Update Dependencies)
echo   2. Update Dependencies Saja (Tanpa Reset Database)
echo   3. Reset Database Saja
echo   4. Masukkan Data Dummy (Seed) Saja
echo   5. Jalankan Aplikasi (Start Servers)
echo   6. Keluar
echo.
set /p menu_choice="Pilih menu [1-6]: "

if "%menu_choice%"=="1" goto FULL_SETUP
if "%menu_choice%"=="2" goto UPDATE_DEPS
if "%menu_choice%"=="3" goto RESET_DB
if "%menu_choice%"=="4" goto SEED_DB
if "%menu_choice%"=="5" goto START_SERVERS
if "%menu_choice%"=="6" goto END

echo [ERROR] Pilihan tidak valid.
pause
goto MAIN_MENU

:CHECK_MYSQL
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
exit /b 0

:DO_RESET_DB
call :CHECK_MYSQL
if %errorlevel% neq 0 goto MAIN_MENU

echo.
echo [INFO] Memeriksa database...
mysql -u root -e "USE lab_inventory_db" >nul 2>&1
if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo  WARNING - Database Already Exists!
    echo ========================================
    echo.
    echo Database 'lab_inventory_db' sudah ada di sistem.
    echo.
    set /p reset_choice="Lanjutkan reset database? Semua data akan dihapus! (Y/N): "
    if /i "!reset_choice!"=="Y" (
        echo [INFO] Menghapus database lama...
        mysql -u root -e "DROP DATABASE IF EXISTS lab_inventory_db;" >nul 2>&1
        if !errorlevel! neq 0 (
            echo [ERROR] Gagal menghapus database!
            pause
            exit /b 1
        )
        echo [OK] Database lama berhasil dihapus
    ) else (
        echo [INFO] Reset database dibatalkan.
        exit /b 1
    )
)

echo [INFO] Membuat database dan table...
mysql -u root < database\schema.sql
if %errorlevel% neq 0 (
    echo [ERROR] Gagal membuat database!
    pause
    exit /b 1
)
echo [OK] Database dan table berhasil dibuat

echo [INFO] Memasukkan data awal...
mysql -u root lab_inventory_db < database\seed.sql
if %errorlevel% neq 0 (
    echo [ERROR] Gagal seed data!
    pause
    exit /b 1
)
echo [OK] Data awal berhasil dimasukkan
exit /b 0

:DO_SEED_DB
call :CHECK_MYSQL
if %errorlevel% neq 0 exit /b 1

echo.
mysql -u root -e "USE lab_inventory_db" >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Database 'lab_inventory_db' belum ada!
    echo Silakan jalankan Reset Database terlebih dahulu.
    exit /b 1
)

echo [INFO] Memasukkan data awal (seed)...
mysql -u root lab_inventory_db < database\seed.sql
if %errorlevel% neq 0 (
    echo [ERROR] Gagal seed data!
    exit /b 1
)
echo [OK] Data awal berhasil dimasukkan
exit /b 0

:DO_UPDATE_DEPS
echo.
echo [INFO] Menginstall/Update Backend Node.js dependencies...
cd backend-node
if exist node_modules\ (
    set /p update_deps="[?] Direktori node_modules ditemukan. Perbarui/Install ulang dependencies backend? (Y/N, default: N): "
    if /i "!update_deps!"=="Y" (
        echo [INFO] Menginstall ulang backend dependencies...
        call npm install
        if !errorlevel! neq 0 (
            echo [ERROR] Gagal install backend packages!
            cd ..
            pause
            exit /b 1
        )
        echo [OK] Backend dependencies berhasil diperbarui
    ) else (
        echo [INFO] Melewati instalasi dependencies backend...
    )
) else (
    call npm install
    if !errorlevel! neq 0 (
        echo [ERROR] Gagal install backend packages!
        cd ..
        pause
        exit /b 1
    )
    echo [OK] Backend dependencies berhasil diinstall
)
cd ..

echo.
echo [INFO] Menginstall/Update Frontend dependencies...
cd frontend-laravel

if not exist .env (
    echo [INFO] File .env tidak ditemukan, membuat dari .env.example...
    copy .env.example .env >nul 2>&1
    if !errorlevel! neq 0 (
        echo [ERROR] Gagal membuat .env!
        cd ..\..
        pause
        exit /b 1
    )
    echo [OK] File .env berhasil dibuat
)

if exist vendor\ (
    set /p update_composer="[?] Direktori vendor ditemukan. Perbarui/Install ulang composer? (Y/N, default: N): "
    if /i "!update_composer!"=="Y" (
        echo [INFO] Menginstall ulang composer dependencies...
        call composer install
        if !errorlevel! neq 0 (
            echo [ERROR] Gagal install composer packages!
            cd ..\..
            pause
            exit /b 1
        )
        echo [OK] Composer packages berhasil diperbarui
    ) else (
        echo [INFO] Melewati composer install...
    )
) else (
    call composer install
    if !errorlevel! neq 0 (
        echo [ERROR] Gagal install composer packages!
        cd ..\..
        pause
        exit /b 1
    )
    echo [OK] Composer packages berhasil diinstall
)

if exist node_modules\ (
    set /p update_npm="[?] Direktori node_modules ditemukan. Perbarui/Install ulang npm frontend? (Y/N, default: N): "
    if /i "!update_npm!"=="Y" (
        echo [INFO] Menginstall ulang npm dependencies frontend...
        call npm install
        if !errorlevel! neq 0 (
            echo [ERROR] Gagal install npm packages!
            cd ..\..
            pause
            exit /b 1
        )
        echo [OK] Frontend npm packages berhasil diperbarui
    ) else (
        echo [INFO] Melewati npm install frontend...
    )
) else (
    call npm install
    if !errorlevel! neq 0 (
        echo [ERROR] Gagal install npm packages!
        cd ..\..
        pause
        exit /b 1
    )
    echo [OK] Frontend npm packages berhasil diinstall
)

for /f "tokens=*" %%i in ('findstr /C:"APP_KEY=base64:" .env 2^>nul') do (
    set "has_key=1"
)
if not defined has_key (
    echo [INFO] Generating APP_KEY...
    call php artisan key:generate >nul 2>&1
    echo [OK] APP_KEY generated
)
cd ..\..
exit /b 0

:FULL_SETUP
call :DO_RESET_DB
if !errorlevel! neq 0 (
    echo.
    echo [INFO] Operasi dibatalkan.
    pause
    goto MAIN_MENU
)
call :DO_UPDATE_DEPS
echo.
echo [OK] Full Setup Selesai!
pause
goto MAIN_MENU

:RESET_DB
call :DO_RESET_DB
if !errorlevel! neq 0 (
    echo.
    echo [INFO] Operasi dibatalkan.
) else (
    echo.
    echo [OK] Reset Database Selesai!
)
pause
goto MAIN_MENU

:SEED_DB
call :DO_SEED_DB
if !errorlevel! neq 0 (
    echo.
    echo [INFO] Operasi dibatalkan.
) else (
    echo.
    echo [OK] Seed Database Selesai!
)
pause
goto MAIN_MENU

:UPDATE_DEPS
call :DO_UPDATE_DEPS
echo.
echo [OK] Update Dependencies Selesai!
pause
goto MAIN_MENU

:START_SERVERS
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

echo [INFO] Membuka browser ke http://localhost:8000...
start http://localhost:8000

echo.
echo Untuk login, gunakan:
echo   Email: admin@example.com
echo   Password: password123
echo.
echo Ketiga terminal server akan tetap berjalan di background.
pause
goto END

:END
exit /b 0
