<?php

Namespace Controller ;

class Testify extends Base {

    private $injectedActions = array();

    public function execute($pageVars) {

        $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars) ;
        // if we don't have an object, its an array of errors
        if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }

        $action = $pageVars["route"]["action"];
        if ($action=="standard") {
          $this->content["genCreateResult"] = $thisModel->askWhetherToTestify();
          return array ("type"=>"view", "view"=>"testify", "pageVars"=>$this->content); }

        else if (in_array($action, array_keys($this->injectedActions))) {
          $extendedModel = new $this->injectedActions[$action]() ;
          $this->content["genCreateResult"] = $extendedModel->askWhetherToTestify();
          return array ("type"=>"view", "view"=>"testify", "pageVars"=>$this->content);
        }

    }

    public function injectTestifyAction($action, $modelName) {
       $this->injectedActions[] = array($action => $modelName);
    }

}