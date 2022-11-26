<?php
session_start();
$_SESSION['change'] = $_POST['value'];

$questions = array(
    1 => "What city were you born in?",
    2 => "What is your oldest sibling’s middle name?",
    3 => "What was the first concert you attended?",
    4 => "What was the make and model of your first car?",
    5 => "In what city or town did your parents meet?",
);

// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'firstexample';

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($stmt = $con->prepare('SELECT id_one, id_two, id_three FROM security_questions WHERE id = ?')) 
{
	$stmt->bind_param('i', $_SESSION['id']);
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($one, $two, $three);
	$stmt->fetch();
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Profile Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <style>
		form {
			text-align: center;
			align-items: center;
			flex-wrap: wrap;
			justify-content: center;
			padding-top: 20px;
		}
		form label 
		{
			display: inline-block;
			vertical-align: middle;
			text-align: center;
			justify-content: center;
			align-items: center;
			width: 200px;
			height: 50px;
			font-weight: bold;
			background-color: #435165;
			color: #ffffff;
		}
		form input[type="submit"] {
			display: inline-block;
			text-align: center;
			width: 100%;
			padding: 15px;
			margin-top: 20px;
			background-color: #435165;
			border: 0;
			cursor: pointer;
			font-weight: bold;
			color: #ffffff;
			transition: background-color 0.2s;
		}
		form input[type="submit"]:hover {
		background-color: #435165;
			transition: background-color 0.2s;
		}
            </style>
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<h1>Website Title</h1>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="req_eval_html.php"><i class="fas fa-dragon"></i>Request Evaluation</a>
				<a href="list_eval.php"><i class="fas fa-dragon"></i>View Evaluations</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
            <?php
			if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) {echo "<p class='alert alert-danger'>". $_SESSION["error"] . " </p>"; $_SESSION['error'] = NULL;}
			?>
			<h2>Answer Security Questions To Continue</h2>
			<form action="change_profile_item_check.php" method="POST" autocomplete="off">
			<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
            <label for=<?php echo $questions[$one] ?>><?php echo $questions[$one] ?></label>
				<input type="text" name="first_answer" placeholder="Answer" id="first_answer" required><br><br>
            <label for=<?php echo $questions[$two] ?>><?php echo $questions[$two] ?></label>
				<input type="text" name="second_answer" placeholder="Answer" id="second_answer" required><br><br>
            <label for=<?php echo $questions[$three] ?>><?php echo $questions[$three] ?></label>
				<input type="text" name="third_answer" placeholder="Answer" id="third_answer" required><br><br>
			<input type="submit" value="Continue">
			</form>
	</body>
</html>