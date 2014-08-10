<?php

Namespace Model;

class HaltPhlagrantFileLoaderAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("PhlagrantFileLoader") ;

    public function load() {
        if (file_exists(getcwd()."/Phlagrantfile")) {
            require_once(getcwd()."/Phlagrantfile"); }
        else if (file_exists(getcwd()."/phlagrantfile")) {
            include_once(getcwd()."/phlagrantfile"); }
        else if (file_exists(getcwd()."/build/config/phlagrant/phlagrantfile")) {
            include_once(getcwd()."/build/config/phlagrant/phlagrantfile"); }
        else if (file_exists(getcwd()."/build/config/phlagrant/phlagrantfile")) {
            include_once(getcwd()."/build/config/phlagrant/phlagrantfile"); }
        $phlagrant = (class_exists('\Model\Phlagrantfile')) ?
            new \Model\Phlagrantfile($this->params) : null ;
        return $phlagrant;
    }

}