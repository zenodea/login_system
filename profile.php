<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'firstexample';
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// We don't have the password or email info stored in sessions so instead we can get the results from the database.
$stmt = $con->prepare('SELECT phone_no, email, admin FROM accounts WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($phone, $email, $admin);
$stmt->fetch();
$stmt->close();

if ($admin == 0)
{
	$admin = "False";
}
else
{
	$admin = "True";
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Profile Page</title>
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
			<h2>Profile Page</h2>
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
				</table><br>
		<form action="change_profile_item_html.php" method="POST">
			<input type=hidden value="password" name="value" />
			<input type="submit" value="Change Password" />
		</form>
		<br>
		<form action="change_profile_item_html.php" method="POST">
			<input type=hidden value="email" name="value" />
			<input type="submit" value="Change Email" />
		</form>
		<br>
		<form action="change_profile_item_html.php" method="POST">
			<input type=hidden value="phone" name="value" />
			<input type="submit" value="Change Phone" />
		</form>
			</div>
		</div>
	</body>
</html>