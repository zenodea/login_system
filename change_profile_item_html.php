<?php
session_start();

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}

//Value of the change (password, email, phone)
$_SESSION['change'] = $_POST['value'];

//Storing the security questions
$questions = array(
    1 => "What city were you born in?",
    2 => "What is your oldest sibling’s middle name?",
    3 => "What was the first concert you attended?",
    4 => "What was the make and model of your first car?",
    5 => "In what city or town did your parents meet?",
);

// Change this to your connection info.
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

//Get the salted and hashed security questions
if ($stmt = $con->prepare('SELECT id_one, id_two, id_three FROM security_questions WHERE id = ?')) 
{
	$stmt->bind_param('i', $_SESSION['id']);
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($one, $two, $three);
	$stmt->fetch();
	$stmt->close();
}

//Preparing and storing csrf token
$token =  bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
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

		<title>Profile Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body class="loggedin changeprof">
		<nav class="navtop">
			<div>
				<h1>Love Joy</h1>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="req_eval_html.php"><i class="fas fa-dragon"></i>Request Evaluation</a>
				<a href="list_eval.php"><i class="fas fa-dragon"></i>View Evaluations</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
            <?php
				if (isset($_SESSION['correct']) & !empty($_SESSION['correct'])){echo "<p class='alert alert-success'>". $_SESSION['correct'] . " </p>"; $_SESSION['correct'] = NULL;}
				if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) {echo "<p class='alert alert-danger'>". $_SESSION["error"] . " </p>"; $_SESSION['error'] = NULL;}
			?>
			<h2>Answer Security Questions To Continue</h2>
			<form action="change_profile_item_check.php" method="POST" autocomplete="off">
				<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				
				<label for=<?php echo $questions[$one] ?>><?php echo htmlspecialchars($questions[$one]) ?></label>
					<input type="text" name="first_answer" placeholder="Answer" id="first_answer" required><br><br>

				<label for=<?php echo $questions[$two] ?>><?php echo htmlspecialchars($questions[$two]) ?></label>
					<input type="text" name="second_answer" placeholder="Answer" id="second_answer" required><br><br>

				<label for=<?php echo $questions[$three] ?>><?php echo htmlspecialchars($questions[$three]) ?></label>
					<input type="text" name="third_answer" placeholder="Answer" id="third_answer" required><br><br>

				<input type="submit" value="Continue">
			</form>
	</body>
</html>