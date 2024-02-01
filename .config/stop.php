<?php
    $logfile = fopen('logfile.log','a');
    $state = fopen('state.txt','r');
    $tmp = fgets($state);
    fclose($state);
    //validates if the service is running, stops it and submit a log
    //or log if the service was not running
    if($tmp=="1"){
        $state = fopen('state.txt','w');
        $output = date("Y-m-d H:i:s") . " -- INFORMATION: Service stopped\n";
        fwrite($logfile, $output);
        fwrite($state, "0");
        fclose($state);
    } else {
        $output = date("Y-m-d H:i:s") . " -- INFORMATION: The service is not running\n";
        fwrite($logfile, $output);
    }
    fclose($logfile);
    exit(0);
    die;
?>