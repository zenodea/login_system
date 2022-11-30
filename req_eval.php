<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

if (!isset($_SESSION['loggedin'])) 
{
	header('Location: index.html');
	exit;
}

//CSRF token check (and time check)
if(isset($_POST) & !empty($_POST))
{
	if(isset($_POST['csrf_token']))
	{
		if($_POST['csrf_token'] == $_SESSION['csrf_token'])
		{
		}
		else
		{
			$_SESSION['error'] = 'Token Error, try again!';
			session_unset();
			header('Location: register.php');
			exit();
		}
	}
	$maximum_time = 600;
	if (isset($_SESSION['csrf_token_time']))
	{
		$token_time = $_SESSION['csrf_token_time'];
		if(($token_time + $maximum_time) <= time())
		{
			unset($_SESSION['csrf_token_time']);
			unset($_SESSION['csrf_token']);
			$_SESSION['error'] = 'Token Expired, try again!';
			session_unset();
			header('Location: register.php');
			exit();
		}
	}
}

//Captcha Check
if(isset($_POST['g-recaptcha-response']))
{
  $captcha=$_POST['g-recaptcha-response'];
}
$secretKey = "6Ldmoj0jAAAAAIWrcfVRMYAb-C19UvaDA3Me_069";
$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
$response = file_get_contents($url);
$responseKeys = json_decode($response,true);
// should return JSON with success as true
if($responseKeys["success"]) 
{
}
else
{
	$_SESSION['error'] = 'Please complete capcha!';
	header('Location: req_eval_html.php');
	exit();
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

if (!empty($_FILES['userfile']['name'])) 
{
	//Prepare file upload information
	$allowed = array('png', 'jpg');
	$filename = $_FILES['userfile']['name'];
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	$uploaddir = "uploads/";
	$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

	//Check file type
	if (!in_array($ext, $allowed)) 
	{
		$_SESSION['error'] = "Wrong File Format (Please use png or jpg)!";
		header('Location: req_eval_html.php');
		exit();
	}

	// Check if file already exists
	if (file_exists($uploadfile)) 
	{
		$_SESSION['error'] = "File already exists!";
		header('Location: req_eval_html.php');
		exit();
	}

	// Check file size
	if ($_FILES["userfile"]["size"] > 500000) 
	{
		$_SESSION['error'] = "Upload failed, file size to large!";
		header('Location: req_eval_html.php');
		exit();
	}

	// Move File
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
	{
		$_SESSION['correct'] = "The file ". htmlspecialchars(basename( $_FILES["usefile"]["name"])). " has been uploaded.";
	}
	else 
	{
		$_SESSION['error'] = $_FILES['userfile']['tmp_name'];
		header('Location: req_eval_html.php');
		exit();
	}
}
else
{
	$uploadfile = "None";
}

// Preparing variabels
$id = $_SESSION['id'];
$header = $_POST['topic'];
$body = $_POST['body'];
$contact = $_POST['contact'];

//Prepare Encryption
$password_evaluation = uniqid();
$key = substr(hash('sha256', $password_evaluation, true), 0, 32);
$cipher = 'aes-256-gcm';
$iv_len = openssl_cipher_iv_length($cipher);
$tag_length = 16;
$iv = openssl_random_pseudo_bytes($iv_len);
$tag = ""; // will be filled by openssl_encrypt

if ($stmt = $con->prepare("INSERT INTO evaluations (id_user, header, comment, url, contact) VALUES (?, ?, ?, ?, ?)")) 
{
	//Encrypting header 
	$header_encrypt = openssl_encrypt($header, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
	$header_encrypt = base64_encode($iv.$header_encrypt.$tag);

	//Encrypting body
	$body_encrypt = openssl_encrypt($body, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
	$body_encrypt = base64_encode($iv.$body_encrypt.$tag);

	//Encrypting contact 
	$contact_encrypt = openssl_encrypt($contact, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
	$contact_encrypt = base64_encode($iv.$contact_encrypt.$tag);

	$stmt->bind_param('sssss', $id, $header_encrypt, $body_encrypt, $uploadfile, $contact_encrypt);
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
				$key = substr(hash('sha256', $password, true), 0, 32);
				$cipher = 'aes-256-gcm';
				$iv_len = openssl_cipher_iv_length($cipher);
				$tag_length = 16;
				$iv = openssl_random_pseudo_bytes($iv_len);
				$tag = ""; // will be filled by openssl_encrypt

				//Encrypting evaluation key 
				$encrypted_photo_key = openssl_encrypt($password_evaluation, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
				$encrypted_photo_key = base64_encode($iv.$encrypted_photo_key.$tag);

				$stmt->bind_param('iis', $evaluation_id, $row['id'], $encrypted_photo_key);
				$stmt->execute();
				$stmt->close();
			}
  		}
	} 
	else 
	{
		echo "0 results";
	}
} 
$_SESSION['correct'] = "Evaluation succesfully uploaded!";
header('Location: req_eval_html.php');
exit();

?>

