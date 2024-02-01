<?php
    //simple code to echo the status of the server
    $state = fopen('state.txt','r');
    $tmp = fgets($state);
    fclose($state);
    echo $tmp;
    exit(0);
    die;
?>