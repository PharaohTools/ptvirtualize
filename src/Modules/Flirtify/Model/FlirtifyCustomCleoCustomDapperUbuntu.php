<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class FlirtifyCustomCleoCustomDapperUbuntu extends Base {

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

    protected function getDapperfile($envType) {
        $envType = strtolower($envType) ;
        if (isset($this->params["$envType-dapperfile"])) {
            return $this->params["$envType-dapperfile"] ; }
        if (isset($this->params["$envType-dapperstrano-autopilot"])) {
            return $this->params["$envType-dapperstrano-autopilot"] ; }
        $question = "Enter path to your ".ucfirst($envType)." Dapperstrano Deployment File" ;
        $this->params["$envType-nodes-environment"] = $this->askForInput($question) ;
        return $this->params["$envType-nodes-environment"] ;
    }


}