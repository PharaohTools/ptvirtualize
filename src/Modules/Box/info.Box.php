<?php

Namespace Info;

class BoxInfo extends Base {

    public $hidden = false;

    public $name = "Box - Manage Base Boxes for Virtualize";

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
  This command allows you to manage the Base boxes available to you in Virtualize

  Box, box

        - add
        Initialises the Box as usable by Virtualize
        example: virtualize box add
        example: virtualize box add --yes --guess
            --source="/home/dave/file.box" # where the box file is
            --target="opt/virtualize/boxes" # will guess the dir next to virtualize install dir
            --name="vanillaubuntu"

        - remove
        Removes the box as usable by Virtualize
        example: virtualize box remove

        - package
        Packages a box as usable by Virtualize
        example: virtualize box package
        example virtualize box package --yes --guess
            --name="Vanilla Ubuntu 12.04 amd 64"
            --vmname="a4dc638f-2721-40c4-a943-2f2565c83fee" # use name or id of virtual machine
            --provider="virtualbox" # guess will use virtualbox
            --group="virtualize"
            --slug="" # guess can generate this based on name field
            --description="A Vanilla install of Ubuntu..."
            --home_location="http://www.someplace.net/" # guess will set this to http://www.virtualizeboxes.co.uk/
            --target="/opt/virtualize/boxes" # save location, will guess /opt/virtualize/boxes

        - list
        List boxes installed in Virtualize
        example: virtualize box list

HELPDATA;
      return $help ;
    }

}