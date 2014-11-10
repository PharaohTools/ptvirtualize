<?php

/**
 * Pharaoh Tools Constants
 */

if (in_array(PHP_OS, array("Windows", "WINNT"))) {
    $pf = getenv('ProgramFiles') ;
    $pf = str_replace(" (x86)", "", $pf) ;
    $pf = $pf.'\\' ;
    $command = "where /R \"{$pf}\" \"*VBoxManage*\" " ;
    echo $command."\n" ;
    $outputArray = array();
    exec($command, $outputArray);
    define('VBOXMGCOMM', "{$outputArray[0]} ") ;
    define('CLEOCOMM', "cleopatra.bat") ;
    define('DAPPCOMM', "dapperstrano.bat") ;
    define('PHLCOMM', "phlagrant.bat") ;
    define('BOXDIR', $pf.'phlagrant\boxes') ;
    define('DS', "\\");
    define('BASE_TEMP_DIR', 'C:\\Temp\\'); }
else if (in_array(PHP_OS, array("Linux", "Solaris", "FreeBSD", "OpenBSD", "Darwin"))) {
    define('VBOXMGCOMM', "vboxmanage ") ;
    define('CLEOCOMM', "cleopatra ") ;
    define('DAPPCOMM', "dapperstrano ") ;
    define('PHLCOMM', "phlagrant.bat") ;
    define('BOXDIR', '/opt/phlagrant/boxes') ;
    define('DS', "/");
    define('BASE_TEMP_DIR', '/tmp/'); }