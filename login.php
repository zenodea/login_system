<?php
  session_start(); // must be before any output
  if (!isset($_SESSION['counter']))
  {
	$_SESSION['counter'] = 0;
  }

  $token =  bin2hex(random_bytes(32));
  $_SESSION['csrf_token'] = $token;
  $_SESSION['csrf_token_time'] = time();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Login</title>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="login">
			<h1>Login</h1>
			<?php 
			if (isset($_SESSION['usernameError']) & !empty($_SESSION['usernameError'])){echo "<p class='alert alert-danger'>". $_SESSION['usernameError'] . " </p>";$_SESSION['usernameError'] = NULL;}
			if (isset($_SESSION['passwordError']) & !empty($_SESSION['passwordError'])){echo "<p class='alert alert-danger'>". $_SESSION['passwordError'] . " </p>"; $_SESSION['passwordError'] = NULL;}
			if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) {echo "<p class='alert alert-danger'>". $_SESSION["error"] . " </p>"; $_SESSION['error'] = NULL;}
			if (isset($_SESSION['success']) & !empty($_SESSION['success']))
			{
				foreach($_SESSION['success'] as $key => $value)
				{
				echo "<p class='alert alert-success'>". $value . "</p>"; 
				}
			}
			$_SESSION['success'] = NULL;
			?>
		<form  action="authenticate.php"  method="POST" required>
				<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				<label for="username">
					<i class="fas fa-user"></i>
				</label>
				<input type="text" name="username" placeholder="Username" id="username">
				<label for="password">
					<i class="fas fa-lock"></i>
				</label>
				<input type="password" name="password" placeholder="Password" id="password">
				<input type="submit" value="Login" >
			</form>
		<form action="register.php">
		<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
			<input type="submit" value="Register" />
		</form>
		<form action="recovery_html.php">
		<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
			<input type="submit" value="Forgot Password" />
		</form>
	</body>
</html>