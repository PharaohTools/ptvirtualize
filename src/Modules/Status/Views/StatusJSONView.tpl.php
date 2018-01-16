<?php

   if (is_array($pageVars["result"])) {
       $json_result = json_encode($pageVars["result"], JSON_PRETTY_PRINT) ;
   } else {
       $json_result = 'Unable to provide output in JSON format' ;
   }

   echo $json_result ;

?>
