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
$DATABASE_NAME = 'lovejoy_db';

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($stmt = $con->prepare('SELECT url FROM evaluations WHERE id = ?'))
{
    $stmt->bind_param('i',$_POST['remove']);
    $stmt->execute();
    $stmt->bind_result($result); 
    $stmt->fetch();
    $stmt->close();
    if (!unlink($result) & $result != "None")
    {
        $_SESSION['error'] = "Resolve Error, image file does not exist!";
        header('Location: list_eval.php');
        exit();
    }
    else
    {
        if ($stmt = $con->prepare('DELETE FROM evaluations WHERE id = ?'))
            {
                $stmt->bind_param('i',$_POST['remove']);
                $stmt->execute();
                $stmt->close();
                $_SESSION['correct'] = "Evaluation succesfully resolved!";
                header('Location: list_eval.php');
                exit();
            }
        else
        {
            $_SESSION['error'] = "Resolve Error, unknown error!";
            header('Location: list_eval.php');
            exit();
        }
    }
}

?>