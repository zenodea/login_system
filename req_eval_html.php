<?php
session_start(); // must be before any output

if (!isset($_SESSION['loggedin'])) 
{
	header('Location: index.html');
	exit;
}

// Change this to your connection info.
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
	echo "yikes";
}
 
// Preparing decryption
$password = $_SESSION['password'];
$key = substr(hash('sha256', $password, true), 0, 32);
$cipher = 'aes-256-gcm';
$iv_len = openssl_cipher_iv_length($cipher);
$tag_length = 16;

// Preparing and setting CSRF token
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
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<title>Request Evalutation Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<script src='https://www.google.com/recaptcha/api.js' async defer></script>
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
<body class="loggedin reqeval">
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

				//Decrypt Phone Number
				$phone = $category["phone_no"];
				$encrypted = base64_decode($phone);
				$iv = substr($encrypted, 0, $iv_len);
				$ciphertext = substr($encrypted, $iv_len, -$tag_length);
				$tag = substr($encrypted, -$tag_length);
				$phone = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

				//Decrypt Email 
				$email = $category["email"];
				$encrypted = base64_decode($email);
				$iv = substr($encrypted, 0, $iv_len);
				$ciphertext = substr($encrypted, $iv_len, -$tag_length);
				$tag = substr($encrypted, -$tag_length);
				$email = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
			?>
			<option value=<?php echo $email;?>>
				<?php 
				echo "Email: ".$email;
				?>
			</option>
			<option value=<?php echo $phone;?>>
				<?php 
				echo "Phone Number: ". $phone;
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