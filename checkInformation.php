<?php
session_start();

$_SESSION['username'] = $_POST['username'];
$_SESSION['password'] = $_POST['password'];
$_SESSION['email'] = $_POST['email'];
$_SESSION['phone'] = $_POST['phone'];

$error = array();


//csrf token check (and time check)
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
			header('Location: register.php');
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
			header('Location: register.php');
			exit();
		}
	}
}

//Captcha Checker
if(isset($_POST['g-recaptcha-response']))
{
  $captcha=$_POST['g-recaptcha-response'];
}
$secretKey = "6Ldmoj0jAAAAAIWrcfVRMYAb-C19UvaDA3Me_069";
$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
$response = file_get_contents($url);
$responseKeys = json_decode($response,true);

// should return JSON with success as true
if($responseKeys["success"]) 
{
}
else
{
	$_SESSION['error'] = 'Please complete capcha!';
	header('Location: login.php');
	exit();
}

// Validate password strength
$uppercase = preg_match('@[A-Z]@', $_SESSION['password']);
$lowercase = preg_match('@[a-z]@', $_SESSION['password']);
$number    = preg_match('@[0-9]@', $_SESSION['password']);
$specialChars = preg_match('@[^\w]@', $_SESSION['password']);

if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($_SESSION['password']) < 8) 
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
	if (strlen($NEW_PASSWORD) < 8)
	{
		array_push($error,"Password has to longer than 8 characters!");
	}
	$_SESSION['error'] = $error;
    session_unset();
    header('Location: register.php');
	exit();
}


#Check Email is correct
if (!filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) 
{
    array_push($error,"Invalid email!");
    $_SESSION['error'] = $error;
    session_unset();
    header('Location: register.php');
    exit();
}

#Check Phone is correct
if (!is_numeric($_SESSION['phone']))
{
    array_push($error,"Phone number should contain only numbers!");
    $_SESSION['error'] = $error;
    session_unset();
    header('Location: register.php');
    exit();
}

if (strlen($_SESSION['phone']) > 15)
{
    array_push($error,"Phone number is invalid!");
    $_SESSION['error'] = $error;
    session_unset();
    header('Location: register.php');
    exit();
}


header('Location: security_questions.php');
exit();
?>