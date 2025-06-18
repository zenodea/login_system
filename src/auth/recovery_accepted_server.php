<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

$calc = hash_hmac('sha256', 'recovery_accepted_server.php', $_SESSION['second_token']);
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
				header('Location: recovery_accepted_client.php');
				exit();
			}
		}
		else
		{
			array_push($error,'Token error, try again!');
			session_unset();
			$_SESSION['error'] = $error;
			header('Location: recovery_accepted_client.php');
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
			header('Location: recovery_accepted_client.php');
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
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Preparing error array for 
$error = array();

// Making sure both password match
if ($_POST['password'] != $_POST['retype'])
{
    array_push($error, 'Passwords do not match!');
    $_SESSION['error'] = $success;
    header('Location: '.$_SESSION['url']);
    exit();
}

// Validate password strength
$uppercase = preg_match('@[A-Z]@', $_POST['password']);
$lowercase = preg_match('@[a-z]@', $_POST['password']);
$number    = preg_match('@[0-9]@', $_POST['password']);
$specialChars = preg_match('@[^\w]@', $_POST['password']);

if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($_POST['password']) < 8) 
{
	if (!$uppercase)
	{
		array_push($error,"Password has to contain at least one UpperCase Letter!");
	}
	if (!$lowercase)
	{
		array_push($error,"Password has to contain at least one LowerCase Letter!");
	}
	if (!$number)
	{
		array_push($error,"Password has to contain at least one Number Letter!");
	}
	if (!$specialChars)
	{
		array_push($error,"Password has to contain at least one Special Character!");
	}
	if (strlen($_POST['password']) < 8)
	{
		array_push($error,"Password has to longer than 8 characters!");
	}
	$_SESSION['error'] = $error;
    header('Location: '.$_SESSION['url']);
	exit();
}

if ($stmt = $con->prepare('DELETE FROM recovery_password WHERE username = ?')) 
{
    // Set the new activation code to 'activated', this is how we can check if the user has activated their account.
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
	$stmt->close();
    
	// Hashing the new password with higher cost (slower)
	$options = array('cost'=> '15');
	$password = password_hash($_POST['password'], PASSWORD_BCRYPT, $options);
    if ($stmt = $con->prepare('UPDATE accounts SET pass = ? WHERE username = ?')) 
    {
        // Set the new activation code to 'activated', this is how we can check if the user has activated their account.
        $stmt->bind_param('ss', $password, $_SESSION['username']);
        $stmt->execute();
		$stmt->close();

        session_unset();
        $success = array();
        array_push($success, 'Password Succesfully Changed!');
        $_SESSION['success'] = $success;
        header('Location: ../login/login_client.php');
        exit();
    }
}
?>