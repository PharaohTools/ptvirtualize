<?php

Namespace Model;

class BoxUbuntu extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian") ;
    public $distros = array("Ubuntu") ;
    public $versions = array("11.04", "11.10", "12.04", "12.10", "13.04") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default", "BoxAdd") ;
    protected $actionsToMethods ;

    protected $source ;
    protected $target ;
    protected $name ;
    protected $provider ;
    protected $metadata ;

    public function __construct($params) {
        parent::__construct($params);
        $this->actionsToMethods = $this->setActionsToMethods() ;
        $this->autopilotDefiner = "Box" ;
        $this->installCommands = array("apt-get install -y ufw") ;
        $this->uninstallCommands = array("apt-get remove -y ufw") ;
        $this->programDataFolder = "" ;
        $this->programNameMachine = "box" ; // command and app dir name
        $this->programNameFriendly = "!Box!!" ; // 12 chars
        $this->programNameInstaller = "Box" ;
        $this->initialize();
    }

    protected function setActionsToMethods() {
        return array(
            "add" => "performBoxAdd"
        ) ;
    }

    public function performBoxAdd() {
        // box add
        // get the .pbox file (if remote) --
        // get save location --
        // untar the single metadata.json file out of it
        // check the provider
        // load the provider and invoke the add box method there
        // - vbix module
        //  - untar it there
        //  - import it
        $this->source = $this->getOriginalBoxLocation();
        $this->target = $this->getTargetBoxLocation();
        $this->name = $this->getBoxNewName();
        $this->metadata = $this->extractMetadata();
        $this->findProvider() ;
        $this->attemptBoxAdd() ;
        # vbox module
        return true;
    }

    public function performBoxRemove() {
        $this->target = $this->getTargetBoxLocation();
        $this->name = $this->getBoxNewName();
        $this->metadata = $this->getMetadataFromFS();
        $this->findProvider("BoxRemove") ;
        $this->attemptBoxRemove() ;
        # vbox module
        return true;
    }

    protected function getOriginalBoxLocation() {
        if (isset($this->params["source"])) { return $this->params["source"]; }
        else {
            $source = self::askForInput("Enter Box Source Path:", true);
            return $source ; }
    }

    protected function getTargetBoxLocation() {
        // @todo dont hardcode the /opt/phlagrant/
        if (isset($this->params["target"])) { return $this->params["target"]; }
        else if (isset($this->params["guess"])) {
            $target = '/opt/phlagrant/boxes' ;
            if (!file_exists($target)) { mkdir($target, true) ; }
            return $target ; }
        else {
            $target = self::askForInput("Enter Box Target Path:", true);
            return $target ; }
    }

    protected function getBoxNewName() {
        if (isset($this->params["name"])) { return $this->params["name"]; }
        else {
            $name = self::askForInput("Enter Box Name:", true);
            return $name ;}
    }

    protected function extractMetadata() {
        $boxFile = $this->source ;
        $command = "tar --extract --file=$boxFile -C /tmp ./metadata.json" ;
        self::executeAndOutput($command);
        $fData = file_get_contents("/tmp/metadata.json") ;
        $command = "rm /tmp/metadata.json" ;
        self::executeAndOutput($command);
        $fdo = json_decode($fData) ;
        return $fdo ;
    }

    protected function getMetadataFromFS() {
        $file = "{$this->target}/{$this->name}/metadata.json" ;
        $string = file_get_contents($file) ;
        $fdo = json_decode($string) ;
        return $fdo ;
    }

    protected function findProvider($modGroup = "BoxAdd") {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->metadata)) {
            if (isset($this->metadata->provider)) {
                $logging->log("Provider {$this->metadata->provider} found in metadata.json") ;
                $this->provider = $this->getProvider($this->metadata->provider, $modGroup) ; }
            else {
                $logging->log("No Provider configured in Metadata object."); } }
        else {
            $logging->log("No Metadata object found."); }
    }

    protected function getProvider($provider, $modGroup = "BoxAdd") {
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

    protected function attemptBoxAdd() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (is_object($this->provider)) {
            $logging->log("Attempting to add box via provider {$this->metadata->provider}...");
            $this->provider->addBox($this->source, $this->target, $this->name) ; }
        else {
            $logging->log("No Provider available, will not attempt to add box."); }
    }

    protected function attemptBoxRemove() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (is_object($this->provider)) {
            $logging->log("Attempting to remove box via provider {$this->metadata->provider}...");
            $this->provider->removeBox($this->target, $this->name) ; }
        else {
            $logging->log("No Provider available, will not attempt to remove box."); }
    }

}