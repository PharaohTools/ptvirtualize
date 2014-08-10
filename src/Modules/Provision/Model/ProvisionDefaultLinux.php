<?php

Namespace Model ;

class ProvisionDefaultLinux extends Base {

    public $sftp ;
    public $ssh ;

    public function __construct() {
        $this->setSftp();
        $this->setSsh();
    }

    private function setSftp() {
        $this->sftp = array() ;
        $this->sftp["source"] = "Ubuntu_64" ;
        $this->sftp["target"] = "Ubuntu_64" ;
    }

    private function setSsh() {
        $this->sftp = array() ;
        $this->sftp["source"] = "Ubuntu_64" ;
        $this->sftp["target"] = "Ubuntu_64" ;
    }

    public function provision($phlagrantfile, $papyrus) {
        $provisionFile = $phlagrantfile->config["vm"]["default_tmp_dir"]."provision.php" ;
        $sftpParams = $this->params ;
        $sftpParams["yes"] = true ;
        $sftpParams["guess"] = true ;
        $sftpParams["source"] = getcwd()."/build/config/cleopatra/cleofy/autopilots/generic/Phlagrant/cleofy-cm-phlagrant.php" ;
        $sftpParams["target"] = $provisionFile ;
        $sftpFactory = new \Model\SFTP();
        $sftp = $sftpFactory->getModel($sftpParams) ;
        $sftp->performSFTPPut();

        $sshParams = $this->params ;
        $sshParams["ssh-data"] = $this->setSSHData($provisionFile) ;
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performInvokeSSHData() ;
    }

}
