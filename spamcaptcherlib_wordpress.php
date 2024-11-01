<?php
/*
 * This is a PHP library that handles calling SpamCaptcher.
 *    - Documentation and latest version
 *          https://www.spamcaptcher.com/captcha/purePHP.jsp
 *    - Get a SpamCaptcher API Key
 *          https://www.spamcaptcher.com
 *
 * Copyright (c) 2011 SpamCaptcher -- https://www.spamcaptcher.com
 * AUTHOR:
 *   Kieran Miller
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class SpamCaptcher
{
    /**
	 * Whether or not the user authenticated the session
	 * (either solved the CAPTCHA or used a TrustMe Account)
	 */
    private $isValid = false;
	
	/**
	 * How likely session is to contain spam.
	 */
    private $spamScore = 0;
	
	/**
	 * Unique identifer for your account. Can be made public.
	 */
    private $accountID = "663d6a9abf99c7635619f2f7657c02fb913854fb57293923";
	
	/**
	 * Private key for your account. Do NOT show to anyone else.
	 */
    private $privateKey = "098f6bcd4621d373cade4e832627b4f6";
	
	/**
	 * The language or framework identifier.
	 */
	private $languageOrFrameworkID = 'wp';
	
	/**
	 * The version of the SpamCaptcher library you are using.
	 */
	private $languageOrFrameworkVersion = "1.2.0";
	
	/**
	 * The unique identifer for the session.
	 */
    private $sessionID;
	
	/**
	 * The unique session ID generated by your site.
	 */
    private $customerSessionID;
	
	/**
	 * The value for the settings object in JavaScript.
	 * See our Client-Side Scripting API for more information.
	 */
    private $initSettings = "{}";
	
	/**
	 * Whether to use SSL during server-side validation.
	 * Note: This does NOT have to do with whether your 
	 * site is accessed over SSL (i.e. https).
	 */
    private $useSSL = true;
	
	/**
	 * Whether to force the user to authenticate
	 * the session with their TrustMe Account.
	 */
    private $forceTrustMeAccount = false;
	
	/**
	 * Whether to allow the user to authenticate
	 * the session with their TrustMe Account.
	 */
    private $allowTrustMeAccount = true;
	
	/**
	* Whether to overwrite your Global TrustMe
	* Account settings you have set at https://www.spamcaptcher.com.
	*/
	private $overwriteGlobalTMASettings = false;
	
	/**
	* Whether to use a proof-of-work challenge 
	* instead of the normal CAPTCHA.
	*/
	private $useProofOfWorkCaptcha = false;
	
	/**
	* The average amount of time, in seconds, the
	* proof-of-work should take a "standard" computer.
	*/
	private $proofOfWorkTime = 1;
	
	/**
	 * The maximum spam score that will result in
	 * a recommended action of SHOULD_PASS.
	 */
    private $MAX_PASSABLE_SCORE = 35;
	
	/**
	 * The maximum spam score that will result in
	 * a recommended action of SHOULD_MODERATE.
	 */
    private $MAX_MODERATE_SCORE = 99;
	
	/**
	 * The expected amount of time a user will take to
	 * complete the form. This is only used when there
	 * are problems communicating with the SpamCaptcher
	 * server.
	 */
    private $timeToCompleteForm = 300;
	
	/**
	 * Recommended action indicating that the session is not spam.
	 */
    public static $SHOULD_PASS = 0;
	
	/**
	 * Recommended action indicating that the session may be spam
	 * and requires you to manually moderate it. 
	 */
    public static $SHOULD_MODERATE = 1;
	
	/**
	 * Recommended action indicating that the session is spam or 
	 * otherwise malicious and that you should delete/ignore the contents.
	 */
    public static $SHOULD_DELETE = 2;
	
	/**
	 * Flag value for a duplicate post.
	 */
    public static $FLAG_FOR_MULTI_POST = 1;
	
	/**
	 * Flag value for a post that violates your Terms of Services.
	 */
    public static $FLAG_FOR_VIOLATES_TOS = 2;
	
	/**
	 * Flag value for a post that is spam.
	 */
    public static $FLAG_FOR_SPAM = 3;
	
	/**
	 * User action for account registration.
	 */
    public static $USER_ACTION_ACCOUNT_REGISTRATION = "ar";
	
	/**
	 * User action for recovering or resetting a forgotten password.
	 */
    public static $USER_ACTION_FORGOT_PASSWORD = "fp";
	
	/**
	 * User action for changing the user's settings.
	 */
    public static $USER_ACTION_CHANGE_SETTINGS = "cs";
	
	/**
	 * User action for unlocking a locked account.
	 */
    public static $USER_ACTION_UNLOCK_ACCOUNT = "ua";
	
	/**
	 * User action for logging in to an account.
	 */
    public static $USER_ACTION_ACCOUNT_LOGIN = "al";
	
	/**
	 * User action for creating a post.
	 */
    public static $USER_ACTION_CREATE_POST = "cp";
	
	/**
	 * User action for leaving a comment.
	 */
    public static $USER_ACTION_LEAVE_COMMENT = "lc";
	
	/**
	 * User action for uploading data.
	 */
    public static $USER_ACTION_UPLOAD_DATA = "ud";
	
	/**
	 * User action for deleting data.
	 */
    public static $USER_ACTION_DELETE_DATA = "dd";
	
	/**
	 * User action for viewing data.
	 */
    public static $USER_ACTION_VIEW_DATA = "vd";
	
	/**
	 * User action for making a purchase.
	 */
    public static $USER_ACTION_MAKE_PURCHASE = "mp";
	
	/**
	 * User action for casting a vote.
	 */
    public static $USER_ACTION_CAST_VOTE = "cv";
	
	/**
	 * The recommended action for the session.
	 */
    private $recommendedAction = 1; // default to moderate
	
	/**
	 * The base URL to make server-side API calls to.
	 */
    private $baseURL = "api.spamcaptcher.com";
	
	/**
	* The action that the user is taking.
	*/
	private $userAction = "";
	
	/**
	 * Constructor. If you do not wish to set the Account ID
	 * and Account Private Key just call this with no arguments.
	 */
	public function __construct($accID = null, $pwd = null) {
	   $this->setAccountID($accID);
	   $this->setPrivateKey($pwd);
	   $this->useSSL = $this->is_ssl_capable();
	}
   
	/**
	 * Sets the Account ID.
	 */
	public function setAccountID($accID){
		if (isset($accID)){
			$this->accountID = $accID;
		}
	}
	
	/**
	 * Sets the Account Private Key.
	 */
	public function setPrivateKey($pwd){
		if (isset($pwd)){
			$this->privateKey = $pwd;
		}
	}
	
	/**
	 * Sets the Customer Session ID (i.e. your unique identifier for the session).
	 */
	public function setCustomerSessionID($csessID){
		$this->customerSessionID = $csessID;
	}

	/**
	 * Returns the Customer Session ID (i.e. your unique identifier for the session).
	 */
	public function getCustomerSessionID(){
		return $this->customerSessionID;
	}
	
	/**
	 * Sets the SpamCaptcher Session ID.
	 */
	public function setSessionID($sessID){
		$this->sessionID = $sessID;
	}
	
	/**
	 * Returns the SpamCaptcher Session ID.
	 */
	public function getSessionID(){
		return $this->sessionID;
	}
	
	/**
	 * Returns the JavaScript settings object.
	 */
	public function getSettings(){
		return $this->initSettings;
	}
	
	/**
	 * Sets the JavaScript settings object.
	 */
	public function setSettings($settings){
		$this->initSettings = $settings;
	}
	
	/**
	 * Sets the minimum moderation score.
	 */
	public function setMinModerationScore($minMod){
		$this->MAX_PASSABLE_SCORE = $minMod - 1;
	}
	
	/**
	 * Sets the maximum moderation score.
	 */
	public function setMaxModerationScore($maxMod){
		$this->MAX_MODERATE_SCORE = $maxMod;
	}
	
	/**
	 * Returns the isValid value indicating whether the user authenticated the session
	 * (i.e. whether the user solved the CAPTCHA or used a TrustMe Account).
	 */
	public function getIsValid(){
		return $this->isValid;
	}
	
	/**
	 * Returns the recommended action 
	 * (i.e. either SHOULD_PASS, SHOULD_MODERATE or SHOULD_DELETE).
	 */
	public function getRecommendedAction(){
		return $this->recommendedAction;
	}
	
	/**
	 * Returns the spam score.
	 */
	public function getSpamScore(){
		return $this->spamScore;
	}
	
	/**
	 * Returns the user action.
	 */
	public function getUserAction(){
		return $this->userAction;
	}
	
	/**
	 * Sets the user action.
	 */
	public function setUserAction($user_action){
		$this->userAction = $user_action;
	}
	
	/**
	 * Returns whether server-to-server SSL will be used.
	 */
	public function getUseSSL(){
		return $this->useSSL;
	}
	
	/**
	 * Sets whether server-to-server SSL will be used.
	 * Note: Does NOT have to do with whether your site uses https.
	 */
	public function setUseSSL($use_ssl){
		$this->useSSL = $use_ssl;
	}

	/**
	 * Returns the expected time to complete the form.
	 */
	public function getTimeToCompleteForm(){
		return $this->timeToCompleteForm;
	}
	
	/**
	 * Sets the expected time to complete the form.
	 */
	public function setTimeToCompleteForm($timetocomplete){
		$this->timeToCompleteForm = $timetocomplete;
	}

	/**
	 * Sets whether the user can authenticate the session with a TrustMe Account.
	 */
	public function setAllowTrustMeAccount($allow_tma){
		$this->allowTrustMeAccount = $allow_tma;
		$this->overwriteGlobalTMASettings = true;
	}
	
	/**
	 * Sets whether the user MUST authenticate the session with a TrustMe Account.
	 */
	public function setForceTrustMeAccount($force_tma){
		$this->forceTrustMeAccount = $force_tma;
		$this->overwriteGlobalTMASettings = true;
	}
	
	/**
	 * Sets whether the session's TrustMe Account settings should be based on the global ones or session specific ones.
	 */
    public function setOverwriteGlobalTrustMeAccountSettings($overwrite_gtmas){
        $this->overwriteGlobalTMASettings = $overwrite_gtmas;
    }
	
	/**
	 * Sets whether a proof-of-work should be used instead of the standard CAPTCHA.
	 */
    public function useProofOfWork($usePOW){
        $this->useProofOfWorkCaptcha = $usePOW;
    }
	
	/**
	 * Sets the amount of time, in seconds, a "standard" computer should take to solve the proof-of-work.
	 */
    public function setProofOfWorkTime($powTime){
        $this->proofOfWorkTime = $powTime;
    }
	
	/**
	 * Returns the HTML code to employ our service (i.e. place our CAPTCHA) on your site.
	 */
	public function getCaptcha(){
		return "<script type=\"text/javascript\">var spamCaptcher ={settings : " . $this->initSettings . "};spamCaptcher.settings.accountID = \"" . $this->accountID . "\";</script><script type=\"text/javascript\" src=\"https://api.spamcaptcher.com/initCaptcha.js\"></script><noscript>SpamCaptcher NoScript Session:&nbsp;<input type=\"text\" name=\"spamCaptcherSessionID\" /><br /><iframe height=\"275px\" width=\"500px\" src=\"https://api.spamcaptcher.com/noscript/getCaptcha.jsp?k=" . $this->accountID ."&atma=" . ($this->allowTrustMeAccount ? "1" : "0") . "&ftma=" . ($this->forceTrustMeAccount ? "1" : "0") . "&ogtmas=" . ($this->overwriteGlobalTMASettings ? "1" : "0") . "\"><strong>Please upgrade your browser to one that supports iframes or enable JavaScript.</strong></iframe></noscript>";
	}
   
   /**
	* Sends a post request and returns the returned body.
	*/
   private function postData($host, $path, $useSSL, $data){
		// define port and protocol
		$port = 443;
		$protocol = "ssl://";
		if (!$useSSL){
			$port = 80;
			$protocol = "";
		}
		
		$poststring = "";
		foreach ($data as $key => $val){
			// build the string of data to use in POST
			if (is_array($val)){
				foreach ($val as $sub_key => $sub_val){
					$poststring .= urlencode($key) . "=" . urlencode($sub_val) . "&";
				}
			}else{
				$poststring .= urlencode($key) . "=" . urlencode($val) . "&";
			}
		}
		// strip off trailing ampersand
		$poststring = substr($poststring, 0, -1);
		
		// open the socket
		$fp = fsockopen($protocol . $host, $port, $errno, $errstr, $timeout = 30);
		
		if (!$fp){
			// couldn't open socket so returning no data
			return null;
		}
		
		// send the data to the server
		fputs($fp, "POST $path HTTP/1.1\r\n");
		fputs($fp, "Host: $host\r\n"); 
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
		fputs($fp, "Content-length: " . strlen($poststring) . "\r\n"); 
		fputs($fp, "Connection: close\r\n\r\n"); 
		fputs($fp, $poststring . "\r\n\r\n"); 

		$header = "";
		$body = "";
		
		// grab the header data ... not currently used but might be in the future
		do 
		{
			$header .= fgets ( $fp, 128 );
		} while ( strpos ( $header, "\r\n\r\n" ) === false ); // loop until the end of the header
		
		// grab the body data ... this is what is returned
		while ( ! feof ( $fp ) )
		{
			$body .= fgets ( $fp, 128 );
		}
		
		// close socket
		fclose($fp); 

		return $body;
   }
   
   /**
	* Validates the session and returns the recommended action.
    */
   public function validate($args){
		$responseReceived = false;
		if (!(isset($this->sessionID))){
			if ($this->serverDownShouldModerate()){
				// couldn't access the server, moderate the session
				$this->recommendedAction = self::$SHOULD_MODERATE;
			}else{
				// no session ID but the server hasn't experienced downtime.
				$this->recommendedAction = self::$SHOULD_DELETE;
			}
		}else{
			$args['lofi'] = $this->languageOrFrameworkID;
			$args['lofv'] = $this->languageOrFrameworkVersion;
			$args['k'] = $this->accountID;
			$args['pwd'] = $this->privateKey;
			$args['ua'] = $this->userAction;
            if (isset($this->customerSessionID)){
                $args['c'] = $this->customerSessionID;
            }
			$args['upow'] = $this->useProofOfWorkCaptcha ? "1" : "0";
			$args['powt'] = $this->proofOfWorkTime;
			$xmlresponse = $this->postData($this->baseURL, "/validate", $this->useSSL, $args);
			if ($xmlresponse){
				$doc = DOMDocument::loadXML($xmlresponse);
				if ($doc){
					$isValidResponse = $doc->getElementsByTagName('isValid');
					if (!($isValidResponse && $isValidResponse->item(0))){
						// got a response but it isn't in the expected format
						$this->recommendedAction = self::$SHOULD_MODERATE;
					}else{
						// parse out result from spamcaptcher server
						$this->spamScore = $doc->getElementsByTagName('spamScore')->item(0)->nodeValue;
						$this->isValid = $this->strToBoolean($doc->getElementsByTagName('isValid')->item(0)->nodeValue);
						$responseReceived = true;
					}
				}else{
					// got a response but it isn't in the expected format
					$this->recommendedAction = self::$SHOULD_MODERATE;
				}
			}else{
				// couldn't access the server, moderate the session
				$this->recommendedAction = self::$SHOULD_MODERATE;
			}
		}
		if ($responseReceived){
			if (!$this->isValid){
				// CAPTCHA was NOT solved correctly AND no TrustMe Account was used
				$this->recommendedAction = self::$SHOULD_DELETE;
			}else{
				if ($this->spamScore > $this->MAX_MODERATE_SCORE){
					// SpamScore is too high
					$this->recommendedAction = self::$SHOULD_DELETE;
				}elseif ($this->spamScore > $this->MAX_PASSABLE_SCORE){
					// SpamScore is questionable
					$this->recommendedAction = self::$SHOULD_MODERATE;
				}else{
					// Goldilocks is happy with the SpamScore
					// Yes yes, technically one of the scores
					// should have been "too low" for me to make
					// a Goldilocks reference ... I don't care.
					$this->recommendedAction = self::$SHOULD_PASS;
				}
			}
		}
		return $this->recommendedAction;
   }
   
   /**
	* Validates the session and returns the recommended action.
    */
   public function doValidation($value){
        $args = array (
			'ip' => $_SERVER['REMOTE_ADDR'],
			'id' => $value["spamCaptcherSessionID"],
			'ftma' => ($this->forceTrustMeAccount ? "1" : "0"),
			'atma' => ($this->allowTrustMeAccount ? "1" : "0"),
			'ogtmas' => ($this->overwriteGlobalTMASettings ? "1" : "0"),
			'spamCaptcherAnswer' => $value["spamCaptcherAnswer"]
		);
        $this->sessionID = $value["spamCaptcherSessionID"];
        return $this->validate($args);
   }
   
   /**
    * Validates the session and returns true if and only if recommended action is not SHOULD_DELETE.
    */ 
   public function isValid($value){
        return ($this->doValidation($value) != self::$SHOULD_DELETE);
   }
   
   /**
	* Flags the session identified by either the SpamCaptcher Session ID
	* or the Customer Session ID (only one is required but if you use the
	* Customer Session ID you must have supplied it to us during validation).
	*/
   public function flag($session_id, $csess_id, $flagType, $data){
		$args = array (
			'id' => $session_id,
			'c' => $csess_id,
			'k' => $this->accountID,
			'pwd' => $this->privateKey,
			'lofi' => $this->languageOrFrameworkID,
			'lofv' => $this->languageOrFrameworkVersion,
			'f' => "$flagType", 
			'data' => $data
		);
		$xmlresponse = $this->postData($this->baseURL, "/flag", $this->useSSL, $args);
   }
   
   /**
	* Encodes the given data into a query string format
	* @param $data - array of string elements to be encoded
	* @return string - encoded request
	*/
	private function spamcaptcher_qsencode ($data) {
		$req = "";
		if (!is_null($data)){
			foreach ( $data as $key => $value ){
				$strData = '';
				if (!is_null($value)){
					if (is_string($value)){
						$strData = $key . "=" . urlencode( stripslashes($value) ) . '&';
					}elseif (is_array($value)){
						foreach ($value as $val){
							$strData .= $key . "=" . urlencode( stripslashes($val) ) . '&';
						}
					}
				}
				$req .= $strData;
			}

			// Cut the last '&'
			$req=substr($req,0,strlen($req)-1);
		}
		return $req;
	}
	
	/**
	 * Determines whether a session should be moderated due to the SpamCaptcher API Server going down.
	 * Note: This is not an expected occurrence but rather a safeguard against unlikely downtime.
	 */
	public function serverDownShouldModerate(){
		$args = array (
			'k' => $this->accountID,
			'pwd' => $this->privateKey,
			'lofi' => $this->languageOrFrameworkID,
			'lofv' => $this->languageOrFrameworkVersion
		);
		$xmlresponse = $this->postData($this->baseURL,"/checkStatus",$this->useSSL,$args);
		$retVal = false;
		if ($xmlresponse){
			$doc = DOMDocument::loadXML($xmlresponse);
			if ($doc){
				$isRunningResponse = $doc->getElementsByTagName('isRunning');
				if (!($isRunningResponse && $isRunningResponse->item(0))){
					$retVal = true;
				}else{
					$retVal = !($this->strToBoolean($isRunningResponse->item(0)->nodeValue));
					if (!$retVal){
						$secondsSinceLastDowntime = $doc->getElementsByTagName('SecondsSinceLastDowntime')->item(0)->nodeValue;
						$secondsSinceLastRestart = $doc->getElementsByTagName('SecondsSinceLastRestart')->item(0)->nodeValue;
						if ($this->timeToCompleteForm > $secondsSinceLastRestart && $this->timeToCompleteForm < $secondsSinceLastDowntime){
							$retVal = true;
						}
					}
				}
			}else{
				$retVal = true;
			}
		}else{
			$retVal = true;
		}
		return $retVal;
	}
	
	/**
	 * Converts a string value to a boolean one. Returns true if and only if argument is "true".
	 */
	private function strToBoolean($value) {
		if ($value && strtolower($value) === "true") {
		  return true;
		} else {
		  return false;
		}
	}
	
	/**
	 * Determines whether your server is capable of communicating with our server over SSL.
	 */
	private function is_ssl_capable(){
		return defined('OPENSSL_VERSION_NUMBER') && is_numeric(OPENSSL_VERSION_NUMBER);
	}
}

