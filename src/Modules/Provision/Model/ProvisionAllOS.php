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
        $this->loadFiles();
        $this->osProvisioner->provision($hook);
    }

    public function provisionHook($hook, $type) {
        $this->loadFiles();
        $this->osProvisioner->provisionHook($hook, $type);
    }

    public function loadFiles() {
        $this->virtualizerfile = $this->loadVirtualizerFile();
        $this->papyrus = $this->loadPapyrusLocal();
        $this->osProvisioner = $this->loadOSProvisioner() ;
    }

    protected function loadVirtualizerFile() {
        $prFactory = new \Model\VirtualizerRequired();
        $virtualizerFileLoader = $prFactory->getModel($this->params, "VirtualizerFileLoader") ;
        return $virtualizerFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\VirtualizerRequired();
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load($this->virtualizerfile) ;
    }

    protected function loadOSProvisioner() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $provFile = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."OSProvisioners".DIRECTORY_SEPARATOR.
            $this->virtualizerfile->config["vm"]["ostype"].".php" ;
        if (file_exists($provFile)) {
            require_once ($provFile) ;
            $logging->log("OS Provisioner found for {$this->virtualizerfile->config["vm"]["ostype"]}") ;
            $osp = new \Model\OSProvisioner($this->params) ;
            $osp->virtualizerfile = $this->virtualizerfile;
            $osp->papyrus = $this->papyrus;
            return $osp ; }
        $logging->log("No suitable OS Provisioner found");
        return null ;
    }

}