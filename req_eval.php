<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

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

// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'lovejoy_db';

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (!empty($_FILES['userfile']['name'])) {
	//Prepare file upload information
	$allowed = array('png', 'jpg');
	$filename = $_FILES['userfile']['name'];
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	$uploaddir = "uploads/";
	$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

	//Check file type
	if (!in_array($ext, $allowed)) {
		$_SESSION['error'] = "Wrong File Format (Please use png or jpg)!";
		header('Location: req_eval_html.php');
		exit();
	}

	// Check if file already exists
	if (file_exists($uploadfile)) {
		$_SESSION['error'] = "File already exists!";
		header('Location: req_eval_html.php');
		exit();
	}

	// Check file size
	if ($_FILES["userfile"]["size"] > 500000) {
		$_SESSION['error'] = "Upload failed, file size to large!";
		header('Location: req_eval_html.php');
		exit();
	}
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		$_SESSION['correct'] = "The file ". htmlspecialchars(basename( $_FILES["usefile"]["name"])). " has been uploaded.";
	}
	else {
		$_SESSION['error'] = $_FILES['userfile']['tmp_name'];
		header('Location: req_eval_html.php');
		exit();
	}
}
else
{
	$uploadfile = "None";
}
$id = $_SESSION['id'];
$header = $_POST['topic'];
$body = $_POST['body'];
$contact = $_POST['contact'];

if ($stmt = $con->prepare("INSERT INTO evaluations (id_user, header, comment, url, contact) VALUES (?, ?, ?, ?, ?)")) {
		$stmt->bind_param('sssss', $id, $header, $body, $uploadfile, $contact);
		$stmt->execute();
	} 
header('Location: req_eval_html.php');
exit();
?>

