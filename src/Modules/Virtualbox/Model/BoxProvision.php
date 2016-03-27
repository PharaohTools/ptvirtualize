<?php

Namespace Model;

class BoxProvision extends BaseVirtualboxAllOS {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxProvision") ;

    public function getProvisionableStates() {
        return array("running") ;
    }

}