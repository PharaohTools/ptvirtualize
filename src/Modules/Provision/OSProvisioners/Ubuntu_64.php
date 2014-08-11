<?php

Namespace Model ;

class OSProvisioner extends ProvisionDefaultLinux {

    public $ostype = "Ubuntu 64 or 32 Bit from 10.04 onwards" ;

    protected function setSSHData($provisionFile) {
        $sshData = <<<"SSHDATA"
sudo apt-get update
sudo apt-get install -y php5 git
git clone http://git.pharoah-tools.org.uk/git/phpengine/cleopatra.git
sudo php cleopatra/install-silent
cleopatra auto x --af=$provisionFile
SSHDATA;
        return $sshData ;
    }

}