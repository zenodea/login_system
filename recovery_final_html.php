<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

// Change this to your connection info.
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) 
{
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

// Preparing and Setting CSRF Token 
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta
			http-equiv="Content-Security-Policy"
			content="default-src 'none'; 
					script-src 
							'self' 
							https://apis.google.comhttps://apis.google.com 
							https://www.google.com/recaptcha/ 
							https://www.gstatic.com/recaptcha/;
					style-src 
							'self' 
							https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css 
							https://fonts.googleapis.com 
							https://www.google.com/recaptcha/ 
							https://www.gstatic.com/recaptcha/;
					form-action 'self';
					img-src 'self' www.gstatic.com;
					frame-src 'self' https://www.google.com/recaptcha/;
					object-src 'self' 'none';
					base-uri 'self' 'none';" 
  		/>
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

				<label for="password"> <i class="fas fa-lock"></i> </label>
					<input type="password" name="password" placeholder="New Password" id="password">
				<label for="password"> <i class="fas fa-lock"> </i></label>
					<input type="password" name="retype" placeholder="Retype Password" id="retype">

				<input type="submit" value="Reset Password" >
			</form>
	</body>
</html>