<?php

Namespace Model ;

class Phlagrantfile extends PhlagrantfileBase {

    public $config ;

    public function __construct() {
        $this->setConfig();
    }

    private function setConfig() {
        $this->setDefaultConfig();
        # $this->config["vm"]["gui_mode"] = "gui" ;
        $this->config["vm"]["box"] = "vanillabuntu" ;
        # Shared folder - This should map to the workstation environment vhost path parent...
        $this->config["vm"]["shared_folders"][] =
            array(
                "name" => "host_www",
                "host_path" => "/var/www/",
            ) ;
        # Provisioning
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharoahTools",
                "tool" => "cleopatra",
                "target" => "guest",
                "script" => getcwd()."/<%tpl.php%>cleofile-guest</%tpl.php%>"
                // build/config/cleopatra/cleofy/autopilots/generic/Phlagrant/cleofy-cm-phlagrant.php
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharoahTools",
                "tool" => "dapperstrano",
                "target" => "guest",
                "script" => getcwd()."/<%tpl.php%>dapperfile-guest</%tpl.php%>"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharoahTools",
                "tool" => "dapperstrano",
                "target" => "host",
                "script" => getcwd()."/<%tpl.php%>dapperfile-host</%tpl.php%>"
            ) ;
        $this->config["vm"]["post_up_message"] = "Your Phlagrant Box has been brought up. This box was configured to be " .
            "provisioned by both Cleopatra and Dapperstrano. Your application should now be accessible.";
    }

}
