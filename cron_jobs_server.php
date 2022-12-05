<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
$configs = include('config/config.php');

// Preparing connection information for the db
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];


// Creating connection with db
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($stmt = $con->prepare('DELETE FROM ip WHERE expiration_date + INTERVAL 10 MINUTE <= NOW()'))
{
    $stmt->execute();
    $stmt->close();
}

// Deleting old password_recovery
if ($stmt = $con->prepare('DELETE FROM recovery_password WHERE expiration_date + INTERVAL 5 HOUR <= NOW()'))
{
    $stmt->execute();
    $stmt->close();
}

// Deleting unactivated accounts after expiry date of one day
if ($stmt = $con->prepare('DELETE FROM accounts WHERE activation_code != "activated" AND  date_of_creation + INTERVAL 1 DAY <= NOW()'))
{
    $stmt->execute();
    $stmt->close();
}
?>