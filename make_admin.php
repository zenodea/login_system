<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

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

// Check password is correct
if ($stmt = $con->prepare('SELECT id, pass FROM accounts WHERE username = ?')) 
{
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password);
        $stmt->fetch();
        $stmt->close();
        if (!password_verify($_POST['password'], $password)) 
        {
            $_SESSION["error"] = "Password is Wrong!";	
            header('Location: make_admin_html.php');
            exit();
        }
    }
    else
    {
        $_SESSION["error"] = "Username does not exist!";	
        header('Location: make_admin_html.php');
        exit();
    }
}

$key = openssl_pkey_new(array('private_key_bits' => 2048));
$key_details = openssl_pkey_get_details($key);
$public_key = $key_details['key'];
if ($stmt = $con->prepare('INSERT INTO admin_key VALUES (?, ?)'))
{
    //Prepare Encryption
    $key = substr(hash('sha256', $password, true), 0, 32);
    $cipher = 'aes-256-gcm';
    $iv_len = openssl_cipher_iv_length($cipher);
    $tag_length = 16;
    $iv = openssl_random_pseudo_bytes($iv_len);
    $tag = ""; // will be filled by openssl_encrypt

    //Encrypting private key
    $key_encrypted = openssl_encrypt($key, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
    $key_encrypted = base64_encode($iv.$key_encrypted.$tag);

	$stmt->bind_param('is', $id, $key_encrypted);
	$stmt->execute();
    if ($stmt = $con->prepare('UPDATE accounts SET public_key = ?, admin = ? WHERE id = ?'))
    {
        $admin = 1;
        $stmt->bind_param('sii', $public_key, $admin, $id);
        $stmt->execute();
    }
}

$_SESSION["correct"] = "Username succesfully made into admin!";	
header('Location: make_admin_html.php');
exit();
?>