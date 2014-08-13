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
        $this->config["vm"]["ostype"] = "Ubuntu_64" ;
        $this->config["vm"]["name"] = "phlagrant-box" ;
        $this->config["vm"]["box"] = "vanillabuntu" ;
        $this->config["vm"]["memory"] = "1024" ;
        $this->config["ssh"]["user"] = "phlagrant" ;
        $this->config["ssh"]["password"] = "phlagrant" ;
        $this->config["ssh"]["timeout"] = "30" ;
        $this->config["network"]["nic1"] = "nat" ;
        $this->config["network"]["natpf1"] = "ssh,tcp,,3022,,22" ;
        $this->config["network"]["nic2"] = "hostonly" ;
        $this->config["network"]["hostonlyadapter2"] = "vboxnet0" ;
        // Shared folder - This should map to the workstation environment vhost path parent...
        // $this->config["vm"]["shared_folders"][] = array("1024") ;
        // Networking - maybe use array constants for defaults or something?
        // $this->config["vm"]["networks"][] = array("1024") ;
        // Provisioning

        $this->config["vm"]["provision"] = array() ;
        $this->config["vm"]["provision"][] = array( "group" => "PharoahTools", "Provisioner" => "cleopatra", "script" => "/var/www/pharoah-tools/build/config/cleopatra/cleofy/autopilots/generic/Phlagrant/cleofy-cm-phlagrant.php" ) ;
    }

}
