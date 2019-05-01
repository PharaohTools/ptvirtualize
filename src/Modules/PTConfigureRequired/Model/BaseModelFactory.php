<?php

Namespace Model;

class BaseModelFactory {

    public static function getModel($params, $modelGroup = "Default") {
        $thisModule = substr(get_called_class(), 6) ;
        $model = \Model\SystemDetectionFactory::getCompatibleModel($thisModule, $modelGroup, $params);
//        var_dump('$model', $model, '$params', $params) ;
        return $model;
    }

}