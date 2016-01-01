<?php

Namespace Model ;

class ProvisionDefaultAllOS extends Base {

    public $virtufile;
    public $papyrus ;
    protected $provisionModel ;

    public function provision( $hook = "") {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $provisionOuts = array() ;
        if ($hook != "") { $hook = "_$hook" ; }
        foreach ($this->virtufile->config["vm"]["provision$hook"] as $provisionerSettings) {
            $curout = $this->doSingleProvision($provisionerSettings) ;
            $provisionOuts[] = $curout ;
            $cur_xc = \Core\BootStrap::getExitCode() ;
            if ($curout==false || $cur_xc !==0) {
                $logging->log("Provisioning Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return $provisionOuts ; } }
        return $provisionOuts ;
    }

    public function provisionHook($hook, $type) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Provisioning from Virtufile settings if available for $hook $type", "Provision") ;
        $provisionOuts = $this->provisionVirtufile($hook, $type) ;
        $logging->log("Provisioning from hook directories if available for $hook $type", "Provision") ;
        $provisionOuts = array_merge($provisionOuts, $this->provisionHookDirs($hook, $type)) ;
        $cur_xc = \Core\BootStrap::getExitCode() ;
        if (in_array(false, $provisionOuts) || $cur_xc !==0) {
            $logging->log("Provisioning Hooks Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ; }
        return $provisionOuts ;
    }

    protected function provisionVirtufile($module, $hook) {
        $provisionOuts = array() ;
        if ($module=="up" && $hook == "default") { $pstr = "provision" ; }
        else { $pstr = "provision_{$module}_{$hook}" ; }

        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->virtufile->config["vm"][$pstr]) &&
            count($this->virtufile->config["vm"][$pstr]) > 0){
            foreach ($this->virtufile->config["vm"][$pstr] as $provisionerSettings) {
                if (isset($this->params["hooks"]) && in_array($hook, $this->getParameterHooks())) {
                    $logging->log("Requested hooks include $module $hook, executing", "Provision") ;
                    $curout = $this->doSingleProvision($provisionerSettings) ;
                    $provisionOuts[] = $curout ;
                    $cur_xc = \Core\BootStrap::getExitCode() ;
                    if ($curout==false || $cur_xc !==0) {
                        $logging->log("Provisioning Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                        return $provisionOuts ; }  }
                else {
                    $curout = $this->doSingleProvision($provisionerSettings) ;
                    $provisionOuts[] = $curout ;
                    $cur_xc = \Core\BootStrap::getExitCode() ;
                    if ($curout==false || $cur_xc !==0) {
                        $logging->log("Provisioning Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                        return $provisionOuts ; } } } }
        return $provisionOuts ;
    }

    protected function getParameterHooks() {
        if (!isset($this->params["hooks"])) { return array() ; }
        $tags = explode(",", $this->params["hooks"]) ;
        return $tags ;
    }

    protected function provisionHookDirs($hook, $type) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $provisionOuts = array() ;
        // @todo this will do for now but should be dynamic
        $provisioners_and_tools = array(
            "PharaohTools" => array("ptconfigure", "ptdeploy"),
            "Shell" => array("shell") ) ;
        $provisioners = array_keys($provisioners_and_tools) ;
        foreach ($provisioners as $provisioner) {
            $tools = $provisioners_and_tools[$provisioner] ;
            foreach ($tools as $tool) {
                $targets = array("host", "guest") ;
                foreach ($targets as $target) {
                    $dir = getcwd().DS."build".DS."config".DS."ptvirtualize".DS."hooks".DS."$provisioner".DS.
                        "$tool".DS."$hook".DS."$target".DS."$type" ;
                    $hookDirectoryExists = file_exists($dir) ;
                    // var_dump("hde", $hookDirectoryExists, "hdd", $hookDirectoryIsDir) ;
                    if ($hookDirectoryExists) {
                        $relDir = str_replace(getcwd(), "", $dir) ;
                        $logging->log("Virtualize hook directory $relDir found") ;
                        $hookDirFiles = scandir($dir) ;
                        foreach ($hookDirFiles as $hookDirFile) {
                            if (substr($hookDirFile, strlen($hookDirFile)-4) == ".php") {
                                $logging->log("Virtualize hook file $dir".DS."$hookDirFile found") ;
                                $provisionerSettings =
                                    array(
                                        "provisioner" => $provisioner,
                                        "tool" => $tool,
                                        "target" => $target,
                                        "script" => "$dir".DS."$hookDirFile" );
                                $logging->log("Executing $hookDirFile with $tool", $this->getModuleName()) ;
                                $curout = $this->doSingleProvision($provisionerSettings) ;
                                $provisionOuts[] = $curout ;
                                $cur_xc = \Core\BootStrap::getExitCode() ;
                                if ($curout==false || $cur_xc !==0) {
                                    $logging->log("Provisioning Hook directory Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                                    return $provisionOuts ; } } } } } } }
        return $provisionOuts ;
    }


    // @todo this should support other provisioners than pharaoh, provide some override here to allow
    // @todo chef solo, puppet agent, salt or ansible to get invoked
    protected function doSingleProvision($provisionerSettings) {
        $pharaohSpellings = array("Pharaoh", "pharaoh", "PharaohTools", "pharaohTools", "Pharaoh", "pharaoh", "PharaohTools", "pharaohTools") ;
        if (in_array($provisionerSettings["provisioner"], $pharaohSpellings)) {
            $provisionObjectFactory = new \Model\PharaohTools() ; }
        else if (in_array($provisionerSettings["provisioner"], array("shell", "bash", "Shell", "Bash"))) {
            $provisionObjectFactory = new \Model\Shell() ; }
        $provisionObject = $provisionObjectFactory->getModel($this->params, "Provision");
        $provisionObject->virtufile = $this->virtufile;
        $provisionObject->papyrus = $this->papyrus;
        $res = $provisionObject->provision($provisionerSettings, $this) ;
        return $res ;
    }

}
