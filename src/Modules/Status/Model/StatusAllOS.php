<?php

Namespace Model;

class StatusAllOS extends BaseFunctionModel {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function statusShow() {
        $this->loadFiles();
        $this->findProvider("BoxStatus");
        return $this->provider->statusShow($this->phlagrantfile->config["vm"]["name"]);
    }

    public function statusFull() {
        $this->loadFiles();
        $this->findProvider("BoxStatus");
        return $this->provider->statusFull($this->phlagrantfile->config["vm"]["name"]);
    }

}