<?php

Namespace Model;

class DestroyAllLinux extends BaseLinuxApp {

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

    public function destroyNow() {
        $this->loadFiles();
        $command = "vboxmanage controlvm {$this->phlagrantfile->config["vm"]["name"]} acpipowerbutton" ;
        echo $command ;
        $this->executeAndOutput($command);
    }

    public function destroyPause() {
        $this->loadFiles();
        $command = "vboxmanage controlvm {$this->phlagrantfile->config["vm"]["name"]} pause" ;
        $this->executeAndOutput($command);
    }

    public function destroyHard() {
        $this->loadFiles();
        $command = "vboxmanage controlvm {$this->phlagrantfile->config["vm"]["name"]} poweroff" ;
        $this->executeAndOutput($command);
    }

    protected function loadFiles() {
        $this->phlagrantfile = $this->loadPhlagrantFile();
        $this->papyrus = $this->loadPapyrusLocal();
    }

    protected function loadPhlagrantFile() {
        $upFactory = new \Model\Up();
        $phlagrantFileLoader = $upFactory->getModel($this->params, "PhlagrantFileLoader") ;
        return $phlagrantFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        return \Model\AppConfig::getProjectVariable("phlagrant-box", true) ;
    }

}