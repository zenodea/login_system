<?php
session_start();

// Checking if user is logged in
if (!isset($_SESSION['loggedin'])) 
{
	header('Location: ../index.html');
	exit;
}

// Checking that the url contains https
if($_SERVER['HTTPS'] != 'on')
{
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Preparing connection information for the db
$configs = include('../config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    // Creating connection with db
}

// If a picture has been decrypted, it gets deleted.
if (file_exists('../uploads/temp.png')) 
{
   unlink('../uploads/temp.png');
}

$calc = hash_hmac('sha256', 'list_eval_client.php', $_SESSION['second_token']);
//CSRF token check with per-form token check, also timeout check
if(isset($_POST) & !empty($_POST))
{
	if(isset($_POST['csrf_token']))
	{
		if (hash_equals($calc,$_POST['token']))
		{
			if(hash_equals($_POST['csrf_token'], $_SESSION['csrf_token']))
			{
				// All good, continue...
			}
			else
			{
				array_push($error,'Token error, try again!');
				unset($_SESSION['csrf_token_time']);
				unset($_SESSION['csrf_token']);
				unset($_SESSION['second_token']);
				$_SESSION['error'] = $error;
				header('Location: req_eval_client.php');
				exit();
			}
		}
		else
		{
			array_push($error,'Token error, try again!');
			unset($_SESSION['csrf_token_time']);
			unset($_SESSION['csrf_token']);
			unset($_SESSION['second_token']);
			$_SESSION['error'] = $error;
			header('Location: req_eval_client.php');
			exit();
		}
	}
	$maximum_time = 100;
	if (isset($_SESSION['csrf_token_time']))
	{
		$token_time = $_SESSION['csrf_token_time'];
		if(($token_time + $maximum_time) <= time())
		{
			unset($_SESSION['csrf_token_time']);
			unset($_SESSION['csrf_token']);
			unset($_SESSION['second_token']);
        	array_push($error,'Timeout error, try again!');
			$_SESSION['error'] = $error;
			header('Location: req_eval_client.php');
			exit();
		}
	}
}
// Prepare statement to check if user is admin
if ($stmt = $con->prepare('SELECT admin, id, public_key FROM accounts WHERE id = ?'))
{
	$stmt->bind_param('i',$_SESSION['id']);
	$stmt->execute();
	$stmt->bind_result($admin, $user_id, $public_key);
	$stmt->fetch();
	$stmt->close();
	if ($admin == 0)
	{
		$error = array();
		array_push($error, 'You need to be an administrator to view evaluations!');
		$_SESSION['error'] = $error;
		header('Location: ../profile/profile_client.php');
		exit();
	}
	else
	{
		// Obtain the private key of the current admin
		if ($stmt = $con->prepare('SELECT p_key FROM admin_key WHERE id = ?'))
		{
			$stmt->bind_param('i',$user_id);
			$stmt->execute();
			$stmt->bind_result($p_key);
			$stmt->fetch();
			$stmt->close();

			// Get and decrypt private key for admin
			$password = $_SESSION['password'];
			$key = substr(hash('sha256', $password, true), 0, 32);
			$cipher = 'aes-256-gcm';
			$iv_len = openssl_cipher_iv_length($cipher);
			$tag_length = 16;
			
			// Decrypting the private key
			$textToDecrypt = $p_key;
			$encrypted = base64_decode($textToDecrypt);
			$iv = substr($encrypted, 0, $iv_len);
			$ciphertext = substr($encrypted, $iv_len, -$tag_length);
			$tag = substr($encrypted, -$tag_length);
			$private_key = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

			// query to select all evaluations
			$query = 'SELECT id, id_user, header, comment, url, contact FROM evaluations';
			$result = $con->query($query);
		}
	}
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
	</head>
    <nav class="navtop">
		<div>
			<h1>Love Joy</h1>
			<a href="../profile/profile_client.php"></i>Profile</a>
			<a href="req_eval_client.php"></i>Request Evaluation</a>
			<a href="list_eval_client.php"></i>View Evaluations</a>
			<a href="../profile/logout_server.php"></i>Logout</a>
		</div>
	</nav>
	<?php 
	if (isset($_SESSION['correct']) & !empty($_SESSION['correct']))
	{
		echo "<p class='alert alert-success'>". htmlspecialchars($_SESSION['correct']) . " </p>"; 
		$_SESSION['correct'] = NULL;
	}
	if (isset($_SESSION['error']) & !empty($_SESSION['error'])) 
	{
		echo "<p class='alert alert-danger'>". htmlspecialchars($_SESSION['error']) . " </p>"; 
		$_SESSION['error'] = NULL;
	}
	?>
	<body class="loggedin listeval">
		<div class="content">
			<h2>List of Evaluations</h2>
			<div>
				<table style="table-layout: fixed; width: 100%">
					<tr>
						<th>Account ID</th>
						<th>Header</th>
						<th>Comment</th>
						<th>Image</th>
						<th>Contact</th>
						<th>Resolved</th>
					</tr>
			<?php 
				while ($row = $result->fetch_assoc()) 
				{
					// Obtaining the cipher (associated with the ) for the current document
					if ($stmt = $con->prepare('SELECT document_cipher FROM document_key WHERE id_evaluation = ? AND id_user = ?'))
					{
						$stmt->bind_param('ii',$row['id'],$user_id);
						$stmt->execute();
						$stmt->bind_result($curr_cipher);
						$stmt->fetch();
						$stmt->close();

						// Decrypting the cipher using the private key
						if (!openssl_private_decrypt($curr_cipher, $decrypted_curr_cipher, $private_key))
						{
							$error = array();
							array_push($error, 'Error with administration key, please contact a supervisor!');
							$_SESSION['error'] = $error;
							header('Location: ../profile/profile.php');
							exit();
						}
					}
					// Preparing decryption items
					$password = $decrypted_curr_cipher;
					$key = substr(hash('sha256', $password, true), 0, 32);
					$_SESSION['key'] = $key;
					$cipher = 'aes-256-gcm';
					$iv_len = openssl_cipher_iv_length($cipher);
					$tag_length = 16;

					?><td><?php echo htmlspecialchars($row['id_user']);?></td><?php
					// header to decrypt
					$textToDecrypt = $row['header'];
					$encrypted = base64_decode($textToDecrypt);
					$iv = substr($encrypted, 0, $iv_len);
					$ciphertext = substr($encrypted, $iv_len, -$tag_length);
					$tag = substr($encrypted, -$tag_length);
					$email = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
					?><td><?php echo htmlspecialchars($email);?></td><?php

					// comment to decrypt
					$textToDecrypt = $row['comment'];
					$encrypted = base64_decode($textToDecrypt);
					$iv = substr($encrypted, 0, $iv_len);
					$ciphertext = substr($encrypted, $iv_len, -$tag_length);
					$tag = substr($encrypted, -$tag_length);
					$comment = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
					?><td><?php echo htmlspecialchars($comment);?></td><?php

					// contact to decrypt
					$textToDecrypt = $row['contact'];
					$encrypted = base64_decode($textToDecrypt);
					$iv = substr($encrypted, 0, $iv_len);
					$ciphertext = substr($encrypted, $iv_len, -$tag_length);
					$tag = substr($encrypted, -$tag_length);
					$contact = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
					if ($row['url'] == 'none')
					{
						?>
						<td><?php echo htmlspecialchars('None');?></td>
						<td> 
						<?php
					}
					else
					{
						// url to decrypt
						$textToDecrypt = $row['url'];
						$encrypted = base64_decode($textToDecrypt);
						$iv = substr($encrypted, 0, $iv_len);
						$ciphertext = substr($encrypted, $iv_len, -$tag_length);
						$tag = substr($encrypted, -$tag_length);
						$url = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
						?>
					<form action="show_img_client.php" method="POST">
						<input type="hidden" name="description" value=<?php echo htmlspecialchars($email);?> id="description" hidden>
						<input type="hidden" name="image" value=<?php echo htmlspecialchars($url);?> id="image" hidden>
						<td><input type="submit" value="See Picture" id="see_pic"></td>
					</form>
						<td> 
						<?php
					}
					?>
					<input type="submit" value=<?php echo htmlspecialchars($contact)?> id="contact"/>
						<td>
					<form action="remove_eval_server.php" method="POST">

						<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
						<input type="hidden" name="token" value="<?php echo htmlspecialchars(hash_hmac('sha256', 'remove_eval_server.php', $_SESSION['second_token']));?>"/>

						<input type="hidden" name="remove" value=<?php echo htmlspecialchars($row['id']);?> id="remove" />
						<input class="button" name="submit_button" value="Remove" type="submit" id="remove"/>
					</form>
					</td>
					</tr>
					<?php } ?>
				</table><br>
			</div>
		</div>
	</body>
</html>