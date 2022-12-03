<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

require_once './vendor/autoload.php';

// Change this to your connection info.
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    echo "yikes";
}

// Creating new google client instance
$client = new Google_Client();

// Enter your Client ID
$client->setClientId($configs['client_key_google']);
// Enter your Client Secrect
$client->setClientSecret($configs['secret_key_google']);
// Enter the Redirect URL
$client->setRedirectUri('http://localhost/ComputerSecurity/login_google.php?');

// Adding those scopes which we want to get (email & profile Information)
$client->addScope("email");
$client->addScope("profile");

if(isset($_GET['code']))
{
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if(!isset($token["error"]))
    {
        $client->setAccessToken($token['access_token']);
        // getting profile information
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        // Storing data into database
        $id = $google_account_info->id;
        $full_name = trim($google_account_info->name);
        $email = $google_account_info->email;
        $phone = $google_account_info->phone;
        echo $id."<br>";
        echo $full_name."<br>";
        echo $email."<br>";
        if ($stmt = $con->prepare('SELECT id, username FROM accounts WHERE google_id = ?'))
        {
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) 
            {
                $stmt->bind_result($id_account, $username);
                $stmt->fetch();
                $_SESSION['name'] = $username;
                $_SESSION['id'] = $id_account;
    			$_SESSION['loggedin'] = TRUE;
                header('Location: profile.php');
                exit;
            }
            else 
            {
                if ($stmt = $con->prepare('INSERT INTO accounts(google_id, username, email, admin, activation_code)
                                          VALUES (?, ?, ?, ?, ?)'))
                {
                    $admin = 0;
                    $activation_code = "activated";
                    $stmt->bind_param('sssis', $id, $full_name, $email, $admin, $activation_code);
                    $stmt->execute();
                    $error = array();
                    array_push($error,'Account succesfully created, you may login now!');
                    session_unset();
                    $_SESSION['success'] = $error;
                    header('Location: login.php');
                    exit();
                }
            }
        }
    }
    }
    else
    {
        $error = array();
        array_push($error,'Account error, try again!');
        session_unset();
        $_SESSION['error'] = $error;
        header('Location: login.php');
        exit();
    }


?>