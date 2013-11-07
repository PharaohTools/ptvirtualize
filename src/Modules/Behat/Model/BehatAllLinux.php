<?php

Namespace Model;

class BehatAllLinux extends BaseTestInit {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian", "Redhat") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Initializer") ;

  public function __construct($params) {
    parent::__construct($params);
    $this->autopilotDefiner = "Behat";
    $this->fileSources = array(
      array(
        "https://github.com/phpengine/cleopatra.git",
        "cleopatra",
        null // can be null for none
      )
    );
    $this->programNameMachine = "cleopatra"; // command and app dir name
    $this->programNameFriendly = " Behat! "; // 12 chars
    $this->programNameInstaller = "Behat - Update to latest version";
    $this->programExecutorTargetPath = 'cleopatra/src/Bootstrap.php';
    $this->initialize();
  }

}