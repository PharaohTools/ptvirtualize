<?php

Namespace Model;

class BoxUpOther extends BaseFunctionModel {

    // Compatibility
    public $os = array("Linux", "Darwin") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("UpOther") ;

    public function vmExists($name) {
        $out = $this->executeAndLoad(VBOXMGCOMM." list vms");
        if (strpos($out, $name)!= false) { return true ; }
        return false ;
    }

    public function vmIsRunning($name) {
        $out = $this->executeAndLoad(VBOXMGCOMM." list runningvms");
        if (strpos($out, $name ) != false ) { return true ; }
        return false ;
    }


}