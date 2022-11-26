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
$DATABASE_NAME = 'firstexample';

$error = array();

if ($_POST['password'] != $_POST['retype'])
{
    array_push($error, 'Passwords do not match!');
    $_SESSION['error'] = $success;
    header('Location: '.$_session['url']);
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

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($stmt = $con->prepare('DELETE FROM recovery_password WHERE username = ?')) 
{
    // Set the new activation code to 'activated', this is how we can check if the user has activated their account.
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    if ($stmt = $con->prepare('UPDATE accounts SET pass = ? WHERE username = ?')) 
    {
        // Set the new activation code to 'activated', this is how we can check if the user has activated their account.
        $stmt->bind_param('ss', $password, $_SESSION['username']);
        $stmt->execute();

        session_unset();
        $success = array();
        array_push($success, 'Password Succesfully Changed!');
        $_SESSION['success'] = $success;
        header('Location: login.php');
        exit();
    }
}
?>