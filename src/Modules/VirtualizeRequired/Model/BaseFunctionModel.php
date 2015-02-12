<?php

Namespace Model;

class BaseFunctionModel extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public $virtufile;
    public $papyrus ;
    public $provider ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    protected function loadFiles() {
        $this->virtufile = $this->loadVirtualizeFile();
        $this->papyrus = $this->loadPapyrusLocal();
    }

    protected function loadVirtualizeFile() {
        $prFactory = new \Model\VirtualizeRequired();
        $virtualizeFileLoader = $prFactory->getModel($this->params, "VirtualizeFileLoader") ;
        return $virtualizeFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\VirtualizeRequired();
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load($this->virtufile) ;
    }

    protected function findProvider($modGroup = "BoxDestroy") {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->virtufile->config["vm"]["provider"])) {
            $logging->log("Provider {$this->virtufile->config["vm"]["provider"]} found in Virtufile") ;
            $this->provider = $this->getProvider($this->virtufile->config["vm"]["provider"], $modGroup) ; }
        else {
            $logging->log("No Provider configured in Virtufile."); }
    }

    protected function getProvider($provider, $modGroup) {
        $infoObjects = \Core\AutoLoader::getInfoObjects();
        $allProviders = array();
        foreach($infoObjects as $infoObject) {
            if ( method_exists($infoObject, "vmProviderName") ) {
                $allProviders[] = $infoObject->vmProviderName(); } }
        foreach($allProviders as $oneProvider) {
            if ( $provider == $oneProvider ) {
                $className = '\Model\\'.ucfirst($oneProvider) ;
                $providerFactory = new $className();
                $provider = $providerFactory->getModel($this->params, $modGroup);
                return $provider ; } }
        return false ;
    }

}