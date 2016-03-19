<?php

ini_set('max_execution_time',90);
session_start();
define('APPNAME', 'House Map');
define('CLIENT_ID', 'f7d5bdf7-c6a5-475d-95ce-25e49efb6436');
define('CLIENT_SECRET', 'b299a4b9-e2cf-48ef-995f-ed9c762ed820');
define('DEBUG', false);
define('DEBUG2', false);
define('FORCED', false);

// helper function to put a hidden field to the form
function hidden($pname, $pvalue) {
    $inpstr = "<input type=\"hidden\" name=\"$pname\"  value=\"$pvalue\" />";
    return $inpstr;
}

// function to make a curl call
function curl_call($host, $headertype=FALSE, $nvpstr=FALSE, $calltype="GET")
{
	//setting the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $host);
	if ($headertype) {
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headertype);
    }

	//turning off peer verification
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	// curl_setopt($ch, CURLOPT_VERBOSE, TRUE);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    if ($calltype=="POST" && $nvpstr) {
    	curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpstr);
        if (DEBUG2) echo "calling via POST";
    } else {
    	curl_setopt($ch, CURLOPT_POST, FALSE);
        if (DEBUG2) echo "calling via $calltype";
        if ($calltype!="GET") curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $calltype);
        if ($nvpstr) curl_setopt($curl, CURLOPT_POSTFIELDS, $nvpstr);
    }

	//getting response from server
    $response = curl_exec($ch);
    
    // handle errors
    if (curl_errno($ch)) {
        // moving to display page to display curl errors
        $_SESSION['curl_error_no']=curl_errno($ch) ;
        $_SESSION['curl_error_msg']=curl_error($ch);
        // $location = "authfailure.php";
        // header("Location: $location");  
        // echo "Error from curl<br />" . $_SESSION['curl_error_msg'];
        $nvpResArray = false;
    } else {
        $nvpResArray = json_decode($response, TRUE);
        if (!$nvpResArray) {
            $nvpResArray = "Error: Json not returned from curl<br />";
            $nvpResArray.= print_r($response, true);
        }
    }
    curl_close($ch);

    return $nvpResArray;
}


// function to get authorization code
// this does a redirect back here with results
function getAuthCode($returl)
{
	//NVPRequest for submitting to server
	$nvpreq="response_type=code&client_id=" . urlencode(CLIENT_ID) . "&scope=app&redirect_uri=" . urlencode($returl);

	// redirect to the smartthings api request page
	$location = "https://graph.api.smartthings.com/oauth/authorize?" . $nvpreq;
	header("Location: $location");
}

function getAccessToken($returl, $code) {

    $host = "https://graph.api.smartthings.com/oauth/token";
    $ctype = "application/x-www-form-urlencoded";
    $headertype = array('Content-Type: ' . $ctype);
    
    $nvpreq = "grant_type=authorization_code&code=" . urlencode($code) . "&client_id=" . urlencode(CLIENT_ID) .
                         "&client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app" . "&redirect_uri=" . urlencode($returl);
    
    $response = curl_call($host, $headertype, $nvpreq, "POST");

    // save the access token    
    if ($response) {
        $token = $response["access_token"];
    } else {
        $token = false;
    }

    return $token;
}

// get the endpoints
function getEndPoints($returl, $token) {

    $host = "https://graph.api.smartthings.com/api/smartapps/endpoints";
    $headertype = array("Authorization: Bearer " . $token);
    $response = curl_call($host, $headertype);

    if (!$response) {
        unset($_SESSION["access_code"]);
        unset($_SESSION["access_token"]);
        unset($_SESSION["access_endpoints"]);
        $response = false;
    }
    return $response;

}

