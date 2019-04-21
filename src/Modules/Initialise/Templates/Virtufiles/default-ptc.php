<?php

<%code.tpl.php%>
    echo '//'."\n" ;
    echo '//'." Created by Pharaoh Virtualize at ".date("H:i:s d/m/Y", time())."\n" ;
    echo '//'."\n" ;
</%code.tpl.php%>

Namespace Model ;

class Virtufile extends VirtufileBase {

    public $config ;

    public function __construct() {
        $this->setConfig();
    }

    private function setConfig() {
        $this->setDefaultConfig();
        $this->config["vm"]["name"] = "<%var.tpl.php%>name</%var.tpl.php%>" ;
        $this->config["vm"]["gui_mode"] = "<%var.tpl.php%>gui_mode</%var.tpl.php%>" ;
<%code.tpl.php%>
        if (isset($box) && !isset($box_url)) {
            echo '        $this->config["vm"]["box"] = "<%var.tpl.php%>box</%var.tpl.php%>" ;'."\n" ;
        } else {
            echo '        $this->config["vm"]["box"] = "<%var.tpl.php%>box</%var.tpl.php%>" ;'."\n" ;
            echo '        $this->config["vm"]["box_url"] = "<%var.tpl.php%>box_url</%var.tpl.php%>" ;'."\n" ;
        }</%code.tpl.php%>

        # Shared folder - This should map to the workstation environment vhost path parent...
        $this->config["vm"]["shared_folders"][] =
            array(
                "name" => "host_www",
                "host_path" => getcwd().DS,
                "guest_path" => "/var/www/hostshare/",
                'symlinks' => 'enable'
            ) ;

        # /usr/share/virtualbox/VBoxGuestAdditions.iso

        # Provisioning
//        $this->config["vm"]["provision"][] =
//            array(
//                "provisioner" => "Shell",
//                "tool" => "shell",
//                "target" => "guest",
//                "default" => "GuestAdditions"
//            ) ;
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
        $this->config["vm"]["post_up_message"] = "Your Virtualize Box has been brought up. This box is configured to be " .
            "provisioned by PTConfigure's default Virtualize provisioning.";
    }

}
