<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class InitialiseCustomPTConfigureCustomPTDeployAllOS extends Base {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("CustomPTConfigureCustomPTDeploy") ;

    private $environments ;
    private $environmentReplacements ;

    public function __construct($params) {
      parent::__construct($params);
    }

    public function askWhetherToInitialise() {
        if ($this->askToScreenWhetherToInitialise() != true) { return false; }
        $this->doInitialise() ;
        return true;
    }

    public function askToScreenWhetherToInitialise() {
        if (isset($this->params["yes"]) && $this->params["yes"]==true) { return true ; }
        $question = 'Initialise This?';
        return self::askYesOrNo($question, true);
    }

    protected function doInitialise() {
        $templatesDir = str_replace("Model", "Templates".DS."Virtufiles", dirname(__FILE__) ) ;
        $template = $templatesDir . DS."custom-ptconfigure-ptdeploy.php";
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $targetLocation = "Virtufile" ;
        $templator->template(
            file_get_contents($template),
            array(
                "ptconfigurefile-guest" => $this->getPTConfigurefile("guest"),
                "ptdeployfile-guest" => $this->getPTDeployfile("guest"),
                "ptdeployfile-host" => $this->getPTDeployfile("host"),
                "ptdeployfile-host-destroy" => $this->getPTDeployfile("host", "destroy"),
            ),
            $targetLocation );
        echo $targetLocation."\n";
    }

    protected function getPTConfigurefile($envType) {
        $envType = strtolower($envType) ;
        if (isset($this->params["$envType-ptconfigurefile"])) {
            return $this->params["$envType-ptconfigurefile"] ; }
        if (isset($this->params["$envType-ptconfigure-autopilot"])) {
            return $this->params["$envType-ptconfigure-autopilot"] ; }
        $question = "Enter path to your ".ucfirst($envType)." PTConfigure Deployment File" ;
        $this->params["$envType-nodes-environment"] = $this->askForInput($question) ;
        return $this->params["$envType-nodes-environment"] ;
    }

    protected function getPTDeployfile($envType, $provisionType = "up") {
        $envType = strtolower($envType) ;
        if ($provisionType == "up") {
            if (isset($this->params["$envType-ptdeployfile"])) {
                return $this->params["$envType-ptdeployfile"] ; }
            if (isset($this->params["$envType-ptdeploy-autopilot"])) {
                return $this->params["$envType-ptdeploy-autopilot"] ; }
            if (isset($this->params["guess"]) && ($envType=="guest") ) {
                $p = DS.'build'.DS.'config'.DS.'ptdeploy'.DS.'ptdeployfy'.DS.'autopilots'.DS.'generated'.DS.'ptvirtualize-box-ptvirtualize-install-code-data.php';
                return $p ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = DS.'build'.DS.'config'.DS.'ptdeploy'.DS.'ptdeployfy'.DS.'autopilots'.DS.'generated'.DS.'ptvirtualize-host-ptvirtualize-host-install-host-file-entry.php';
                return $p ; } }
        else if ($provisionType == "destroy") {
            if (isset($this->params["$envType-ptdeployfile-destroy"])) {
                return $this->params["$envType-ptdeployfile-destroy"] ; }
            if (isset($this->params["$envType-ptdeploy-autopilot-destroy"])) {
                return $this->params["$envType-ptdeploy-autopilot-destroy"] ; }
            if (isset($this->params["guess"]) && ($envType=="host") ) {
                $p = DS.'build'.DS.'config'.DS.'ptdeploy'.DS.'ptdeployfy'.DS.'autopilots'.DS.'generated'.DS.'ptvirtualize-host-ptvirtualize-host-uninstall-host-file-entry.php';
                return $p ; } }
        $forDestruct = ($provisionType == "destroy") ? " For Destruction" : "" ;
        $question = "Enter path to your ".ucfirst($envType)." PTDeploy Deployment File$forDestruct" ;
        $df = $this->askForInput($question) ;
        return $df ;
    }


}