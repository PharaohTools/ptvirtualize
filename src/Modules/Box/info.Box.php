<?php

Namespace Info;

class BoxInfo extends Base {

    public $hidden = false;

    public $name = "Box - Manage Base Boxes for Phlagrant";

    public function __construct() {
        parent::__construct();
    }

    public function routesAvailable() {
        return array( "Box" =>  array_merge(array("add", "remove", "list") ) );
    }

    public function routeAliases() {
        return array("box"=>"Box");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This command allows you to manage the Base boxes available to you in Phlagrant

  Box, box

        - add
        Initialises the Box as usable by Phlagrant
        example: phlagrant box add

        - remove
        Removes the box as usable by Phlagrant
        example: phlagrant box remove

        - list
        List boxes installed in Phlagrant
        example: phlagrant box list

HELPDATA;
      return $help ;
    }

}