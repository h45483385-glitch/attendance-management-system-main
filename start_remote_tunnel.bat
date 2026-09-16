@echo off
title Attendance Management System - Remote Testing Launcher
echo =========================================================================
echo    AMS Remote Testing & Tunnel Launcher (Bangalore / Remote Access)
echo =========================================================================
echo.

if not exist "%~dp0cloudflared.exe" (
    echo [*] Downloading Cloudflare Tunnel binary for instant HTTPS remote access...
    curl.exe -L -o "%~dp0cloudflared.exe" "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"
    echo [*] Cloudflare tunnel downloaded successfully.
)

echo.
echo [1/3] Clearing Laravel caches...
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear

echo.
echo [2/3] Launching Cloudflare HTTPS Tunnel in a new window...
start "Cloudflare HTTPS Tunnel (Share this URL with Bangalore team)" cmd /k "%~dp0cloudflared.exe tunnel --url http://127.0.0.1:8000"

echo.
echo [3/3] Starting Laravel Server on 0.0.0.0:8000...
echo       Local Access:   http://127.0.0.1:8000
echo       Remote Access:  Check the tunnel window for the https://*.trycloudflare.com link!
echo =========================================================================
php artisan serve --host=0.0.0.0 --port=8000
pause
