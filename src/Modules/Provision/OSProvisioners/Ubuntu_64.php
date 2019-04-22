<?php

Namespace Model ;

class OSProvisioner extends ProvisionDefaultAllOS {

    public $ostype = "Ubuntu 64 or 32 Bit from 10.04 onwards" ;

    public $updated = null ;

    public function getPTConfigureInitSSHData($provisionFile, $provisionerSettings) {

        $comms  = "" ;
        if ($this->updated !== true) {
            $comms .= "apt-get -qq update -y ; sleep 3  ; " ;
            $this->updated = true ;
        }

        $comms .= "( apt-get -qq install -y php5 php5-curl || true ) && " ;
        $comms .= "( apt-get -qq install -y php7.* php7.*-curl php7.*-xml || true ) && " ;
        $comms .= "( apt-get -qq install -y php php-curl php-xml || true ) ; " ;

        $comms .= " apt-get -qq install -y git ; " ;
        $comms .= " rm -rf /tmp/ptconfigure ; " ;
        $comms .= " git clone https://github.com/PharaohTools/ptconfigure.git /tmp/ptconfigure ; " ;
        $comms .= " php /tmp/ptconfigure/install-silent ; " ;
        $comms .= "" ;


		$sshData = "" ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
//        $is_force = isset($provisionerSettings['force']) && ($provisionerSettings['force']==true || $provisionerSettings['force']==true) ;
//        if ($is_force == false) {
//            $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S bash -c '{$check_deps} || {$comms}' \n" ;
//        } else {
            $logging->log("Force install for Pharaoh Configure", $this->getModuleName());
            $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S bash -c '{$comms}' \n" ;
//        }
        return $sshData ;
    }

    public function getGuestAdditionsSSHData($provisionFile) {
        $sshData = "" ;

        if ($this->updated !== true) {
            $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} | sudo -S apt-get -qq update "."\n" ;
            $this->updated = true ;
        }
        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} | " .
            " sudo -S apt-get -qq install -y virtualbox-guest-dkms virtualbox-guest-additions-iso"."\n" ;

        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} "
            .'| sudo -S ln -sf /opt/VBoxGuestAdditions-*/lib/VBoxGuestAdditions /usr/lib/VBoxGuestAdditions'."\n" ;

        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} | sudo -S mkdir -p /mnt/guestadditionsiso/ "."\n" ;
        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} | sudo -S mount -o loop /usr/share/virtualbox/VBoxGuestAdditions.iso /mnt/guestadditionsiso/"."\n" ;
        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} | sudo -S /mnt/guestadditionsiso/VBoxLinuxAdditions.run"."\n" ;
        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} | sudo -S umount /mnt/guestadditionsiso/ "."\n" ;
        return $sshData ;
    }

    public function getMountSharesSSHData($provisionFile) {
        $sshData = "" ;

        if ($this->updated !== true) {
            $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} | sudo -S apt-get -qq update "."\n" ;
            $this->updated = true ;
        }
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
