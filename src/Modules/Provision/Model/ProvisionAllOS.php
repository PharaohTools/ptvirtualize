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

    protected $osProvisioner ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function provisionNow($hook = "") {
        if ($this->loadFiles() == false) { return false; }
        return $this->osProvisioner->provision($hook);
    }

    public function provisionHook($hook, $type) {
        if ($this->loadFiles() == false) { return false; }
        return $this->osProvisioner->provisionHook($hook, $type);
    }

    public function loadFiles() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $this->virtufile = $this->loadVirtufile();
        $this->papyrus = $this->loadPapyrusLocal();
        $this->osProvisioner = $this->loadOSProvisioner() ;
        if (in_array(false, array($this->virtufile, $this->osProvisioner))) {
            \Core\BootStrap::setExitCode(1);
            $logging->log("Unable to load a required file", $this->getModuleName()) ;
            return false ; }
        return true ;
    }

    protected function loadVirtufile() {
        $prFactory = new \Model\PTVirtualizeRequired();
        $ptvirtualizeFileLoader = $prFactory->getModel($this->params, "VirtufileLoader") ;
        return $ptvirtualizeFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\PTVirtualizeRequired();
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load($this->virtufile) ;
    }

    protected function loadOSProvisioner() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $provFile = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."OSProvisioners".DIRECTORY_SEPARATOR.
            $this->virtufile->config["vm"]["ostype"].".php" ;
        if (file_exists($provFile)) {
            require_once ($provFile) ;
            $logging->log("OS Provisioner found for {$this->virtufile->config["vm"]["ostype"]}", $this->getModuleName()) ;
            $osp = new \Model\OSProvisioner($this->params) ;
            $osp->virtufile = $this->virtufile;
            $osp->papyrus = $this->papyrus;
            return $osp ; }
        $logging->log("No suitable OS Provisioner found for {$this->virtufile->config["vm"]["ostype"]}", $this->getModuleName()) ;
        return null ;
    }

}