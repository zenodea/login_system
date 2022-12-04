<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();


// Preparing error array
$error = array();

//Captcha Checker
if(isset($_POST['g-recaptcha-response']))
{
  $captcha=$_POST['g-recaptcha-response'];
}
$secretKey = "6Ldmoj0jAAAAAIWrcfVRMYAb-C19UvaDA3Me_069";
$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
$response = file_get_contents($url);
$responseKeys = json_decode($response,true);
if($responseKeys["success"]) 
{
}
else
{
	$error = array();
	array_push($error, 'Please complete capcha!');
	$_SESSION['error'] = $error;
	header('Location: recovery_send_client.php');
	exit();
}

$calc = hash_hmac('sha256', 'recovery_send_server.php', $_SESSION['second_token']);
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
				header('Location: recovery_send_client.php');
				exit();
			}
		}
		else
		{
			array_push($error,'Token error, try again!');
			session_unset();
			$_SESSION['error'] = $error;
			header('Location: recovery_send_client.php');
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
			header('Location: recovery_send_client.php');
			exit();
		}
	}
}

// Change this to your connection info.
$configs = include('../config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    echo "yikes";
}

if ($stmt = $con->prepare('SELECT email, admin FROM accounts WHERE username = ?'))
{
	$stmt->bind_param('s',$_POST['user']);
	$stmt->execute();
    $stmt->store_result();
	if ($stmt->num_rows > 0)
	{
		$stmt->bind_result($email, $admin);
		$stmt->fetch();
		if ($admin == 1)
		{
			array_push($error,'User is an admin, please contact another admin to restore the account!');
			$_SESSION['error'] = $error;
			header('Location: recovery_send_client.php');
			exit();
		}
		if ($stmt = $con->prepare('INSERT INTO recovery_password VALUES (?, ?, CURRENT_TIMESTAMP)'))
		{
			// Creating uniqid(); for recovery code
			$uniqid = uniqid();
			$stmt->bind_param('ss',$_POST['user'], $uniqid);
			$stmt->execute();
			$stmt->close();

			// Preparing mail 
			$from    = 'lovejoy_no_reply@gmail.com';
			$subject = 'Account Recovery Password';
			$headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
			$activate_link = 'localhost/ComputerSecurity/recovery/recovery_final_client.php?username=' . $_POST['user'] . '&code=' . $uniqid;
			$message = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';
			mail($NEW_EMAIL, $subject, $message, $headers);

			// Success
			$success = array();
			array_push($success,'An email has been sent with a recovery link.');
			array_push($success,'Remember to use it before it expires! (5 hours)');
			$_SESSION['success'] = $success;
			header('Location: recovery_send_client.php');
			exit();
		}
	}
	else
	{
        array_push($error,'Username does not exist!');
        $_SESSION['error'] = $error;
        header('Location: recovery_send_client.php');
        exit();
	}
}
?>