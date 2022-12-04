<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

// Making sure the url utilises https
if($_SERVER['HTTPS'] != 'on')
{
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) 
{
	header('Location: ../index.html');
	exit;
}

// header to decrypt
// Preparing decryption items
$key = $_SESSION['key'];
$cipher = 'aes-256-gcm';
$iv_len = openssl_cipher_iv_length($cipher);
$tag_length = 16;

// Decrypting the image correlated to the selected evaluation
$encrypted = file_get_contents($_POST['image']);
$iv = substr($encrypted, 0, $iv_len);
$ciphertext = substr($encrypted, $iv_len, -$tag_length);
$tag = substr($encrypted, -$tag_length);
$newFinalContent = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

// Creating a temp file with the decrypted picture
if (file_put_contents('../uploads/temp.png', $newFinalContent))
{

}
else
{
	$_SESSION['error'] = $_SESSION['key'];
	header('Location: req_eval_client.php');
	exit();
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
			content="default-src 'self'; 
					script-src 
							'self' 
							https://apis.google.comhttps://apis.google.com 
							https://www.google.com/recaptcha/ 
							https://www.gstatic.com/recaptcha/;
					style-src 
							'self' 
							https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css 
							https://use.fontawesome.com/releases/v5.7.1/css/all.css
							https://fonts.googleapis.com 
							https://www.google.com/recaptcha/ 
							https://www.gstatic.com/recaptcha/;
					form-action 'self';
					img-src 'self' www.gstatic.com;
					frame-src 'self' https://www.google.com/recaptcha/;
					object-src 'self' ;
					base-uri 'self' ;" 
  		/>
		<title>Profile Page</title>
		<link href="../css/style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<style>
    </style>
	</head>
    <nav class="navtop">
		<div>
			<h1>Love Joy</h1>
		</div>
	</nav>
	<?php 
		if (isset($_SESSION['correct']) & !empty($_SESSION['correct'])){echo "<p class='alert alert-success'>". htmlspecialchars($_SESSION['correct']) . " </p>"; $_SESSION['correct'] = NULL;}
		if (isset($_SESSION['error']) & !empty($_SESSION['error'])) {echo "<p class='alert alert-danger'>". htmlspecialchars($_SESSION['error']) . " </p>"; $_SESSION['error'] = NULL;}
	?>
	<body class="loggedin showimage">
		<div class="content">
			<h2>Evaluation: <?php echo htmlspecialchars($_POST['description']);?> Picture</h2>
			 <img src=<?php echo htmlspecialchars('../uploads/temp.png')?> class="center"> 
			<form action="list_eval_client.php" method="POST">

				<input type="hidden" name="token" value="<?php echo htmlspecialchars(hash_hmac('sha256', 'list_eval_client.php', $_SESSION['second_token']));?>"/>
				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">

				<input class="button" name="Go Back" value="Go Back" type="submit" id="contact"/>
			</form>
		</div>
	</body>
</html>