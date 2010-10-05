@echo off
setlocal

rem do some NT cmd.exe magic, emit cmd stub for each php file

for /f "tokens=1 delims=." %%P in ('dir /b *.php') do (
  if exist %%P.cmd del /q %%P.cmd
  set p=%%p
  echo @echo off                                       > %%P.cmd
  echo setlocal                                        >>%%P.cmd
  echo.                                                >>%%P.cmd
  echo if exist "%%programfiles%%\PHP\php.exe" ^(      >>%%P.cmd
  echo     set php-binary=%%programfiles%%\PHP\php.exe >>%%P.cmd
  echo     goto runscript                              >>%%P.cmd
  echo ^) else if exist C:\PHP\php.exe ^(              >>%%P.cmd
  echo     set php-binary=C:\PHP\php.exe               >>%%P.cmd
  echo     goto runscript                              >>%%P.cmd
  echo ^) else if exist D:\PHP\php.exe ^(              >>%%P.cmd
  echo     set php-binary=D:\PHP\php.exe               >>%%P.cmd 
  echo     goto runscript                              >>%%P.cmd
  echo ^) else ^(                                      >>%%P.cmd
  echo     echo Can not find PHP interpreter!          >>%%P.cmd
  echo     exit 1                                      >>%%P.cmd
  echo ^)                                              >>%%P.cmd
  echo goto :eof                                       >>%%P.cmd
  echo.                                                >>%%P.cmd
  echo :runscript                                      >>%%P.cmd
  echo ^"%%php-binary%%^" -f %%0.php -- %%*            >>%%P.cmd
)
