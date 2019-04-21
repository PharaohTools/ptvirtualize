<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class InitialiseDefaultAllOS extends Base {

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

    private function findExtraParameters() {
        $repo_home = 'https://repositories.internal.pharaohtools.com/index.php?control=BinaryServer&action=serve&item=' ;
        $repo_item = 'iso_php_virtualize_boxes_-_ubuntu_16.04_server' ;
        $repo = $repo_home.$repo_item ;
        $defaults =
            array(
                'name' => 'ptvirtualize_default',
                'gui_mode' => "gui",
                'box_url' => $repo,
                'box' => "isophpexampleapp",
            );
        $defaults_with_overrides = $this->applyParameterOverrides($defaults) ;
        return $defaults_with_overrides ;
    }

    private function applyParameterOverrides($defaults) {
        $overrides = $defaults ;
        $default_keys = array_keys($defaults) ;
        foreach ($default_keys as $default_key) {
            if (isset($this->params[$default_key])) {
                $overrides[$default_key] = $this->params[$default_key] ;
            }
        }
        if (isset($this->params['box']) && !isset($this->params['box_url'])) {
            unset($overrides['box_url']) ;
        }
        return $overrides ;
    }

    private function doInitialise() {
        $templatesDir = str_replace("Model", "Templates".DS."Virtufiles", dirname(__FILE__) ) ;
        if (isset($this->params['default-template']) ) {
            $template = $templatesDir . DS.$this->params['default-template'];
        } else  if (isset($this->params['template']) ) {
            $template = $this->params['default-template'];
        } else {
            $template = $templatesDir . DS."default-ptc.php";
        }
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $targetLocation = "Virtufile" ;
        $extra_parameters = $this->findExtraParameters() ;
        $templator->template(
            file_get_contents($template),
            $extra_parameters,
            $targetLocation );
        echo 'Virtufile written to '.$targetLocation."\n";
    }

}