@echo off
set MIBDIRS=C:\_majordomo\server\php\Extras\mibs
c:
cd /d \_majordomo\htdocs\objects
..\..\server\php\php.exe index.php %1 %2 %3 %4 %5 %6 %7 %8 %9>>log.txt
