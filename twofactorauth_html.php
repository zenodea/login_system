<?php
session_start();
require_once(__DIR__.'/vendor/autoload.php'); 

use RobThree\Auth\TwoFactorAuth;
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Activate Two Factor Authentication</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<h1>Website Title</h1>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="req_eval_html.php"><i class="fas fa-dragon"></i>Request Evaluation</a>
				<a href="list_eval.php"><i class="fas fa-dragon"></i>View Evaluations</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
            <h2>Activate Two Factor Authentication</h2>
            <div style="text-align: center;">
                <?php
                    // in practice you would require the composer loader if it was not already part of your framework or project
                    spl_autoload_register(function ($className) {
                        include_once str_replace(array('RobThree\\Auth', '\\'), array(__DIR__.'/../lib', '/'), $className) . '.php';
                    });

                    // substitute your company or app name here
                    $tfa = new RobThree\Auth\TwoFactorAuth('Lovejoy');
                ?>
                <?php
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
                    When aforementioned code (<?php echo $code; ?>) was entered, the result would be:
                    <?php if ($tfa->verifyCode($secret, $code) === true) { ?>
                        <span style="color:#0c0">OK</span>
                    <?php } else { ?>
                        <span style="color:#c00">FAIL</span>
                    <?php } ?>
                </li>
            <form action="twofactorauth.php" method="POST">
                <input type=hidden value=<?php echo $secret;?> name="secret" />
                <input type="submit" value="Complete (Make sure the app is connected before continuing)!" />
            </form>
        </div>
    </div>
</body>
</html>