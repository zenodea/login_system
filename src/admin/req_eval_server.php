<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

$configs = include('../config/config.php');

// make sure user is logged in
if (!isset($_SESSION['loggedin'])) 
{
	header('Location: ../index.html');
	exit;
}

$calc = hash_hmac('sha256', 'req_eval_server.php', $_SESSION['second_token']);
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

//Captcha Check
if(isset($_POST['g-recaptcha-response']))
{
  $captcha=$_POST['g-recaptcha-response'];
}

$secretKey = $configs['secret_captcha_key_google'];

$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
$response = file_get_contents($url);
$responseKeys = json_decode($response,true);
// should return JSON with success as true
if($responseKeys['success']) 
{
}
else
{
	$_SESSION['error'] = 'Please complete capcha!';
	header('Location: req_eval_client.php');
	exit();
}

// Preparing connection information for the db
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Creating connection with db
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Make sure that a file has been uploaded
if (!empty($_FILES['userfile']['name'])) 
{
	//Prepare file upload information
	$allowed = array('png', 'jpg');
	$filename = $_FILES['userfile']['name'];
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	$uploaddir = '../uploads/';
	$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

	//Check file type
	if (!in_array($ext, $allowed)) 
	{
		$_SESSION['error'] = 'Wrong File Format (Please use png or jpg)!';
		header('Location: req_eval_client.php');
		exit();
	}

	// Check file size
	if ($_FILES['userfile']['size'] > 500000) 
	{
		$_SESSION['error'] = 'Upload failed, file size to large!';
		header('Location: req_eval_client.php');
		exit();
	}

	// Move File
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
	{
		$_SESSION['correct'] = 'The file '. htmlspecialchars(basename( $_FILES['userfile']['name'])). 'has been uploaded.';
		$newFileName = $uploaddir.uniqid().'.'.$ext;
		rename($uploadfile, $newFileName);
	}
	else 
	{
		$_SESSION['error'] = 'Error in uploading the file, try again!';
		header('Location: req_eval_client.php');
		exit();
	}
}
else
{
	$uploadfile = 'none';
}

// Preparing variabels
$id = $_SESSION['id'];
$header = $_POST['topic'];
$body = $_POST['body'];
$contact = $_POST['contact'];

//Prepare Encryption
$password_evaluation = openssl_random_pseudo_bytes(32); 
$key = substr(hash('sha256', $password_evaluation, true), 0, 32);
$cipher = 'aes-256-gcm';
$iv_len = openssl_cipher_iv_length($cipher);
$tag_length = 16;
$iv = openssl_random_pseudo_bytes($iv_len);
$tag = ""; // will be filled by openssl_encrypt

// Begin insertion of evaluation into the database
if ($stmt = $con->prepare("INSERT INTO evaluations (id_user, header, comment, url, contact) VALUES (?, ?, ?, ?, ?)")) 
{
	//Encrypting Photo
	if ($uploadfile != 'none')
	{
		$contents = file_get_contents($newFileName);
		$contents_encrytped = openssl_encrypt($contents, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
		//Encrypting header 
		if (file_put_contents($newFileName, $iv.$contents_encrytped.$tag))
		{

		}
		else
		{
			$_SESSION['error'] = "Errors uploading file, try again!";
			header('Location: req_eval_client.php');
			exit();
		}
	}
	else
	{
		$newFileName = 'none';
	}
	
	//Encrypting header 
	$header_encrypt = openssl_encrypt($header, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
	$header_encrypt = base64_encode($iv.$header_encrypt.$tag);

	//Encrypting body
	$body_encrypt = openssl_encrypt($body, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
	$body_encrypt = base64_encode($iv.$body_encrypt.$tag);

	//Encrypting contact 
	$contact_encrypt = openssl_encrypt($contact, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
	$contact_encrypt = base64_encode($iv.$contact_encrypt.$tag);

	//Encrypting url 
	$url_encrypt = openssl_encrypt($newFileName, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
	$url_encrypt = base64_encode($iv.$url_encrypt.$tag);

	// Inserting the encrypted information
	if ($newFileName == 'none')
	{
		$stmt->bind_param('sssss', $id, $header_encrypt, $body_encrypt, $newFileName, $contact_encrypt);
	}
	else
	{
		$stmt->bind_param('sssss', $id, $header_encrypt, $body_encrypt, $url_encrypt, $contact_encrypt);
	}

	$stmt->execute();
	$evaluation_id = $con->insert_id;
	$stmt->close();

	$sql = "SELECT id, public_key FROM accounts WHERE admin = 1 ";
	$result = $con->query($sql);	

	if ($result->num_rows > 0)
	{
  		while($row = $result->fetch_assoc()) 	
  		{
			if ($stmt = $con->prepare("INSERT INTO document_key VALUES (?,?,?)"))
			{
				//Prepare Encryption
				$password = $row['public_key'];

				echo $password;
				//Encrypting evaluation key 
				if (!openssl_public_encrypt($password_evaluation, $encrypted_photo_key, $password))
				{
					throw new Exception(openssl_error_string());
				}
				
				$stmt->bind_param('iis', $evaluation_id, $row['id'], $encrypted_photo_key);
				$stmt->execute();
				$stmt->close();
			}
  		}
	} 
	else 
	{
		echo '0 results';
	}
} 
$_SESSION['correct'] = 'Evaluation succesfully uploaded!';
header('Location: req_eval_client.php');
exit();

?>

