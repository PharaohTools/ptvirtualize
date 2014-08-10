<?php

Namespace Model;

class ProvisionAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    protected $phlagrantfile;
    protected $papyrus ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function provisionNow() {
        $this->loadFiles();
        /*
         *
         * sftp module,sftp into the box
         * source=dirname(dirname(__FILE__))."/Autopilots/Cleopatra/provision.php" # cleopatra cleofy workstation/phlagrant default
         * target=$this->phlagrantfile->config["vm"]["default_tmp_dir"]."provision.php"
         *
         * ssh into the box
         * ensure php5 and git are installed
         * install cleopatra
         * cleopatra auto x --af=$this->phlagrantfile->config["vm"]["default_tmp_dir"]."provision.php"
         *
         */
        $command = "VBoxManage unregistervm {$this->phlagrantfile->config["vm"]["name"]} --delete" ;
        echo $command ;
        $this->executeAndOutput($command);
    }

    protected function loadFiles() {
        $this->phlagrantfile = $this->loadPhlagrantFile();
        $this->papyrus = $this->loadPapyrusLocal();
    }

    protected function loadPhlagrantFile() {
        $prFactory = new \Model\PhlagrantRequired();
        $phlagrantFileLoader = $prFactory->getModel($this->params, "PhlagrantFileLoader") ;
        return $phlagrantFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\PhlagrantRequired();
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load() ;
    }

}