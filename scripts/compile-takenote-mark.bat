@echo off

REM Compress Javascript [standard resource locations]
java -jar yui_compressor\yuicompressor-2.4.8.jar ..\web\public\js\takenote.js --type js -o ..\web\public\js\takenote.min.js

REM Compress CSS
java -jar yui_compressor\yuicompressor-2.4.8.jar ..\web\public\css\buttons.css --type css -o ..\web\public\css\buttons.min.css
java -jar yui_compressor\yuicompressor-2.4.8.jar ..\web\public\css\style.css --type css -o ..\web\public\css\style.min.css
java -jar yui_compressor\yuicompressor-2.4.8.jar ..\web\public\css\style2.css --type css -o ..\web\public\css\style2.min.css
java -jar yui_compressor\yuicompressor-2.4.8.jar ..\web\public\css\tasks.css --type css -o ..\web\public\css\tasks.min.css
