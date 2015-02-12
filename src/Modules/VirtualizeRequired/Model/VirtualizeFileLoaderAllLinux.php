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
        $virtualize = $this->loadClass() ;
        return $virtualize;
    }

    public function loadClass() {
        $virtufilename = $this->getPfile() ;
        $cname = (isset($this->params["classname"])) ?
            $this->params["classname"] :
            str_replace(".php", "", $virtufilename) ;
        $cname = '\Model\\'.$cname  ;
        $virtualize = (class_exists($cname)) ? new $cname($this->params) : null ;
        return $virtualize;
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
        else if (file_exists(getcwd().DS."build/config/virtualize/Virtufile")) {
            require_once(getcwd().DS."build/config/virtualize/Virtufile"); }
        else if (file_exists(getcwd().DS."build/config/virtualize/virtufile")) {
            require_once(getcwd().DS."build/config/virtualize/virtufile"); }
    }

}
