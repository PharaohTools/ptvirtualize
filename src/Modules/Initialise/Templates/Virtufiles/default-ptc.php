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
        $this->config["vm"]["box"] = "<%tpl.php%>vm_box</%tpl.php%>" ;
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
                "default" => "PTConfigureInit"
            ) ;
        $this->config["vm"]["provision"][] =
            array(
                "provisioner" => "PharaohTools",
                "tool" => "ptconfigure",
                "target" => "guest",
                "script" => getcwd().DS."build".DS."config".DS."ptconfigure".DS."ptconfigurefy".DS."autopilots".DS."generic".DS."Virtualize".DS."ptconfigurefy-cm-ptvirtualize-box.php"
            ) ;
        $this->config["vm"]["post_up_message"] = "Your Virtualize Box has been brought up. This box is configured to be " .
            "provisioned by PTConfigure's default Virtualize provisioning.";
    }

}
