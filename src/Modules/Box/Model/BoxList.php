<?php

Namespace Model;

class BoxList extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian") ;
    public $distros = array("Ubuntu") ;
    public $versions = array("11.04", "11.10", "12.04", "12.10", "13.04") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Listing") ;
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
            "list" => "performBoxList"
        ) ;
    }

    public function performBoxList() {
        // box list
        // get the box directory  +
        // get list of contents
        // get list of directories containing a metadata.json and box.ova
        // load each metadata.json into an array
        // send the array of objects back or false
        $this->target = $this->getTargetBoxLocation();
        $boxList = $this->getBoxList();
        return $boxList;
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

    protected function getBoxList() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (is_dir($this->target)) {
            if (is_readable($this->target)) {
                $allcontents = scandir($this->target) ;
                $boxObjectArray = $this->getBoxObjects($allcontents) ;
                return $boxObjectArray ; }
            else {
                $logging->log("The specified box path is not readable", $this->getModuleName()); }}
        else {
            $logging->log("The specified box path is not a directory", $this->getModuleName()); }
        return false ;
    }

    private function getBoxObjects($allcontents) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $objs = array();
        foreach ($allcontents as $onecontent) {
            if (!in_array($onecontent, array(".", ".."))) { // ignore dotfiles
                if (is_dir($this->target.$onecontent)) { // its a dir
                    $mdf = $this->target.$onecontent.DS."metadata.json" ;
                    $logging->log("Looking for metadata.json at ".$mdf, $this->getModuleName());
                    if (file_exists($mdf)) {
                        $logging->log("Found metadata.json at ".$mdf, $this->getModuleName());
                        $ob = json_decode(file_get_contents($mdf)) ;
                        $ob->loc = "$this->target{$onecontent}" ;
                        $objs[] = $ob ; }
                    else {
                        $logging->log("No metadata.json at ".$mdf, $this->getModuleName()); } } } }
        return $objs ;
    }

}