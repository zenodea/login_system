<?php
session_start(); // must be before any output

// Preparing and Setting Token
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
		<title>Register</title>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link href="style.css" rel="stylesheet" type="text/css">
		<script src='https://www.google.com/recaptcha/api.js' async defer></script>
	</head>
	<body>
		<div class="login">
			<h1>Register</h1>
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
			<form action="checkInformation.php" method="POST" autocomplete="off">
				<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				<input type="hidden" name="token" value="<?php echo htmlspecialchars(hash_hmac('sha256', 'checkInformation.php', $_SESSION['second_token']))?>"/>
				<label for="username">
					Username
				</label>
				<input type="text" name="username" placeholder="Username" id="username" required>
				<label for="email">
					Email
				</label>
				<input type="text" name="email" placeholder="email" id="email" required>
				<label for="phone">
					Phone
				</label>
				<input type="text" name="phone" placeholder="Phone" id="phone" required>
				<label>
					Password
				</label>
				<input type="password" name="password" placeholder="Password" id="password" required>
				<br>
				<div class="g-recaptcha" data-sitekey="6Ldmoj0jAAAAAKYyHaDbjhvncIOSjkFGTxMeT-OG"></div>
				<input type="submit" value="Register">
		</form>
		<form action="login.php">
			<input type="submit" value="Login" />
		</form>
		<form action="recovery_html.php">
			<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				<input type="submit" value="Forgot Password" />
		</form>
		</div>
	</body>
</html>