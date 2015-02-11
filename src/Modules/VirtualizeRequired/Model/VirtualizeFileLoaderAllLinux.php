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
        $virtualizefilename = $this->getPfile() ;
        $cname = (isset($this->params["classname"])) ?
            $this->params["classname"] :
            str_replace(".php", "", $virtualizefilename) ;
        $cname = '\Model\\'.$cname  ;
        $virtualize = (class_exists($cname)) ? new $cname($this->params) : null ;
        return $virtualize;
    }

    private function getPfile() {
        $virtualizefile = "Virtualizefile" ;
        $virtualizefile = (isset($this->params["pfile"])) ? $this->params["pfile"] : $virtualizefile ;
        $virtualizefile = (isset($this->params["virtualizefile"])) ? $this->params["virtualizefile"] : $virtualizefile ;
        return $virtualizefile;
    }

    protected function findFile() {
        $virtualizefile = $this->getPfile() ;
        if (file_exists(getcwd().DS."$virtualizefile") && is_file(getcwd().DS."$virtualizefile")) {
            require_once(getcwd().DS."$virtualizefile"); }
        else if (file_exists($virtualizefile)) {
            require_once($virtualizefile); }
        else if (!is_null($virtualizefile)) {
            require_once(getcwd().DS."Virtualizefile"); }
        else if (file_exists(getcwd().DS."Virtualizefile")) {
            require_once(getcwd().DS."Virtualizefile"); }
        else if (file_exists(getcwd().DS."virtualizefile")) {
            require_once(getcwd().DS."virtualizefile"); }
        else if (file_exists(getcwd().DS."build/config/virtualize/Virtualizefile")) {
            require_once(getcwd().DS."build/config/virtualize/Virtualizefile"); }
        else if (file_exists(getcwd().DS."build/config/virtualize/virtualizefile")) {
            require_once(getcwd().DS."build/config/virtualize/virtualizefile"); }
    }

}
