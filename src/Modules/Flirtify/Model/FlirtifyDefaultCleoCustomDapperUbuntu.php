<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class FlirtifyDefaultCleoCustomDapperUbuntu extends Base {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian") ;
    public $distros = array("Ubuntu") ;
    public $versions = array("12.04", "12.10", "13.04", "13.10", "14.04") ;
    public $architectures = array("32", "64") ;

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
        $templatesDir = str_replace("Model", "Templates/Phlagrantfiles", dirname(__FILE__) ) ;
        $template = $templatesDir . "/default-cleo-dapper.php";
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $targetLocation = "Phlagrantfile" ;
        $templator->template(
            file_get_contents($template),
            array(
                "dapperfile-guest" => $this->getDapperfile("guest"),
                "dapperfile-host" => $this->getDapperfile("host"),
            ),
            $targetLocation );
        echo $targetLocation."\n";
    }

    protected function getDapperfile($envType) {
        $envType = strtolower($envType) ;
        if (isset($this->params["$envType-dapperfile"])) {
            return $this->params["$envType-dapperfile"] ; }
        if (isset($this->params["$envType-dapperstrano-autopilot"])) {
            return $this->params["$envType-dapperstrano-autopilot"] ; }

        if (isset($this->params["guess"]) && ($envType=="guest") ) {
            $p = '/build/config/dapperstrano/dapperfy/autopilots/generated/phlagrant-box-phlagrant-install-code-data.php';
            return $p ; }

        if (isset($this->params["guess"]) && ($envType=="host") ) {
            $p = '/build/config/dapperstrano/dapperfy/autopilots/generated/phlagrant-host-host-install-host-file-entry.php';
            return $p ; }

        $question = "Enter path to your ".ucfirst($envType)." Dapperstrano Deployment File" ;
        $df = $this->askForInput($question) ;
        return $df ;
    }


}