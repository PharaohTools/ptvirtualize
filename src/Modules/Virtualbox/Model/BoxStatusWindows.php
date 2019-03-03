<?php

Namespace Model;

class BoxStatusWindows extends BoxStatusLinuxMac {

    // Compatibility
    public $os = array("Windows", 'WINNT', 'Windows_NT') ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxStatus") ;

    public function statusFull($name) {
        $command = VBOXMGCOMM." showvminfo \"{$name}\" 2> NUL" ;
        $status = $this->executeAndLoad($command) ;
        return $status ;
    }

    public function statusData($name) {
        $command = VBOXMGCOMM." showvminfo \"{$name}\" --machinereadable 2> NUL" ;
        $status_string = $this->executeAndLoad($command) ;
        $lines = explode("\n", $status_string) ;
        $status = [] ;
        foreach ($lines as $line) {
            $pair = explode("=", $line) ;
            if (isset($pair[0]) && isset($pair[1])) {
                $status[$pair[0]] = $pair[1] ;
            }
        }
        return $status ;
    }

}