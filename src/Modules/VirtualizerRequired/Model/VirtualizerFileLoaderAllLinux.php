<?php

Namespace Model;

class VirtualizerFileLoaderAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("VirtualizerFileLoader") ;

    public function load() {
        $this->findFile() ;
        $virtualizer = $this->loadClass() ;
        return $virtualizer;
    }

    public function loadClass() {
        $virtualizerfilename = $this->getPfile() ;
        $cname = (isset($this->params["classname"])) ?
            $this->params["classname"] :
            str_replace(".php", "", $virtualizerfilename) ;
        $cname = '\Model\\'.$cname  ;
        $virtualizer = (class_exists($cname)) ? new $cname($this->params) : null ;
        return $virtualizer;
    }

    private function getPfile() {
        $virtualizerfile = "Virtualizerfile" ;
        $virtualizerfile = (isset($this->params["pfile"])) ? $this->params["pfile"] : $virtualizerfile ;
        $virtualizerfile = (isset($this->params["virtualizerfile"])) ? $this->params["virtualizerfile"] : $virtualizerfile ;
        return $virtualizerfile;
    }

    protected function findFile() {
        $virtualizerfile = $this->getPfile() ;
        if (file_exists(getcwd().DS."$virtualizerfile") && is_file(getcwd().DS."$virtualizerfile")) {
            require_once(getcwd().DS."$virtualizerfile"); }
        else if (file_exists($virtualizerfile)) {
            require_once($virtualizerfile); }
        else if (!is_null($virtualizerfile)) {
            require_once(getcwd().DS."Virtualizerfile"); }
        else if (file_exists(getcwd().DS."Virtualizerfile")) {
            require_once(getcwd().DS."Virtualizerfile"); }
        else if (file_exists(getcwd().DS."virtualizerfile")) {
            require_once(getcwd().DS."virtualizerfile"); }
        else if (file_exists(getcwd().DS."build/config/virtualizer/Virtualizerfile")) {
            require_once(getcwd().DS."build/config/virtualizer/Virtualizerfile"); }
        else if (file_exists(getcwd().DS."build/config/virtualizer/virtualizerfile")) {
            require_once(getcwd().DS."build/config/virtualizer/virtualizerfile"); }
    }

}