/**
 * Returns the HTML needed to put our service (i.e. CAPTCHA and TrustMe Account) on your site.
 */
function spamcaptcher_get_captcha($accountID = null, $settings = "{}"){
	$sc_obj = new SpamCaptcher($accountID, null);
	$sc_obj->setSettings($settings);
	return $sc_obj->getCaptcha();
}

/**
 * Returns the recommended action (i.e. SHOULD_PASS, SHOULD_MODERATE or SHOULD_DELETE) for the session.
 */
function spamcaptcher_validate($accountID = null, $privateKey = null, $forceTrustMeAccount = false, $allowTrustMeAccount = true, $overwriteGlobalTMASettings = false, $csessID = null, $useProofOfWork = false, $proofOfWorkTime = 1){
	$sessionID = null;
	$answer = null;
	if (isset($_POST["spamCaptcherSessionID"])){
		$sessionID = $_POST["spamCaptcherSessionID"];
		$answer = $_POST["spamCaptcherAnswer"];
	}elseif (isset($_GET["spamCaptcherSessionID"])){
		$sessionID = $_GET["spamCaptcherSessionID"];
		$answer = $_GET["spamCaptcherAnswer"];
	}
	$args = array (
		'ip' => $_SERVER['REMOTE_ADDR'],
		'ftma' => ($forceTrustMeAccount ? "1" : "0"),
		'atma' => ($allowTrustMeAccount ? "1" : "0"),
		'id' => $sessionID,
		'ogtmas' => ($overwriteGlobalTMASettings ? "1" : "0"),
		'spamCaptcherAnswer' => $answer
	);
	$sc_obj = new SpamCaptcher($accountID, $privateKey);
	$sc_obj->setCustomerSessionID($csessID);
	$sc_obj->setSessionID($sessionID);
	$sc_obj->useProofOfWork($useProofOfWork);
	$sc_obj->setProofOfWorkTime($proofOfWorkTime);
	return $sc_obj->validate($args);
}

/**
 * Flags the session identified by either the SpamCaptcher Session ID
 * or the Customer Session ID (only one is required but if you use the
 * Customer Session ID you must have supplied it to us during validation).
 */
function spamcaptcher_flag($sessionID, $csessID, $flagType, $accountID = null, $privateKey = null, $data = null){
	$sc_obj = new SpamCaptcher($accountID, $privateKey);
	$sc_obj->flag($sessionID, $csessID, $flagType, $data);
}

?>