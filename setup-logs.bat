@echo off
echo Setting up log directory permissions...

REM Create logs directory if it doesn't exist
if not exist "logs" mkdir logs

REM Get current user
for /f "tokens=*" %%a in ('whoami') do set CURRENT_USER=%%a

REM Grant full permissions to logs directory
icacls "logs" /grant "%CURRENT_USER%":(OI)(CI)F /T /Q

REM Create initial log file if it doesn't exist
if not exist "logs\app.log" (
    echo. > "logs\app.log"
    icacls "logs\app.log" /grant "%CURRENT_USER%":(F) /Q
)

echo Done! Log directory is now properly configured.
pause 