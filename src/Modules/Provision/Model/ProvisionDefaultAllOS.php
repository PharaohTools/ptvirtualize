<?php

Namespace Model ;

class ProvisionDefaultAllOS extends Base {

    public $virtufile;
    public $papyrus ;
    protected $provisionModel ;

    public function provision( $hook = "") {
        $provisionOuts = array() ;
        if ($hook != "") {$hook = "_$hook" ; }
        foreach ($this->virtufile->config["vm"]["provision$hook"] as $provisionerSettings) {
            $provisionOuts[] = $this->doSingleProvision($provisionerSettings) ; }
        return $provisionOuts ;
    }

    public function provisionHook($hook, $type) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Provisioning from Virtufile settings if available for $hook $type") ;
        $provisionOuts = $this->provisionVirtufile($hook, $type) ;
        $logging->log("Provisioning from hook directories if available for $hook $type") ;
        $provisionOuts = array_merge($provisionOuts, $this->provisionHookDirs($hook, $type)) ;
        return $provisionOuts ;
    }

    protected function provisionVirtufile($hook, $type) {
        $provisionOuts = array() ;
        if (isset($this->virtufile->config["vm"]["provision_{$hook}_{$type}"]) &&
            count($this->virtufile->config["vm"]["provision_{$hook}_{$type}"]) > 0){
            foreach ($this->virtufile->config["vm"]["provision_{$hook}_{$type}"] as $provisionerSettings) {
                $provisionOuts[] = $this->doSingleProvision($provisionerSettings) ; } }
        return $provisionOuts ;
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
                                $provisionOuts[] = $this->doSingleProvision($provisionerSettings) ;
                                $logging->log("Executing $hookDirFile with $tool") ; } } } } } }
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
        return $provisionObject->provision($provisionerSettings, $this) ;
    }

}
