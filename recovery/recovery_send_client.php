<?php
session_start(); // must be before any output

if($_SERVER['HTTPS'] != 'on')
{
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

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
					object-src 'self' ;
					base-uri 'self' ;" 
  		/>
		<title>Recovery pssword</title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link href="../css/style.css" rel="stylesheet" type="text/css">
		<script src='https://www.google.com/recaptcha/api.js' async defer></script>
	</head>
	<body>
		<div class="login">
			<h1>Recovery</h1>
			<?php
				if (isset($_SESSION['error']) & !empty($_SESSION['error'])) 
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
			<form action="recovery_send_server.php" method="POST" class="signup-form">

				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
				<input type="hidden" name="token" value="<?php echo htmlspecialchars(hash_hmac('sha256', 'recovery_send_server.php', $_SESSION['second_token']))?>"/>

				<label for="Username">
					 Username 
				</i> </label>
					<input type="text" name="user" placeholder="user" id="user" required>
				<div class="g-recaptcha" data-sitekey="6Ldmoj0jAAAAAKYyHaDbjhvncIOSjkFGTxMeT-OG"></div>
				<input type="submit" value="Submit Password Recovery Request">
			</form>
			<form action="../register/register_client.php">
				<input type="submit" value="Register" />
			</form>
			<form action="../login/login_client.php">
				<input type="submit" value="Login" />
			</form>
		</div>
	</body>
</html>