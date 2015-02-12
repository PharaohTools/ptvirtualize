<?php

Namespace Model ;

class Virtufile extends VirtufileBase {

    public $config ;

    public function __construct() {
        $this->setConfig();
    }

    private function setConfig() {
        $this->setDefaultConfig();
        # $this->config["vm"]["gui_mode"] = "gui" ;
        $this->config["vm"]["box"] = "pharaohubuntu14041amd64" ;
        # Shared folder - This should map to the workstation environment vhost path parent...
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
                "script" => getcwd().DS."build".DS."config".DS."cleopatra".DS."cleofy".DS."autopilots".DS."generic".DS."Virtualize".DS."cleofy-cm-virtualize-host.php"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "cleopatra",
                "target" => "guest",
                "script" => getcwd().DS."build".DS."config".DS."cleopatra".DS."cleofy".DS."autopilots".DS."generic".DS."Virtualize".DS."cleofy-cm-virtualize-box.php"
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
        $this->config["vm"]["post_up_message"] = "Your Virtualize Box has been brought up. This guest was configured to be " .
            "provisioned by both Cleopatra and Dapperstrano, and the host also by Dapperstrano. Your application " .
            "should now be accessible by browser.";
    }

}
