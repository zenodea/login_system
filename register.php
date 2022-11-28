<?php
session_start(); // must be before any output

$token =  bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>


<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
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
				<label for="username">
					<i class="fas fa-user"></i>
				</label>
				<input type="text" name="username" placeholder="Username" id="username" required>
				<label for="email">
					<i class="fas fa-envelope"></i>
				</label>
				<input type="text" name="email" placeholder="email" id="email" required>
				<label for="phone">
					<i class="fas fa-phone"></i>
				</label>
				<input type="text" name="phone" placeholder="Phone" id="phone" required>
				<label for="password">
					<i class="fas fa-lock"></i>
				</label>
				<input type="password" name="password" placeholder="Password" id="password" required>
				<br>
				<div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
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