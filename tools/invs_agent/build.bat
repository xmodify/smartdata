@echo off
cd /d "%~dp0"
echo [1/2] Generating Windows resource with icon and manifest...
"%USERPROFILE%\go\bin\rsrc.exe" -arch amd64 -ico icon.ico -manifest invs_agent.exe.manifest -o rsrc_windows_amd64.syso
if %ERRORLEVEL% NEQ 0 (
    rsrc -arch amd64 -ico icon.ico -manifest invs_agent.exe.manifest -o rsrc_windows_amd64.syso
)
echo [2/2] Building invs_agent.exe (???????????????????????????????????? CMD)...
go build -ldflags="-s -w -H windowsgui" -o invs_agent.exe .
if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================================
    echo  [SUCCESS] invs_agent.exe built with embedded icon!
    echo ========================================================
    del /f /q rsrc_windows_amd64.syso >nul 2>&1
) else (
    echo.
    echo [FAILED] Build failed with error %ERRORLEVEL%
)
timeout /t 2 >nul 2>&1