// this makes a basic call to get the sensor status and return as a formatted table
function getSensors($returl, $endpt, $access_token) {
    $tc = "";
    
    $host = $endpt . "/sensors";
    $headertype = array("Authorization: Bearer " . $access_token);
    $nvpreq = "client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app&client_id=" . urlencode(CLIENT_ID);
    $response = curl_call($host, $headertype, $nvpreq, POST);
    $sensornames = array();

    $i= 0;        
    if ( count($response) ) {
        $tc.= "<br /><br /><form name=\"sensordetails\" action=\"" . $returl . "\"  method=\"POST\">";
        $tc.= hidden("sensordetail", "1");
        $tc.= "<table class=\"sensortable\" align=\"left\" width=\"900\">";
        foreach ($response as $k => $thesensor) {
            $i++;
            // $ipos = strpos($thesensor["name"]," ");
            // if ( !$ipos ) $ipos = count($thesensor[$k]["name"]);
            $tc.= "<tr><td>" . $thesensor["name"] . "</td><td>" . $thesensor["value"] . "</td><td><input type=\"checkbox\" name=\"sensorgroup[]\" value=\"$i\" /></td></tr>";
            $sensornames[] = $thesensor["name"];
        }
        $tc .= "<tr><td colspan=\"3\" align=\"right\"><input class=\"paybutton\" value=\"Get History\" name=\"submit2\" type=\"submit\" /></td></tr>";
        $tc.= "</table>";
        // $tc .= print_r($response, true);
        $tc.= "</form>";
        
        // save sensor names for later use
        $_SESSION["sensornames"] = $sensornames;
    }
    
    return $tc;
}

// this makes a basic call to get the sensor status and return as a formatted table
function showSensorDetails($returl, $endpt, $access_token, $sensorname) {
    $tc = "";

    if ( isset($_SESSION["sensornames"]) ) {

        $host = $endpt . "/onesensor?picked=" . urlencode($sensorname);
        $headertype = array("Authorization: Bearer " . $access_token);
        // $nvpstr = "picked=" . urlencode($sensorname);
        $response = curl_call($host, $headertype);

        if ( count($response) ) {
            $hsize = $response["size"];
            $tc.= "<br /><br /><h4>Details for sensor: [" . $sensorname . "] with $hsize Time pts</h3>";
            
            $history = $response["history"];
    
            $tc.= "<table class=\"sensortable\" align=\"left\" width=\"900\">";
            $tc.= "<tr><td>" . "Motion Type" . "</td><td>" . "Calendar Date" . "</td><td>" . "UNIX Time" . "</td></tr>";
            foreach($history as $timestamp) {
                if ($timestamp["name"] == "motion") {
                    $tc.= "<tr><td>" . $timestamp["value"] . "</td><td>" . $timestamp["date"] . "</td><td>" . $timestamp["unixTime"] . "</td></tr>";
                }
            }
            $tc.= "</table>";
            // $tc .= print_r($response, true) . "<br />";
        }
    }
    
    return $tc;
}

function getSwitches($returl, $endpt, $access_token) {
    $tc = "";
    
    $host = $endpt . "/switches";
    $headertype = array("Authorization: Bearer " . $access_token);
    $nvpreq = "client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app&client_id=" . urlencode(CLIENT_ID);
    $response = curl_call($host, $headertype, $nvpreq, "POST");


    $sensornames = array();
    $i= 0;        
    if ( $response && is_array($response) && count($response) ) {
        // $tc.= "<br /><br /><form name=\"setswitch\" action=\"" . $returl . "\"  method=\"POST\">";
        $tc.= "<br /><br /><form name=\"setswitch\" method=\"POST\">";
        $tc.= hidden("setswitch", "1");
        $tc.= "<table class=\"sensortable\" align=\"left\" width=\"900\">";
        foreach ($response as $k => $thesensor) {
            $i++;
            // $ipos = strpos($thesensor["name"]," ");
            // if ( !$ipos ) $ipos = count($thesensor[$k]["name"]);
            if ($thesensor["value"]=="on") $bclass = "greendot";
            else $bclass = "reddot";
            $bid = $thesensor["id"];
            $bname = "sw-$i";
            
            $tc.= "<tr><td>" . $thesensor["name"] . "</td><td>" .
                "<input name=\"$bname\" id=\"$bid\" class=\"$bclass\" type=\"button\" value=\"" .
                $thesensor["value"] . "\" /></td><td>&nbsp;</td></tr>";
            $sensornames[] = $thesensor["name"];
        }
        // $tc .= "<tr><td colspan=\"3\" align=\"right\"><input class=\"paybutton\" value=\"Set Switch\" name=\"submit3\" type=\"submit\" /></td></tr>";
        $tc.= "</table>";
        // $tc .= print_r($response, true);
        $tc.= "</form>";
        
    } else {
        $tc.= "<br />Problem encountered<br />" . "host = $host <br />" . print_r($response, true);
    }
        
    // save sensor names for later use
    $_SESSION["switchnames"] = $sensornames;
    
    return $tc;
}

