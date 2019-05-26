<?php

Namespace Model;

class ProvisionAllOS extends BaseFunctionModel {

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
        $this->findProvider("BoxProvision");
        if ($this->currentStateIsProvisionable() == false) {
            \Core\BootStrap::setExitCode(1) ;
            return false; }
//        return $this->osProvisioner->provision($hook);
        return $this->provisionVm();
    }

    public function provisionVm($onlyIfRequestedByParam = false, $extra_params = null) {
        if (isset($extra_params) && is_array($extra_params)) {
            $this->params = array_merge($this->params, $extra_params) ; }
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if ($onlyIfRequestedByParam == true) {
            $gpsp = $this->getParamBySynonym("provision") ;
            if ($gpsp !== true ) {
                $logging->log("Not provisioning as provision parameter not set", $this->getModuleName());
                return true; } }
        if (isset($this->params["hooks"])) {
            $logging->log("Specific execution hooks requested, {$this->params["hooks"]}", $this->getModuleName());
            $hooks = $this->getParameterHooks();
            $pns = array();
            foreach ($hooks as $hook) {
                $res = $this->runHook("up", $hook);
                $pns[] = $res ;
                if ($res == false) {
                    return false ;
                } } }
//        return (in_array(false, $pns)) ? false : true ;
        $pn = $this->runHook("up", "default");
        $this->postProvisionMessage();
        return $pn ;
    }

    public function provisionDefaults($onlyIfRequestedByParam = false, $extra_params = null) {
        if (isset($extra_params) && is_array($extra_params)) {
            $this->params = array_merge($this->params, $extra_params) ; }
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if ($onlyIfRequestedByParam == true) {
            $gpsp = $this->getParamBySynonym("provision") ;
            if ($gpsp !== true ) {
                $logging->log("Not provisioning defaults as provision parameter not set", $this->getModuleName());
                return true; } }
//        if (isset($this->params["hooks"])) {
//            $logging->log("Specific execution hooks requested, {$this->params["hooks"]}", $this->getModuleName());
//            $hooks = $this->getParameterHooks();
//            $pns = array();
//            foreach ($hooks as $hook) {
//                $res = $this->runHook("up", $hook);
//                $pns[] = $res ;
//                if ($res == false) {
//                    return false ;
//                } } }
//        return (in_array(false, $pns)) ? false : true ;
        $pn = $this->runHook("default", "default");
        $this->postProvisionMessage();
        return $pn ;
    }

    protected function postProvisionMessage() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("{$this->virtufile->config["vm"]["post_up_message"]}", $this->getModuleName());
    }

    public function runHook($hook, $type) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->params["ignore-hooks"]) ) {
            $logging->log("Not provisioning $hook $type hooks as ignore hooks parameter is set", $this->getModuleName());
            return true ; }
        $logging->log("Provisioning $hook $type hooks", $this->getModuleName());
        return $this->provisionHook($hook, $type);
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
            $logging->log("Unable to load a required file", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ; }
        return true ;
    }

    protected function getParamBySynonym($param) {
        $ray["modify"] = array("modify", "mod") ;
        $ray["provision"] = array("provision", "pro") ;
        foreach($ray[$param] as $entry) {
            if (isset($this->params[$entry])) {
                return true ; } }
        return null ;
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

    protected function currentStateIsProvisionable() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $provisionables = $this->provider->getProvisionableStates();
        if ($this->provider->isVMInStatus($this->virtufile->config["vm"]["name"], $provisionables) == true) {
            $logging->log("This VM is in a Provisionable state...", $this->getModuleName()) ;
            return true ; }
        $logging->log("This VM is not in a Provisionable state...", $this->getModuleName()) ;
        return false ;
    }

    protected function getParameterHooks() {
        if (!isset($this->params["hooks"])) { return array() ; }
        $tags = explode(",", $this->params["hooks"]) ;
        return $tags ;
    }

}