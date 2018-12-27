@echo off

rem SET mypath=%~dp0
rem echo %mypath:~0,-1%

SET HOME=c:\Users\%USERNAME%

php gcc-cli.php %*