function setSwitch($returnURL, $endpt, $access_token, $swid, $swval) {
    
    $host = $endpt . "/setswitch";
    $headertype = array("Authorization: Bearer " . $access_token);

    $nvpreq = "client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app&client_id=" . urlencode(CLIENT_ID) .
              "&swid=" . urlencode($swid) . "&swvalue=" . urlencode($swval);

    curl_call($host, $headertype, $nvpreq, "POST");

    return $response;
}


function getDoors($returl, $endpt, $access_token) {
    $tc = "";
    
    $host = $endpt . "/contacts";
    $headertype = array("Authorization: Bearer " . $access_token);
    $nvpreq = "client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app&client_id=" . urlencode(CLIENT_ID);
    $response = curl_call($host, $headertype, $nvpreq, "POST");


    $sensornames = array();
    $i= 0;        
    if ( is_array($response) && count($response) ) {
        $tc.= "<br /><br /><form name=\"setswitch\" action=\"" . $returl . "\"  method=\"POST\">";
        // $tc.= "<br /><br /><form name=\"doordetails\" method=\"POST\">";
        $tc.= hidden("doordetail", "1");
        $tc.= "<table class=\"sensortable\" align=\"left\" width=\"900\">";
        foreach ($response as $k => $thesensor) {
            $i++;
            // $ipos = strpos($thesensor["name"]," ");
            // if ( !$ipos ) $ipos = count($thesensor[$k]["name"]);
            $tc.= "<tr><td>" . $thesensor["name"] . "</td><td>" . $thesensor["value"] . "</td><td><input type=\"checkbox\" name=\"contactgroup[]\" value=\"$i\" /></td></tr>";
            $sensornames[] = $thesensor["name"];
        }
        $tc .= "<tr><td colspan=\"3\" align=\"right\"><input class=\"paybutton\" value=\"Door Details\" name=\"submit4\" type=\"submit\" /></td></tr>";
        $tc.= "</table>";
        // $tc .= print_r($response, true);
        $tc.= "</form>";
        
    } else {
        $tc.= "<br />Problem encountered<br />" . "host = $host <br />" . print_r($response, true);
    }
        
    // save sensor names for later use
    $_SESSION["doornames"] = $sensornames;
    
    return $tc;
}

// this makes a basic call to get the sensor status and return as a formatted table
function showDoorDetails($returl, $endpt, $access_token, $sensorname) {
    $tc = "";

    if ( isset($_SESSION["doornames"]) ) {

        $host = $endpt . "/onecontact?picked=" . urlencode($sensorname);
        $headertype = array("Authorization: Bearer " . $access_token);
        // $nvpstr = "picked=" . urlencode($sensorname);
        $response = curl_call($host, $headertype);

        if ( count($response) ) {
            $tc.= "<br /><br /><h4>Details for contact: [" . $sensorname . "]</h3>";
            
            $history = $response["history"];
    
            $tc.= "<table class=\"sensortable\" align=\"left\" width=\"900\">";
            $tc.= "<tr><td>" . "Contact Type" . "</td><td>" . "Calendar Date" . "</td><td>" . "UNIX Time" . "</td></tr>";
            foreach($history as $timestamp) {
                if ($timestamp["name"] == "contact") {
                    $tc.= "<tr><td>" . $timestamp["value"] . "</td><td>" . $timestamp["date"] . "</td><td>" . $timestamp["unixTime"] . "</td></tr>";
                }
            }
            $tc.= "</table>";
            if (DEBUG) $tc .= "<br />" . print_r($response, true) . "<br />";
        }
    } else {
        $tc.= "showDoorDetails: $returl, $endpt, $access_token, $sensorname";
    }
    
    return $tc;
}

