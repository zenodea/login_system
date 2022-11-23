<?php
session_start();
$empty = FALSE;

++$_SESSION['counter'];

if (isset($_POST) & !empty($_POST))
{
	if(empty($_POST['username'])) { $_SESSION['usernameError'] = "Insert Username";$empty = TRUE; }
	if(empty($_POST['password'])) { $_SESSION['passwordError'] = "Insert Password"; $empty = TRUE; }
}
if ($empty == TRUE)
{
	header('Location: login.php');
	exit();
}

// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'firstexample';



// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($_SESSION['counter'] > 3)
{
	$_SESSION['counter'] = 0;
	$ip = $_SERVER["REMOTE_ADDR"];
	mysqli_query($connection, "INSERT INTO `ip` (`address` ,`timestamp`)VALUES ('$ip',CURRENT_TIMESTAMP)");
	$result = mysqli_query($connection, "SELECT COUNT(*) FROM `ip` WHERE `address` LIKE '$ip' AND `timestamp` > (now() - interval 10 minute)");
	$count = mysqli_fetch_array($result, MYSQLI_NUM);
	$_SESSION["error"] = "Please wait 10 minutes before trying again!";	
	header('Location: login.php');
	exit();
}

if ($stmt = $con->prepare('SELECT timestamp FROM ip WHERE address = ?'))
{
	$stmt->bind_param('s', $_SERVER["REMOTE_ADDR"]);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->bind_result($time);
	$stmt->fetch();
	if (!empty($time))
	{
		$_SESSION["error"] = "Please wait 10 minutes before trying again!";	
		header('Location: login.php');
		exit();
	}
}
// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if ( !isset($_POST['username'], $_POST['password']) ) {
	// Could not get the data that should have been sent.
	header('LocationL index.html');
}

// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
if ($stmt = $con->prepare('SELECT id, pass, activation_code FROM accounts WHERE username = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();

	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		$stmt->bind_result($id, $password, $authentication_code);
		$stmt->fetch();
		// Account exists, now we verify the password.
		// Note: remember to use password_hash in your registration file to store the hashed passwords.
		if (password_verify($_POST['password'], $password)) {
			if ($authentication_code == 'activated')
			{
				// Verification success! User has logged-in!
				// Create sessions, so we know the user is logged in, they basically act like cookies but remember the data on the server.
				session_regenerate_id();
				$_SESSION['loggedin'] = TRUE;
				$_SESSION['name'] = $_POST['username'];
				$_SESSION['id'] = $id;
				header('Location: profile.php');
				exit();
			} 
			else 
			{
			$_SESSION["error"] = "Activate Account First!";	
			header('Location: login.php');
			exit();
			}
	}
	else
	{
		$_SESSION["error"] = "Password is Wrong!";	
		header('Location: login.php');
		exit();
	}
	} else {
		// Incorrect username
	$_SESSION["error"] = "Profile does not exist!";
	header('Location: login.php');
	exit();
	}

	$stmt->close();
}
?>
