<?php
    include_once('../vendor/autoload.php');
    use PiPHP\GPIO\GPIO;
    use PiPHP\GPIO\Pin\PinInterface;
    use PiPHP\GPIO\Pin\InputPinInterface;
    //open/create the log file
    $logfile = fopen('logfile.log','a');
    $state = fopen('state.txt','r');
    $gpio = new GPIO();
    //return the service init status
    $initStatus = -1;

    /**
     * 
     * Validate the syntax of the configuration file(config.ini)
     * Check that all the configs are valid, if not add a log and abort the initialization 
     * 
     * @return bool return a boolean validating if the configuration is valid
     */
    function validateConfig():bool{
        //Check if the file is valid in php ini syntax
        if(@parse_ini_file("config.ini") == false){
            ReportError(-1);
            return false;
        }

        $config = parse_ini_file("config.ini",true);

        if(!array_key_exists("GPIO_Pin_Direction",$config)){
            ReportError(10);
            return false;
        }

        $aux = $config["GPIO_Pin_Direction"];

        if(count($aux)<27){
            ReportError(11);
            return false;
        } 
        for ($i=0; $i < 28; $i++) { 
            if(!array_key_exists("GPIO_".$i,$aux)){
                ReportError(12, $i);
                return false;
            }
            if($aux["GPIO_".$i]!=0 & $aux["GPIO_".$i]!=0){
                ReportError(13,$i);
                return false;
            }
        }

        if(!array_key_exists("GPIO_Pin_Value",$config)){
            ReportError(20);
            return false;
        }

        $aux = $config["GPIO_Pin_Value"];

        if(count($aux)<27){
            ReportError(21);
            return false;
        } 
        for ($i=0; $i < 28; $i++) { 
            if(!array_key_exists("GPIO_".$i,$aux)){
                ReportError(22);
                return false;
            }
            if($aux["GPIO_".$i]!=0 & $aux["GPIO_".$i]!=0){
                ReportError(23,$i);
                return false;
            }
        }
        addLog("INFORMATION: All config working correctly\n");
        return true;
    }

    /**
     * 
     * Start the gpio with the values from the configuration file
     * if the start fails it add a log and abort the initialization
     * IMPORTANT ALWAYS RUN THIS COMMAND AFTER VALIDATING THE CONFIG
     * 
     */
    function startGPIO(): bool{
        global $state;
        global $gpio;
        $config = parse_ini_file("config.ini",true);
        try {
            for ($i=0; $i < 28; $i++) { 
                if($config["GPIO_Pin_Direction"]["GPIO_".$i]==0){
                    $gpio->getOutputPin($i)->
                    setValue(($config["GPIO_Pin_Value"]["GPIO_".$i]==0)? PinInterface::VALUE_HIGH : PinInterface::VALUE_LOW);
                } else {
                    $gpio->getInputPin($i);
                }
            }
            return true;
        } catch(Exception $e) {
            fwrite($state, "-1");
            addLog("FATAL ERROR: There was an exception initializing the GPIOs, Is other process accessing them or the php daemon user is not in GPIO group?\n");
            return false;
        }
        return false;
    }

    /**
     * 
     * add a log with an error depending the input
     * error code -1: the config file is corrupted and cannot be readed
     * error codes 1x: error in the gpio_pin_direction of the file
     * error codes 2x: error in the gpio_pin_value of the file
     * 
     * @param int $code set the error code to submit
     * @param int $pin set the number of the problem pin(if is a general error just leave empty)
     */
    function ReportError(int $code, int $pin = -1){
        global $state;
        switch ($code) {
            case -1:
                addLog("Cannot read the config file or the file is corrupted\n",3);
                break;
            case 10:
                addLog("Cannot find the GPIO_Pin_Direction section in the config file\n",2);
                break;
            case 11:
                addLog("Some GPIO direction Config are missing check the config file and restart the service\n",2);
                break;
            case 12:
                addLog("GPIO_".$pin." direction etiquete is missing check the config and restart the service\n",2);
                break;
            case 13:
                addLog("GPIO_".$pin." direction has an invalid value check the config and restart the service\n",2);
                break;
            case 20:
                addLog("Cannot find the GPIO_Pin_Value section in the config file\n",2);
                break;
            case 21:
                addLog("Some GPIO value Config are missing check the config file and restart the service\n",2);
                break;
            case 12:
                addLog("GPIO_".$pin." Value etiquete is missing check the config and restart the service\n",2);
                break;
            case 13:
                addLog("GPIO_".$pin." Value has an invalid value check the config and restart the service\n",2);
                break;
        }
        fwrite($state, "-1");
    }

    /**
     * 
     * save the inputed log in the format of:
     * Y-m-d H:i:s -- TYPE: MESSAGE
     * The valid log types are:
     * INFORMATION (default)
     * WARNING
     * ERROR
     * FATAL ERROR
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
                $status = "WARNING: ";
                break;
            
            case 2:
                $status = "ERROR: ";
                break;
            
            case 3:
                $status = "FATAL ERROR: ";
                break;
            
            default:
                $status = "INFORMATION: ";
                break;
        }
        $output = date("Y-m-d H:i:s") . " -- " . $status . $message;
        fwrite($logfile, $output);
    }

    //validate if the last sesion stoped correctly
    $tmp = fgets($state);
    if($tmp!="0"){
        addLog("The last session terminated abruptly or has an error on init\n",1);
    }
    fclose($state);
    $state = fopen('state.txt','w');

    //validates and starts the service or stops it if an error occurs
    if(validateConfig() && startGPIO()){
        addLog("Service started correctly\n");
        fwrite($state, "1");
        $initStatus = 0;
    } else {
        addLog("There was a problem on initialization and the service halted\n",3);
        $initStatus = 1;
    }
    fclose($state);
    fclose($logfile);
    exit($initStatus);
    die;
?>