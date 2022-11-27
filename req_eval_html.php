<?php
session_start(); // must be before any output

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}

// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'lovejoy_db';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
	echo "yikes";
}

$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>

<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<style>
		select {
			display: inline-block;
			width: 310px;
			height: 50px;
			border: 1px solid #dee0e4;
			margin-top: 20px;
			margin-bottom: 20px;
		}
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
		form input[type="password"], .register form input[type="text"], .register form input[type="email"] {
			text-align: center;
			width: 310px;
			height: 50px;
			border: 1px solid #dee0e4;
			margin-top: 20px;
			margin-bottom: 20px;
			font-weight: bold;
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
		<meta charset="utf-8">
		<title>Request Evalutation Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<nav class="navtop">
		<div>
			<h1>Website Title</h1>
			<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
			<a href="req_eval_html.php"><i class="fas fa-dragon"></i>Request Evaluation</a>
			<a href="list_eval.php"><i class="fas fa-dragon"></i>View Evaluations</a>
			<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
		</div>
	</nav>
<body class="loggedin">
<div class="content">
<h2>Request Evalutaion Form</h2>
<div>
<?php 
if (isset($_SESSION['correct']) & !empty($_SESSION['correct'])){echo "<p class='alert alert-success'>". $_SESSION['correct'] . " </p>"; $_SESSION['correct'] = NULL;}
if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) {echo "<p class='alert alert-danger'>". $_SESSION["error"] . " </p>"; $_SESSION['error'] = NULL;}
?>
<form action="req_eval.php" method="POST" class="signup-form" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
  <label for="topic">Topic</label>
  <input type="text" id="topic" name="topic"><br><br>
  <label for="body">Body</label>
  <input type="text" id="body" name="body"><br><br>
  <input type="hidden" name="MAX_FILE_SIZE" value="512000" />
  <label for="body" style="width : 200px">Upload Picture</label>
  <input name="userfile" type="file" /><br><br>
  <label for="body" style="width : 200px">Contact Method</label>
  <select name="contact">
		<?php
			// Get all the categories from category table
			$sql = "SELECT * FROM `accounts` where id=".$_SESSION['id'];
			$all_categories = mysqli_query($con,$sql);
			// use a while loop to fetch data
			// from the $all_categories variable
			// and individually display as an option
			$category = mysqli_fetch_array($all_categories,MYSQLI_ASSOC);
		?>
		<option value=<?php echo $category["email"];?>>
			<?php echo "Email: ".$category["email"];?>
		</option>
		<option value=<?php echo $category["phone_no"];?>>
			<?php echo "Phone Number: ". $category["phone_no"];?>
		</option>
  </select>

  <input type="submit" value="Submit">
</form>
 </div>
</body>
</html>