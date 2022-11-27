<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}


if ($_SESSION['change'] == "phone")
{
    $value = "Phone Number";
}
elseif ($_SESSION['change'] == "email")
{
    $value = "Email";
}
elseif ($_SESSION['change'] == "password")
{
    $value = "Password";
    $oldValue = "Hidden";
}

// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'lovejoy_db';

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($stmt = $con->prepare('SELECT email, phone_no FROM accounts WHERE id = ?')) 
{
	$stmt->bind_param('i', $_SESSION['id']);
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($old_email, $old_phone_no);
	$stmt->fetch();
    if ($value == "Phone Number")
    {
        $oldValue = $old_phone_no;
    }
    elseif ($value == "Email")
    {
        $oldValue = $old_email;
    }
}

$token =  bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
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
        <?php 
        if (isset($_SESSION['correct']) & !empty($_SESSION['correct'])){echo "<p class='alert alert-success'>". $_SESSION['correct'] . " </p>"; $_SESSION['correct'] = NULL;}
		if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) 
			{
				if (is_array($_SESSION['error']))
				{
				foreach($_SESSION['error'] as $key => $value)
				{
				echo "<p class='alert alert-danger'>". $value . "</p>"; 
				}
				}
				else
				{
					echo "<p class='alert alert-danger'>". $_SESSION["error"] . " </p>"; 
				}
			$_SESSION['error'] = NULL;
			}
        ?>
		<div class="content">
            <?php
			if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) {echo "<p class='alert alert-danger'>". $_SESSION["error"] . " </p>"; $_SESSION['error'] = NULL;}
			?>
			<h2>Answer Security Questions To Continue</h2>
			<form action="change_value.php" method="POST" autocomplete="off">
			<input type="hidden" name="csrf_token" value="<?php echo $token;?>">

            <label><?php echo "Old " . $value;?></label>
			<input type="text" name="oldValue" value=<?php echo $oldValue;?> id="Old Value" disabled="disabled"><br><br>

            <label><?php echo "New " . $value;?></label>
			<input type="text" name="newValue" placeholder="New Value" id="New Value" required><br><br>

			<input type="submit" value="Continue">
			</form>
	</body>
</html>