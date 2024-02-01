<?php
/**
     * 
     * save the inputed log in the format of:
     * Y-m-d H:i:s -- TYPE: MESSAGE
     * The valid log types are:
     * 0-INFORMATION (default)
     * 1-ALERT
     * 2-WARNING
     * 3-ERROR
     * 4-FATAL ERROR
     * 
     * @param string $message the message log to submit
     * @param int $type the type of log to submit
     */
    function addLog(string $message, int $type=0){
        global $logfile;
        switch ($type) {
            case 0:
                $status = "INFORMATION: ";
                break;
            
            case 1:
                $status = "ALERT: ";
                break;
            
            case 2:
                $status = "WARNING: ";
                break;
                
            case 3:
                $status = "ERROR: ";
                break;
            
            case 4:
                $status = "FATAL ERROR: ";
                break;
            
            default:
                $status = "INFORMATION: ";
                break;
        }
        $output = date("Y-m-d H:i:s") . " -- " . $status . $message;
        fwrite($logfile, $output);
    }

    //simple function to stop the running of the api if the service not initialized correctly
    function Server_init(bool $api=true): bool{
        $state = fopen('../.config/state.txt','r');
        $array_response = array();
        $tmp = fgets($state);
        fclose($state);
        if($tmp!='1'){
            if($api){
                header("Content-Type:application/json",true,503);
                $array_response["Status"]=503;
                $array_response["Title"]="Service Unavailable";
                $array_response["Detail"]="Server failed to init";
                $jsonResponse = json_encode($array_response);
                echo $jsonResponse;
            }
            return false;
        }
        return true;
    }
?>