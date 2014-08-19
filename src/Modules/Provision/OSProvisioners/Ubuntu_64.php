<?php

Namespace Model ;

class OSProvisioner extends ProvisionDefaultLinux {

    public $ostype = "Ubuntu 64 or 32 Bit from 10.04 onwards" ;

    public function getCleopatraInitSSHData($provisionFile) {
        $sshData = <<<"SSHDATA"
echo {$this->phlagrantfile->config["ssh"]["password"]} | sudo -S apt-get update -y
echo {$this->phlagrantfile->config["ssh"]["password"]} | sudo -S apt-get install -y php5 git
git clone http://git.pharoah-tools.org.uk/git/phpengine/cleopatra.git
echo {$this->phlagrantfile->config["ssh"]["password"]} | sudo -S php cleopatra/install-silent
SSHDATA;
        return $sshData ;
    }

    public function getMountSharesSSHData($provisionFile) {
        $sshData = "" ;
        $sshData .= "echo {$this->phlagrantfile->config["ssh"]["password"]} "
            .'| sudo ln -s /opt/VBoxGuestAdditions-4.3.10/lib/VBoxGuestAdditions /usr/lib/VBoxGuestAdditions'."\n" ;
        foreach ($this->phlagrantfile->config["vm"]["shared_folders"] as $sharedFolder) {
            $sshData .= "echo {$this->phlagrantfile->config["ssh"]["password"]} "
                . '| sudo -S mount -t vboxsf ' . $sharedFolder["name"].' '.$sharedFolder["host_path"].' '."\n" ; }
        return $sshData ;
    }

    public function getStandardCleopatraSSHData($provisionFile) {
        $sshData = <<<"SSHDATA"
echo {$this->phlagrantfile->config["ssh"]["password"]} | sudo -S cleopatra auto x --af=$provisionFile
SSHDATA;
        return $sshData ;
    }

    public function getStandardDapperstranoSSHData($provisionFile) {
        $sshData = <<<"SSHDATA"
echo {$this->phlagrantfile->config["ssh"]["password"]} | sudo -S dapperstrano auto x --af=$provisionFile
SSHDATA;
        return $sshData ;
    }

    public function getStandardShellSSHData($provisionFile) {
        $sshData = <<<"SSHDATA"
echo {$this->phlagrantfile->config["ssh"]["password"]} | sudo -S sh $provisionFile
SSHDATA;
        return $sshData ;
    }

}