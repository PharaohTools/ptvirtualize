Virtualize from Pharaoh Tools
-------------------

Pharaoh Virtualize Development Environments.





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