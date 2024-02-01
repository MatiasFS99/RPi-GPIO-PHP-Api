<?php
    include_once($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php');
    include_once($_SERVER['DOCUMENT_ROOT'].'/libs/helpers.php');
    use PiPHP\GPIO\GPIO;
    use PiPHP\GPIO\Pin\PinInterface;
    use start_validator\serverStatus;

    // Represent the json output
    $array_response=array();

    // Call the apikey data base
    $api_db = new SQLite3($_SERVER['DOCUMENT_ROOT'].'/db/apikeys.db');
    
    // Create a GPIO object
	$gpio = new GPIO();
	$rele=array($gpio->getOutputPin(21), $gpio->getOutputPin(20), $gpio->getOutputPin(16), 
				$gpio->getOutputPin(5), $gpio->getOutputPin(6), $gpio->getOutputPin(13),
				$gpio->getOutputPin(19), $gpio->getOutputPin(26)
                //Recheck the pinout and the led board
                /*, $gpio->getOutputPin(27),
                $gpio->getOutputPin(17)*/ 
                );

    //the 27(9 in array) is the red led
    //the 17(10 in array) is the green led
	$pinId=array(
		21, 20,	16, 5, 6, 13, 19, 26, /*27, 17*/
	);

    /**
     * 
     * validate the json syntax from POST
     * 
     * @return return true if the syntax of the json is valid
     */
    function JsonValidate(){
        @json_decode(file_get_contents('php://input'));
        return (json_last_error() === JSON_ERROR_NONE);
    }
    
    /**
     * 
     * Flip the current I/O value of a gpio pin designated by his order in the array above
     * 
     * @param  int $num the number of pin to flip
     * @return string return a message string of the current I/O value of the pin and his name
     */
    function releOnOff(int $num): string{
		global $rele;
		global $pinId;
		if(($aux=$rele[$num]->getValue()) == 0){
			$rele[$num]->setValue(PinInterface::VALUE_HIGH);
		}else{
			$rele[$num]->setValue(PinInterface::VALUE_LOW);
		}
		$tx=$rele[$num]->getValue();

		return "The actual state of pin number {$pinId[$num]} is {$tx}.";
	}

    /**
     * 
     * Get the current I/O value of a gpio pin designated by his order in the array above
     * 
     * @param  int $num the number of pin to get the status.
     * @return string return a message string of the current I/O value of the pin and his name
     */ 
    function getStatus(int $num): string{
        global $rele;
		global $pinId;
		$tx=$rele[$num]->getValue();
		return "The actual state of pin number {$pinId[$num]} is {$tx}.";
    }

    /**
     * 
     * Get the current I/O value of all the gpio pins
     * 
     * @return array return an array with the id and I/O value of the gpio pins
     */ 

    function getAllStatus(): array{
        global $rele;
		global $pinId;
        $output=array();
        for ($i=0; $i < count($rele); $i++) { 
            $output[$pinId[$i]]=$rele[$i]->getValue();
        }
        return $output;
    }

    /**
     * 
     * Check with the database if the user exist and if the inserted apikey is valid and return true if valid
     * 
     * @param string $user the username to validate the api
     * @param string $api_key the api_key to validate
     * 
     * @return bool Return true if the identity is valid or false if not
     */
    function validateApiKey(string $user, string $api_key): bool{
        global $api_db;
        $sentence = $api_db->prepare("
            select exists(
                SELECT * FROM valid_keys WHERE user=:USER
            )
        ");
        $sentence->bindValue(':USER',$user, SQLITE3_TEXT);
        if($sentence->execute()->fetchArray()[0]!=1){
            return false;
        }
        $sentence = $api_db->prepare("
                SELECT api_key FROM valid_keys WHERE user=:USER
        ");
        $sentence->bindValue(':USER',$user, SQLITE3_TEXT);
        $hash = $sentence->execute()->fetchArray()["api_key"];
        return password_verify($api_key,$hash);
    }

    /**
     * 
     * Validate the entries of the submited orders and execute or return an error
     * 
     * @param $request represent the decoded json from post.
     * @return return the output of the execution or an error.
     */
    function validate($request){
        global $rele;
        if(!isset($request->api_key)){
            Response("The api key is required.", 403,"Forbidden");
            addLog("Unauthorized connection attempt stopped",1);
            return;
        }

        if(!isset($request->api_key->user)||!isset($request->api_key->api_key)){
            Response("The api key and user are required.", 403,"Forbidden");
            addLog("Unauthorized connection attempt stopped",1);
            return;
        }

        if(!validateApiKey($request->api_key->user,$request->api_key->api_key)){
            Response("The api key is invalid.", 403,"Forbidden");
            addLog("Unauthorized connection attempt stopped",1);
            return;
        }

        if(!isset($request->command)){
            Response("The 'command' value is empty and is required");
            return;
        }
        switch ($request->command) {
            case 'flip':
                if(!isset($request->pin)){
                    Response("The 'pin' value is required.");
                    return;
                }
                if(is_null($request->pin)||!is_numeric($request->pin)){
                    Response("The 'pin' value is empty or not a number.");
                    return;
                }
                $input = intval($request->pin);
                if($input<0 || $input>count($rele)){
                    Response("The 'pin' value is out of bounds");
                    return;
                } else {
                    Response(releOnOff($input),200,"Success");
                    addLog("Pin value change");
                    return;
                }
                break;
            case 'status':
                if(!isset($request->pin)){
                    Response("The 'pin' value is required.");
                    return;
                }
                if(is_null($request->pin)||!is_numeric($request->pin)){
                    Response("The 'pin' value is empty or not a number.");
                    return;
                }
                $input = intval($request->pin);
                if($input<0 || $input>count($rele)){
                    Response("The 'pin' value is out of bounds");
                    return;
                } else {
                    Response(getStatus($input),200,"Success");
                    return;
                }
                break;
            case 'allStatus':
                Response("Returned all pin status",200,"Success",getAllStatus());
                break;
            default:
            Response("Invalid command check and try again later");
                break;
        }
    }
    /**
     * Generate a http response in json format
     * @param string $detail Detailed explanation of the error
     * @param int $status http status response code
     * @param string $title Title of the status response
     */
    function Response(string $detail = "It is required to send a json with the order.", int $status = 400, string $title = "Bad Request", $load = NULL){
        global $array_response;
        header("Content-Type:application/json",true,$status);
        $array_response["Status"]=$status;
        $array_response["Title"]=$title;
        $array_response["Detail"]=$detail;
        if(!is_null($load)){
            $array_response["Data Response"]=$load;
        }
    }

    if(Server_init()){
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json'){
            if(JsonValidate()){
                $json_request = file_get_contents('php://input');
                $request_array=json_decode($json_request);
                validate($request_array);
            } else {
                Response("The submited json is corrupted, check and try again.");
            }
        } else {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                Response("Only post method is allowed",405,"Method Not Allowed");
                addLog("Unauthorized method connection attempt stopped",1);
            } else {
                Response();
            }
        }
        $jsonResponse = json_encode($array_response);
        echo $jsonResponse;
    }
    $api_db->close();
    die();
?>