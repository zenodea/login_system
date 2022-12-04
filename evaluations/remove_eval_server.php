<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

$calc = hash_hmac('sha256', 'remove_eval_server.php', $_SESSION['second_token']);
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
				session_unset();
				$_SESSION['error'] = $error;
				header('Location: login_client.php');
				exit();
			}
		}
		else
		{
			array_push($error,'Token error, try again!');
			session_unset();
			$_SESSION['error'] = $error;
			header('Location: login_client.php');
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
        	array_push($error,'Timeout error, try again!');
			session_unset();
			$_SESSION['error'] = $error;
			header('Location: login_client.php');
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

// Obtain the private key of the current admin
if ($stmt = $con->prepare('SELECT p_key FROM admin_key WHERE id = ?'))
{
    $stmt->bind_param('i',$_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($p_key);
    $stmt->fetch();
    $stmt->close();

    // Get and decrypt private key for admin
    $password = $_SESSION['password'];
    $key = substr(hash('sha256', $password, true), 0, 32);
    $cipher = 'aes-256-gcm';
    $iv_len = openssl_cipher_iv_length($cipher);
    $tag_length = 16;
    
    // Decrypting the private key
    $textToDecrypt = $p_key;
    $encrypted = base64_decode($textToDecrypt);
    $iv = substr($encrypted, 0, $iv_len);
    $ciphertext = substr($encrypted, $iv_len, -$tag_length);
    $tag = substr($encrypted, -$tag_length);
    $private_key = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    
    // Decrypting document cipher
    if ($stmt = $con->prepare('SELECT document_cipher FROM document_key WHERE id_evaluation = ? AND id_user = ?'))
    {
        $stmt->bind_param('ii',$_POST['remove'],$_SESSION['id']);
        $stmt->execute();
        $stmt->bind_result($curr_cipher);
        $stmt->fetch();
        $stmt->close();

        // Decrypting the cipher using the private key
        if (!openssl_private_decrypt($curr_cipher, $decrypted_curr_cipher, $private_key))
        {
            $error = array();
            array_push($error, 'Error with administration key, please contact a supervisor!');
            $_SESSION['error'] = $error;
            header('Location: list_eval_client.php');
            exit();
        }

        // Preparing decryption items
        $password = $decrypted_curr_cipher;
        $key = substr(hash('sha256', $password, true), 0, 32);
        $_SESSION['key'] = $key;
        $cipher = 'aes-256-gcm';
        $iv_len = openssl_cipher_iv_length($cipher);
        $tag_length = 16;
    }
}


// Getting url for the picture.
if ($stmt = $con->prepare('SELECT url FROM evaluations WHERE id = ?'))
{
    $stmt->bind_param('i',$_POST['remove']);
    $stmt->execute();
    $stmt->bind_result($result); 
    $stmt->fetch();
    $stmt->close();

    if ( $result != 'none')
    {

        // Decrypting the url of the picture
        $textToDecrypt = $result;
        $encrypted = base64_decode($textToDecrypt);
        $iv = substr($encrypted, 0, $iv_len);
        $ciphertext = substr($encrypted, $iv_len, -$tag_length);
        $tag = substr($encrypted, -$tag_length);
        $url = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

        // Checking for errors
        if (!unlink($url))
        {
            $_SESSION['error'] = $decrypted_curr_cipher;
            header('Location: list_eval_client.php');
            exit();
        }
    }
    if ($stmt = $con->prepare('DELETE FROM evaluations WHERE id = ?'))
    {
        $stmt->bind_param('i',$_POST['remove']);
        $stmt->execute();
        $stmt->close();
        if ($stmt = $con->prepare('DELETE FROM document_key WHERE id_evaluation = ?'))
        {
            $stmt->bind_param('i',$_POST['remove']);
            $stmt->execute();
            $stmt->close();
            $_SESSION['correct'] = 'Evaluation succesfully resolved!';
            header('Location: list_eval_client.php');
            exit();
        }
    }
    else
    {
        $_SESSION['error'] = 'Resolve Error, unknown error!';
        header('Location: list_eval_client.php');
        exit();
    }
}
?>