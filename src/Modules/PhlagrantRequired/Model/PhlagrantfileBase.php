<?php

Namespace Model ;

class PhlagrantfileBase extends BaseLinuxApp {

    public $config ;

    protected function setDefaultConfig($defaultConfigType = null) {
        // @todo I need to create an array, or includes or something of $defaultConfigType, to set different defaults
        $config = array() ;
        # Default System Settings
        $config["vm"]["name"] = "phlagrant-box" ;
        $config["vm"]["provider"] = "virtualbox" ; # @todo I'm not sure if we're actually using this
        $config["vm"]["ostype"] = "Ubuntu_64" ;
        $config["vm"]["gui_mode"] = "headless" ;
        $config["vm"]["ip_find_timeout"] = 180 ; # The time in seconds Phlagrant will allow for Guest Additions to find IP's
        $config["vm"]["ssh_find_timeout"] = 300 ; # The time in seconds that Phlagrant will wait for the machine SSH port to accept connections.
        $config["vm"]["box"] = "vanillabuntu" ; # This configures what box the machine will be brought up against. The value here should be the name of an installed box or @todo  a shorthand name of a box in Vagrant Cloud.
        // @todo config.vm.box_url - If $config ["vm"]["box"] is an installed box you can ignore this. Otherwise point to a url it can be downloaded from
        $config["vm"]["cpus"] = 1 ;
        $config["vm"]["memory"] = 2048 ;
        $config["vm"]["vram"] = 32 ;
        $config["vm"]["default_tmp_dir"] = DS.'tmp'.DS ;
        $config["vm"]["graceful_halt_timeout"] = 30 ; # The time in seconds that Phlagrant wait for the machine to gracefully halt by soft power under phlagrant halt now. Defaults to 120 seconds.
        $config["vm"]["ssh_halt_timeout"] = 60 ; # The time in seconds that Phlagrant will wait for the machine to shutdown via SSH when phlagrant halt now is called, if still running after soft power down. Defaults to 60 seconds.
        $config["vm"]["post_up_message"] = "Your default Phlagrant post_up_message..." ; # A message to show after Phlagrant Up/Reload.
        # Default Provisioning
        $this->config["vm"]["provision"] = array() ;
        # Default Shared Folders
        $this->config["vm"]["shared_folders"] = array() ;
        # Default SSH Settings
        $config["ssh"]["user"] = "phlagrant" ;
        $config["ssh"]["password"] = "phlagrant" ;
        $config["ssh"]["timeout"] = "30" ;
        # Default Network Settings
        $config["network"]["nic1"] = "nat" ;
        $config["network"]["nic2"] = "hostonly" ;
        $config["network"]["hostonlyadapter2"] = $this->getDefaultHostNetworkName() ;

        // @todo waiting config vars
        //$config["vm"]["box_check_update"] - If true, Vagrant will check for updates to the configured box on every vagrant up. If an update is found, Vagrant will tell the user. By default this is true. Updates will only be checked for boxes that properly support updates (boxes from Vagrant Cloud or some other versioned box).
        //$config["vm"]["box_download_checksum"] - The checksum of the box specified by $config["vm"]["box_url. If not specified, no checksum comparison will be done. If specified, Vagrant will compare the checksum of the downloaded box to this value and error if they do not match. Checksum checking is only done when Vagrant must download the box.
        //If this is specified, then $config["vm"]["box_download_checksum_type"] must also be specified.
        //$config["vm"]["box_download_checksum_type"] - The type of checksum specified by $config["vm"]["box_download_checksum (if any). Supported values are currently "md5", "sha1", and "sha256".
        //$config["vm"]["box_download_client_cert"] - Path to a client certificate to use when downloading the box, if it is necessary. By default, no client certificate is used to download the box.
        //$config["vm"]["box_download_insecure"] - If true, then SSL certificates from the server will not be verified. By default, if the URL is an HTTPS URL, then SSL certs will be verified.
        //$config["vm"]["box_version"] - The version of the box to use. This defaults to ">= 0" (the latest version available). This can contain an arbitrary list of constraints, separated by commas, such as: >= 1.0, < 1.5. When constraints are given, Vagrant will use the latest available box satisfying these constraints.
        // @todo surely by using vboxmanage this is not an issue?
        //$config["vm"]["usable_port_range"] - A range of ports Vagrant can use for handling port collisions and such. Defaults to 2200..2250.

        $this->config = $config ;

    }

    protected function getDefaultHostNetworkName() {

        $command = VBOXMGCOMM." list hostonlyifs " ;

        $out = $this->executeAndLoad($command);
        $outLines = explode("\n", $out);
        $outStr = "" ;
        foreach ($outLines as $outLine) {
            if (strpos($outLine, "Name:") !== false) {
                $outStr .= $outLine."\n" ;
                break; } }
        var_dump($outStr);
        $lpos = strrpos($outStr, "  ") + 2 ;
        $name = substr($outStr, $lpos, strlen($outStr)-1) ;
        $name = str_replace("\n", "", $name) ;
        $name = str_replace("\r", "", $name) ;
        var_dump($name);
        return $name ;
    }

}
