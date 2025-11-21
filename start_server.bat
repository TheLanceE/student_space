@echo off
echo Starting PHP Development Server...
echo.
echo Server will be available at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.
cd /d "%~dp0"
php -S localhost:8000
pause

