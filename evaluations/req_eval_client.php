<?php
session_start(); // must be before any output

// Force user to load with https
if($_SERVER['HTTPS'] != 'on')
{
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Check that the user is loggedin
if (!isset($_SESSION['loggedin'])) 
{
	header('Location: ../index.html');
	exit;
}

// Preparing connection information for the db
$configs = include('../config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Connect to database
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
	// Creating connection with db
}
 
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
			content="default-src ; 
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
					object-src 'self' ;
					base-uri 'self' ;" 
  		/>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<title>Request Evalutation Page</title>
		<link href="../css/style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	</head>
	<nav class="navtop">
		<div>
			<h1>Love Joy</h1>
			<a href="../profile/profile_client.php"><i class="fas fa-user-circle"></i>Profile</a>
			<a href="req_eval_client.php"><i class="fas fa-dragon"></i>Request Evaluation</a>
			<a href="list_eval_client.php"><i class="fas fa-dragon"></i>View Evaluations</a>
			<a href="../profile/logout_server.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
		</div>
	</nav>
<body class="loggedin reqeval">
	<div class="content">
		<h2>Request Evalutaion Form</h2>
	<div>
	<?php 
		if (isset($_SESSION['correct']) & !empty($_SESSION['correct'])){echo "<p class='alert alert-success'>". htmlspecialchars($_SESSION['correct']) . " </p>"; $_SESSION['correct'] = NULL;}
		if (isset($_SESSION['error']) & !empty($_SESSION['error'])) {echo "<p class='alert alert-danger'>". htmlspecialchars($_SESSION['error']) . " </p>"; $_SESSION['error'] = NULL;}
	?>
	<form action="req_eval_server.php" method="POST" class="signup-form" enctype="multipart/form-data">

		<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
		<input type="hidden" name="token" value="<?php echo htmlspecialchars(hash_hmac('sha256', 'req_eval_server.php', $_SESSION['second_token']))?>"/>

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
				$sql = 'SELECT * FROM accounts where id='.$_SESSION['id'];
				$all_categories = mysqli_query($con,$sql);
				// use a while loop to fetch data
				// from the $all_categories variable
				// and individually display as an option
				$category = mysqli_fetch_array($all_categories,MYSQLI_ASSOC);
			?>
			<option value=<?php echo htmlspecialchars($category["email"]);?>>
				<?php 
				echo "Email: ".htmlspecialchars($category["email"]);
				?>
			</option>
			<option value=<?php echo htmlspecialchars($category["phone_no"]);?>>
				<?php 
				echo "Phone Number: ". htmlspecialchars($category["phone_no"]);
				?>
			</option>
	</select>
	<input type="submit" value="Submit">
	<div class="text-center">
	<div class="g-recaptcha" data-sitekey="6Ldmoj0jAAAAAKYyHaDbjhvncIOSjkFGTxMeT-OG"></div>
	</div>
	</form>
	</div>
</body>
</html>