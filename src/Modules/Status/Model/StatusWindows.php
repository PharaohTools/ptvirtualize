<?php

Namespace Model;

class StatusWindows extends StatusLinuxMac {

    // Compatibility
    public $os = array("Windows", 'WINNT') ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public function listVms() {
        error_reporting(0) ;
        $comm = 'cd/ && dir /s/b Virtufile ' ;
        $res = self::executeAndLoad($comm) ;
        if ($res === '') { return false ; }
        $lines = explode("\n", $res) ;
        $vms = array_diff($lines, array('')) ;
        return $vms;
    }

}