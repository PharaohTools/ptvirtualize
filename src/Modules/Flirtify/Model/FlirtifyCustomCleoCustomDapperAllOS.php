<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class FlirtifyCustomCleoCustomDapperAllOS extends Base {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian") ;
    public $distros = array("Ubuntu") ;
    public $versions = array("12.04", "12.10", "13.04", "13.10", "14.04") ;
    public $architectures = array("32", "64") ;

    // Model Group
    public $modelGroup = array("CustomCleoCustomDapper") ;

    private $environments ;
    private $environmentReplacements ;

    public function __construct($params) {
      parent::__construct($params);
    }

    public function askWhetherToFlirtify() {
        if ($this->askToScreenWhetherToFlirtify() != true) { return false; }
        $this->doFlirtify() ;
        return true;
    }

    public function askToScreenWhetherToFlirtify() {
        if (isset($this->params["yes"]) && $this->params["yes"]==true) { return true ; }
        $question = 'Flirtify This?';
        return self::askYesOrNo($question, true);
    }

    protected function doFlirtify() {
        $templatesDir = str_replace("Model", "Templates/Phlagrantfiles", dirname(__FILE__) ) ;
        $template = $templatesDir . "/custom-cleo-dapper.php";
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $targetLocation = "Phlagrantfile" ;
        $templator->template(
            file_get_contents($template),
            array(
                "cleofile-guest" => $this->getCleofile("guest"),
                "dapperfile-guest" => $this->getDapperfile("guest"),
                "dapperfile-host" => $this->getDapperfile("host"),
                "dapperfile-host-destroy" => $this->getDapperfile("host", "destroy"),
            ),
            $targetLocation );
        echo $targetLocation."\n";
    }

    protected function getCleofile($envType) {
        $envType = strtolower($envType) ;
        if (isset($this->params["$envType-cleofile"])) {
            return $this->params["$envType-cleofile"] ; }
        if (isset($this->params["$envType-cleopatra-autopilot"])) {
            return $this->params["$envType-cleopatra-autopilot"] ; }
        $question = "Enter path to your ".ucfirst($envType)." Cleopatra Deployment File" ;
        $this->params["$envType-nodes-environment"] = $this->askForInput($question) ;
        return $this->params["$envType-nodes-environment"] ;
    }

    protected function getDapperfile($envType, $provisionType = "up") {
        $envType = strtolower($envType) ;
        if ($provisionType == "up") {
            if (isset($this->params["$envType-dapperfile"])) {
                return $this->params["$envType-dapperfile"] ; }
            if (isset($this->params["$envType-dapperstrano-autopilot"])) {
                return $this->params["$envType-dapperstrano-autopilot"] ; }
            if (isset($this->params["guess"]) && ($envType=="guest") ) {
                $p = '/build/config/dapperstrano/dapperfy/autopilots/generated/phlagrant-box-phlagrant-install-code-data.php';
                return $p ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = '/build/config/dapperstrano/dapperfy/autopilots/generated/phlagrant-host-phlagrant-host-install-host-file-entry.php';
                return $p ; } }
        else if ($provisionType == "destroy") {
            if (isset($this->params["$envType-dapperfile-destroy"])) {
                return $this->params["$envType-dapperfile-destroy"] ; }
            if (isset($this->params["$envType-dapperstrano-autopilot-destroy"])) {
                return $this->params["$envType-dapperstrano-autopilot-destroy"] ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = '/build/config/dapperstrano/dapperfy/autopilots/generated/phlagrant-host-phlagrant-host-uninstall-host-file-entry.php';
                return $p ; } }
        $forDestruct = ($provisionType == "destroy") ? " For Destruction" : "" ;
        $question = "Enter path to your ".ucfirst($envType)." Dapperstrano Deployment File$forDestruct" ;
        $df = $this->askForInput($question) ;
        return $df ;
    }


}