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

    private function findExtraParameters() {
        $bu = 'https://repositories.internal.pharaohtools.com/index.php?control=BinaryServer&action=serve&item=iso_php_virtualize_boxes_-_ubuntu_16.04_server' ;
        $defaults =
            array(
                'box_name' => 'pharaohubuntu14041amd64',
                'vm_name' => 'ptv_ubuntu',
                'vm_gui_mode' => "gui",
                'vm_box_url' => $bu,
                'vm_box' => "isophpexampleapp",
            );
        return $defaults ;
    }

    private function doFlirtify() {
        $templatesDir = str_replace("Model", "Templates".DS."Virtufiles", dirname(__FILE__) ) ;
        $template = $templatesDir . DS."default-ptc.php";
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