<?php

Namespace Model ;

class OSProvisioner extends ProvisionDefaultAllOS {

    public $ostype = "Windows" ;

    public function getPTConfigureInitSSHData($provisionFile) {
		$sshData = "" ;
//        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S apt-get update -y\n" ;
//        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S apt-get install -y php5 git\n" ;
//        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S rm -rf ptconfigure\n" ;
//        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S git clone https://github.com/PharaohTools/ptconfigure.git\n" ;
//        $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S php ptconfigure/install-silent" ;
        return $sshData ;
    }

    public function getMountSharesSSHData($provisionFile) {
        $sshData = "" ;
//        $sshData .= "echo {$this->virtufile->config["ssh"]["password"]} "
//            .'| sudo -S ln -sf /opt/VBoxGuestAdditions-*/lib/VBoxGuestAdditions /usr/lib/VBoxGuestAdditions'."\n" ;
        $all = array() ;
        foreach ($this->virtufile->config["vm"]["shared_folders"] as $sharedFolder) {
            $guestPath = (isset($sharedFolder["guest_path"])) ? $sharedFolder["guest_path"] : $sharedFolder["host_path"] ;
            $one = "mkdir -p ".$guestPath."\n" ;
            $one .= 'net use *: \\\\vboxsvr\\'.$sharedFolder["name"].' '.$guestPath.' ' ;
            $all[] = $one ; }
        $str = implode(PHP_EOL, $all) ;
        $sshData .= $str ;
        return $sshData ;
    }

    public function getStandardPTConfigureSSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) { $paramString .= " --$paramKey=$paramValue" ;}
        $sshData =
            'echo '.$this->virtufile->config["ssh"]["password"].' | sudo -S ptconfigure auto x --af='.
            $provisionFile.$paramString ;
        $sshData = "" ;
        return $sshData ;
    }

    public function getStandardPTDeploySSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) { $paramString .= " --$paramKey=$paramValue" ;}
        $sshData =
            'echo '.$this->virtufile->config["ssh"]["password"].' | sudo -S ptdeploy auto x --af='.
            $provisionFile.$paramString ;
        $sshData = "" ;
        return $sshData ;
    }

    public function getStandardShellSSHData($provisionFile) {
        $sshData = "" ;
//        $sshData = <<<"SSHDATA"
//echo {$this->virtufile->config["ssh"]["password"]} | sudo -S sh $provisionFile
//SSHDATA;
        return $sshData ;
    }

    private function get_drives() {
        $d='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $drives='';
        for($i=0;$i<strlen($d);$i++)
            if(is_dir($d[$i].':\\'))
                $drives.=$d[$i];
        echo $drives;
    }


}
