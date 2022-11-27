<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();
require_once(__DIR__.'/vendor/autoload.php'); 
use RobThree\Auth\TwoFactorAuth;

// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'lovejoy_db';



// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$sql = "SELECT secret FROM 2fa WHERE id = ".$_SESSION['id'];

$result = $con->query($sql);

if (mysqli_num_rows($result) > 0) 
{
    while($row = mysqli_fetch_assoc($result)) 
    {
        $secret = $row['secret'];
    }
}

$tfa = new RobThree\Auth\TwoFactorAuth('Lovejoy');

if ($tfa->verifyCode($secret, $_POST['2fa']) === true)
{
    $_SESSION['loggedin'] = TRUE;
    header('Location: profile.php');
    exit();
}
else
{
    $_SESSION['error'] = "Wrong PIN, try again!";
    header('Location: login_2fa_html.php');
    exit();
}
?>