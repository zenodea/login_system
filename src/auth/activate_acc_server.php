<?php
session_start();

// Preparing connection information for the db
$configs = include('../config/config.php');
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

//Check if email and code have been set
if (isset($_GET['email'], $_GET['code'])) 
{
	//Prepare stmt to get all data from accounts
	if ($stmt = $con->prepare('SELECT * FROM accounts WHERE email = ? AND activation_code = ?')) 
	{
		$stmt->bind_param('ss', $_GET['email'], $_GET['code']);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows > 0) 
		{
			//update the activation_code of account to "activated"
			if ($stmt = $con->prepare('UPDATE accounts SET activation_code = ? WHERE email = ? AND activation_code = ?')) 
			{
				// Set the new activation code to 'activated', this is how we can check if the user has activated their account.
				$newcode = 'activated';
				$stmt->bind_param('sss', $newcode, $_GET['email'], $_GET['code']);
				$stmt->execute();

				//Success, move to register.php
				$success = array();
				array_push($success, 'Your account is now activated! You can now <a href="../login/login_client.php">login</a>!');
				$_SESSION['success'] = $success;
				header('Location: register_client.php');
				exit();
			}
		} 
		else 
		{
			// Error
			$error = array();
			array_push($error, 'The account is already activated or doesn\'t exist!');
			$_SESSION['error'] = $error;
			header('Location: register_client.php');
			exit();
		}
	}
}
?>