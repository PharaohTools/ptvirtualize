<?php

Namespace Controller ;

class Behat extends Base {

    public function execute($pageVars) {
        $defaultExecution = $this->defaultExecution($pageVars) ;
        if (is_array($defaultExecution)) { return $defaultExecution ; }
    }

    protected function defaultExecution($pageVars) {
        $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars, "Initializer") ;
        // if we don't have an object, its an array of errors
        if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }
        $isDefaultAction = self::checkDefaultActions($pageVars, array(), $thisModel) ;
        if ( is_array($isDefaultAction) ) { return $isDefaultAction; }
        return null ;
    }

}