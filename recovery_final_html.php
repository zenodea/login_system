<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

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

if (isset($_GET['username'], $_GET['code'])) 
{
	if ($stmt = $con->prepare('SELECT expiration_date FROM recovery_password WHERE username = ? AND recovery_code = ?')) 
    {
		$stmt->bind_param('ss', $_GET['username'], $_GET['code']);
		$stmt->execute();
		// Store the result so we can check if the account exists in the database.
		$stmt->store_result();
		if ($stmt->num_rows > 0) 
        {
            $stmt->bind_result($expiration_date);
            $stmt->fetch();
            if (time() > strtotime("+5 hours", strtotime($expiration_date)))
            {
                if ($stmt = $con->prepare('DELETE FROM recovery_password WHERE username = ?'))
                {
                    $stmt->bind_param('s', $_GET['username']);
                    $stmt->execute();
                    $error = array();
                    array_push($error, 'Recovery link expired, send request again!');
                    $_SESSION['error'] = $error;
                    header('Location: recovery_html.php');
                    exit();
                }
            }
            else
            {
                $_SESSION['username'] = $_GET['username'];
                $_SESSION['url'] = $_SERVER['REQUEST_URI'];
            }
        }	
		else 
        {
			$error = array();
			array_push($error, 'No recovery password sent');
			$_SESSION['error'] = $error;
			header('Location: recovery_html.php');
			exit();
		}
	}
}

$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Recovery Password</title>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="login">
			<h1>Change Password</h1>
			<?php
			if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) 
			{
				foreach($_SESSION['error'] as $key => $value)
				{
				echo "<p class='alert alert-danger'>". $value . "</p>"; 
				}
			}
			$_SESSION['error'] = NULL;
			if (isset($_SESSION['success']) & !empty($_SESSION['success']))
			{
				foreach($_SESSION['success'] as $key => $value)
				{
				echo "<p class='alert alert-success'>". $value . "</p>"; 
				}
			}
			$_SESSION['success'] = NULL;
			?>
		<form  action="recovery_final.php"  method="POST" required>
				<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				<label for="password">
					<i class="fas fa-lock"></i>
				</label>
				<input type="password" name="password" placeholder="New Password" id="password">
				<label for="password">
					<i class="fas fa-lock"></i>
				</label>
				<input type="password" name="retype" placeholder="Retype Password" id="retype">
				<input type="submit" value="Reset Password" >
		</form>
	</body>
</html>