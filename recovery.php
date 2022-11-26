<?php
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

if ($stmt = $con->prepare('SELECT mail FROM accounts WHERE username = ?'))
{
	$stmt->bind_param('s',$_POST['user']);
	$stmt->execute();
	$stmt->bind_result($email);
	$stmt->fetch();
	$stmt->close();
	if ($email == 0)
	{
        array_push($error,'Username does not exist!');
        $_SESSION['error'] = $success;
        header('Location: recovery_html.php');
        exit();
	}
    if ($stmt = $con->prepare('INSERT INTO recovery_password VALUES ?, ?, ?'))
    {
        $uniqid = uniqid();
        $stmt->bind_param('sss',$_POST['user'], $uniqid, );
        $stmt->execute();
	    $stmt->close();
        $from    = 'noreply215872@gmail.com';
        $subject = 'Account Recovery Password';
        $headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
        // Update the activation variable below
        $activate_link = 'localhost/ComputerSecurity/activate.php?email=' . $NEW_EMAIL . '&code=' . $uniqid;
        $message = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';
        mail($NEW_EMAIL, $subject, $message, $headers);
        $success = array();
        array_push($success,'An email has been sent with a recovery link.');
        array_push($success,'Remember to use it before it expires! (5 hours)');
        $_SESSION['success'] = $success;
        header('Location: recovery_html.php');
        exit();
    }
}
?>