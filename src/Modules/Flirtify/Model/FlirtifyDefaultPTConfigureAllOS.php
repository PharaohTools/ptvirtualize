<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class FlirtifyDefaultPTConfigureAllOS extends Base {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default", "DefaultPTConfigure") ;

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
        $templatesDir = str_replace("Model", "Templates".DS."Virtufiles", dirname(__FILE__) ) ;
        $template = $templatesDir . DS."default-ptconfigure.php";
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $targetLocation = "Virtufile" ;
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