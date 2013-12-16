<?php

Namespace Controller ;

class Testify extends Base {

    private $injectedActions = array();

    public function execute($pageVars) {

        $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars) ;
        // if we don't have an object, its an array of errors
        if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }

        var_dump($thisModel) ;
        die("daveage") ;

        $action = $pageVars["route"]["action"] ;
        $thisModel->testRequest = $action ;

        if ($action=="help") {
            $helpModel = new \Model\Help();
            $this->content["helpData"] = $helpModel->getHelpData($pageVars["route"]["control"]);
            return array ("type"=>"view", "view"=>"help", "pageVars"=>$this->content); }

        else if ( in_array($action, array("standard-php", "joomla", "drupal7", "php-js", "html-js"))) {
            $this->content["testifyResult"] = $thisModel->askWhetherToTestify();
            return array ("type"=>"view", "view"=>"testify", "pageVars"=>$this->content); }

        else if (in_array($action, array_keys($this->injectedActions))) {
            $extendedModel = new $this->injectedActions[$action]() ;
            $this->content["testifyResult"] = $extendedModel->askWhetherToTestify();
            return array ("type"=>"view", "view"=>"testify", "pageVars"=>$this->content);
        }

    }

    public function injectTestifyAction($action, $modelName) {
       $this->injectedActions[] = array($action => $modelName);
    }

}