<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

// Preparing security questions
$question_one =  $_POST['first_answer'];
$question_two =  $_POST['second_answer'];
$question_three = $_POST['third_answer'];

// Change this to your connection info.
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}

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
			unset($_SESSION['csrf_token_time']);
			unset($_SESSION['csrf_token']);
			$_SESSION['error'] = "CSRF token error, try again!";
			header('Location: change_profile_item_html.php');
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
			header('Location: change_profile_item_html.php');
			exit();
		}
	}
}

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($stmt = $con->prepare('SELECT first_q, second_q, third_q FROM security_questions WHERE id = ?')) 
{
	$stmt->bind_param('i', $_SESSION['id']);
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($one, $two, $three);
	$stmt->fetch();
	$stmt->close();

	//Checking security question one
	if (!password_verify($question_one, $one))
	{
		$_SESSION['error'] = 'One of the security questions is incorrect, please try again!';
		header('Location: profile.php');
		exit();
	}

	//Checking security question two 
	if (!password_verify($question_two, $two))
	{
		$_SESSION['error'] = 'One of the security questions is incorrect, please try again!';
		header('Location: profile.php');
		exit();
	}

	//Checking security question three 
	if (!password_verify($question_three, $three))
	{
		$_SESSION['error'] = 'One of the security questions is incorrect, please try again!';
		header('Location: profile.php');
		exit();
	}

	else
	{
		$_SESSION['correct'] = 'Please enter the new value!';
		header('Location: change_value_html.php');
		exit();
	}
}

?>