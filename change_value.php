<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

$finalValue = $_POST['newValue'];

if ($_SESSION['change'] == "phone")
{
    #Check Phone is correct
    if (!is_numeric($finalValue))
    {
        array_push($error,"Phone number should contain only numbers!");
        $_SESSION['error'] = $error;
        header('Location: change_value_html.php');
        exit();
    }

    if (strlen($finalValue) > 15)
    {
        array_push($error,"Phone number is invalid!");
        $_SESSION['error'] = $error;
        header('Location: change_value_html.php');
        exit();
    }
    $valueToSelect = "phone_no";
}

elseif ($_SESSION['change'] == "email")
{
    $error = array();
    if (!filter_var($finalValue, FILTER_VALIDATE_EMAIL)) 
    {
        array_push($error,"Invalid email!");
        $_SESSION['error'] = $error;
        header('Location: change_value_html.php');
        exit();
    }
    $valueToSelect = "email";
}

elseif ($_SESSION['change'] == "password")
{
    // Validate password strength
    $uppercase = preg_match('@[A-Z]@', $finalValue);
    $lowercase = preg_match('@[a-z]@', $finalValue);
    $number    = preg_match('@[0-9]@', $finalValue);
    $specialChars = preg_match('@[^\w]@', $finalValue);

    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($finalValue) < 8) 
    {
        $error = array();
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
        header('Location: change_value_html.php');
        exit();
    }
	$finalValue = password_hash($finalValue, PASSWORD_DEFAULT);
    $valueToSelect = "pass";
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

if ($stmt = $con->prepare('UPDATE accounts SET '.$valueToSelect.' = ? WHERE id = ?')) 
{
	$stmt->bind_param('si',  $finalValue, $_SESSION['id']);
	$stmt->execute();
    $_SESSION['correct'] = "Change succesfully made!";
    header('Location: change_profile_item_html.php');
    exit();
}
?>