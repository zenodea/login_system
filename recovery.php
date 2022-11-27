<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();
$error = array();

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
			header('Location: recovery_html.php');
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
			header('Location: recovery_html.php');
			exit();
		}
	}
}

// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'lovejoy_db';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    echo "yikes";
}

if ($stmt = $con->prepare('SELECT email FROM accounts WHERE username = ?'))
{
	$stmt->bind_param('s',$_POST['user']);
	$stmt->execute();
    $stmt->store_result();
	if ($stmt->num_rows > 0)
	{
		$stmt->bind_result($email);
		$stmt->fetch();
		if ($stmt = $con->prepare('INSERT INTO recovery_password VALUES (?, ?, CURRENT_TIMESTAMP)'))
		{
				$uniqid = uniqid();
				$stmt->bind_param('ss',$_POST['user'], $uniqid);
				$stmt->execute();
				$stmt->close();
				$from    = 'noreply215872@gmail.com';
				$subject = 'Account Recovery Password';
				$headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
				// Update the activation variable below
				$activate_link = 'localhost/ComputerSecurity/recovery_final_html.php?username=' . $_POST['user'] . '&code=' . $uniqid;
				$message = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';
				mail($NEW_EMAIL, $subject, $message, $headers);
				$success = array();
				array_push($success,'An email has been sent with a recovery link.');
				array_push($success,'Remember to use it before it expires! (5 hours)');
				$_SESSION['success'] = $success;
				header('Location: recovery_html.php');
				exit();
			}
	}
	else
	{
        array_push($error,'Username does not exist!');
        $_SESSION['error'] = $error;
        header('Location: recovery_html.php');
        exit();
	}
}
?>