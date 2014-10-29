<?php

Namespace Model;

class StatusAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    protected $phlagrantfile;
    protected $papyrus ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function statusShow() {
        $this->loadFiles();
        $command = "vboxmanage showvminfo \"{$this->phlagrantfile->config["vm"]["name"]}\" | grep \"State:\"  " ;
        $status = $this->executeAndLoad($command) ;
        return $status ;
    }

    public function statusFull() {
        $this->loadFiles();
        $command = "vboxmanage showvminfo \"{$this->phlagrantfile->config["vm"]["name"]}\" " ;
        $status = $this->executeAndLoad($command) ;
        return $status ;
    }

    protected function loadFiles() {
        $this->phlagrantfile = $this->loadPhlagrantFile();
        $this->papyrus = $this->loadPapyrusLocal();
    }

    protected function loadPhlagrantFile() {
        $prFactory = new \Model\PhlagrantRequired();
        $phlagrantFileLoader = $prFactory->getModel($this->params, "PhlagrantFileLoader") ;
        return $phlagrantFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\PhlagrantRequired();
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load($this->phlagrantfile->config["vm"]["name"]) ;
    }

}