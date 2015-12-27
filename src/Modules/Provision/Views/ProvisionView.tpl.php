<?php

foreach ($pageVars["result"] as $key => $value) {
    ?>Step <?php echo $key ; ?> : <?php echo ($value==true) ? "Success" : "Failure" ; ?><?php
}

$success = !in_array(false, $pageVars["result"]) ;

if ($success==true) { ?>
Provision Successful<?php
}

else { ?>
Provision Failed<?php
}

?>In Pharaoh Virtualize Provision