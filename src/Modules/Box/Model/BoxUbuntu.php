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
    protected $vmname ;
    protected $provider ;
    protected $metadata ;

    public function __construct($params) {
        parent::__construct($params);
        $this->actionsToMethods = $this->setActionsToMethods() ;
        $this->autopilotDefiner = "Box" ;
        $this->installCommands = array() ;
        $this->uninstallCommands = array() ;
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
        if ($this->downloadIfRemote() == false) {
            return false; }
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

    public function performBoxPackage() {
        $this->metadata = new \StdClass() ;
        $this->name = $this->getBoxNewName();
        $this->metadata->provider = $this->askForProvider();
        $this->metadata->name = $this->name;
        $this->metadata->description = $this->askForDescription();
        $this->metadata->group = $this->askForGroup();
        $this->metadata->slug = $this->askForSlug();
        $this->metadata->home_location = $this->askForHomeLocation();
        $this->target = $this->getTargetBoxLocation();
        $this->vmname = $this->getVmName();
        $this->findProvider("BoxPackage") ;
        $this->attemptBoxPackage() ;
        return true;
    }

    protected function askForProvider() {
        if (isset($this->params["provider"])) { return $this->params["provider"]; }
        else if (isset($this->params["guess"])) { return "virtualbox"; }
        else {
            $source = self::askForInput("Enter Provider Name for Box Metadata:", true);
            return $source ; }
    }

    protected function askForDescription() {
        if (isset($this->params["description"])) { return $this->params["description"]; }
        else {
            $source = self::askForInput("Enter Description for Box Metadata:", true);
            return $source ; }
    }

    protected function askForGroup() {
        if (isset($this->params["group"])) { return $this->params["group"]; }
        else {
            $source = self::askForInput("Enter Group for Box Metadata:", true);
            return $source ; }
    }

    protected function askForSlug() {
        if (isset($this->params["slug"])) { return $this->params["slug"]; }
        else if (isset($this->params["guess"])) { return $this->formatSlug($this->params["name"]); }
        else {
            $source = self::askForInput("Enter Slug for Box Metadata:", true);
            return $source ; }
    }

    protected function formatSlug($slug) {
        $slug = str_replace(" ", "", $slug);
        $slug = str_replace(".", "", $slug);
        $slug = str_replace("_", "", $slug);
        $slug = strtolower($slug);
        return $slug ;
    }

    protected function askForHomeLocation() {
        if (isset($this->params["home-location"])) {
            return $this->ensureTrailingSlash($this->params["home-location"]); }
        else if (isset($this->params["guess"])) { return "http://www.ptvirtualizeboxes.co.uk/"; }
        else {
            $source = self::askForInput("Enter Home Location:", true);
            return $this->ensureTrailingSlash($source) ; }
    }

    protected function getOriginalBoxLocation() {
        if (isset($this->params["source"])) {
            return $this->params["source"]; }
        else {
            $source = self::askForInput("Enter Box Source Path:", true);
            return $source ; }
    }

    protected function getTargetBoxLocation() {
        if (isset($this->params["target"])) {
            return $this->ensureTrailingSlash($this->params["target"]) ; }
        else if (isset($this->params["guess"])) {
            $target = BOXDIR ;
            if (!file_exists($target)) { mkdir($target, true) ; }
            return $this->ensureTrailingSlash($target) ; }
        else {
            $target = self::askForInput("Enter Box Target Path:", true);
            return $this->ensureTrailingSlash($target) ; }
    }

    protected function getBoxNewName() {
        if (isset($this->params["name"])) { return $this->params["name"]; }
        else {
            $name = self::askForInput("Enter Box Name:", true);
            return $name ;}
    }

    protected function getVmName() {
        if (isset($this->params["vmname"])) { return $this->params["vmname"]; }
        else {
            $name = self::askForInput("Enter VM Name (or ID) to Export:", true);
            return $name ;}
    }

    protected function extractMetadata() {
        $boxFile = $this->source ;
        $command = "tar --extract --file=$boxFile -C ".BASE_TEMP_DIR." .".DS."metadata.json" ;
        self::executeAndOutput($command);
        $fData = file_get_contents(BASE_TEMP_DIR."metadata.json") ;
        $command = "rm ".BASE_TEMP_DIR."metadata.json" ;
        self::executeAndOutput($command);
        $fdo = json_decode($fData) ;
        return $fdo ;
    }

    protected function getMetadataFromFS() {
        $file = "{$this->target}{$this->name}".DS."metadata.json" ;
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

    protected function downloadIfRemote() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (substr($this->source, 0, 7) == "http://" || substr($this->source, 0, 8) == "https://") {
            $this->source = $this->ensureTrailingSlash($this->source);
            $logging->log("Box is remote not local, will download to temp directory before adding...");
            set_time_limit(0); // unlimited max execution time
            $tmpFile = BASE_TEMP_DIR.'file.box' ;
            $logging->log("Downloading File ...");
            if (substr($this->source, strlen($this->source)-1, 1) == '/') {
                $this->source = substr($this->source, 0, strlen($this->source)-1) ; }
            // @todo error return false
            self::executeAndOutput("wget -O $tmpFile {$this->source}") ;
            $this->source = $tmpFile ;
            $logging->log("Download complete ...");
            return true ;}
        return true ;
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

    protected function attemptBoxPackage() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (is_object($this->provider)) {
            $logging->log("Attempting to package box via provider {$this->metadata->provider}...");
            $this->provider->packageBox($this->target, $this->vmname, $this->metadata) ; }
        else {
            $logging->log("No Provider available, will not attempt to package box."); }
    }

}