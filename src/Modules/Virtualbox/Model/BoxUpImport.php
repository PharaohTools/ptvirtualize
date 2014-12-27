<?php

Namespace Model;

class BoxUpImport extends BaseFunctionModel {

    // Compatibility
    public $os = array("Linux", "Darwin") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("UpImport") ;

    //@todo need windows version
    public function import($file, $ostype, $name) {
        $command  = VBOXMGCOMM." import {$file} --vsys 0 --ostype {$ostype}" ;
        $command .= " --vmname {$name}" ;
        $this->executeAndOutput($command);
        $command = "echo $?" ;
        $ret = $this->executeAndLoad($command);
        return ($ret == "0") ? true : false ;
    }

    public function getResumableStates() {
        return array("paused") ;
    }

}