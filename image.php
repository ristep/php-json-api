<?php 
header('content-type: image/png'); 
$theImage = "img/100.png";//the real image url. 
echo file_get_contents($theImage); 
?> 