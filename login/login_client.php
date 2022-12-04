<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start(); // must be before any output
if (!isset($_SESSION['counter']))
{
$_SESSION['counter'] = 0;
}

if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

require_once '../vendor/autoload.php';

$configs = include('../config/config.php');

// Creating new google client instance
$client = new Google_Client();

// Enter your Client ID
$client->setClientId($configs['client_key_google']);
// Enter your Client Secrect
$client->setClientSecret($configs['secret_key_google']);
// Enter the Redirect URL
$client->setRedirectUri('http://localhost/ComputerSecurity/login_google.php?');

// Adding those scopes which we want to get (email & profile Information)
$client->addScope("email");
$client->addScope("profile");

// Preparing and setting CSRF token
$token =  bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();

// Per-form csrf token
$second_token = bin2hex(random_bytes(32));
$_SESSION['second_token'] = $second_token;
?>

<!DOCTYPE html>
<html>
	<head>
		<!--Setting up CSP-->
		<meta
			http-equiv="Content-Security-Policy"
			content="default-src 'self'; 
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
		<title>Login</title>
		<link rel="stylesheet" href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css'>
		<link href="../css/style.css" rel="stylesheet" type="text/css">
		<script src='https://www.google.com/recaptcha/api.js' nonce="{NONCE}"></script>
		
	</head>
	<body>
		<div class="login">
			<h1>Login</h1>
			<?php
				if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) 
				{
					foreach($_SESSION['error'] as $key => $value)
					{
					echo "<p class='alert alert-danger'>". htmlspecialchars($value) . "</p>"; 
					}
				}
				$_SESSION['error'] = NULL;
				if (isset($_SESSION['success']) & !empty($_SESSION['success']))
				{
					foreach($_SESSION['success'] as $key => $value)
					{
					echo "<p class='alert alert-success'>". htmlspecialchars($value) . "</p>"; 
					}
				}
				$_SESSION['success'] = NULL;
			?>
			<form  action="auth_login_server.php"  method="POST" required>
				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
				<input type="hidden" name="token" value="<?php echo htmlspecialchars(hash_hmac('sha256', 'auth_login_server.php', $_SESSION['second_token']));?>"/>
				<label for="username">
					Username
				</label>
				<input type="text" name="username" placeholder="Username" id="username">
				<label for="password">
					Password
				</label>
				<input type="password" name="password" placeholder="Password" id="password">
				<br>
				<div class="g-recaptcha" data-sitekey="6Ldmoj0jAAAAAKYyHaDbjhvncIOSjkFGTxMeT-OG"></div>
				<input type="submit" value="Login" >
			</form>

			<a href="<?php echo htmlspecialchars($client->createAuthUrl()); ?>"><button>Log in with Google account</button></a>


			<form action="../register/register_client.php">
				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
					<input type="submit" value="Register" />
			</form>
			<form action="../recovery/recovery_send_client.php">
				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
					<input type="submit" value="Forgot Password" />
			</form>
	</body>
</html>