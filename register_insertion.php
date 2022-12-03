<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

// Preparing Error array
$error = array();

$calc = hash_hmac('sha256', 'register_insertion.php', $_SESSION['second_token']);
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
				unset($_SESSION['csrf_token_time']);
				unset($_SESSION['csrf_token']);
				unset($_SESSION['second_token']);
				$_SESSION['error'] = $error;
				header('Location: security_questions.php');
				exit();
			}
		}
		else
		{
			array_push($error,'Token error, try again!');
			unset($_SESSION['csrf_token_time']);
			unset($_SESSION['csrf_token']);
			unset($_SESSION['second_token']);
			$_SESSION['error'] = $error;
			header('Location: security_questions.php');
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
			unset($_SESSION['second_token']);
        	array_push($error,'Timeout error, try again!');
			$_SESSION['error'] = $error;
			header('Location: security_questions.php');
			exit();
		}
	}
}

// Change this to your connection info.
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    echo "yikes";
}

// Setting up data
$NEW_USERNAME 	= $_SESSION['username'];
$NEW_EMAIL  	= $_SESSION['email'];
$NEW_PASSWORD 	=  $_SESSION['password'];
$NEW_PHONE 		= $_SESSION['phone'];

// Setting up security questions
$question_one = array($_POST['first_question'] => $_POST['first_answer']);
$question_two = array($_POST['second_question'] => $_POST['second_answer']);
$question_three = array($_POST['third_question'] => $_POST['third_answer']);

// Making sure that the three security questions are unique
if (array_key_exists(key($question_one), $question_two) 
	| array_key_exists(key($question_one), $question_three)
	| array_key_exists(key($question_two), $question_one)
	| array_key_exists(key($question_two), $question_three)
	| array_key_exists(key($question_three), $question_one)
	| array_key_exists(key($question_three), $question_two))
{
	array_push($error,"Please choose three distinct security questions!");
	$_SESSION['error'] = $error;
	header('Location: security_questions.php');
	exit();
}

if ($stmt = $con->prepare('SELECT id FROM accounts WHERE username = ?')) 
{
	$stmt->bind_param('s', $NEW_USERNAME);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt -> num_rows > 0)
	{
		array_push($error,"Username already taken!");
		$_SESSION['error'] = $error;
		header('Location: register.php');
		exit();
	}
	if ($stmt = $con->prepare('SELECT id FROM accounts WHERE email = ?')) 
	{
		$stmt->bind_param('s', $NEW_EMAIL);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt -> num_rows > 0)
		{
			array_push($error,"Email already taken!");
			$_SESSION['error'] = $error;
			header('Location: register.php');
			exit();
		}
		else
		{
			// Username doesnt exists, insert new account
			if ($stmt = $con->prepare('INSERT INTO accounts (username, pass, email, phone_no, admin, activation_code) VALUES (?, ?, ?, ?, ?, ?)')) 
			{
					// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
					$admin = 0;

					$options = array('cost'=> '15');
					$password = password_hash($NEW_PASSWORD, PASSWORD_BCRYPT, $options);

					$uniqid = uniqid();

					$stmt->bind_param('ssssis', $NEW_USERNAME, $password, $NEW_EMAIL, $NEW_PHONE, $admin, $uniqid);
					$stmt->execute();
					if  ($stmt = $con->prepare('INSERT INTO security_questions VALUES (?, ?, ?, ?, ?, ?, ?)'))
					{
						// Get newest ID (from newly created account)
						$result = $con->insert_id;
						// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
						$stmt->bind_param('iisisis', $result, 
										key($question_one), password_hash(current($question_one), PASSWORD_BCRYPT, $options), 
										key($question_two), password_hash(current($question_two), PASSWORD_BCRYPT, $options),
										key($question_three), password_hash(current($question_three), PASSWORD_BCRYPT, $options),);
						$stmt->execute();

						// Preparing Mail
						$from    = 'lovejoy_no_reply@gmail.com';
						$subject = 'Account Activation Required';
						$headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
						$activate_link = 'localhost/ComputerSecurity/activate.php?email=' . $NEW_EMAIL . '&code=' . $uniqid;
						$message = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';
						mail($NEW_EMAIL, $subject, $message, $headers);

						// Success
						$success = array();
						array_push($success,'Account Succesfully Created!');
						array_push($success,'An email has been sent with an activation link.');
						array_push($success,'please Activate your Account!'); $_SESSION['success'] = $success;
						header('Location: register.php');
						exit();
					}
					else
					{
					// Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
					echo 'Could not prepare statement!';
					}
			} 
			else 
			{
				// Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
				echo 'Could not prepare statement!';
			}
		}
	}
} 
?>

