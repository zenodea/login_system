<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) 
{
	header('Location: index.html');
	exit;
}

// Saving the value wanted to be changed
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
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) 
{
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
	$stmt->close();
    if ($value == "Phone Number")
    {
        $oldValue = $old_phone_no;
    }
    elseif ($value == "Email")
    {
        $oldValue = $old_email;
    }
}

// Preparing CSRF Token
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
				<h1>Love Joy</h1>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="req_eval_html.php"><i class="fas fa-dragon"></i>Request Evaluation</a>
				<a href="list_eval.php"><i class="fas fa-dragon"></i>View Evaluations</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
        <?php 
			if (isset($_SESSION['correct']) & !empty($_SESSION['correct']))
			{
				echo "<p class='alert alert-success'>". htmlspecialchars($_SESSION['correct']) . " </p>"; $_SESSION['correct'] = NULL;
			}
			if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) 
			{
				if (is_array($_SESSION['error']))
				{
					foreach($_SESSION['error'] as $key => $values)
					{
						echo "<p class='alert alert-danger'>". htmlspecialchars($values) . "</p>"; 
					}
				}
				else
				{
					echo "<p class='alert alert-danger'>". htmlspecialchars($_SESSION["error"]) . " </p>"; 
				}
				$_SESSION['error'] = NULL;
			}
        ?>
		<div class="content">
            <?php
				if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) 
				{
					echo "<p class='alert alert-danger'>". htmlspecialchars($_SESSION["error"]) . " </p>"; 
					$_SESSION['error'] = NULL;
				}
			?>
			<h2>Answer Security Questions To Continue</h2>
			<form action="change_value.php" method="POST" autocomplete="off">
				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">

				<label><?php echo "Old " . htmlspecialchars($value);?></label>
					<input type="text" name="oldValue" value=<?php echo $oldValue;?> id="Old Value" disabled="disabled"><br><br>

				<label><?php echo "New " . htmlspecialchars($value);?></label>
					<input type="text" name="newValue" placeholder="New Value" id="New Value" required><br><br>

				<input type="submit" value="Continue">
			</form>
	</body>
</html>