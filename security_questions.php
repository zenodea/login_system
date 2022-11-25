<?php
  session_start(); // must be before any output
  
  $token = md5(uniqid(rand(), true));
  $_SESSION['csrf_token'] = $token;
  $_SESSION['csrf_token_time'] = time();

  $questions = array(
    1 => "What city were you born in?",
    2 => "What is your oldest sibling’s middle name?",
    3 => "What was the first concert you attended?",
    4 => "What was the make and model of your first car?",
    5 => "In what city or town did your parents meet?",
);
?>

<!DOCTYPE html>
<html>
	<head>
		<style>
		select {
			display: inline-block;
			width: 310px;
			height: 50px;
			border: 1px solid #dee0e4;
			margin-top: 20px;
			margin-bottom: 20px;
		}
        </style>
		<meta charset="utf-8">
		<title>Register</title>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="login">
			<h1>Security Questions</h1>
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
			<form action="register_insertion.php" method="POST" autocomplete="off">
			<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
            <select name="first_question" id="first_question">
                <option value="">--- select security question ---</option>
            <?php foreach($questions as $value => $key): ?>
                <option value=<?= $value; ?>><?= $key; ?></option>
            <?php endforeach; ?>
            </select>
				<input type="text" name="first_answer" placeholder="Answer" id="first_answer" required>
            <select name="second_question" id="second_question">
                <option value="">--- Select Security Question ---</option>
            <?php foreach($questions as $value => $key): ?>
                <option value=<?= $value; ?>><?= $key; ?></option>
            <?php endforeach; ?>
            </select>
				<input type="text" name="second_answer" placeholder="Answer" id="second_answer" required>
            <select name="third_question" id="third_question">
                <option value="">--- Select Security Question ---</option>
            <?php foreach($questions as $value => $key): ?>
                <option value=<?= $value; ?>><?= $key; ?></option>
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