<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class FlirtifyDefaultCleoUbuntu extends Base {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian") ;
    public $distros = array("Ubuntu") ;
    public $versions = array("12.04", "12.10", "13.04", "13.10", "14.04") ;
    public $architectures = array("32", "64") ;

    // Model Group
    public $modelGroup = array("Default", "DefaultCleo") ;

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

    private function doFlirtify() {
        $templatesDir = str_replace("Model", "Templates/Phlagrantfiles", dirname(__FILE__) ) ;
        $template = $templatesDir . "/default-cleo.php";
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $targetLocation = "Phlagrantfile" ;
        $templator->template(
            file_get_contents($template),
            array(
                //"env_name" => $environment["any-app"]["gen_env_name"],
                //"first_server_target" => $environment["servers"][0]["target"],
            ),
            $targetLocation );
        echo $targetLocation."\n";
    }

}