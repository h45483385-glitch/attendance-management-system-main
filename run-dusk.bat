@echo off
echo Setting environment overrides...
set APP_URL=http://127.0.0.1:8000
set APP_KEY=base64:xQWj312QXkO/cFhA+smMnGK97p8HUvg7u45UJ1Jgn/g=

echo Starting PHP Development Server...
start /b php artisan serve --host=127.0.0.1 --port=8000 > NUL 2>&1

echo Waiting for server to be ready...
:WAIT_SERVER
powershell -Command "try { (Invoke-WebRequest -Uri 'http://127.0.0.1:8000' -UseBasicParsing -TimeoutSec 1).StatusCode } catch { '' }" 2>NUL | findstr /r "[0-9]" > NUL
if errorlevel 1 (
    ping 127.0.0.1 -n 2 > NUL
    goto WAIT_SERVER
)
echo Server is ready!

echo Starting ChromeDriver on port 9515...
start /b vendor\laravel\dusk\bin\chromedriver-win.exe --port=9515 > NUL 2>&1
ping 127.0.0.1 -n 3 > NUL

echo Running Dusk tests...
php artisan dusk

echo Stopping Processes...
taskkill /F /IM chromedriver-win.exe > NUL 2>&1
wmic process where "commandline like '%%artisan serve%%'" call terminate > NUL 2>&1
