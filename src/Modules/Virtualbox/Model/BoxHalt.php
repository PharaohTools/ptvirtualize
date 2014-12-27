<?php

Namespace Model;

class BoxHalt extends BaseVirtualboxAllOS {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxHalt") ;

    public function haltSoft($name) {
        $command = VBOXMGCOMM." controlvm {$name} acpipowerbutton" ;
        $this->executeAndOutput($command);
    }

    public function haltPause($name) {
        $command = VBOXMGCOMM." controlvm {$name} pause" ;
        $this->executeAndOutput($command);
    }

    public function haltHard($name) {
        $command = VBOXMGCOMM." controlvm {$name} poweroff" ;
        $this->executeAndOutput($command);
    }

    public function getHaltableStates() {
        return array("running") ;
    }

}