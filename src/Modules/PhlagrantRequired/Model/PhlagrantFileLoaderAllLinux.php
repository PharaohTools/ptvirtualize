<?php

Namespace Model;

class PhlagrantFileLoaderAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("PhlagrantFileLoader") ;

    public function load() {
        $this->findFile() ;
        $phlagrant = $this->loadClass() ;
        return $phlagrant;
    }

    public function loadClass() {
        $phlagrantfilename = $this->getPfile() ;
        $cname = (isset($this->params["classname"])) ?
            $this->params["classname"] :
            str_replace(".php", "", $phlagrantfilename) ;
        $cname = '\Model\\'.$cname  ;
        $phlagrant = (class_exists($cname)) ? new $cname($this->params) : null ;
        return $phlagrant;
    }

    private function getPfile() {
        $phlagrantfile = "Phlagrantfile" ;
        $phlagrantfile = (isset($this->params["pfile"])) ? $this->params["pfile"] : $phlagrantfile ;
        $phlagrantfile = (isset($this->params["phlagrantfile"])) ? $this->params["phlagrantfile"] : $phlagrantfile ;
        return $phlagrantfile;
    }

    protected function findFile() {
        $phlagrantfile = $this->getPfile() ;
        if (file_exists(getcwd().DS."$phlagrantfile") && is_file(getcwd().DS."$phlagrantfile")) {
            require_once(getcwd().DS."$phlagrantfile"); }
        else if (file_exists($phlagrantfile)) {
            require_once($phlagrantfile); }
        else if (!is_null($phlagrantfile)) {
            require_once(getcwd().DS."Phlagrantfile"); }
        else if (file_exists(getcwd().DS."Phlagrantfile")) {
            require_once(getcwd().DS."Phlagrantfile"); }
        else if (file_exists(getcwd().DS."phlagrantfile")) {
            require_once(getcwd().DS."phlagrantfile"); }
        else if (file_exists(getcwd().DS."build/config/phlagrant/Phlagrantfile")) {
            require_once(getcwd().DS."build/config/phlagrant/Phlagrantfile"); }
        else if (file_exists(getcwd().DS."build/config/phlagrant/phlagrantfile")) {
            require_once(getcwd().DS."build/config/phlagrant/phlagrantfile"); }
    }

}
