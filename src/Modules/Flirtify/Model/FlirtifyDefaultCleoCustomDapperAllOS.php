<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class FlirtifyDefaultCleoCustomDapperAllOS extends Base {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("DefaultCleoCustomDapper") ;

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
        $template = $templatesDir . DS."default-cleo-dapper.php";
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $targetLocation = "Phlagrantfile" ;
        $templator->template(
            file_get_contents($template),
            array(
                "dapperfile-guest" => $this->getDapperfile("guest"),
                "dapperfile-host" => $this->getDapperfile("host"),
                "dapperfile-host-destroy" => $this->getDapperfile("host", "destroy"),
            ),
            $targetLocation );
        echo $targetLocation."\n";
    }

    protected function getDapperfile($envType, $provisionType = "up") {
        $envType = strtolower($envType) ;
        if ($provisionType == "up") {
            if (isset($this->params["$envType-dapperfile"])) {
                return $this->params["$envType-dapperfile"] ; }
            if (isset($this->params["$envType-dapperstrano-autopilot"])) {
                return $this->params["$envType-dapperstrano-autopilot"] ; }
            if (isset($this->params["guess"]) && ($envType=="guest") ) {
                $p = ''.DS.'build'.DS.'config'.DS.'dapperstrano'.DS.'dapperfy'.DS.'autopilots'.DS.'generated'.DS.'phlagrant-box-phlagrant-install-code-data.php';
                return $p ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = ''.DS.'build'.DS.'config'.DS.'dapperstrano'.DS.'dapperfy'.DS.'autopilots'.DS.'generated'.DS.'phlagrant-host-phlagrant-host-install-host-file-entry.php';
                return $p ; } }
        else if ($provisionType == "destroy") {
            if (isset($this->params["$envType-dapperfile-destroy"])) {
                return $this->params["$envType-dapperfile-destroy"] ; }
            if (isset($this->params["$envType-dapperstrano-autopilot-destroy"])) {
                return $this->params["$envType-dapperstrano-autopilot-destroy"] ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = ''.DS.'build'.DS.'config'.DS.'dapperstrano'.DS.'dapperfy'.DS.'autopilots'.DS.'generated'.DS.'phlagrant-host-phlagrant-host-uninstall-host-file-entry.php';
                return $p ; } }
        $forDestruct = ($provisionType == "destroy") ? " For Destruction" : "" ;
        $question = "Enter path to your ".ucfirst($envType)." Dapperstrano Deployment File$forDestruct" ;
        $df = $this->askForInput($question) ;
        return $df ;
    }

    protected function getPhlagrantHostEnvironment($envType) {
        $envType = strtolower($envType) ;
        if (isset($this->params["$envType-dapperfile"])) {
            return $this->params["$envType-dapperfile"] ; }
        if (isset($this->params["$envType-dapperstrano-autopilot"])) {
            return $this->params["$envType-dapperstrano-autopilot"] ; }

        if (isset($this->params["guess"]) && ($envType=="guest") ) {
            $p = DS.'build'.DS.'config'.DS.'dapperstrano'.DS.'dapperfy'.DS.'autopilots'.DS.'generated'.DS.'phlagrant-box-phlagrant-install-code-data.php';
            return $p ; }

        if (isset($this->params["guess"]) && ($envType=="host") ) {
            $p = DS.'build'.DS.'config'.DS.'dapperstrano'.DS.'dapperfy'.DS.'autopilots'.DS.'generated'.DS.'phlagrant-host-host-install-host-file-entry.php';
            return $p ; }

        $question = "Enter path to your ".ucfirst($envType)." Dapperstrano Deployment File" ;
        $df = $this->askForInput($question) ;
        return $df ;
    }


}