<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class FlirtifyCustomCleoCustomDapperAllOS extends Base {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

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
        $templatesDir = str_replace("Model", "Templates".DS."Phlagrantfiles", dirname(__FILE__) ) ;
        $template = $templatesDir . DS."custom-cleo-dapper.php";
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
                $p = DS.'build'.DS.'config'.DS.'dapperstrano'.DS.'dapperfy'.DS.'autopilots'.DS.'generated'.DS.'phlagrant-box-phlagrant-install-code-data.php';
                return $p ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = DS.'build'.DS.'config'.DS.'dapperstrano'.DS.'dapperfy'.DS.'autopilots'.DS.'generated'.DS.'phlagrant-host-phlagrant-host-install-host-file-entry.php';
                return $p ; } }
        else if ($provisionType == "destroy") {
            if (isset($this->params["$envType-dapperfile-destroy"])) {
                return $this->params["$envType-dapperfile-destroy"] ; }
            if (isset($this->params["$envType-dapperstrano-autopilot-destroy"])) {
                return $this->params["$envType-dapperstrano-autopilot-destroy"] ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = DS.'build'.DS.'config'.DS.'dapperstrano'.DS.'dapperfy'.DS.'autopilots'.DS.'generated'.DS.'phlagrant-host-phlagrant-host-uninstall-host-file-entry.php';
                return $p ; } }
        $forDestruct = ($provisionType == "destroy") ? " For Destruction" : "" ;
        $question = "Enter path to your ".ucfirst($envType)." Dapperstrano Deployment File$forDestruct" ;
        $df = $this->askForInput($question) ;
        return $df ;
    }


}