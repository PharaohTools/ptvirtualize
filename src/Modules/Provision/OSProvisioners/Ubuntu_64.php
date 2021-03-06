<?php

Namespace Model ;

class OSProvisioner extends ProvisionDefaultAllOS {

    public $ostype = "Ubuntu 64 or 32 Bit from 10.04 onwards" ;

    public $updated = null ;

    public function getPTConfigureInitSSHData($provisionFile, $provisionerSettings) {

        $comms = "" ;
        if ($this->updated !== true) {
            $comms .= "apt-get -qq update -y ; sleep 3  ; " ;
            $this->updated = true ;
        }

        $comms .= "( apt-get -qq install -y php5 php5-curl || true ) && " ;
        $comms .= "( (apt-get -qq install -y php7.* php7.*-curl php7.*-xml || true) && (apt-get -qq remove -y php7.*-snmp || true) ) && " ;
        $comms .= "( apt-get -qq install -y php php-curl php-xml || true ) ; " ;
        $comms .= " apt-get -qq install -y git ; " ;
        $comms .= " rm -rf /tmp/ptconfigure ; " ;
        $comms .= " git clone https://github.com/PharaohTools/ptconfigure.git /tmp/ptconfigure ; " ;
        $comms .= " php /tmp/ptconfigure/install-silent ; " ;
        $comms .= "" ;

        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);

		$sshData = "" ;
        $is_force = ( isset($provisionerSettings['force']) && $provisionerSettings['force']==true ) ;
        if ($is_force == false) {
            $sshData .= 'ptconfigure > /dev/null ; ' ;
            $sshData .= 'ptc_exit_status=$? ; ' ;
            $sshData .= 'if [ "$ptc_exit_status" = "0" ] ; then ' ;
            $sshData .= "  echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S bash -c '{$comms}' ; " ;
            $sshData .= 'fi' ;
        } else {
            $logging->log("Force install for Pharaoh Configure", $this->getModuleName());
            $sshData .= "echo ".$this->virtufile->config["ssh"]["password"]." | sudo -S bash -c '{$comms}' \n" ;
        }
//        var_dump('ssh data return', $sshData) ;
        return $sshData ;
    }

    public function getGuestAdditionsSSHData($provisionFile) {
        $sshData = "" ;

        $cstart = "echo {$this->virtufile->config["ssh"]["password"]} | sudo -S " ;

        if ($this->updated !== true) {
            $sshData .= "apt-get -qq update "."\n" ;
            $this->updated = true ;
        }
        $sshData .= "apt-get -qq install -y expect virtualbox-guest-x11 " ;
        $sshData .= "virtualbox-guest-dkms virtualbox-guest-additions-iso > /dev/null "."\n" ;
        $sshData .= "ln -sf /opt/VBoxGuestAdditions-*/lib/VBoxGuestAdditions /usr/lib/VBoxGuestAdditions\n" ;
        $sshData .= "mkdir -p /mnt/guestadditionsiso/ "."\n" ;
        $sshData .= "if grep -qs '/mnt/guestadditionsiso' /proc/mounts; then echo \"Guest Additions ISO is mounted\"; else mount -o loop /usr/share/virtualbox/VBoxGuestAdditions.iso /mnt/guestadditionsiso ; fi ; "."\n" ;
        $sshData .= "usermod -a -G vboxsf root"."\n" ;
        $sshData .= "usermod -a -G vboxsf {$this->virtufile->config["ssh"]["user"]}"."\n" ;
//        $sshData .= "rm -rf /tmp/ga-expect.expect"."\n" ;
//        $sshData .= "echo -e '#!/usr/bin/expect\\nset timeout 180\\nspawn /mnt/guestadditionsiso/VBoxLinuxAdditions.run\\nexpect \"yes or no\"\\nsend \"yes\\r\" \\ninteract\\n' > /tmp/ga-expect.expect ;\n" ;
        $sshData .= "/mnt/guestadditionsiso/VBoxLinuxAdditions.run || true"."\n" ;
//        $sshData .= "chmod +x /tmp/ga-expect.expect"."\n" ;
//        $sshData .= "/tmp/ga-expect.expect"."\n" ;
        $sshData .= "umount /mnt/guestadditionsiso/ "."\n" ;
//        $sshData .= "rm -rf /tmp/ga-expect.expect"."\n" ;
        return $sshData ;
    }

    public function getMountSharesSSHData($provisionFile) {
        $sshData = "" ;

        $all = array() ;
        foreach ($this->virtufile->config["vm"]["shared_folders"] as $sharedFolder) {
            $guestPath = (isset($sharedFolder["guest_path"])) ? $sharedFolder["guest_path"] : $sharedFolder["host_path"] ;
            // @todo might be better not to sudo this creation, or allow it more params (owner, perms)
            $one = 'mkdir -p '.$guestPath."\n" ;
            $one .= 'mount -t vboxsf ' . $sharedFolder["name"].' '.$guestPath.' '."\n" ;

            $types = ['user', 'group'] ;
            foreach ($types as $type) {
                if (isset($sharedFolder[$type])){
                    if ($type === 'user') {
                        $one .= ' -o uid="'.$sharedFolder[$type].'"' ;
                    } else {
                        $one .= ' -o gid="'.$sharedFolder[$type].'"' ;
                    }
                }
            }
            $all[] = $one ; }
        $str = implode("\n", $all) ;
        $sshData .= $str ;
        return $sshData ;
    }

    public function getStandardPTConfigureSSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) {
            if (is_array($paramValue)) {
                $paramString .= " --$paramKey=\"".implode(',', $paramValue)."\"";
            } else {
                $paramString .= " --$paramKey=$paramValue" ;
            }}
        $sshData =
            'echo '.$this->virtufile->config["ssh"]["password"].' | sudo -S ptconfigure auto x --af='.
            $provisionFile.$paramString ;
        return $sshData ;
    }

    public function getStandardPTDeploySSHData($provisionFile, $params = array() ) {
        $paramString = "" ;
        foreach ($params as $paramKey => $paramValue) {
            if (is_array($paramValue)) {
                $paramString .= " --$paramKey=\"".implode(',', $paramValue)."\"";
            } else {
                $paramString .= " --$paramKey=$paramValue" ;
            }}
        $sshData =
            'echo '.$this->virtufile->config["ssh"]["password"].' | sudo -S ptdeploy auto x --af='.
            $provisionFile.$paramString ;
        return $sshData ;
    }

    public function getStandardShellSSHData($provisionFile, $provisionerSettings) {
        if (isset($provisionerSettings['data'])) {
            $ssh_data_string = $provisionerSettings['data'] ;
        } else {
            $ssh_data_string = $provisionFile ;
        }
        $sshData = <<<"SSHDATA"
$ssh_data_string ;
SSHDATA;
        return $sshData ;
    }

}
