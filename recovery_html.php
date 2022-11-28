<?php
session_start(); // must be before any output

// Preparing and setting CSRF token
$token =  bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Recovery pssword</title>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link href="style.css" rel="stylesheet" type="text/css">
		<script src='https://www.google.com/recaptcha/api.js' async defer></script>
	</head>
	<body>
		<div class="login">
			<h1>Recovery</h1>
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
			<form action="recovery.php" method="POST" class="signup-form">
				<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				<label for="Username"> <i class="fas fa-user"></i> </label>
					<input type="text" name="user" placeholder="user" id="user" required>
				<div class="g-recaptcha" data-sitekey="6Ldmoj0jAAAAAKYyHaDbjhvncIOSjkFGTxMeT-OG"></div>
				<input type="submit" value="Submit Password Recovery Request">
			</form>
			<form action="register.php">
				<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
					<input type="submit" value="Register" />
			</form>
			<form action="login.php">
				<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				<input type="submit" value="Login" />
			</form>
		</div>
	</body>
</html>