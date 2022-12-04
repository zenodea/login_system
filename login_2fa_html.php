<?php
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

// Preparing CSRF Token
$token =  bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>

<!DOCTYPE html>
<html>
	<head>
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
							https://use.fontawesome.com/releases/v5.7.1/css/all.css
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
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="login">
			<h1>Login</h1>
			<?php 
			if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) {echo "<p class='alert alert-danger'>". htmlspecialchars($_SESSION["error"]) . " </p>"; $_SESSION['error'] = NULL;}
			?>
			<form  action="login_2fa.php"  method="POST" required>
				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
				<label for="password">
					PIN
				</label>
				<input type="password" name="2fa" placeholder="2FA PIN" id="password">
				<input type="submit" value="Login" >
			</form>
			<form action="register.php">
			<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
				<input type="submit" value="Register" />
			</form>
			<form action="recovery_html.php">
			<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
				<input type="submit" value="Forgot Password" />
			</form>
	</body>
</html>