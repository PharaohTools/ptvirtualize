<?php

Namespace Model;

class Testify extends BaseModelFactory {

    public static function getModel($params) {
        $thisModule = substr(get_called_class(), 6) ;
        $model = \Model\SystemDetectionFactory::getCompatibleModel($thisModule, "Testifyer", $params);
        return $model;
    }

}