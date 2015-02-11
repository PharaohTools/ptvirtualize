Virtualizer by Golden Contact Computing
-------------------

About:
-----------------
Virtualizer is for controlling Virtual Machines in Development Environments.

-------------------------------------------------------------

Available Commands:
---------------------------------------

<?php
foreach ($pageVars["modulesInfo"] as $moduleInfo) {
  if ($moduleInfo["hidden"] != true) {
    echo $moduleInfo["command"].' - '.$moduleInfo["name"]."\n";
  }
}

?>