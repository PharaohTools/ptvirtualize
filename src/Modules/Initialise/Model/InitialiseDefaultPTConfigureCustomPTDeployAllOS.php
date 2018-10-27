<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class FlirtifyDefaultPTConfigureCustomPTDeployAllOS extends Base {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("DefaultPTConfigureCustomPTDeploy") ;

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
        $templatesDir = str_replace("Model", "Templates".DS."Virtufiles", dirname(__FILE__) ) ;
        $template = $templatesDir . DS."default-ptconfigure-ptdeploy.php";
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $targetLocation = "Virtufile" ;
        $templator->template(
            file_get_contents($template),
            array(
                "ptdeployfile-guest" => $this->getPTDeployfile("guest"),
                "ptdeployfile-host" => $this->getPTDeployfile("host"),
                "ptdeployfile-host-destroy" => $this->getPTDeployfile("host", "destroy"),
            ),
            $targetLocation );
        echo $targetLocation."\n";
    }

    protected function getPTDeployfile($envType, $provisionType = "up") {
        $envType = strtolower($envType) ;
        if ($provisionType == "up") {
            if (isset($this->params["$envType-ptdeployfile"])) {
                return $this->params["$envType-ptdeployfile"] ; }
            if (isset($this->params["$envType-ptdeploy-autopilot"])) {
                return $this->params["$envType-ptdeploy-autopilot"] ; }
            if (isset($this->params["guess"]) && ($envType=="guest") ) {
                $p = ''.DS.'build'.DS.'config'.DS.'ptdeploy'.DS.'ptdeployfy'.DS.'autopilots'.DS.'generated'.DS.'ptvirtualize-box-ptvirtualize-install-code-data.php';
                return $p ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = ''.DS.'build'.DS.'config'.DS.'ptdeploy'.DS.'ptdeployfy'.DS.'autopilots'.DS.'generated'.DS.'ptvirtualize-host-ptvirtualize-host-install-host-file-entry.php';
                return $p ; } }
        else if ($provisionType == "destroy") {
            if (isset($this->params["$envType-ptdeployfile-destroy"])) {
                return $this->params["$envType-ptdeployfile-destroy"] ; }
            if (isset($this->params["$envType-ptdeploy-autopilot-destroy"])) {
                return $this->params["$envType-ptdeploy-autopilot-destroy"] ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = ''.DS.'build'.DS.'config'.DS.'ptdeploy'.DS.'ptdeployfy'.DS.'autopilots'.DS.'generated'.DS.'ptvirtualize-host-ptvirtualize-host-uninstall-host-file-entry.php';
                return $p ; } }
        $forDestruct = ($provisionType == "destroy") ? " For Destruction" : "" ;
        $question = "Enter path to your ".ucfirst($envType)." PTDeploy Deployment File$forDestruct" ;
        $df = $this->askForInput($question) ;
        return $df ;
    }

    protected function getVirtualizeHostEnvironment($envType) {
        $envType = strtolower($envType) ;
        if (isset($this->params["$envType-ptdeployfile"])) {
            return $this->params["$envType-ptdeployfile"] ; }
        if (isset($this->params["$envType-ptdeploy-autopilot"])) {
            return $this->params["$envType-ptdeploy-autopilot"] ; }

        if (isset($this->params["guess"]) && ($envType=="guest") ) {
            $p = DS.'build'.DS.'config'.DS.'ptdeploy'.DS.'ptdeployfy'.DS.'autopilots'.DS.'generated'.DS.'ptvirtualize-box-ptvirtualize-install-code-data.php';
            return $p ; }

        if (isset($this->params["guess"]) && ($envType=="host") ) {
            $p = DS.'build'.DS.'config'.DS.'ptdeploy'.DS.'ptdeployfy'.DS.'autopilots'.DS.'generated'.DS.'ptvirtualize-host-host-install-host-file-entry.php';
            return $p ; }

        $question = "Enter path to your ".ucfirst($envType)." PTDeploy Deployment File" ;
        $df = $this->askForInput($question) ;
        return $df ;
    }


}