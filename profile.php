<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();

// If the user is not logged in redirect to the login page...
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
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// We don't have the password or email info stored in sessions so instead we can get the results from the database.
if ($stmt = $con->prepare('SELECT phone_no, email, admin FROM accounts WHERE id = ?'))
{
	// In this case we can use the account ID to get the account info.
	$stmt->bind_param('i', $_SESSION['id']);
	$stmt->execute();
	$stmt->bind_result($phone, $email, $admin);
	$stmt->fetch();
	$stmt->close();
}
else
{

}

if ($admin == 0)
{
	$admin = "False";
}
else
{
	$admin = "True";
}

if ($stmt = $con->prepare('SELECT id FROM 2fa WHERE id = ?'))
{
	// In this case we can use the account ID to get the account info.
	$stmt->bind_param('i', $_SESSION['id']);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt->num_rows > 0)
	{
		$twofact = "Active";
	}
	else
	{
		$twofact = "Not Active";
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta
			http-equiv="Content-Security-Policy"
			content="default-src 'none'; 
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
					object-src 'self' 'none';
					base-uri 'self' 'none';" 
  		/>
		<title>Profile Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<h1>Love Joy</h1>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="req_eval_html.php"><i class="fas fa-dragon"></i>Request Evaluation</a>
				<a href="list_eval.php"><i class="fas fa-dragon"></i>View Evaluations</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
			<h2>Profile Page</h2>
			<?php
				if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) 
				{
					foreach($_SESSION['error'] as $key => $value)
					{
						echo "<p class='alert alert-danger'>". $value . "</p>"; 
					}
				}
				$_SESSION['error'] = NULL;
				if (isset($_SESSION['success']) & !empty($_SESSION['success']))
				{
					foreach($_SESSION['success'] as $key => $value)
					{
						echo "<p class='alert alert-success'>". $value . "</p>"; 
					}
				}
				$_SESSION['success'] = NULL;
			?>
			<div>
				<p>Your account details are below:</p>
				<table>
					<tr>
						<td>Username:</td>
						<td><?=htmlspecialchars($_SESSION['name'])?></td>
					</tr>
					<tr>
						<td>Phone Number:</td>
						<td><?=htmlspecialchars($phone)?></td>
					</tr>
					<tr>
						<td>Email:</td>
						<td><?=htmlspecialchars($email)?></td>
					</tr>
					<tr>
						<td>Admin:</td>
						<td><?=htmlspecialchars($admin)?></td>
					</tr>
					<tr>
						<td>2 Factor Authentication:</td>
						<td><?=htmlspecialchars($twofact)?></td>
					</tr>
				</table><br>
			<?php
				if ($twofact == "Not Active")
				{
					?>
					<form action="twofactorauth_html.php" method="POST">
					<input type="submit" value="Activate 2FA" />
					</form>
					<?php
				}
				else
				{
					?>
					<form action="twofactor_deactivate.php" method="POST">
					<input type="submit" value="Deactivate 2FA" />
					</form>
					<?php
				}
			?>
		<br>
		<form action="change_profile_item_html.php" method="POST"  class="signup-form">
			<input type=hidden value="password" name="value" />
			<input type="submit" value="Change Password" />
		</form>
		<br>
		<form action="change_profile_item_html.php" method="POST"  class="signup-form">
			<input type=hidden value="email" name="value" />
			<input type="submit" value="Change Email" />
		</form>
		<br>
		<form action="change_profile_item_html.php" method="POST" class="signup-form">
			<input type=hidden value="phone" name="value" />
			<input type="submit" value="Change Phone" />
		</form>
		<?php
		if ($admin == "True")
		{
			?>
				<form action="make_admin_html.php" method="POST">
					<br>
					<input type="submit"  value="Make admin"/>
				</form>
			<?php
		}
		?>
			</div>
		</div>
	</body>
</html>