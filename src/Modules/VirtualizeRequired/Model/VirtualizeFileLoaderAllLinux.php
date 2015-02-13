<?php

Namespace Model;

class VirtualizeFileLoaderAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("VirtualizeFileLoader") ;

    public function load() {
        $this->findFile() ;
        $ptvirtualize = $this->loadClass() ;
        return $ptvirtualize;
    }

    public function loadClass() {
        $virtufilename = $this->getPfile() ;
        $cname = (isset($this->params["classname"])) ?
            $this->params["classname"] :
            str_replace(".php", "", $virtufilename) ;
        $cname = '\Model\\'.$cname  ;
        $ptvirtualize = (class_exists($cname)) ? new $cname($this->params) : null ;
        return $ptvirtualize;
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
            require_once(getcwd().DS."$virtufile"); }
        else if (file_exists($virtufile)) {
            require_once($virtufile); }
        else if (!is_null($virtufile)) {
            require_once(getcwd().DS."Virtufile"); }
        else if (file_exists(getcwd().DS."Virtufile")) {
            require_once(getcwd().DS."Virtufile"); }
        else if (file_exists(getcwd().DS."virtufile")) {
            require_once(getcwd().DS."virtufile"); }
        else if (file_exists(getcwd().DS."build/config/ptvirtualize/Virtufile")) {
            require_once(getcwd().DS."build/config/ptvirtualize/Virtufile"); }
        else if (file_exists(getcwd().DS."build/config/ptvirtualize/virtufile")) {
            require_once(getcwd().DS."build/config/ptvirtualize/virtufile"); }
    }

}
