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

// Change this to your connection info.
$configs = include('config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Check password is correct
if ($stmt = $con->prepare('SELECT id, pass, admin FROM accounts WHERE username = ?')) 
{
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password, $admin);
        $stmt->fetch();
        $stmt->close();
        if ($admin == 1)
        {
            $_SESSION["error"] = "User is already an admin!";	
            header('Location: make_admin_html.php');
            exit();
        }
        if (!password_verify($_POST['password'], $password)) 
        {
            $_SESSION["error"] = "Password is Wrong!";	
            header('Location: make_admin_html.php');
            exit();
        }
    }
    else
    {
        $_SESSION["error"] = "Username does not exist!";	
        header('Location: make_admin_html.php');
        exit();
    }
}

$config = array(
    "digest_alg" => "sha512",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);
   
// Create the private and public key
$key = openssl_pkey_new($config);
openssl_pkey_export($key, $private_key, NULL, $config);
$key_details = openssl_pkey_get_details($key);
$public_key = $key_details['key'];
if ($stmt = $con->prepare('INSERT INTO admin_key VALUES (?, ?)'))
{
    //Prepare Encryption
    $key_something = substr(hash('sha256', $_POST['password'], true), 0, 32);
    $cipher = 'aes-256-gcm';
    $iv_len = openssl_cipher_iv_length($cipher);
    $tag_length = 16;
    $iv = openssl_random_pseudo_bytes($iv_len);
    $tag = ""; // will be filled by openssl_encrypt

    //Encrypting private key
    $key_encrypted = openssl_encrypt($private_key, $cipher, $key_something, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
    $key_encrypted = base64_encode($iv.$key_encrypted.$tag);

	$stmt->bind_param('is', $id, $key_encrypted);
	$stmt->execute();
    if ($stmt = $con->prepare('UPDATE accounts SET public_key = ?, admin = ? WHERE id = ?'))
    {
        $admin = 1;
        $stmt->bind_param('sii', $public_key, $admin, $id);
        $stmt->execute();
        $stmt->close();
        if ($stmt = $con->prepare('SELECT p_key FROM admin_key WHERE id = ?'))
        {
            $stmt->bind_param('i', $_SESSION['id']);
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
			    
			if ($stmt = $con->prepare('SELECT id_evaluation, document_cipher FROM document_key WHERE id_user = ?'))
			{
				$stmt->bind_param('i', $_SESSION['id']);
				$stmt->execute();
				$result = $stmt->get_result();
				while ($row = $result->fetch_assoc())
				{
					if (!openssl_private_decrypt($row['document_cipher'], $decrypted_curr_cipher, $private_key))
					{
						$error = array();
						array_push($error, "Error with administration key, please contact a supervisor!");
						$_SESSION['error'] = $error;
						header('Location: profile.php');
						exit();
					}
					if (!openssl_public_encrypt($decrypted_curr_cipher, $encrypted_photo_key, $public_key))
					{
						throw new Exception(openssl_error_string());
					}
					if ($stmt = $con->prepare('INSERT INTO document_key VALUES (?, ?, ?)'))
					{
						$stmt->bind_param('iis', $row['id_evaluation'], $id, $encrypted_photo_key);
						$stmt->execute();
						$stmt->close();
					}
				}
			}
        }
    }
}
$_SESSION["correct"] = "Username succesfully made into admin!";	
header('Location: make_admin_html.php');
exit();
?>