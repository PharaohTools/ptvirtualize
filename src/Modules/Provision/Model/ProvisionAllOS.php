<?php

Namespace Model;

class ProvisionAllOS extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    protected $phlagrantfile;
    protected $papyrus ;
    protected $osProvisioner ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function provisionNow($hook = "") {
        $this->loadFiles();
        $this->osProvisioner->provision($hook);
    }

    public function provisionHook($hook, $type) {
        $this->loadFiles();
        $this->osProvisioner->provisionHook($hook, $type);
    }

    protected function loadFiles() {
        $this->phlagrantfile = $this->loadPhlagrantFile();
        $this->papyrus = $this->loadPapyrusLocal();
        $this->osProvisioner = $this->loadOSProvisioner() ;
    }

    protected function loadPhlagrantFile() {
        $prFactory = new \Model\PhlagrantRequired();
        $phlagrantFileLoader = $prFactory->getModel($this->params, "PhlagrantFileLoader") ;
        return $phlagrantFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\PhlagrantRequired();
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load($this->phlagrantfile) ;
    }

    protected function loadOSProvisioner() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $provFile = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."OSProvisioners".DIRECTORY_SEPARATOR.
            $this->phlagrantfile->config["vm"]["ostype"].".php" ;
        if (file_exists($provFile)) {
            require_once ($provFile) ;
            $logging->log("OS Provisioner found for {$this->phlagrantfile->config["vm"]["ostype"]}") ;
            $osp = new \Model\OSProvisioner($this->params) ;
            $osp->phlagrantfile = $this->phlagrantfile;
            $osp->papyrus = $this->papyrus;
            return $osp ; }
        $logging->log("No suitable OS Provisionerfound");
        return null ;
    }

}