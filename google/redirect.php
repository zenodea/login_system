<?php
require_once 'vendor/autoload.php';

// init configuration
$clientID = '<YOUR_CLIENT_ID>';
$clientSecret = '<YOUR_CLIENT_SECRET>';
$redirectUri = '<REDIRECT_URI>';

// create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// authenticate code from Google OAuth Flow
if (isset($_GET['code'])) 
{
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $email =  $google_account_info->email;
    $name =  $google_account_info->name;
} 
else 
{
    echo "<a href='".$client->createAuthUrl()."'>Google Login</a>";
}
?>