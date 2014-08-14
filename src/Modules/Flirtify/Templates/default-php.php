<?php

Namespace Model ;

class Phlagrantfile extends PhlagrantfileBase {

    public $config ;

    public function __construct() {
        $this->setConfig();
    }

    private function setConfig() {
        $this->setDefaultConfig();
        // $this->config["vm"]["gui_mode"] = "gui" ;
        $this->config["vm"]["box"] = "vanillabuntu" ;
        // Shared folder - This should map to the workstation environment vhost path parent...
        // $this->config["vm"]["shared_folders"][] = array("1024") ;
        // Networking - maybe use array constants for defaults or something?
        // $this->config["vm"]["networks"][] = array("1024") ;
        // Provisioning

        $this->config["vm"]["provision"] = array() ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharoahTools",
                "tool" => "cleopatra",
                "script" => getcwd()."build/config/cleopatra/cleofy/autopilots/generic/Phlagrant/cleofy-cm-phlagrant.php"
            ) ;
    }

}
