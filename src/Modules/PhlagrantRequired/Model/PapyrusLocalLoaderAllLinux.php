<?php

Namespace Model;

class PapyrusLocalLoaderAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("PapyrusLocalLoader") ;

    public function load($pfile = null) {
        $boxname = (!is_null($pfile)) ? $pfile->config["vm"]["name"] : "phlagrant-box" ;
        $pf = \Model\AppConfig::getProjectVariable($boxname, true) ;
        if (is_array($pf) && count($pf)>0) {
            return $pf ; }
        return array() ;
    }

}