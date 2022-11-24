<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

$error = array();

// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'firstexample';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    echo "yikes";
}

$NEW_USERNAME 	= $_SESSION['username'];
$NEW_EMAIL  	= $_SESSION['email'];
$NEW_PASSWORD 	=  $_SESSION['password'];
$NEW_PHONE 		= $_SESSION['phone'];

$question_one = array($_POST['first_question'] => $_POST['first_answer']);
$question_two = array($_POST['second_question'] => $_POST['second_answer']);
$question_three = array($_POST['third_question'] => $_POST['third_answer']);

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
		array_push($error,"Username alredy taken!");
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
				$password = password_hash($NEW_PASSWORD, PASSWORD_DEFAULT);
				$uniqid = uniqid();
				$stmt->bind_param('ssssis', $NEW_USERNAME, $password, $NEW_EMAIL, $NEW_PHONE, $admin, $uniqid);
				$stmt->execute();
			if  ($stmt = $con->prepare('INSERT INTO security_questions VALUES (?, ?, ?, ?, ?, ?, ?)'))
			{
				$result = $con->insert_id;
				// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
				$stmt->bind_param('iisisis', $result, key($question_one), current($question_one), key($question_two), current($question_two),
								  key($question_three), current($question_three),);
				$stmt->execute();
				$from    = 'noreply@yourdomain.com';
				$subject = 'Account Activation Required';
				$headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
				// Update the activation variable below
				$activate_link = 'http://yourdomain.com/phplogin/activate.php?email=' . $NEW_EMAIL . '&code=' . $uniqid;
				$message = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';
				mail($NEW_EMAIL, $subject, $message, $headers);
				$success = array();
				array_push($success,'Account Succesfully Created!');
				array_push($success,'An email has been sent with an activation link.');
				array_push($success,'please Activate your Account!');
				$_SESSION['success'] = $success;
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
?>

