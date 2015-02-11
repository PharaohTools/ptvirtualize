<?php
   if (is_array($pageVars["result"])) {
       $i = 0;
       foreach ($pageVars["result"] as $box) {
           echo "Box $i:\n";
           echo "  Path: {$box->loc}\n";
           echo "  Provider: {$box->provider}\n";
           echo "  Name: {$box->name}\n";
           echo "  Description: {$box->description}\n";
           echo "  Group: {$box->group}\n";
           echo "  Slug: {$box->slug}\n";
           echo "  Home Location: {$box->home_location}\n\n";
           $i++; } }
?>
----------------
In Virtualizer Box List

