<?php

//Welcome to the PHP sample app for webhooks for QuickBooks Online!
//Be sure to read the README.md for all the instructions.


//Read the app credentials from the credentials.json file, and fails on some common errors.
$credentials = file_get_contents("credentials.json");
$json_a = json_decode($credentials, true);
$appToken = $json_a['App Token'];
$oauthConsumerSecret = $json_a['OAuth Consumer Secret'];
$oauthConsumerKey = $json_a['OAuth Consumer Key'];
$webhooksToken = $json_a['Webhooks Token'];
$callback_url = $json_a['Callback URL'];
if($credentials == FALSE){
	echo 'You need to create a copy of the credentials-sample.json file, rename it credentials.json and fill with the correct values for your app.'.PHP_EOL;
	exit();
}
if($appToken=='xxxxx'||$oauthConsumerKey=='xxxxx'||$oauthConsumerSecret=='xxxxx'){
	echo 'you need to fill in credentials.json with your own app\'s values';
	exit();
}



//This block of code should execute for responding to the webhook notification
$output = 'Received a request at '.date("D M j G:i:s", $_SERVER['REQUEST_TIME']).' at '.$_SERVER['HTTP_HOST'].' from '.$_SERVER['HTTP_USER_AGENT'].PHP_EOL;
$fp = fopen('request.log', 'a');
fwrite($fp, $output);
fclose($fp);
if (isset($_SERVER['HTTP_INTUIT_SIGNATURE'])){
	$output .='Body of the request was :'.PHP_EOL;
	$output .= print_r(json_decode(file_get_contents("php://input")),TRUE);
	$payloadHash = hash_hmac('sha256',file_get_contents("php://input"),$webhooksToken);
	$singatureHash = bin2hex(base64_decode($_SERVER['HTTP_INTUIT_SIGNATURE']));
	if($payloadHash == $singatureHash){
		$output .= PHP_EOL.'Request is verified'.PHP_EOL;
	}else{
		$output .=PHP_EOL."Unable to verify request, using a token of '".$webhooksToken."' the payload hash was ".$payloadHash.' while the intuit signature was '.$singatureHash.PHP_EOL;

		$output.=print_r(file_get_contents("php://input"),TRUE);
	}
	$fp = fopen('request.log', 'a');
	fwrite($fp, $output);
	fclose($fp);
	exit();
}


//Otherwise, initiate OAuth to connect an app to a sandbox company.

session_start();

$req_url = 'https://oauth.intuit.com/oauth/v1/get_request_token';
$authurl = 'https://appcenter.intuit.com/Connect/Begin';
$acc_url = 'https://oauth.intuit.com/oauth/v1/get_access_token';
$api_url = 'https://sandbox-quickbooks.api.intuit.com';
$conskey = $oauthConsumerKey;
$conssec = $oauthConsumerSecret;
// https://oauth.intuit.com/oauth/v1/get_request_token
if(!isset($_GET['oauth_token']) && $_SESSION['state']==1) $_SESSION['state'] = 0;
try {
	$oauth = new OAuth($conskey,$conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
	$oauth->enableDebug();
	if(!isset($_GET['oauth_token']) && !$_SESSION['state']) {
		$request_token_info = $oauth->getRequestToken($req_url,$callback_url);
		$_SESSION['secret'] = $request_token_info['oauth_token_secret'];
		$_SESSION['state'] = 1;
		header('Location: '.$authurl.'?oauth_token='.$request_token_info['oauth_token']);
		exit;
	} else if($_SESSION['state']==1) {
		$oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
		$_SESSION['realmId'] = $_GET['realmId'];
		$access_token_info = $oauth->getAccessToken($acc_url);
		$_SESSION['state'] = 2;
		$_SESSION['token'] = $access_token_info['oauth_token'];
		$_SESSION['secret'] = $access_token_info['oauth_token_secret'];
	} 
	$oauth->setToken($_SESSION['token'],$_SESSION['secret']);
	echo 'Congratulations, your app is now connected!';
	//If everything works out, you can use a sample request to test your connection
	// $oauth->fetch($api_url."/v3/company/".$_SESSION['realmId']."/query?query=".urlencode("select * from Customer"),Array(),OAUTH_HTTP_METHOD_GET,Array('Accept'=>'application/json'));
	// var_dump(json_decode($oauth->getLastResponse()));
} catch(OAuthException $E) {
	echo '<pre>';
	print_r($E);
}
