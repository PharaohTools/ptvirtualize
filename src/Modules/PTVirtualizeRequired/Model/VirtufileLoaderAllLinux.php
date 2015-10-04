<?php

Namespace Model;

class VirtufileLoaderAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("VirtufileLoader") ;

    public function load() {
        if ($this->findFile() ==false) {
            return false; }
        $ptvirtualize = $this->loadClass() ;
        return $ptvirtualize;
    }

    public function loadClass() {
        $virtufilename = $this->getPfile() ;
        $cname = (isset($this->params["classname"])) ?
            $this->params["classname"] :
            str_replace(".php", "", $virtufilename) ;
        $cname = '\Model\\'.$cname  ;
        if (class_exists($cname)) {
            $ptvirtualize = new $cname($this->params) ;
            return $ptvirtualize; }
    }

    private function getPfile() {
        $virtufile = "Virtufile" ;
        $virtufile = (isset($this->params["pfile"])) ? $this->params["pfile"] : $virtufile ;
        $virtufile = (isset($this->params["virtufile"])) ? $this->params["virtufile"] : $virtufile ;
        return $virtufile;
    }

    protected function findFile() {
        $virtufile = $this->getPfile() ;
        if (file_exists(getcwd().DS."$virtufile") && is_file(getcwd().DS."$virtufile")) {
            $res = $this->tryRequiring(getcwd().DS."$virtufile");
            if ($res==true) return $res ; }
        if (file_exists($virtufile)) {
            $res = $this->tryRequiring($virtufile);
            if ($res==true) return $res ; }
        if (!is_null($virtufile)) {
            $res = $this->tryRequiring(getcwd().DS."Virtufile");
            if ($res==true) return $res ; }
        if (file_exists(getcwd().DS."Virtufile")) {
            $res = $this->tryRequiring(getcwd().DS."Virtufile");
            if ($res==true) return $res ; }
        if (file_exists(getcwd().DS."virtufile")) {
            $res = $this->tryRequiring(getcwd().DS."virtufile");
            if ($res==true) return $res ; }
        if (file_exists(getcwd().DS."build".DS."config".DS."ptvirtualize".DS."Virtufile")) {
            $res = $this->tryRequiring(getcwd().DS."build".DS."config".DS."ptvirtualize".DS."Virtufile");
            if ($res==true) return $res ; }
        if (file_exists(getcwd().DS."build".DS."config".DS."ptvirtualize".DS."virtufile")) {
            $res = $this->tryRequiring(getcwd().DS."build".DS."config".DS."ptvirtualize".DS."virtufile");
            if ($res==true) return $res ; }

        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Unable to find Virtufile", $this->getModuleName()) ;
        \Core\BootStrap::setExitCode(1) ;
        return false ;
    }

    private function tryRequiring($fyley) {
        try {
            $res = include_once($fyley);
            return $res ;
        }
        catch (\Exception $e) {
            return false ;
        }
    }

}
