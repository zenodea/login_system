<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();
$empty = FALSE;

// Configs used for db credentials, and captcha credentials
$configs = include('../config/config.php');

++$_SESSION['counter'];

$error = array();

//Captcha Checker
if(isset($_POST['g-recaptcha-response']))
{
  $captcha=$_POST['g-recaptcha-response'];
}
$secretKey = $configs['secret_captcha_key_google'];
$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
$response = file_get_contents($url);
$responseKeys = json_decode($response,true);
if($responseKeys['success']) 
{
}
else
{
	array_push($error, 'Please complete capcha!');
	$_SESSION['error'] = $error;
	header('Location: login_client.php');
	exit();
}

$calc = hash_hmac('sha256', 'auth_login_server.php', $_SESSION['second_token']);
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
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Creating connection with db
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

//Check if three attempts have been made
if ($stmt = $con->prepare('SELECT * FROM ip WHERE address = ?'))
{
	$stmt->bind_param('s', $_SERVER['REMOTE_ADDR']);
	$stmt->execute();

	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();

	// If ip is in the table, check time since insertion
	if ($stmt->num_rows > 0)
	{
		$ip = $_SERVER['REMOTE_ADDR'];

		// Check if 10 minutes passed from 'timestamp'
		$result = mysqli_query($con, "SELECT * FROM `ip` WHERE `address` = '$ip' AND `timestamp`  + INTERVAL 10 MINUTE < NOW()");
		if ($result->num_rows > 0)
		{
			mysqli_query($con, "DELETE FROM `ip` WHERE `address` = '$ip'");
			$_SESSION['counter'] = 0;
			array_push($error, 'You may try again!');
			$_SESSION['error'] = $error;	
			header('Location: login_client.php');
			exit();
		}

		// If 10 minutes have not passed, go back to login screen
		else
		{
			$_SESSION['counter'] = 0;
			array_push($error, 'Please wait before trying again!');
			$_SESSION['error'] = $error;	
			header('Location: login_client.php');
			exit();
		}
	}
	
	// If ip not in table, add ip address to table (alongside timestamp)
	else
	{
		if ($_SESSION['counter'] == 4)
		{
			if ($stmt = $con->prepare("INSERT INTO `ip` (`address` ,`timestamp`)VALUES (?,CURRENT_TIMESTAMP)"))
			{
				$stmt->bind_param('s', $_SERVER["REMOTE_ADDR"]);
				$stmt->execute();
				$stmt->close();
				array_push($error, "Maximum amount of attempts reached, please wait 10 minutes!");
				$_SESSION["error"] = $error;	
				$_SESSION['counter'] = 0;
				header('Location: login_client.php');
				exit();
			}
		}
	}
}

// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if ( !isset($_POST['username'], $_POST['password']) ) 
{
	// Could not get the data that should have been sent.
	header('Location: ../index.html');
}

// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
if ($stmt = $con->prepare('SELECT id, pass, activation_code, admin FROM accounts WHERE username = ?')) 
{
	// Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();

	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		$stmt->bind_result($id, $password, $authentication_code, $admin);
		$stmt->fetch();

		// Account exists, now we verify the password.
		// Note: remember to use password_hash in your registration file to store the hashed passwords.
		if (password_verify($_POST['password'], $password)) 
		{
			if ($authentication_code == 'activated')
			{
				if ($stmt = $con->prepare('SELECT * FROM 2fa WHERE id = ?'))
				{
					$stmt->bind_param('i', $id);
					$stmt->execute();
					$stmt->store_result();
					if ($stmt->num_rows > 0)
					{
						$_SESSION['error'] = "Insert 2FA PIN!";
						session_regenerate_id();
						$_SESSION['name'] = $_POST['username'];
						$_SESSION['id'] = $id;
						if ($admin == 1)
						{
							$_SESSION['password'] = $_POST['password'];
						}
						header('Location: login_2fa_client.php');
						exit();
					}
					else
					{
						session_regenerate_id();
    					$_SESSION['loggedin'] = TRUE;
						$_SESSION['name'] = $_POST['username'];
						$_SESSION['id'] = $id;
						if ($admin == 1)
						{
							$_SESSION['password'] = $_POST['password'];
						}
						header('Location: ../profile/profile_client.php');
						exit();
					}
				}
			} 
			
			// Account has not been activated yet
			else 
			{
				array_push($error,'Activate Account First');
				session_unset();
				$_SESSION['error'] = $error;
				header('Location: login_client.php');
				exit();
			}
	}

	// If wrong password
	else
	{
		array_push($error,'Password is wrong, try again!');
		$_SESSION['error'] = $error;
		header('Location: login_client.php');
		exit();
	}
	} 

	// Incorrect username
	else 
	{
		array_push($error,'Password is wrong, try again!');
		$_SESSION['error'] = $error;
		header('Location: login_client.php');
		exit();
	}

	$stmt->close();
}
?>
