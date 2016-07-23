<?php
/*
* House Controller web service PHP application for SmartThings
* author: Ken Washington  (c) 2016
*
* general purpose routines included for authorizing a web service
* specific application shows all switches, motion sensors, and door contacts
* thermostats and doors to open and other capabilities will be added later
* uses Ajax to query specific sensor to return most recent 20 historical events
*
* for switches the Ajax call is used to turn the switch on and off from the web
* DEBUG flags can be used to show specific data details during installation
* this must be paired with a SmartApp on the SmartThings side
* and the CLIENT_ID and CLIENT_SECRET must match what is specified here
* to do this you must enable OAUTH2 in the SmartApp panel within SmartThings
*
* The endpoints must also match the names referenced in the routines below
* they are:
*   /sensors     for motion sensors
*   /contacts    for door contacts
*   /switches    for on/off switches including lights and dimmers
*   /onecontact  for returning the history of door contact things
*   /onemotion   for returning the history of motion sensors
*   /setswitch   for turning the switch on and off
*
* Other requirements for using this web app include installing jquery and pointing to it
* the references below are the directories that I use on my server but they can be anything
* you can even point it to the code.jquery site but I didn't like the delay and security risk
* 
* Finally, install this file and the accompanying .js and .css file on your server
* and you should be good to go. Don't forget to provide the CLIENT_ID and CLIENT_SECRET info
*
*/

ini_set('max_execution_time',90);
session_start();
define('APPNAME', 'House Controller');
define('CLIENT_ID', 'f7d5bdf7-c6a5-475d-95ce-25e49efb6436');
define('CLIENT_SECRET', 'b299a4b9-e2cf-48ef-995f-ed9c762ed820');
define('DEBUG', false);
define('DEBUG2', false);
define('DEBUG3', false);
define('DEBUG4', false);

// header and footer
function htmlHeader() {
    $tc = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
    $tc.= '<html><head><title>Smart Motion Sensor Authorization</title>';
    $tc.= '<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type">';
    $tc.= '<link rel="stylesheet" type="text/css" href="housemap.css">';
    $tc.= '<link rel="stylesheet" href="/jquery/jquery-ui-1.11.4/jquery-ui.css">';
    $tc.= '<script src="/jquery/jquery-3.1.0.min.js"></script>';
    $tc.= '<script src="/jquery/jquery-ui-1.11.4/jquery-ui.min.js"></script>';
    $tc.= '<script type="text/javascript" src="housemap3.js"></script>';
    $tc.= '</head><body>';
    $tc.= '<table class="maintable" align="left"><tbody>';
    // $tc.= '<tr><td><img height = "200px" src="https://d3abxbgmpfhmxi.cloudfront.net/assets/img/home-how-it-works-home-monitoring.1b50cecc.jpg"></td></tr>';
    $tc.= '<tr><td>';
    return $tc;
}

function htmlFooter() {
    $tc = "</td></tr></tbody></table></body></html>";
    return $tc;
}

// helper function to put a hidden field inside a form
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
    } else {
    	curl_setopt($ch, CURLOPT_POST, FALSE);
        if ($calltype!="GET") curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $calltype);
        if ($nvpstr) curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpstr);
    }

	//getting response from server
    $response = curl_exec($ch);
    
    // handle errors
    if (curl_errno($ch)) {
        // moving to display page to display curl errors
        $_SESSION['curl_error_no']=curl_errno($ch) ;
        $_SESSION['curl_error_msg']= curl_error($ch) . 
                 "<br />host= $host .
                  <br />headertype= $headertype .
                  <br />nvpstr = $nvpstr . 
                  <br />response = " . print_r($response,true);
        // $location = "authfailure.php";
        // header("Location: $location");  
        // echo "Error from curl<br />" . $_SESSION['curl_error_msg'];
        $nvpResArray = false;
    } else {
        $nvpResArray = json_decode($response, TRUE);
        if (!$nvpResArray) {
            $nvpResArray = "Error: Json not returned from curl<br />";
        }
    }
    curl_close($ch);

    return $nvpResArray;
}

function getResponse($host, $access_token) {

    $headertype = array("Authorization: Bearer " . $access_token);
    $nvpreq = "client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app&client_id=" . urlencode(CLIENT_ID);
    $response = curl_call($host, $headertype, $nvpreq, "POST");
    return $response;
}

