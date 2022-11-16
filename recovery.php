<?php
  session_start(); // must be before any output
  if(isset($_POST) & !empty($_POST))
  {
	if(isset($_POST['csrf_token']))
	{
		if($_POST['csrf_token'] == $_SESSION['csrf_token'])
		{
			echo"token recognized";
		}
		else
		{
			$errors[] = "Issues With Token";
		}
	}
	$maximum_time = 5;
	if (isset($_SESSION['csrf_token_time']))
	{
		$token_time = $_SESSION['csrf_token_time'];
		if(($token_time + $maximum_time) >= time())
		{
			unset($_SESSION['csrf_token_time']);
			unset($_SESSION['csrf_token']);
			$errors[] = 'token expired';
		}
		else
		{
			echo  "all good";
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
		<title>Recovery pssword</title>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="login">
			<h1>Recovery</h1>
			<form action="authenticate.php" method="POST" class="signup-form">
			<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				<?php
				require_once('recaptchalib.php');
				$publickey = "your_public_key"; // you got this from the signup page
				echo recaptcha_get_html($publickey);
			   ?>
                <label for="Username">
                    <i class="fas fa-user"></i>
                </label>
                <input type="text" name="user" placeholder="user" id="user" required>
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