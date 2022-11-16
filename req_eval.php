<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
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
$uploaddir = 'imageFolder/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
	echo "File is valid, and was successfully uploaded.\n";
  } else {
	 echo "Upload failed";
  }
$id = $_SESSION['id'];
$header = $_POST['topic'];
$body = $_POST['body'];

if ($stmt = $con->prepare("INSERT INTO evaluations (id_user, header, comment, url) VALUES (?, ?, ?, ?)")) {
		$stmt->bind_param('ssss', $id, $header, $body, $uploadfile);
		$stmt->execute();
	} 
header('Location: profile.php');
?>

