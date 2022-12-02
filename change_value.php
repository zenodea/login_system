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
			header('Location: change_value_html.php');
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
			header('Location: change_value_html.php');
			exit();
		}
	}
}

// New value inserted by user
$finalValue = $_POST['newValue'];

// If phone number is changed, check length
if ($_SESSION['change'] == "phone")
{
    #Check Phone is correct
    if (!is_numeric($finalValue))
    {
        array_push($error,"Phone number should contain only numbers!");
        $_SESSION['error'] = $error;
        header('Location: change_value_html.php');
        exit();
    }
    if (strlen($finalValue) > 15)
    {
        array_push($error,"Phone number is invalid!");
        $_SESSION['error'] = $error;
        header('Location: change_value_html.php');
        exit();
    }
    $valueToSelect = "phone_no";
}

// If email is changed, check FILTER_VALIDATE_EMAIL
elseif ($_SESSION['change'] == "email")
{
    $error = array();
    if (!filter_var($finalValue, FILTER_VALIDATE_EMAIL)) 
    {
        array_push($error,"Invalid email!");
        $_SESSION['error'] = $error;
        header('Location: change_value_html.php');
        exit();
    }
    $valueToSelect = "email";
}

// If password is changed, check password entropy
elseif ($_SESSION['change'] == "password")
{
    // Validate password strength
    $uppercase = preg_match('@[A-Z]@', $finalValue);
    $lowercase = preg_match('@[a-z]@', $finalValue);
    $number    = preg_match('@[0-9]@', $finalValue);
    $specialChars = preg_match('@[^\w]@', $finalValue);

    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($finalValue) < 8) 
    {
        $error = array();
        if (!$uppercase)
        {
            array_push($error,"Password has to contain at least one UpperCase Letter!");
        }
        if (!$lowercase)
        {
            array_push($error,"Password has to contain at least one LowerCase Letter!");
        }
        if (!$number)
        {
            array_push($error,"Password has to contain at least one Number Letter!");
        }
        if (!$specialChars)
        {
            array_push($error,"Password has to contain at least one Special Character!");
        }
        if (strlen($NEW_PASSWORD) < 8)
        {
            array_push($error,"Password has to longer than 8 characters!");
        }
        $_SESSION['error'] = $error;
        header('Location: change_value_html.php');
        exit();
    }
    $valueToSelect = "password";
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


if ($valueToSelect != "password")
{
    if ($stmt = $con->prepare('UPDATE accounts SET '.$valueToSelect.' = ? WHERE id = ?')) 
    {
            $stmt->bind_param('si',  $finalValue, $_SESSION['id']);
            $stmt->execute();
            $correct = array();
            array_push($correct, "Change succesfully made!");
            $_SESSION['success'] = $correct;
            header('Location: profile.php');
            exit();
    }
}
else
{
    if ($stmt = $con->prepare('SELECT email, phone_no, admin FROM accounts WHERE id = ?'))
    {
        $stmt->bind_param('i', $_SESSION['id']);
        $stmt->execute();
        $stmt->bind_result($email, $phone, $admin);
        $stmt->fetch();
        $stmt->close();

        if($admin == 1)
        {
            if ($stmt = $con->prepare('SELECT p_key FROM admin_key WHERE id = ?'))
            {
                $stmt->bind_param('i', $_SESSION['id']);
                $stmt->execute();
                $stmt->bind_result($encrypted_private_key);
                $stmt->fetch();
                $stmt->close();

                //Prepare Decrypt 
                $password = $_SESSION['password'];
                $key = substr(hash('sha256', $password, true), 0, 32);
                $cipher = 'aes-256-gcm';
                $iv_len = openssl_cipher_iv_length($cipher);
                $tag_length = 16;
                $iv = openssl_random_pseudo_bytes($iv_len);
                $tag = ""; // will be filled by openssl_encrypt
                
                // Private key to decrypt
                $textToDecrypt = $encrypted_private_key;
                $encrypted = base64_decode($textToDecrypt);
                $iv = substr($encrypted, 0, $iv_len);
                $ciphertext = substr($encrypted, $iv_len, -$tag_length);
                $tag = substr($encrypted, -$tag_length);
                $private_key = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
            }
        }

        //Prepare Encyrpt 
        $_SESSION['password'] = $finalValue;
        $password = $_SESSION['password'];
        $key = substr(hash('sha256', $password, true), 0, 32);
        $cipher = 'aes-256-gcm';
        $iv_len = openssl_cipher_iv_length($cipher);
        $tag_length = 16;
        $iv = openssl_random_pseudo_bytes($iv_len);
        $tag = ""; // will be filled by openssl_encrypt

        if ($admin == 1)
        {
            //Encrypting private_key with new key
            $new_enc_p_key = openssl_encrypt($private_key, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, "", $tag_length);
            $final_p_key = base64_encode($iv.$new_enc_p_key.$tag);
        }

        //Salt and Hash new password
        $finalValue = password_hash($finalValue, PASSWORD_DEFAULT);

        if ($stmt = $con->prepare('UPDATE accounts SET  pass = ? WHERE id = ?'))
        {
            $stmt->bind_param('si', $finalValue, $_SESSION['id']);
            $stmt->execute();
            $stmt->close();
            if ($admin == 1)
            {
                if ($stmt = $con->prepare('UPDATE admin_key SET p_key = ? WHERE id = ?'))
                {
                    $stmt->bind_param('si', $final_p_key, $_SESSION['id']);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            $correct = array();
            array_push($correct, "Change succesfully made!");
            $_SESSION['success'] = $correct;
            header('Location: profile.php');
            exit();
        }
    }
}

?>