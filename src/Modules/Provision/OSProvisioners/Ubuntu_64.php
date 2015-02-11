<?php

Namespace Model ;

class OSProvisioner extends ProvisionDefaultLinux {

    public $ostype = "Ubuntu 64 or 32 Bit from 10.04 onwards" ;

    public function getCleopatraInitSSHData($provisionFile) {
		$sshData = "" ;
        $sshData .= "echo ".$this->virtualizefile->config["ssh"]["password"]." | sudo -S apt-get update -y\n" ;
        $sshData .= "echo ".$this->virtualizefile->config["ssh"]["password"]." | sudo -S apt-get install -y php5 git\n" ;
        $sshData .= "echo ".$this->virtualizefile->config["ssh"]["password"]." | sudo -S rm -rf cleopatra\n" ;
        $sshData .= "echo ".$this->virtualizefile->config["ssh"]["password"]." | sudo -S git clone https://github.com/PharaohTools/cleopatra.git\n" ;
        $sshData .= "echo ".$this->virtualizefile->config["ssh"]["password"]." | sudo -S php cleopatra/install-silent\n" ;
        return $sshData ;
    }

    public function getMountSharesSSHData($provisionFile) {
        $sshData = "" ;
        $sshData .= "echo {$this->virtualizefile->config["ssh"]["password"]} "
            .'| sudo -S ln -s /opt/VBoxGuestAdditions-4.3.10/lib/VBoxGuestAdditions /usr/lib/VBoxGuestAdditions'."\n" ;
        foreach ($this->virtualizefile->config["vm"]["shared_folders"] as $sharedFolder) {
            $guestPath = (isset($sharedFolder["guest_path"])) ? $sharedFolder["guest_path"] : $sharedFolder["host_path"] ;
            // @todo might be better not to sudo this creation, or allow it more params (owner, perms)
            $sshData .= "echo {$this->virtualizefile->config["ssh"]["password"]} "
                .'| sudo -S mkdir -p '.$guestPath."\n" ;
            $sshData .= "echo {$this->virtualizefile->config["ssh"]["password"]} "
                . '| sudo -S mount -t vboxsf ' . $sharedFolder["name"].' '.$guestPath.' '."\n" ; }
        return $sshData ;
    }

    public function getStandardCleopatraSSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) { $paramString .= " --$paramKey=$paramValue" ;}
        $sshData = <<<"SSHDATA"
echo {$this->virtualizefile->config["ssh"]["password"]} | sudo -S cleopatra auto x --af={$provisionFile}{$paramString}
SSHDATA;
        return $sshData ;
    }

    public function getStandardDapperstranoSSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) { $paramString .= " --$paramKey=$paramValue" ;}
        $sshData = <<<"SSHDATA"
echo {$this->virtualizefile->config["ssh"]["password"]} | sudo -S dapperstrano auto x --af={$provisionFile}{$paramString}
SSHDATA;
        return $sshData ;
    }

    public function getStandardShellSSHData($provisionFile) {
        $sshData = <<<"SSHDATA"
echo {$this->virtualizefile->config["ssh"]["password"]} | sudo -S sh $provisionFile
SSHDATA;
        return $sshData ;
    }

}
