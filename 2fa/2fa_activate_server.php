<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

$calc = hash_hmac('sha256', 'req_eval_server.php', $_SESSION['second_token']);
//CSRF token check with per-form token check, also timeout check
if(isset($_POST) & !empty($_POST))
{
	if(isset($_POST['csrf_token']))
	{
		if (hash_equals($calc,$_POST['token']))
		{
			if(hash_equals($_POST['csrf_token'], $_SESSION['csrf_token']))
			{
				// All good, continue...
			}
			else
			{
				array_push($error,'Token error, try again!');
				unset($_SESSION['csrf_token_time']);
				unset($_SESSION['csrf_token']);
				unset($_SESSION['second_token']);
				$_SESSION['error'] = $error;
				header('Location: req_eval_client.php');
				exit();
			}
		}
		else
		{
			array_push($error,'Token error, try again!');
			unset($_SESSION['csrf_token_time']);
			unset($_SESSION['csrf_token']);
			unset($_SESSION['second_token']);
			$_SESSION['error'] = $error;
			header('Location: req_eval_client.php');
			exit();
		}
	}
	$maximum_time = 100;
	if (isset($_SESSION['csrf_token_time']))
	{
		$token_time = $_SESSION['csrf_token_time'];
		if(($token_time + $maximum_time) <= time())
		{
			unset($_SESSION['csrf_token_time']);
			unset($_SESSION['csrf_token']);
			unset($_SESSION['second_token']);
        	array_push($error,'Timeout error, try again!');
			$_SESSION['error'] = $error;
			header('Location: req_eval_client.php');
			exit();
		}
	}
}

// Preparing connection information for the db
$configs = include('../config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Creating connection with db
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Preparing to insert 2fa code into the database
if ($stmt = $con->prepare('INSERT INTO 2fa VALUES (?, ?)'))
{
	// In this case we can use the account ID to get the account info.
	$stmt->bind_param('is', $_SESSION['id'], $_POST['secret']);
	$stmt->execute();
	$success = array();
	array_push($success,'Two Factor Authentication Activated!');
	$_SESSION['success'] = $success;
	header('Location: ../profile/profile_client.php');
	exit();
}
?>