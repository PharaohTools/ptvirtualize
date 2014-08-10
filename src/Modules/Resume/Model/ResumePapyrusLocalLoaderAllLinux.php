<?php

Namespace Model;

class ResumePapyrusLocalLoaderAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("PapyrusLocalLoader") ;

    public function load() {
        $pf = \AppConfig::getProjectVariable("phlagrant-box", true) ;
        if (is_array($pf) && count($pf)>0) {
            return $pf ; }
        return $pf ;
    }

}