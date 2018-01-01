<?php

Namespace Model;

class BoxUpImportAllOS extends BaseVirtualboxAllOS {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("UpImport") ;

    //@todo need windows version
    public function import($file, $ostype, $name) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $command  = VBOXMGCOMM."import {$file} --vsys 0 --ostype {$ostype}" ;
        $command .= " --vmname {$name}" ;
        $logging->log("Performing Virtualbox import command {$command}", $this->getModuleName()) ;
        $ret = $this->executeAndGetReturnCode($command, true, true);
//        $command = "echo $?" ;
//        $ret = $this->executeAndLoad($command);
        return (in_array($ret["rc"], array("0"))) ? true : false ;
    }

    public function getResumableStates() {
        return array("paused") ;
    }

}