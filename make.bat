@echo off

set TARGET=%1
if "%TARGET%"=="" (set TARGET=build)

.\vendor\bin\phing -f ./build.xml %TARGET%
