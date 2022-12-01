<?php
session_start();

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
		header('Location: profile.php');
		exit();
	}
	else
	{

		if ($stmt = $con->prepare("SELECT p_key FROM admin_key WHERE id = ?"))
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
			
			$textToDecrypt = $p_key;
			$encrypted = base64_decode($textToDecrypt);
			$iv = substr($encrypted, 0, $iv_len);
			$ciphertext = substr($encrypted, $iv_len, -$tag_length);
			$tag = substr($encrypted, -$tag_length);
			$private_key = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

			$query = "SELECT id, id_user, header, comment, url, contact FROM evaluations";
			$result = $con->query($query);
		}
	}
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
			select{
				text-align-last:right;
				padding-right: 29px;
				direction: rtl;
				width:120px;   

			}
			td {
				word-break: break-all;
				word-break: break-word;
			}
			#list{
					font-size: 10px;
					text-align: center;
					font-weight: bold;
					appearance:listbox;
					width: 100%;
					background-color: #90EE90;
					border: 1;
					border-color: black;
			}
			#see_pic{
					width: 100%;
					background-color: #90EE90;
					border: 0;
					cursor: pointer;
					font-weight: bold;
					transition: background-color 0.2s;
			}
			#remove{
					width: 100%;
					background-color: #ff6961;
					border: 0;
					cursor: pointer;
					font-weight: bold;
					transition: background-color 0.2s;
			}
			#contact{
					width: 100%;
					background-color: #FFD580;
					border: 0;
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
	if (isset($_SESSION['correct']) & !empty($_SESSION['correct']))
	{
		echo "<p class='alert alert-success'>". $_SESSION['correct'] . " </p>"; 
		$_SESSION['correct'] = NULL;
	}
	if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) 
	{
		echo "<p class='alert alert-danger'>". $_SESSION["error"] . " </p>"; 
		$_SESSION['error'] = NULL;
	}
	?>
	<body class="loggedin">
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
					if ($stmt = $con->prepare('SELECT document_cipher FROM document_key WHERE id_evaluation = ? AND id_user = ?'))
					{
						$stmt->bind_param('ii',$row['id'],$user_id);
						$stmt->execute();
						$stmt->bind_result($curr_cipher);
						$stmt->fetch();
						$stmt->close();
						if (!openssl_private_decrypt($curr_cipher, $decrypted_curr_cipher, $private_key))
						{
							$error = array();
							array_push($error, "Error with administration key, please contact a supervisor!");
							$_SESSION['error'] = $error;
							header('Location: profile.php');
							exit();
						}
					}
					// Preparing decryption items
					$password = $decrypted_curr_cipher;
					$key = substr(hash('sha256', $password, true), 0, 32);
					$cipher = 'aes-256-gcm';
					$iv_len = openssl_cipher_iv_length($cipher);
					$tag_length = 16;

					?><td><?php echo $row['id_user'];?></td><?php
					// header to decrypt
					$textToDecrypt = $row['header'];
					$encrypted = base64_decode($textToDecrypt);
					$iv = substr($encrypted, 0, $iv_len);
					$ciphertext = substr($encrypted, $iv_len, -$tag_length);
					$tag = substr($encrypted, -$tag_length);
					$email = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
					?><td><?php echo $email;?></td><?php

					// comment to decrypt
					$textToDecrypt = $row['comment'];
					$encrypted = base64_decode($textToDecrypt);
					$iv = substr($encrypted, 0, $iv_len);
					$ciphertext = substr($encrypted, $iv_len, -$tag_length);
					$tag = substr($encrypted, -$tag_length);
					$comment = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
					?><td><?php echo $comment;?></td><?php

					// contact to decrypt
					$textToDecrypt = $row['contact'];
					$encrypted = base64_decode($textToDecrypt);
					$iv = substr($encrypted, 0, $iv_len);
					$ciphertext = substr($encrypted, $iv_len, -$tag_length);
					$tag = substr($encrypted, -$tag_length);
					$contact = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
					if ($row['url'] == "None")
					{
						?>
						<td><?php echo "None";?></td>
						<td> 
						<?php
					}
					else
					{
						?>
					<form action="show_image.php" method="POST">
						<input type="hidden" name="description" value=<?php echo $email;?> id="description" hidden>
						<input type="hidden" name="key" value=<?php echo $decrypted_curr_cipher;?> id="what" hidden>
						<input type="hidden" name="image" value=<?php echo $row['url'];?> id="image" hidden>
						<td><input type="submit" value="See Picture" id="see_pic"></td>
					</form>
						<td> 
						<?php
					}
					?>
					<input type="submit" value=<?php echo $contact;?> id="contact"/>
						<td>
					<form action="remove_eval.php" method="POST">
						<input type="hidden" name="remove" value=<?php echo $row['id'];?> id="remove" />
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