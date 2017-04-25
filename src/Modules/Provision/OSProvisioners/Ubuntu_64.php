<?php

Namespace Model ;

class OSProvisioner extends ProvisionDefaultAllOS {

    public $ostype = "Ubuntu 64 or 32 Bit from 10.04 onwards" ;

    public function getPTConfigureInitSSHData($provisionFile) {
        $check_deps = "( (php -v) && (git --version) && (ptconfigure > /dev/null) )" ;
        $comms  = "( " ;
        $comms .= "apt-get update -y ; " ;
        $comms .= "( apt-get install -y php5 git ||  apt-get install -y php7.0 git ); " ;
        $comms .= " rm -rf /tmp/ptconfigure ; " ;
        $comms .= " git clone https://github.com/PharaohTools/ptconfigure.git /tmp/ptconfigure ; " ;
        $comms .= "php /tmp/ptconfigure/install-silent ; " ;
        $comms .= ") " ;
		$sshData = "" ;
        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S bash -c '{$check_deps} || {$comms}' \n" ;
        return $sshData ;
    }

    public function getMountSharesSSHData($provisionFile) {
        $sshData = "" ;
//        $sshData .= "( (echo {$this->virtufile->config["ssh"]["password"]} | sudo -S modprobe vboxsf) || "
////            ."(echo {$this->virtufile->config["ssh"]["password"]} | (sudo -S apt-get install -y virtualbox-guest-additions-iso) ) )"."\n" ;
//            ."(echo {$this->virtufile->config["ssh"]["password"]} | (sudo -S apt-get install -y virtualbox-guest-x11 virtualbox-guest-additions-iso) ) )"."\n" ;

        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} | " .
//            " sudo -S apt-get install -y virtualbox-guest-x11 virtualbox-guest-additions-iso"."\n" ;
            " sudo -S apt-get install -y virtualbox-guest-dkms virtualbox-guest-additions-iso"."\n" ;

        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} "
            .'| sudo -S ln -sf /opt/VBoxGuestAdditions-*/lib/VBoxGuestAdditions /usr/lib/VBoxGuestAdditions'."\n" ;
        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} | sudo -S modprobe -a vboxguest vboxsf vboxvideo"."\n" ;
        $all = array() ;
        foreach ($this->virtufile->config["vm"]["shared_folders"] as $sharedFolder) {
            $guestPath = (isset($sharedFolder["guest_path"])) ? $sharedFolder["guest_path"] : $sharedFolder["host_path"] ;
            // @todo might be better not to sudo this creation, or allow it more params (owner, perms)
            $one = "echo {$this->virtufile->config["ssh"]["password"]} "
                .'| sudo -S mkdir -p '.$guestPath."\n" ;
            $one .= "echo {$this->virtufile->config["ssh"]["password"]} "
                . '| sudo -S mount -t vboxsf ' . $sharedFolder["name"].' '.$guestPath.' ' ;
            $all[] = $one ; }
        $str = implode("\n", $all) ;
        $sshData .= $str ;
        return $sshData ;
    }

    public function getStandardPTConfigureSSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) { $paramString .= " --$paramKey=$paramValue" ;}
        $sshData =
            'echo '.$this->virtufile->config["ssh"]["password"].' | sudo -S ptconfigure auto x --af='.
            $provisionFile.$paramString ;
        return $sshData ;
    }

    public function getStandardPTDeploySSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) { $paramString .= " --$paramKey=$paramValue" ;}
        $sshData =
            'echo '.$this->virtufile->config["ssh"]["password"].' | sudo -S ptdeploy auto x --af='.
            $provisionFile.$paramString ;
        return $sshData ;
    }

    public function getStandardShellSSHData($provisionFile) {
        $sshData = <<<"SSHDATA"
echo {$this->virtufile->config["ssh"]["password"]} | sudo -S sh $provisionFile
SSHDATA;
        return $sshData ;
    }

}
