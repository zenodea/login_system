<?php
session_start();

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
$NEW_USERNAME = $_POST['username'];
$NEW_EMAIL = $_POST['email'];
$NEW_PASSWORD =  $_POST['password'];

$error = array();

// Validate password strength
$uppercase = preg_match('@[A-Z]@', $NEW_PASSWORD);
$lowercase = preg_match('@[a-z]@', $NEW_PASSWORD);
$number    = preg_match('@[0-9]@', $NEW_PASSWORD);
$specialChars = preg_match('@[^\w]@', $NEW_PASSWORD);

if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($NEW_PASSWORD) < 8) 
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
    header('Location: register.php');
	exit();
}


#Check Email is correct
if (!filter_var($NEW_EMAIL, FILTER_VALIDATE_EMAIL)) 
{
		array_push($error,"Invalid email!");
		$_SESSION['error'] = $error;
		header('Location: register.php');
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
		if ($stmt = $con->prepare('INSERT INTO accounts (username, pass, email, admin, activation_code) VALUES (?, ?, ?, ?, ?)')) 
		{
			// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
			$admin = 0;
			$password = password_hash($NEW_PASSWORD, PASSWORD_DEFAULT);
			$uniqid = uniqid();
			$stmt->bind_param('sssis', $NEW_USERNAME, $password, $NEW_EMAIL, $admin, $uniqid);
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
} 
?>

