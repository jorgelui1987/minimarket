@echo off
cd /d "%~dp0"
set "PHPEXE=php"
where php >nul 2>nul
if errorlevel 1 (
  for /d %%d in (C:\laragon\bin\php\php*) do set "PHPEXE=%%d\php.exe"
)
if not exist "%PHPEXE%" if exist C:\xampp\php\php.exe set "PHPEXE=C:\xampp\php\php.exe"
echo Usando PHP: %PHPEXE%
echo.
"%PHPEXE%" artisan db:seed --class=AddDataSeeder --force
echo.
echo ===== TERMINADO. Puede cerrar esta ventana. =====
pause
