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
            if (!is_null($cur_xc) && (is_int($cur_xc) && $cur_xc !== 0)) {
                $logging->log("Provisioning Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return $provisionOuts ; } }
        return $provisionOuts ;
    }

    // updatega

    public function provisionHook($hook, $type) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Provisioning from Virtufile settings if available for $hook $type", "Provision") ;
        $provisionOuts1 = $this->provisionVirtufile($hook, $type) ;
//        $logging->log("Provisioning from hook directories if available for $hook $type", "Provision") ;
//        $provisionOuts2 = $this->provisionHookDirs($hook, $type) ;
//        $cur_xc = \Core\BootStrap::getExitCode() ;
//        $provisionOuts3 = array_merge($provisionOuts1, $provisionOuts2) ;
//        var_dump('pvo1: ', $provisionOuts1) ;
        if ($provisionOuts1 === false) {
            $logging->log("Failure executing hooks", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ;
        }
        if (count($provisionOuts1) == 0) {
//            $logging->log("No hooks run", $this->getModuleName()) ;
            return true ;
        }
        $res = (in_array(false, $provisionOuts1)) ? false : true ;
        if ($res == false) {
            $logging->log("Provisioning One Hook Failed", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ; }
        return $provisionOuts1 ;
    }

    protected function provisionVirtufile($module, $hook) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $provisionOuts = array() ;

        // default provisioners
        if (isset($this->virtufile->config["vm"]['defaults'])) {
            if (is_string($this->virtufile->config["vm"]['defaults'])) {
                $these_defaults = explode(',', $this->virtufile->config["vm"]['defaults']) ;
            } else {
                $these_defaults = $this->virtufile->config["vm"]['defaults'] ;
            }
            foreach ($these_defaults as $default_script) {

                $default_script_parsed = $this->findDefaultFromAcronym($default_script) ;
                $one_default_script_settings =
                    [ "provisioner" => "Shell",
                      "tool" => "shell",
                      "target" => "guest",
                      "default" => "$default_script_parsed",
                      "force" => true ] ;

                $curout = $this->doSingleProvision($one_default_script_settings) ;
                $provisionOuts[] = $curout ;
                $cur_xc = \Core\BootStrap::getExitCode() ;

                if (!is_null($cur_xc) && (is_int($cur_xc) && $cur_xc !== 0)) {
                    $logging->log("Provisioning Failed ...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                    return $provisionOuts ; }

            }
        }

        if ($module=="up" && $hook == "default") { $pstr = "provision" ; }
        else { $pstr = "provision_{$module}_{$hook}" ; }

        if (isset($this->virtufile->config["vm"][$pstr]) &&
            count($this->virtufile->config["vm"][$pstr]) > 0){
            foreach ($this->virtufile->config["vm"][$pstr] as $provisionerSettings) {
                if (isset($this->params["hooks"]) && in_array($hook, $this->getParameterHooks())) {
                    $logging->log("Requested hooks include $module $hook, executing", "Provision") ;
                    $curout = $this->doSingleProvision($provisionerSettings) ;
                    $provisionOuts[] = $curout ;
                    $cur_xc = \Core\BootStrap::getExitCode() ;
                    // var_dump('pf1', $curout, $cur_xc) ;
                    if (!is_null($cur_xc) && (is_int($cur_xc) && $cur_xc !== 0)) {
                        $logging->log("Provisioning Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                        return $provisionOuts ; }  }
                else {
                    $curout = $this->doSingleProvision($provisionerSettings) ;
                    $provisionOuts[] = $curout ;
                    $cur_xc = \Core\BootStrap::getExitCode() ;
                    // var_dump('pf2', $curout, $cur_xc) ;
                    if (!is_null($cur_xc) && (is_int($cur_xc) && $cur_xc !== 0)) {
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
                                if (!is_null($cur_xc) && (is_int($cur_xc) && $cur_xc !== 0)) {
                                    $logging->log("Provisioning Hook directory Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                                    return $provisionOuts ; } } } } } } }
        return $provisionOuts ;
    }

    // @todo this should support other provisioners than pharaoh tools, provide some override here to allow
    // @todo chef solo, puppet agent, salt or ansible to get invoked
    protected function doSingleProvision($provisionerSettings) {
        $pharaohSpellings = array("Pharaoh", "pharaoh", "PharaohTools", "pharaohTools", "Pharaoh", "pharaoh", "PharaohTools", "pharaohTools") ;
        if (in_array($provisionerSettings["provisioner"], $pharaohSpellings)) {
            $provisionObjectFactory = new \Model\PharaohTools() ; }
        else if (in_array($provisionerSettings["provisioner"], array("shell", "bash", "Shell", "Bash"))) {
            $provisionObjectFactory = new \Model\Shell() ; }
        else if (in_array($provisionerSettings["provisioner"], array("VirtualKeyboard", "virtualKeyboard", "keyboard", "key"))) {
            $provisionObjectFactory = new \Model\VirtualKeyboard() ; }
        $provisionObject = $provisionObjectFactory->getModel($this->params, "Provision");
        $provisionObject->virtufile = $this->virtufile;
        $provisionObject->papyrus = $this->papyrus;
        $res = $provisionObject->provision($provisionerSettings, $this) ;
        return $res ;
    }

    protected function findDefaultFromAcronym($acronym) {
        $all_defaults = [
            'PHP' => ['php'],
            'PTConfigureInit' => ['ptc', 'pharaohconfigure', 'configure'],
            'MountShares' => ['shares', 'mounts', 'mountshares'],
            'GuestAdditions' => ['ga', 'guestadditions'],
        ] ;

        if (array_key_exists($acronym, array_keys($all_defaults))) {
            return $acronym ;
        }

        foreach ($all_defaults as $titles => $alternates) {
            if (in_array($acronym, $alternates)) {
                return $titles ;
            }
        }
        return null ;
    }

}
