<?php

Namespace Model;

class HaltAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    private $keygenBits;

    public function __construct($params) {
        parent::__construct($params);
        $this->autopilotDefiner = "Halt";
        $this->installCommands = array(
            array("method"=> array("object" => $this, "method" => "askForKeygenBits", "params" => array()) ),
            array("method"=> array("object" => $this, "method" => "createDirectoryStructure", "params" => array()) ),
            array("method"=> array("object" => $this, "method" => "doKeyGen", "params" => array()) ),
        );
        $this->uninstallCommands = array(
            array("method"=> array("object" => $this, "method" => "askForKeygenPath", "params" => array()) ),
            array("method"=> array("object" => $this, "method" => "removeKey", "params" => array()) ),
        );
        $this->programDataFolder = "";
        $this->programNameMachine = "sshkeygen"; // command and app dir name
        $this->programNameFriendly = "sshkeygen!"; // 12 chars
        $this->programNameInstaller = "SSH Key Generation";
        $this->initialize();
    }

    public function askForKeygenBits() {
        if (isset($this->params["ssh-keygen-bits"]) ) {
            $this->keygenBits = $this->params["ssh-keygen-bits"] ; }
        else {
            $question = "Enter number of bits for SSH Key (multiple of 1024):";
            $this->keygenBits = self::askForInput($question, true); }
    }

    public function removeKey() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        if (file_exists($this->params["ssh-keygen-path"])) {
            unlink($this->params["ssh-keygen-path"]) ;
            $logging->log("Removing File at {$this->params["ssh-keygen-path"]} in SSH Keygen") ;
            unlink($this->params["ssh-keygen-path"].".pub") ;
            $logging->log("Removing File at {$this->params["ssh-keygen-path"]}.pub in SSH Keygen") ; }
    }

    public function createDirectoryStructure() {
        if (!file_exists(dirname($this->keygenPath))) {
            mkdir(dirname($this->keygenPath), 0775, true) ; }
    }

    public function doKeyGen() {
        $cmd  = "ssh-keygen -b ".$this->keygenBits.' ' ;
        $cmd .= '-t '.$this->keygenType.' ' ;
        $cmd .= '-f '.$this->keygenPath.' ' ;
        $cmd .= '-q -N "" -C"'.$this->keygenComment.'"' ;
        $this->executeAndOutput($cmd) ;
    }

}