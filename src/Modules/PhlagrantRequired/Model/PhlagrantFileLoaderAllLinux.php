<?php

Namespace Model;

class PhlagrantFileLoaderAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
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
            substr($phlagrantfilename, 0, strlen($phlagrantfilename)-4) ;
        $cname = '\Model\\'.$cname  ;
        $phlagrant = (class_exists($cname)) ? new $cname($this->params) : null ;
        return $phlagrant;
    }

    private function getPfile() {
        $phlagrantfile = null ;
        $phlagrantfile = (isset($this->params["pfile"])) ? $this->params["pfile"] : null ;
        $phlagrantfile = (isset($this->params["phlagrantfile"])) ? $this->params["phlagrantfile"] : $phlagrantfile ;
        return $phlagrantfile;
    }

    protected function findFile() {
        $phlagrantfile = $this->getPfile() ;
        if (file_exists(getcwd()."/$phlagrantfile") && is_file(getcwd()."/$phlagrantfile")) {
            require_once(getcwd()."/$phlagrantfile"); }
        else if (file_exists($phlagrantfile)) {
            require_once($phlagrantfile); }
        else if (!is_null($phlagrantfile)) {
            require_once(getcwd()."/Phlagrantfile"); }
        else if (file_exists(getcwd()."/Phlagrantfile")) {
            require_once(getcwd()."/Phlagrantfile"); }
        else if (file_exists(getcwd()."/phlagrantfile")) {
            require_once(getcwd()."/phlagrantfile"); }
        else if (file_exists(getcwd()."/build/config/phlagrant/Phlagrantfile")) {
            require_once(getcwd()."/build/config/phlagrant/Phlagrantfile"); }
        else if (file_exists(getcwd()."/build/config/phlagrant/phlagrantfile")) {
            require_once(getcwd()."/build/config/phlagrant/phlagrantfile"); }
    }

}
