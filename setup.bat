@echo off
setlocal EnableDelayedExpansion

echo Checking if PHP is installed and in PATH...
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo PHP is not found in PATH
    echo Would you like to add PHP to PATH? [Y/N]
    set /p SETUP_PHP="Choice: "
    if /i "!SETUP_PHP!"=="Y" (
        call add_php_to_path.bat
        echo Please run this script again after restarting your command prompt
        pause
        exit /b 1
    ) else (
        echo PHP must be in PATH to continue
        pause
        exit /b 1
    )
)

echo Checking PHP version...
php -v
echo.

echo Checking if Composer is installed...
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Composer is not installed. Please install Composer first:
    echo Visit https://github.com/composer/windows-setup and download Composer-Setup.exe
    echo After installation, run this script again.
    pause
    exit /b 1
)

echo Installing PHP dependencies...
composer install

echo Creating .env file...
copy .env.example .env

echo Setup complete! Next steps:
echo 1. Edit .env file with your database credentials
echo 2. Create the database using the schema.sql file
echo 3. Point your web server to the public directory
echo 4. Visit http://localhost to access the application
pause 