<?php

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


//Check Password is correct
function function_alert($message) {
      
    // Display the alert box 
    echo "<script>alert('$message');</script>";
}
  
// Validate password strength
$uppercase = preg_match('@[A-Z]@', $NEW_PASSWORD);
$lowercase = preg_match('@[a-z]@', $NEW_PASSWORD);
$number    = preg_match('@[0-9]@', $NEW_PASSWORD);
$specialChars = preg_match('@[^\w]@', $NEW_PASSWORD);

if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($NEW_PASSWORD) < 8) 
{
    header('Location: register.html');
}


#Check Email is correct
if (!filter_var($NEW_EMAIL, FILTER_VALIDATE_EMAIL)) 
{
	exit('Email is not valid!');
}


if ($stmt = $con->prepare('SELECT id FROM accounts WHERE username = ?')) 
{
	$stmt->bind_param('s', $NEW_USERNAME);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt -> num_rows > 0)
	{
		echo 'Username Exists';
	}
	else
	{
		// Username doesnt exists, insert new account
		if ($stmt = $con->prepare('INSERT INTO accounts (username, pass, email, activation_code) VALUES (?, ?, ?, ?)')) {
			// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
			$password = password_hash($NEW_PASSWORD, PASSWORD_DEFAULT);
			$uniqid = uniqid();
			$stmt->bind_param('ssss', $NEW_USERNAME, $password, $NEW_EMAIL, $uniqid);
			$stmt->execute();
			$from    = 'noreply@yourdomain.com';
			$subject = 'Account Activation Required';
			$headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
			// Update the activation variable below
			$activate_link = 'http://yourdomain.com/phplogin/activate.php?email=' . $NEW_EMAIL . '&code=' . $uniqid;
			$message = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';
			mail($NEW_EMAIL, $subject, $message, $headers);
			echo 'Please check your email to activate your account!';		} else {
			// Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
			echo 'Could not prepare statement!';
		}
	}
} 
?>

