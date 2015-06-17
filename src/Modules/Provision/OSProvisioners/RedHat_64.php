<?php

Namespace Model ;

class OSProvisioner extends ProvisionDefaultAllOS {

    public $ostype = "Centos / Red Hat Linux, 64 or 32 Bit from 6 onwards" ;

    public function getPTConfigureInitSSHData($provisionFile) {
		$sshData = "" ;
        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S yum update -y\n" ;
        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S yum install git -y\n" ;
        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S yum install php -y\n" ;
        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S rm -rf ptconfigure\n" ;
        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S git clone https://github.com/PharaohTools/ptconfigure.git\n" ;
        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S php ptconfigure/install-silent\n" ;
        return $sshData ;
    }

    public function getMountSharesSSHData($provisionFile) {
        $sshData = "" ;
        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} "
            .'| sudo -S ln -sf /opt/VBoxGuestAdditions-*/lib/VBoxGuestAdditions /usr/lib/VBoxGuestAdditions'."\n" ;
        foreach ($this->virtufile->config["vm"]["shared_folders"] as $sharedFolder) {
            $guestPath = (isset($sharedFolder["guest_path"])) ? $sharedFolder["guest_path"] : $sharedFolder["host_path"] ;
            // @todo might be better not to sudo this creation, or allow it more params (owner, perms)
            $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} "
                .'| sudo -S mkdir -p '.$guestPath."\n" ;
            $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} "
                . '| sudo -S mount -t vboxsf ' . $sharedFolder["name"].' '.$guestPath.' '."\n" ; }
        return $sshData ;
    }

    public function getStandardPTConfigureSSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) { $paramString .= " --$paramKey=$paramValue" ;}
        $sshData =
            'echo '.$this->virtufile->config["ssh"]["password"].' | sudo -S ptconfigure auto x --af='.
            $provisionFile.$paramString."\n" ;
        return $sshData ;
    }

    public function getStandardPTDeploySSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) { $paramString .= " --$paramKey=$paramValue" ;}
        $sshData =
            'echo '.$this->virtufile->config["ssh"]["password"].' | sudo -S ptdeploy auto x --af='.
            $provisionFile.$paramString."\n" ;
        return $sshData ;
    }

    public function getStandardShellSSHData($provisionFile) {
        $sshData = <<<"SSHDATA"
echo {$this->virtufile->config["ssh"]["password"]} | sudo -S sh $provisionFile
SSHDATA;
        return $sshData ;
    }

}
