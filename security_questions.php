<?php
session_start(); // must be before any output
  
// Preparing array containing security questions
$questions = array(
    1 => "What city were you born in?",
    2 => "What is your oldest sibling’s middle name?",
    3 => "What was the first concert you attended?",
    4 => "What was the make and model of your first car?",
    5 => "In what city or town did your parents meet?",
);

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
		<title>Register</title>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body class="security_questions">
		<div class="security_questions login">
			<h1>Security Questions</h1>
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
			<form action="register_insertion.php" method="POST" autocomplete="off">

				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
				<input type="hidden" name="token" value="<?php echo htmlspecialchars(hash_hmac('sha256', 'register_insertion.php', $_SESSION['second_token']))?>"/>

				<select name="first_question" id="first_question">
					<option value="">--- select security question ---</option>
					<?php foreach($questions as $value => $key): ?>
						<option value=<?= htmlspecialchars($value); ?>><?= htmlspecialchars($key); ?></option>
					<?php endforeach; ?>
				</select>
				<input type="text" name="first_answer" placeholder="Answer" id="first_answer" required>
				<select name="second_question" id="second_question">
					<option value="">--- Select Security Question ---</option>
					<?php foreach($questions as $value => $key): ?>
						<option value=<?= htmlspecialchars($value); ?>><?= htmlspecialchars($key); ?></option>
					<?php endforeach; ?>
					</select>
					<input type="text" name="second_answer" placeholder="Answer" id="second_answer" required>
				<select name="third_question" id="third_question">
					<option value="">--- Select Security Question ---</option>
					<?php foreach($questions as $value => $key): ?>
						<option value=<?= htmlspecialchars($value); ?>><?= htmlspecialchars($key); ?></option>
					<?php endforeach; ?>
				</select>
				<input type="text" name="third_answer" placeholder="Answer" id="third_answer" required>
				<input type="submit" value="Register">
			</form>
		<form action="login.php">
		<input type="submit" value="Login" />
		</form>
	</div>
</body>
</html>