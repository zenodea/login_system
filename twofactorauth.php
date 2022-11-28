<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

//CSRF token check (and time check)
if(isset($_POST) & !empty($_POST))
{
	if(isset($_POST['csrf_token']))
	{
		if($_POST['csrf_token'] == $_SESSION['csrf_token'])
		{
		}
		else
		{
			$_SESSION['error'] = 'Token Error, try again!';
			session_unset();
			header('Location: profile.php');
			exit();
		}
	}
	$maximum_time = 600;
	if (isset($_SESSION['csrf_token_time']))
	{
		$token_time = $_SESSION['csrf_token_time'];
		if(($token_time + $maximum_time) <= time())
		{
			unset($_SESSION['csrf_token_time']);
			unset($_SESSION['csrf_token']);
			$_SESSION['error'] = 'Token Expired, try again!';
			session_unset();
			header('Location: profile.php');
			exit();
		}
	}
}

// Change this to your connection info.
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

//Prepare Encryption
$password = $_SESSION['password'];
$key = substr(hash('sha256', $password, true), 0, 32);
$cipher = 'aes-256-gcm';
$iv_len = openssl_cipher_iv_length($cipher);
$tag_length = 16;
$iv = openssl_random_pseudo_bytes($iv_len);
$tag = ""; // will be filled by openssl_encrypt

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Preparing to insert 2fa code into the database
if ($stmt = $con->prepare('INSERT INTO 2fa VALUES (?, ?)'))
{
	//Encrypting  phone_no
	$secret = openssl_encrypt($_POST['secret'], $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
	$secret = base64_encode($iv.$phone.$tag);
	// In this case we can use the account ID to get the account info.
	$stmt->bind_param('is', $_SESSION['id'], $secret);
	$stmt->execute();
	$success = array();
	array_push($success,'Two Factor Authentication Activated!');
	$_SESSION['success'] = $success;
	header('Location: profile.php');
	exit();
}
?>