<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) 
{
	header('Location: ../index.html');
	exit;
}

$calc = hash_hmac('sha256', 'make_admin_server.php', $_SESSION['second_token']);
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
				session_unset();
				$_SESSION['error'] = $error;
				header('Location: profile_client.php');
				exit();
			}
		}
		else
		{
			array_push($error,'Token error, try again!');
			session_unset();
			$_SESSION['error'] = $error;
			header('Location: profile_client.php');
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
        	array_push($error,'Timeout error, try again!');
			session_unset();
			$_SESSION['error'] = $error;
			header('Location: profile_client.php');
			exit();
		}
	}
}
// Preparing connection information for the db
$configs = include('../config/config.php');
$DATABASE_HOST = $configs['host'];
$DATABASE_USER = $configs['username'];
$DATABASE_PASS = $configs['db_pass'];
$DATABASE_NAME = $configs['db_name'];

// Creating connection with db
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) 
{
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Check password is correct
if ($stmt = $con->prepare('SELECT id, pass, admin, google_id FROM accounts WHERE username = ?')) 
{
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password, $admin, $google_id);
        $stmt->fetch();
        $stmt->close();

		// Check if user is already an admin
        if ($admin == 1)
        {
            $_SESSION['error'] = 'User is already an admin!';	
            header('Location: make_admin_client.php');
            exit();
        }

		// Check if user is a google user
		if (is_null($google_id))
        {
            $_SESSION['error'] = 'User is a google user!';	
            header('Location: make_admin_client.php');
            exit();
        }

		// Verify password
        if (!password_verify($_POST['password'], $password)) 
        {
            $_SESSION['error'] = 'Password is Wrong!';	
            header('Location: make_admin_client.php');
            exit();
        }
    }
    else
    {
        $_SESSION['error'] = 'Username does not exist!';	
        header('Location: make_admin_client.php');
        exit();
    }
}

// Configs for the private key
$config = array(
    'digest_alg' => 'sha512',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
);
   
// Create the private and public key
$key = openssl_pkey_new($config);
openssl_pkey_export($key, $private_key, NULL, $config);
$key_details = openssl_pkey_get_details($key);
$public_key = $key_details['key'];

// Prepare to insert into admin_key the new private_password
if ($stmt = $con->prepare('INSERT INTO admin_key VALUES (?, ?)'))
{
    //Prepare Encryption
    $key_something = substr(hash('sha256', $_POST['password'], true), 0, 32);
    $cipher = 'aes-256-gcm';
    $iv_len = openssl_cipher_iv_length($cipher);
    $tag_length = 16;
    $iv = openssl_random_pseudo_bytes($iv_len);
    $tag = ''; // will be filled by openssl_encrypt

    //Encrypting private key
    $key_encrypted = openssl_encrypt($private_key, $cipher, $key_something, OPENSSL_RAW_DATA, $iv, $tag, '', $tag_length);
    $key_encrypted = base64_encode($iv.$key_encrypted.$tag);

	$stmt->bind_param('is', $id, $key_encrypted);
	$stmt->execute();

	// Adding the public_key to the user's row
    if ($stmt = $con->prepare('UPDATE accounts SET public_key = ?, admin = ? WHERE id = ?'))
    {
        $admin = 1;
        $stmt->bind_param('sii', $public_key, $admin, $id);
        $stmt->execute();
        $stmt->close();

		// Selecting the private_key of the current admin user
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
			
			// Decrypting the private key of the current admin user
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
				
				// Looping through all the document_ciphers, where id_user is the one of the current admin user
				while ($row = $result->fetch_assoc())
				{
					// Decripting the document_cipher with the private_key of the current admin user 
					if (!openssl_private_decrypt($row['document_cipher'], $decrypted_curr_cipher, $private_key))
					{
						$error = array();
						array_push($error, 'Error with administration key, please contact a supervisor!');
						$_SESSION['error'] = $error;
						header('Location: profile_client.php');
						exit();
					}

					// encrypt the document_cipher witht the new user public_key
					if (!openssl_public_encrypt($decrypted_curr_cipher, $encrypted_photo_key, $public_key))
					{
						throw new Exception(openssl_error_string());
					}

					// Inserting the new encrypted document_cipher into the table document_key
					if ($stmt = $con->prepare('INSERT INTO document_key VALUES (?, ?, ?)'))
					{
						$stmt->bind_param('iis', $row['id_evaluation'], $id, $encrypted_photo_key);
						$stmt->execute();
						$stmt->close();
						$_SESSION['correct'] = 'Username succesfully made into admin!';	
						header('Location: make_admin_client.php');
						exit();
					}
				}
			}
        }
    }
}
?>