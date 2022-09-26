<?php 
    $jdf = file_get_contents('data.json');
    $jd = json_decode($jdf, true);
    var_export($jd);
?>