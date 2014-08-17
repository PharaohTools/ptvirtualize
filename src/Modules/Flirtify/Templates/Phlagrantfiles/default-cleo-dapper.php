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
        // Provisioning
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharoahTools",
                "tool" => "cleopatra",
                "target" => "guest",
                "script" => getcwd()."build/config/cleopatra/cleofy/autopilots/generic/Phlagrant/cleofy-cm-phlagrant.php"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharoahTools",
                "tool" => "dapperstrano",
                "target" => "guest",
                "script" => getcwd()."<%tpl.php%>dapperfile-guest</%tpl.php%>"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharoahTools",
                "tool" => "dapperstrano",
                "target" => "host",
                "script" => getcwd()."<%tpl.php%>dapperfile-host</%tpl.php%>"
                // "build/config/dapperstrano/dapperfy/autopilots/generated/phlagrant-host-phlagrant-install-hostfile-entry.php"
            ) ;
        $config["vm"]["post_up_message"] = "Your Phlagrant Box has been brought up. This guest was configured to be " .
            "provisioned by both Cleopatra and Dapperstrano, and the host also by Dapperstrano. Your application " .
            "should now be accessible by browser.";
    }

}
