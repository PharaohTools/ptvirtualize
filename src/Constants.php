<?php

/**
 * Pharaoh Tools Constants
 */

define('PHARAOH_APP', "ptvirtualize") ;

if (in_array(PHP_OS, array("Windows", "WINNT"))) {
    $pf = getenv('ProgramFiles') ;
    $pf = str_replace(" (x86)", "", $pf) ;
    $command = "where /R \"{$pf}\" *VBoxManage* " ;
    $outputArray = array();
    exec($command, $outputArray);
    define('SUDOPREFIX', "");
    define('VBOXMGCOMM', "\"{$outputArray[0]}\" ") ;
    define('PTCCOMM', '"C:\PharaohTools\ptconfigure.cmd"') ;
    define('PTDCOMM', '"C:\PharaohTools\ptdeploy.cmd"') ;
    define('PTVCOMM', '"C:\PharaohTools\ptvirtualize.cmd"') ;
    define('BOXDIR', 'C:\\PharaohTools\boxes') ;
    define('DS', "\\");
    define('BASE_TEMP_DIR', 'C:\\Temp\\'); }
else if (in_array(PHP_OS, array("Linux", "Solaris", "FreeBSD", "OpenBSD", "Darwin"))) {
    $uname = exec('whoami');
    $isAdmin = ($uname == "root") ? true : false ;
    if ($isAdmin == true) { define('SUDOPREFIX', ""); }
    else { define('SUDOPREFIX', "sudo "); }
    define('VBOXMGCOMM', "vboxmanage ") ;
    define('PTCCOMM', "ptconfigure ") ;
    define('PTDCOMM', "ptdeploy ") ;
    define('PTVCOMM', "ptvirtualize") ;
    define('BOXDIR', '/opt/ptvirtualize/boxes') ;
    define('DS', "/");
    define('BASE_TEMP_DIR', '/tmp/'); }