<?php

Namespace Model ;

class Phlagrantfile extends PhlagrantfileBase {

    public $config ;

    public function __construct() {
        $this->setConfig();
    }

    private function setConfig() {
        $this->setDefaultConfig();
        $this->config["vm"]["ostype"] = "Ubuntu_64" ;
        $this->config["vm"]["name"] = "phlagrant-box" ;
        $this->config["vm"]["box"] = "VanillaUbuntu_14.04" ;
        $this->config["vm"]["memory"] = "1024" ;
        $this->config["ssh"]["username"] = "phlagrant" ;
        $this->config["ssh"]["password"] = "phlagrant" ;
        // Shared folder - This should map to the workstation environment vhost path parent...
        // $this->config["vm"]["shared_folders"][] = array("1024") ;
        // Networking - maybe use array constants for defaults or something?
        // $this->config["vm"]["networks"][] = array("1024") ;
        // Provisioning
        $this->config["vm"]["provision"][] = array(
            "group" => "PharoahTools",
            "Provisioner" => "cleopatra",
            "script" => "/var/www/pharoah-tools/build/config/phlagrant/" ) ;
    }

}
