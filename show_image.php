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
// Preparing and setting CSRF token
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
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<style>
			select {
				text-align-last:right;
				padding-right: 29px;
				direction: rtl;
				width:120px;   

			}
			td {
				word-break: break-all;
				word-break: break-word;
			}
			.center {
			display: block;
			margin-left: auto;
			margin-right: auto;
			padding-top: 30px;
			padding-bottom: 30px;
			width: 50%;
			}
			#list {
				font-size: 10px;
				text-align: center;
				font-weight: bold;
				appearance:listbox;
				width: 100%;
				background-color: #90EE90;
				border: 1;
				border-color: black;
		    }
			#remove {
				width: 100%;
				background-color: #ff6961;
				border: 0;
				cursor: pointer;
				font-weight: bold;
				transition: background-color 0.2s;
		    }
			#contact {
				width: 20%;
				background-color: #FFD580;
				border: 1;
				cursor: pointer;
				font-weight: bold;
				transition: background-color 0.2s;
			}
			table {
			border-collapse: collapse;
			}
			th {
			border: 1px solid back;
			padding-left: 50px;
			padding-right: 50px;
			}
			td {
			padding: 10px;
			border: 1px solid;
			}
		tr:nth-child(even) {
			background-color: #D6EEEE;
			}
    </style>
	</head>
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
		if (isset($_SESSION['correct']) & !empty($_SESSION['correct'])){echo "<p class='alert alert-success'>". $_SESSION['correct'] . " </p>"; $_SESSION['correct'] = NULL;}
		if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) {echo "<p class='alert alert-danger'>". $_SESSION["error"] . " </p>"; $_SESSION['error'] = NULL;}
	?>
	<body class="loggedin">
		<div class="content">
			<h2>Evaluation: <?php echo $_POST['description'];?> Picture</h2>
			 <img src=<?php echo $_POST['image']?> class="center"> 
			<form action="list_eval.php" method="POST">
				<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				<input class="button" name="Go Back" value="Go Back" type="submit" id="contact"/>
			</form>
		</div>
	</body>
</html>