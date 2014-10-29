<?php

Namespace Info;

class BoxInfo extends Base {

    public $hidden = false;

    public $name = "Box - Manage Base Boxes for Phlagrant";

    public function __construct() {
        parent::__construct();
    }

    public function routesAvailable() {
        return array( "Box" =>  array_merge(array("help", "add", "remove", "package", "list") ) );
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

        - package
        Packages a box as usable by Phlagrant
        example: phlagrant box package
        example phlagrant box package --yes --guess
            --name="Vanilla Ubuntu 12.04 amd 64"
            --vmname="a4dc638f-2721-40c4-a943-2f2565c83fee" # use name or id of virtual machine
            --provider="virtualbox" # guess will use virtualbox
            --group="phlagrant"
            --slug="" # guess can generate this based on name field
            --description="A Vanilla install of Ubuntu..."
            --home_location="http://www.someplace.net/" # guess will set this to http://www.phlagrantboxes.co.uk/
            --target="/opt/phlagrant/boxes" # save location, will guess /opt/phlagrant/boxes

        - list
        List boxes installed in Phlagrant
        example: phlagrant box list

HELPDATA;
      return $help ;
    }

}