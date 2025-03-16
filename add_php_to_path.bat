@echo off
setlocal EnableDelayedExpansion

:: Check if running with admin privileges
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo This script requires administrator privileges.
    echo Please run as administrator.
    pause
    exit /b 1
)

:: Try to find PHP installation
set "PHP_PATH="
for %%P in (
    "C:\php"
    "C:\xampp\php"
    "C:\Program Files\PHP"
    "C:\Program Files (x86)\PHP"
) do (
    if exist "%%~P\php.exe" (
        set "PHP_PATH=%%~P"
        goto :found
    )
)

:: If PHP wasn't found in common locations, ask user for path
if not defined PHP_PATH (
    echo PHP installation not found in common locations.
    echo Please enter the full path to your PHP installation directory
    echo Example: C:\php
    set /p PHP_PATH="PHP Path: "
)

:found
:: Verify the provided/found path contains php.exe
if not exist "%PHP_PATH%\php.exe" (
    echo PHP executable not found in specified directory.
    echo Please make sure PHP is installed correctly.
    pause
    exit /b 1
)

:: Add PHP to system PATH
for /f "tokens=2*" %%A in ('reg query "HKLM\SYSTEM\CurrentControlSet\Control\Session Manager\Environment" /v Path') do set "CURRENT_PATH=%%B"
echo Current PATH: !CURRENT_PATH!

:: Check if PHP path is already in PATH
echo !CURRENT_PATH! | find /i "%PHP_PATH%" >nul
if !errorlevel! equ 0 (
    echo PHP is already in PATH
) else (
    :: Add PHP path to system PATH
    setx /M PATH "%PHP_PATH%;!CURRENT_PATH!"
    if !errorlevel! equ 0 (
        echo Successfully added PHP to system PATH
    ) else (
        echo Failed to add PHP to system PATH
        pause
        exit /b 1
    )
)

echo.
echo PHP has been added to PATH. You will need to restart any open command prompts
echo or PowerShell windows for the changes to take effect.
pause
exit /b 0 