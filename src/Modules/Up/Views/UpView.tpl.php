<?php

if (isset($pageVars["result"]) && is_array($pageVars["result"])) {

    foreach ($pageVars["result"] as $key => $value) {
        ?>Step <?php echo $key ; ?> : <?php echo ($value==true) ? "Success" : "Failure" ; ?><?php
    }
    $success = !in_array(false, $pageVars["result"]) ;

}
else if (isset($pageVars["result"])) {
    $success = ($pageVars["result"]==true) ? "Success" : "Failure" ;
}
else {
    $success = false ;
}


if ($success==true) { ?>
Up Successful<?php
}

else { ?>
Up Failed<?php
}

?>In Pharaoh Virtualize Up