// get name of this file
$serverName = $_SERVER['SERVER_NAME'];
$serverPort = $_SERVER['SERVER_PORT'];
$uri = $_SERVER['REQUEST_URI'];

$ipos = strpos($uri, '?');
if ( $ipos > 0 ) {  
    $uri = substr($uri, 0, $ipos);
}
$sturi = "";
$endpts = array();

if ( $_SERVER['HTTPS'] && $_SERVER['HTTPS']!="off" ) {
   $url = "https://" . $serverName . ':' . $serverPort;
} else {
   $url = "http://" . $serverName . ':' . $serverPort;
}
$returnURL = $url . $uri;

$tc = "";
$numendpts = 0;
// $access_token = $_SESSION["access_token"];

// check if this is a return from a code authorization call
if ( isset($_GET["code"]) && count($_GET)>0 ) {

    // grab the returned code and make the next call
    $code = $_GET[code];
    $_SESSION["access_code"] = $code;
    
    // make call to get the token
    $token = getAccessToken($returnURL, $code);
    
    // echo "<br />Token set to $token<br />";
	// get the endpoint if the token is valid - returns count
	// actual end points stored in a session variable
	if ($token) {
        $_SESSION["access_token"]= $token;
	    $endpoints = getEndPoints($returnURL, $token);
  	    if ($endpoints) $_SESSION["access_endpoints"] = $endpoints;
	}

    // refresh page to remove GET parameters
    $location = $returnURL;
    header("Location: $location");
	
// check for call to start a new authorization process
} elseif ( isset($_POST["doauthorize"]) ) {

    // clear access token session variables
    unset($_SESSION["access_code"]);
    // unset($_SESSION["access_token"]);
    unset($_SESSION['curl_error_no']);
    unset($_SESSION['curl_error_msg']);
    unset($_SESSION['access_endpoints']);

	getAuthCode($returnURL);
	$tc = "";
	exit(0);

// otherwise this is the initial call so prompt user and redirect to self to get auth code
}

    // print_r($_SESSION);
    // create the form to ask user to process final payment
    $tc .= "<h2>" . APPNAME . "</h2>";
    $first = false;

    // check for valid available token and access points
    if ( !FORCED && isset($_SESSION["access_token"]) && isset($_SESSION["access_endpoints"]) ) {
        // $access_code = $_SESSION["access_code"];
        $access_token = $_SESSION["access_token"];
        $endpoints = $_SESSION["access_endpoints"];
        $numendpts = count($endpoints);

        if (DEBUG) {       
            $tc.= "<div>";
            // $tc.= "Access code = $access_code<br />";
            $tc.= "Access token = $access_token<br />";
            $tc.= "$numendpts EndPoints Available:<br />";
            $tc.= "</div>";
        }
        $i = 0;
        
        foreach ($endpoints as $value) {
            if (!is_array($value)) {
                $first = true;
                $tc.= "<h3>You must authorize this web service to access SmartThings before using</h3>";
                break;
            }
            
            $endclientid = $value["oauthClient"]["clientId"];        
            $endclientsecret = $value["oauthClient"]["clientSecret"];        
            $endpoint = $value["uri"];
            
            // keep collection of end points for later use
            $endpts[] = $endpoint;
            
            $base_url = $value["base_url"];
            $access_url = $value["url"];
            
            if (DEBUG) {
                $tc.= "<div class=\"endpts\">";
                $tc.= "&nbsp;&nbsp;Pt #$i: uri = " . $endpoint . "<br />";
                // $tc.= "&nbsp;&nbsp;Pt #$i: base_url = " . $base_url . "<br />";
                // $tc.= "&nbsp;&nbsp;Pt #$i: access_url = " . $access_url . "<br />";
                $tc.= "&nbsp;&nbsp;Pt #$i: clientid = " . $endclientid . "<br />";
                $tc.= "&nbsp;&nbsp;Pt #$i: secret   = " . $endclientsecret . "<br />";
                $tc.= "</div>";
            }
            $i++;

        }
        
        // $tc.= "<h3>Use form below to re-authorize and obtain a new access token</h3>";
    } else {
        $first = true;
        $tc.= "<h3>You must authorize this web service to access SmartThings before using</h3>";
    }

    // check for switch setting Ajax call
    if (isset($_POST["id"]) && isset($_POST["useajax"]) && isset($_POST["value"])) {

        $swval = $_POST["value"];
        $swid = $_POST["id"];
        // $tc.= "<br />Post variables: <br />";
        // $tc.= print_r($_POST, true);

        // get the next endpoint used for detailed sensor reading
        $endpt = $endpts[0];
        setSwitch($returnURL, $endpt, $access_token, $swid, $swval);
        exit(0);
        
    }
    
    // $tc .= print_r($_POST,true);

    // check for errors
    if ( isset($_SESSION['curl_error_no']) ) {
        $tc.= "<br /><div>Errors detected<br />";
        $tc.= "Error number: " . $_SESSION['curl_error_no'] . "<br />";
        $tc.= "Error msg:    " . $_SESSION['curl_error_msg'] . "</div>";
        unset($_SESSION['curl_error_no']);
        unset($_SESSION['curl_error_msg']);
        
    // display the main page options
    } else if ( $access_token && $endpts && count($endpts) ) {
    
        // perform sensor update for main functionality
        $endpt = $endpts[0];
        $tc.= "<hr /";
        $tc.= getSwitches($returnURL, $endpt, $access_token);
        $tc.= "<hr /";
        $tc.= getSensors($returnURL, $endpt, $access_token);
        $tc.= "<hr /";
        $tc.= getDoors($returnURL, $endpt, $access_token);
        $tc.= "<hr /";
   
    }

    // check for details requested and provide history
    if (isset($_POST["sensordetail"]) && isset($_POST["sensorgroup"])) {
        $sensorOptions = $_POST["sensorgroup"];
        $sensornames = $_SESSION["sensornames"];
        // $tc.= "<br />Post variables: <br />";
        // $tc.= print_r($_POST, true);
        $tc.= "<br />";

        // get the next endpoint used for detailed sensor reading
        $endpt = $endpts[0];
        
        foreach ($sensorOptions as $sensor) {
            $sname = $sensornames[$sensor-1];
            // $tc.= "<br />Sensor value = $sname<br />";
            $tc.= showSensorDetails($returnURL, $endpt, $access_token, $sname);
        }
        $tc.= "<br />";
    }

    // check for details requested and provide history
    if (isset($_POST["doordetail"]) && isset($_POST["contactgroup"])) {
        $sensorOptions = $_POST["contactgroup"];
        $sensornames = $_SESSION["doornames"];
        // $tc.= "<br />Post variables: <br />";
        // $tc.= print_r($_POST, true);
        $tc.= "<br />";

        // get the next endpoint used for detailed sensor reading
        $endpt = $endpts[0];
        
        foreach ($sensorOptions as $sensor) {
            $sname = $sensornames[$sensor-1];
            // $tc.= "<br />Sensor value = $sname<br />";
            $tc.= showDoorDetails($returnURL, $endpt, $access_token, $sname);
        }
        $tc.= "<br />";
    }

    // special hidden parameter telling processor this is valid
    // $tc.= hidden("purchasing", "learnpianopurchase");
    $tc.= "<br /><br /><form name=\"housemap\" action=\"" . $returnURL . "\"  method=\"POST\">";
    $tc.= hidden("doauthorize", "1");

    $tc.= "<table width=\"900\"><tr><td><h4>Click here to authorize or to re-authorize your SmartThings</h4></td>";
    $tc .= "<td align=\"right\"><input class=\"paybutton\" value=\"Authorize\" name=\"submit1\" type=\"submit\" /></td></tr>";
    $tc.= "</table>";
    $tc.= "</form>";

    require_once ("housemap_header.php");
    echo $tc;
    require_once ("housemap_footer.php"); 
    
?>
