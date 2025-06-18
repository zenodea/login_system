<?php
session_start();

if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();

}

require_once('../vendor/autoload.php'); 

use RobThree\Auth\TwoFactorAuth;

// Preparing and setting CSRF token
$token =  bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();

// Per-form csrf token
$second_token = bin2hex(random_bytes(32));
$_SESSION['second_token'] = $second_token;
?>
<!doctype html>
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
					img-src 'self' www.gstatic.com data:;
					frame-src 'self' https://www.google.com/recaptcha/;
					object-src 'self' ;
					base-uri 'self' ;" 
  		/>
		<title>Activate Two Factor Authentication</title>
		<link href="../css/style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body class="loggedin fa">
		<nav class="navtop">
			<div>
				<h1>Love Joy</h1>
				<a href="../profile/profile_client.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="../evaluations/req_eval_client.php"><i class="fas fa-dragon"></i>Request Evaluation</a>
				<a href="../evaluations/list_eval_client.php"><i class="fas fa-dragon"></i>View Evaluations</a>
				<a href="../profile/logout_server.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
            <h2>Activate Two Factor Authentication</h2>
            <div>
                <?php
                    // in practice you would require the composer loader if it was not already part of your framework or project
                    spl_autoload_register(function ($className) {
                        include_once str_replace(array('RobThree\\Auth', '\\'), array(__DIR__.'/../lib', '/'), $className) . '.php';
                    });

                    // substitute your company or app name here
                    $tfa = new RobThree\Auth\TwoFactorAuth('Lovejoy 2FA');
                    $secret = $tfa->createSecret();
                ?>
                <li>
                    Please scan the QR code below with your preffered 2FA service:<br>
                    <img src="<?php echo $tfa->getQRCodeImageAsDataUri('Demo', $secret); ?>"><br>
                    ...or manually insert the code:
                    <?php echo chunk_split($secret, 4, ' '); ?>
                </li>
                <?php
                    $code = $tfa->getCode($secret);
                ?>
                <li>Next, have the user verify the code; at this time the code displayed by a 2FA-app would be: <span style="color:#00c"><?php echo $code; ?></span> (but that changes periodically)</li>
                <li>When the code checks out, 2FA can be / is enabled; store (encrypted?) secret with user and have the user verify a code each time a new session is started.</li>
                <li>
                    When aforementioned code (<?php echo htmlspecialchars($code); ?>) was entered, the result would be:
                    <?php if ($tfa->verifyCode($secret, $code) === true) { ?>
                        <span style="color:#0c0">OK</span>
                    <?php } else { ?>
                        <span style="color:#c00">FAIL</span>
                    <?php } ?>
                </li>
            <form action="2fa_activate_server.php" method="POST">

				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
		        <input type="hidden" name="token" value="<?php echo htmlspecialchars(hash_hmac('sha256', '2fa_activate_server.php', $_SESSION['second_token']))?>"/>

                <input type=hidden value=<?php echo htmlspecialchars($secret);?> name="secret" />
                <input type="submit" value="Complete (Make sure the app is connected before continuing)" />
            </form>
            <br>
            <form action="../profile/profile_client.php" method="POST">
				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token);?>">
                <input type="submit" value="Go Back" />
            </form>
        </div>
    </div>
</body>
</html>