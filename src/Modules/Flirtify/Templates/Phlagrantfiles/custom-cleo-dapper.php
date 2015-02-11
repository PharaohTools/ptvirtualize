<?php

Namespace Model ;

class Virtualizefile extends VirtualizefileBase {

    public $config ;

    public function __construct() {
        $this->setConfig();
    }

    private function setConfig() {
        $this->setDefaultConfig();
        # $this->config["vm"]["gui_mode"] = "gui" ;
        $this->config["vm"]["box"] = "pharaohubuntu14041amd64" ;
        # Shared folders @todo this is not ready for any OS as it requires
        $this->config["vm"]["shared_folders"][] =
            array(
                "name" => "host_www",
                "host_path" => getcwd().DS,
                "guest_path" => "/var/www/hostshare/",
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
                "script" => getcwd().DS."<%tpl.php%>cleofile-host</%tpl.php%>"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "cleopatra",
                "target" => "guest",
                "script" => getcwd().DS."<%tpl.php%>cleofile-guest</%tpl.php%>"
                // build/config/cleopatra/cleofy/autopilots/generic/Virtualize/cleofy-cm-virtualize.php
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "dapperstrano",
                "target" => "host",
                "script" => getcwd().DS."<%tpl.php%>dapperfile-host</%tpl.php%>"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "dapperstrano",
                "target" => "guest",
                "script" => getcwd().DS."<%tpl.php%>dapperfile-guest</%tpl.php%>"
            ) ;
        $this->config["vm"]["provision_destroy_post"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "dapperstrano",
                "target" => "host",
                "script" => getcwd().DS."<%tpl.php%>dapperfile-host-destroy</%tpl.php%>"
            ) ;
        $this->config["vm"]["post_up_message"] = "Your Virtualize Box has been brought up. This box was configured to be " .
            "provisioned by both Cleopatra and Dapperstrano. Your application should now be accessible.";
    }

}
