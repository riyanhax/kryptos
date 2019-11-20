<?php
$cScope         =   'https://www.googleapis.com/auth/calendar';
$cClientID      =   '17415561663-mtirr262n1f297ur40c57pfr6o0gkdc8.apps.googleusercontent.com';
$cClientSecret  =   'K20UKJSTBsiRJNZAVMg4uMEd';
$cRedirectURI   =   'urn:ietf:wg:oauth:2.0:oob';
  
$cAuthCode      =   '';
 
if (empty($cAuthCode)) {
    $rsParams = array(
                        'response_type' => 'code',
                        'client_id' => $cClientID,
                        'redirect_uri' => $cRedirectURI,
                        'access_type' => 'offline',
                        'scope' => $cScope,
                        'approval_prompt' => 'force'
                     );
 
    $cOauthURL = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($rsParams);
    echo("Go to
$cOauthURL
and enter the given value into this script under $cAuthCode
");
    exit();
} // ends if (empty($cAuthCode))
elseif (empty($cRefreshToken)) {
    $cTokenURL = 'https://accounts.google.com/o/oauth2/token';
    $rsPostData = array(
                        'code'          =>   $cAuthCode,
                        'client_id'     =>   $cClientID,
                        'client_secret' =>   $cClientSecret,
                        'redirect_uri'  =>   $cRedirectURI,
                        'grant_type'    =>   'authorization_code',
                        );
    $ch = curl_init();
  
    curl_setopt($ch, CURLOPT_URL, $cTokenURL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rsPostData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
    $cTokenReturn = curl_exec($ch);
    $oToken = json_decode($cTokenReturn);
    echo("Here is your Refresh Token for your application.  Do not loose this!
 
");
    echo("Refresh Token = '" . $oToken->refresh_token . "';
");
} // ends
?>
