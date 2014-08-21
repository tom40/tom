@echo off
php C:\Users\Mark\Documents\Dev\take_note\web\scripts\zf-cli.php -e development -a /shift-operations/no-shift-reminders




REM ************* Delete the audio files associated with olders (invoiced) jobs **********************
REM
REM php C:\Users\Mark\Documents\Dev\take_note\web\scripts\zf-cli.php -e development -a /archive


REM ************* Delete any files still associated with already archived jobs **********************
REM
REM php C:\Users\Mark\Documents\Dev\take_note\web\scripts\zf-cli.php -e development -a /archive/purge


REM ************* Delete any files associated with deleted audio file records **********************
REM
REM php C:\Users\Mark\Documents\Dev\take_note\web\scripts\zf-cli.php -e development -a /archive/purge-files


REM ************* Delete any files that are orphaned from database records **********************
REM
REM php C:\Users\Mark\Documents\Dev\take_note\web\scripts\zf-cli.php -e development -a /archive/purge-files-no-record


REM ************* Send an email to typists that have no shifts in the next 7 days **********************
REM
REM php C:\Users\Mark\Documents\Dev\take_note\web\scripts\zf-cli.php -e development -a /shift-operations/no-shift-reminders