// function to get authorization code
// this does a redirect back here with results
function getAuthCode($returl)
{
    unset($_SESSION['curl_error_no']);
    unset($_SESSION['curl_error_msg']);

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

// get the first endpoint
// this is what the endpoints returned array looks like
// I have no idea why it returns two endpoints but using the first one works
/*
Array ( 
  [0] => 
  Array ( 
    [oauthClient] => Array ( [clientId] => f7d5bdf7-c6a5-475d-95ce-25e49efb6436 ) 
    [location] => Array ( 
                    [id] => 37f57f9b-93db-42f6-ab74-48f1e225be5a 
                    [name] => KenJanaHome ) 
    [uri] => https://graph.api.smartthings.com:443/api/smartapps/installations/afd2b738-0fbb-4fb9-89ff-24603f951b68 
    [base_url] => https://graph.api.smartthings.com:443 
    [url] => /api/smartapps/installations/afd2b738-0fbb-4fb9-89ff-24603f951b68 
  ) 
  [1] => 
  Array ( 
    [oauthClient] => Array ( [clientId] => f7d5bdf7-c6a5-475d-95ce-25e49efb6436 ) 
    [location] => Array ( 
                    [id] => 37f57f9b-93db-42f6-ab74-48f1e225be5a 
                    [name] => KenJanaHome ) 
    [uri] => https://graph.api.smartthings.com:443/api/smartapps/installations/eaf68d76-fc56-4d2d-91cf-9deb706bc439 
    [base_url] => https://graph.api.smartthings.com:443 
    [url] => /api/smartapps/installations/eaf68d76-fc56-4d2d-91cf-9deb706bc439 
  ) 
)
*/	    
function getEndpoint($returl, $access_token) {

    $host = "https://graph.api.smartthings.com/api/smartapps/endpoints";
    $headertype = array("Authorization: Bearer " . $access_token);
    $response = curl_call($host, $headertype);

    $endpt = false;
    $sitename = "";
    if ($response && is_array($response)) {
	    $endclientid = $response[0]["oauthClient"]["clientId"];
	    if ($endclientid == CLIENT_ID) {
    	    $endpt = $response[0]["uri"];
    	    $sitename = $response[0]["location"]["name"];
	    }
    }
    return array($endpt, $sitename);

}

function authButton($returl) {
    $tc = "";
    $tc.= "<form name=\"housemap\" action=\"" . $returl . "\"  method=\"POST\">";
    $tc.= hidden("doauthorize", "1");

    $tc.= "<table width=\"900\"><tr><td><h3>Click here to authorize or re-authorize SmartThings</h3></td>";
    $tc .= "<td align=\"left\"><input class=\"authbutton\" value=\"Authorize\" name=\"submit1\" type=\"submit\" /></td></tr>";
    $tc.= "</table>";
    $tc.= "</form>";
    return $tc;
}

function convertDate($indate, $priordate) {

    $indate = substr($indate,0,10) . " " . substr($indate,11,8) . " UTC";
    
    // sample time:  2016-07-11T21:58:47.708Z
    $utime = strtotime($indate);
    // $utime = $indate;
    $outdate = date("M m  h:i:s A", $utime);
    // $outdate = strftime("%b %d  %I:%M:%S %p", $utime);

    if ($priordate) {
        $priordate = substr($priordate,0,10) . " " . substr($priordate,11,8) . " UTC";
        $ptime = $utime - strtotime($priordate);
        // $ptime = abs($utime - $priordate);
        $hours = (int) ($ptime/3600);
        $min = (int) (($ptime - $hours*3600) / 60);
        $sec = (int) ($ptime - $hours*3600 - $min*60);
        $outdate .= " &nbsp;&nbsp; " . sprintf("%02d:%02d:%02d",$hours,$min,$sec); 
        // $outdate .= " &nbsp;&nbsp; " . strftime("%H:%M:%S", $ptime);
    }

    return $outdate;
}

function sortsensor($a, $b) {
    $atime = strtotime($a["date"]);
    $btime = strtotime($b["date"]);
    if ($atime==$btime) return 0;
    return ($atime < $btime) ? -1 : 1;
}

function sortswitch($a, $b) {
    $atime = $a["name"];
    $btime = $b["name"];
    if ($atime==$btime) return 0;
    return ($atime < $btime) ? -1 : 1;
}

function getSwitches($returl, $endpt, $access_token) {
    $tc = "";
    
//    $host = $endpt . "/switches";
//    $headertype = array("Authorization: Bearer " . $access_token);
//    $nvpreq = "client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app&client_id=" . urlencode(CLIENT_ID);
//    $response = curl_call($host, $headertype, $nvpreq, "POST");

    $response = getResponse($endpt . "/switches", $access_token);
    $i= 0;
    $tc.= "<div id=\"switchtab\">";
    if ( $response && is_array($response) && count($response) ) {
    
        uasort($response, "sortswitch");
    
        $tc.= "<form name=\"setswitch\" method=\"POST\">";
        $tc.= hidden("setswitch", "1");
        $tc.= "<table class=\"switchtable\" align=\"left\" width=\"900\">";
        foreach ($response as $k => $thesensor) {
            $i++;
            if ($thesensor["value"]=="on") $bclass = "swon";
            else $bclass = "swoff";
            $bid = $thesensor["id"];
            $bname = "sw-$i";
            // go to new row every other switch
            if ( $i % 3 == 1 ) $tc.= "<tr>";
            $tc.= "<td width=\"1\">" . " " . "</td>" .
                "<td><input name=\"$bname\" id=\"$bid\" class=\"$bclass\" type=\"button\" value=\"" .
                $thesensor["name"] . "\" /></td><td width=\"1\">&nbsp;</td>";
            if ( $i % 3 == 0 ) $tc.= "</tr>";
        }
        //finish out table with odd numbers of items
        if ($i % 3 == 1) {
            $tc.= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
            $tc.= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
        }
        else if ($i % 3 == 2) {
            $tc.= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
        }
        $tc.= "</table>";
        $tc.= "</form>";
        if (DEBUG3) $tc .= "<br />" . print_r($response, true);
        
    } else {
        $tc.= "<div class=\"error\">Problem encountered retrieving switches.<br />host = $host <br /><pre>" . print_r($response, true) . "</pre></div>";
    }
    $tc.= "</div>";
        
    return $tc;
}

function setSwitch($returnURL, $endpt, $access_token, $swid, $swval) {
    
    $host = $endpt . "/setswitch";
    $headertype = array("Authorization: Bearer " . $access_token);

    $nvpreq = "client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app&client_id=" . urlencode(CLIENT_ID) .
              "&swid=" . urlencode($swid) . "&swvalue=" . urlencode($swval);
    curl_call($host, $headertype, $nvpreq, "POST");

}

// this makes a basic call to get the sensor status and return as a formatted table
function getMotions($returl, $endpt, $access_token) {
    $tc = "";
    
    $host = $endpt . "/sensors";
    $headertype = array("Authorization: Bearer " . $access_token);
    $nvpreq = "client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app&client_id=" . urlencode(CLIENT_ID);
    $response = curl_call($host, $headertype, $nvpreq, "POST");

    $i= 0;        
    $tc.= "<div id=\"sensortab\">";
    if ( $response && is_array($response) && count($response) ) {
        $tc.= "<table class=\"sensorsection\" width=\"900\"><tr><td valign=\"top\" width=\"500\">";
        $tc.= "<form name=\"sensordetails\" action=\"" . $returl . "\"  method=\"POST\">";
        $tc.= hidden("sensordetail", "1");
        $tc.= "<table class=\"sensortable\" align=\"center\" width=\"450\">";
        foreach ($response as $k => $thesensor) {
            $i++;
            $bid = $thesensor["id"];
            $bname = "motion-$i";
            $tc.= "<tr><td name=\"$bname\" class=\"sensoroff\" id=\"$bid\">" . $thesensor["name"] . 
                  "</td><td>" . $thesensor["value"] . 
                  "</td></tr>";
        }
        if (DEBUG4) $tc.= "<tr><td colspan=\"2\">host = $host</td></tr>";
        $tc.= "</table></form></td>";
        $tc.= "<td valign=\"top\" width=\"400\" height=\"400\" id=\"sensordata\"></td></tr></table>";
    
    } else {
        $tc.= "<div class=\"error\">Problem encountered retrieving sensors.<br />host = $host <br /></div>";
    }
    if (DEBUG3) $tc .= "<br />" . print_r($response, true);
    $tc.="</div>";
        
    return $tc;
}


function getContacts($returl, $endpt, $access_token) {
    $tc = "";
    
    $host = $endpt . "/contacts";
    $headertype = array("Authorization: Bearer " . $access_token);
    $nvpreq = "client_secret=" . urlencode(CLIENT_SECRET) . "&scope=app&client_id=" . urlencode(CLIENT_ID);
    $response = curl_call($host, $headertype, $nvpreq, "POST");

    $i= 0;
    $tc.= "<div id=\"doortab\">";
    if ( $response && is_array($response) && count($response) ) {

        $tc.= "<table class=\"sensorsection\" width=\"900\"><tr><td valign=\"top\" width=\"500\">";
        $tc.= "<form name=\"doordetails\" action=\"" . $returl . "\"  method=\"POST\">";
        $tc.= hidden("doordetail", "1");
        $tc.= "<table class=\"sensortable\" align=\"center\" width=\"450\">";

        foreach ($response as $k => $thesensor) {
            $i++;
            $bid = $thesensor["id"];
            $bname = "contact-$i";
            $tc.= "<tr><td name=\"$bname\" class=\"sensoroff\" id=\"$bid\">" . $thesensor["name"] . 
                  "</td><td>" . $thesensor["value"] . 
                  "</td></tr>";
        }
        if (DEBUG4) $tc.= "<tr><td colspan=\"2\">host = $host</td></tr>";
        $tc.= "</table></form></td>";
        $tc.= "<td valign=\"top\" width=\"400\" height=\"400\" id=\"doordata\"></td></tr></table>";
        
    } else {
        $tc.= "<div class=\"error\">Problem encountered retrieving contacts.<br />host = $host <br /></div>";
    }
    if (DEBUG3) $tc .= "<br />" . print_r($response, true);
    $tc.="</div>";
    
    return $tc;
}

// this makes a basic call to get the sensor status and return as a formatted table
function showDetails($endpt, $access_token, $sensortype, $sensorname) {
    $tc = "";
    
    $host = $endpt . urlencode($sensorname);
    $headertype = array("Authorization: Bearer " . $access_token);
    $response = curl_call($host, $headertype);

    if ( $response && is_array($response) && count($response) && ($history = $response["history"]) && is_array($history) ) {
        $tc.= "<h3>History for " . $sensorname . "</h3>";
        $hsize = count($history);
        $shaded = "shaded";
        $tc.= "<table class=\"sensortable\" align=\"left\" width=\"400\">";
        $tc.= "<tr class=\"theader\"><td>" . ucfirst($sensortype) . " Status" . "</td><td>" . "Date / Time" . "</td></tr>";
        foreach($history as $k => $timestamp) {
            if ($timestamp["name"] == $sensortype) {
                $shaded = ($shaded =="shaded") ? "clear" : "shaded";
                $olddate = ($k < $hsize) ? $history[$k+1]["date"] : false;
                $fulldate = convertDate($timestamp["date"], $olddate);
                $tc.= "<tr class=\"$shaded\"><td>" . $timestamp["value"] . "</td><td>" . $fulldate . "</td></tr>";
            }
        }
        $tc.= "</table>";
    } else {
        $tc = "<div class=\"error\">Error retrieving data for " . ucfirst($sensortype) . " SmartThing with Name: $sensorname</div>";
    }
    if (DEBUG3) $tc .= "<br />History: <br />" . print_r($response, true);
    
    return $tc;
}

// *** main routine ***

    // set timezone so dates work where I live instead of where code runs
    date_default_timezone_set("America/Detroit");
    
    // save authorization for this app for about one month
    $expiry = time()+31*24*3600;
    
    // get name of this webpage without any get parameters
    $serverName = $_SERVER['SERVER_NAME'];
    $serverPort = $_SERVER['SERVER_PORT'];
    $uri = $_SERVER['REQUEST_URI'];
    
    $ipos = strpos($uri, '?');
    if ( $ipos > 0 ) {  
        $uri = substr($uri, 0, $ipos);
    }
    
    if ( $_SERVER['HTTPS'] && $_SERVER['HTTPS']!="off" ) {
       $url = "https://" . $serverName . ':' . $serverPort;
    } else {
       $url = "http://" . $serverName . ':' . $serverPort;
    }
    $returnURL = $url . $uri;
    
    // check if this is a return from a code authorization call
    if ( isset($_GET["code"]) && count($_GET)>0 ) {
    
        // grab the returned code and make the next call
        $code = $_GET[code];
        
        // check for manual reset flag for debugging purposes
        if ($code=="0" || $code=="reset") {
        	getAuthCode($returnURL);
    	    exit(0);
        }
        
        // make call to get the token
        $token = getAccessToken($returnURL, $code);
        
        // get the endpoint if the token is valid
        if ($token) {
            setcookie("hmtoken", $token, $expiry, "/", $serverName);
            $endptinfo = getEndpoint($returnURL, $token);
            $endpt = $endptinfo[0];
            $sitename = $endptinfo[1];
        
            // save endpt in a cookie and set success flag for authorization
            if ($endpt) {
                setcookie("hmendpoint", $endpt, $expiry, "/", $serverName);
                setcookie("hmsitename", $sitename, $expiry, "/", $serverName);
            }
                    
        }
    
        if (DEBUG2) {
            echo "<br />serverName = $serverName";
            echo "<br />returnURL = $returnURL";
            echo "<br />code  = $code";
            echo "<br />token = $token";
            echo "<br />endpt = $endpt";
            echo "<br />sitename = $sitename";
            echo "<br />cookies = <br />";
            print_r($_COOKIE);
            exit;
        }
    
        // reload the page to remove GET parameters and activate cookies
        $location = $returnURL;
        header("Location: $location");
    	
    // check for call to start a new authorization process
    } elseif ( isset($_POST["doauthorize"]) ) {
    
    	getAuthCode($returnURL);
    	exit(0);
    
    }

// *** handle the Ajax calls here ***
    
    // check for switch setting Ajax call
    if (isset($_POST["useajax"]) && isset($_POST["value"]) && isset($_POST["id"])) {
        $swval = $_POST["value"];
        $swid = $_POST["id"];
        setSwitch($returnURL, $endpt, $access_token, $swid, $swval);
        exit(0);
    }

    // check for sensor Ajax call - multiple types can be handled
    // as long as the access point is named "oneXXXXXX" where XXXXXX is the sensor type
    // and the name of the sensor to be acted upon in the smartapp is in variable value
    // the smartapp must use the get variable "picked"
    if (isset($_POST["sensorajax"]) && isset($_POST["type"]) && isset($_POST["value"])) {
        $stype = $_POST["type"];
        $sname = stripslashes(urldecode($_POST["value"]));
        header('Content-Type: text/html; charset=utf-8');
        $endptmain = $endpt . "/one" . $stype . "?picked=";
        echo showDetails($endptmain, $access_token, $stype, $sname);
        exit(0);
    }


// otherwise this is either the initial call or a content load or ajax call
    $tc = "";
    $tc .= "<h2>" . APPNAME . "</h2>";
    $tc .= authButton($returnURL);

    $first = false;

    // check for valid available token and access point
    if ( isset($_COOKIE["hmtoken"]) && isset($_COOKIE["hmendpoint"]) ) {
    
        $access_token = $_COOKIE["hmtoken"];
        $endpt = $_COOKIE["hmendpoint"];
        $sitename = $_COOKIE["hmsitename"];

        if (DEBUG) {       
            $tc.= "<div class=\"debug\">";
            $tc.= "access_token = $access_token<br />";
            $tc.= "endpt = $endpt<br />";
            $tc.= "sitename = $sitename<br />";
	        $tc.= "<br />cookies = <br />";
	        $tc.= print_r($_COOKIE, true);
            $tc.= "</div>";
        }

        // double cheeck to make sure the cookies were valid
        if (!$endpt || !$access_token) {
            $first = true;
            $tc.= "<h3>You must authorize this web service to access SmartThings before using</h3>";
        }
        
    } else {
        $first = true;
        $tc.= "<h3>You must authorize this web service to access SmartThings before using</h3>";
    }

    // check for errors
    if ( isset($_SESSION['curl_error_no']) ) {
        $tc.= "<br /><div>Errors detected<br />";
        $tc.= "Error number: " . $_SESSION['curl_error_no'] . "<br />";
        $tc.= "Found Error msg:    " . $_SESSION['curl_error_msg'] . "</div>";
        unset($_SESSION['curl_error_no']);
        unset($_SESSION['curl_error_msg']);
        
    // display the main page
    } else if ( $access_token && $endpt ) {
    
        if ($sitename) {
            $tc.= "<h3>Access granted to location: $sitename</h3>";
        }
        $tc.= '<div id="tabs">
                 <ul>
                 <li><a href="#switchtab">Switches</a></li>
                 <li><a href="#sensortab">Motion Sensors</a></li>
                 <li><a href="#doortab">Doors</a></li>
                 </ul>';
        $tc.= getSwitches($returnURL, $endpt, $access_token);
        $tc.= getMotions($returnURL, $endpt, $access_token);
        $tc.= getContacts($returnURL, $endpt, $access_token);
        $tc.= "</div>";
   
    } else {

// this should never ever happen...
        if (!$first) {
            echo "<br />Something went wrong";
            echo "<br />access_token = $access_token";
            echo "<br />endpoint = $endpt";
            echo "<br />tc dump: <br />";
            echo $tc;
            exit;    
        }
    
    }

    // display the dynamically created web site
    echo htmlHeader();
    echo $tc;
    echo htmlFooter();
    
?>
