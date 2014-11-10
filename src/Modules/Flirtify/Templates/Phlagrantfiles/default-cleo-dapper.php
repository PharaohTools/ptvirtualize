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
                "name" => "host_web_path",
                "host_path" => getcwd().DS,
            ) ;
        # Provisioning
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "Shell",
                "tool" => "shell",
                "target" => "guest",
                "default" => "MountShares"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "Shell",
                "tool" => "shell",
                "target" => "guest",
                "default" => "CleopatraInit"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "cleopatra",
                "target" => "host",
                "script" => getcwd()."/build/config/cleopatra/cleofy/autopilots/generic/Phlagrant/cleofy-cm-phlagrant-host.php"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "cleopatra",
                "target" => "guest",
                "script" => getcwd()."/build/config/cleopatra/cleofy/autopilots/generic/Phlagrant/cleofy-cm-phlagrant-box.php"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "dapperstrano",
                "target" => "host",
                "script" => getcwd()."/<%tpl.php%>dapperfile-host</%tpl.php%>"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "dapperstrano",
                "target" => "guest",
                "script" => getcwd()."/<%tpl.php%>dapperfile-guest</%tpl.php%>"
            ) ;
        $this->config["vm"]["provision_destroy_post"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "dapperstrano",
                "target" => "host",
                "script" => getcwd()."/<%tpl.php%>dapperfile-host-destroy</%tpl.php%>"
            ) ;
        $this->config["vm"]["post_up_message"] = "Your Phlagrant Box has been brought up. This guest was configured to be " .
            "provisioned by both Cleopatra and Dapperstrano, and the host also by Dapperstrano. Your application " .
            "should now be accessible by browser.";
    }

}
