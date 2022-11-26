<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();
// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'firstexample';


// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($stmt = $con->prepare('DELETE FROM 2fa WHERE id = ?'))
{
	// In this case we can use the account ID to get the account info.
	$stmt->bind_param('i', $_SESSION['id']);
	$stmt->execute();
	$success = array();
	array_push($success,'Two Factor Authentication Deactivated!');
	$_SESSION['success'] = $success;
	header('Location: profile.php');
	exit();
}

?>