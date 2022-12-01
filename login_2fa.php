<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();
require_once(__DIR__.'/vendor/autoload.php'); 
use RobThree\Auth\TwoFactorAuth;

// Change this to your connection info.
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
if ($stmt = $con->prepare("SELECT secret FROM 2fa WHERE id =?"))
{
    $stmt->bind_param('i', $_SESSION['id']);
	$stmt->execute();
	$stmt->bind_result($secret_encrypted);
	$stmt->fetch();
	$stmt->close();
}

// Preparing decryption items
$password = $_SESSION['password'];
$key = substr(hash('sha256', $password, true), 0, 32);
$cipher = 'aes-256-gcm';
$iv_len = openssl_cipher_iv_length($cipher);
$tag_length = 16;

// 2FA to decrypt
$textToDecrypt = $secret_encrypted;
$encrypted = base64_decode($textToDecrypt);
$iv = substr($encrypted, 0, $iv_len);
$ciphertext = substr($encrypted, $iv_len, -$tag_length);
$tag = substr($encrypted, -$tag_length);
$secret = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

// Checking 2FA code
$tfa = new RobThree\Auth\TwoFactorAuth('Lovejoy');
if ($tfa->verifyCode($secret, $_POST['2fa']) === true)
{
    $_SESSION['loggedin'] = TRUE;
    header('Location: profile.php');
    exit();
}
else
{
    $_SESSION['error'] = $secret_encrypted;
    header('Location: login_2fa_html.php');
    exit();
}
?>