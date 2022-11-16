<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}
// Change this to your connection info.
$DATABASE_HOST = '127.0.0.1';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'firstexample';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    echo "yikes";
}

if ($stmt = $con->prepare('SELECT admin FROM accounts WHERE id = ?'))
{
	$stmt->bind_param('i',$_SESSION['id']);
	$stmt->execute();
	$stmt->bind_result($admin);
	$stmt->fetch();
	$stmt->close();
	echo $admin;
	if ($admin == 0)
	{
	}
	else
	{
		$query = "SELECT id_user, header, comment, url FROM evaluations";
		$result = $con->query($query);
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
		<style>
			td {
    word-break: break-all;
    word-break: break-word;
}
		form input[type="submit"] {
    width: 100%;
    background-color: #ff6961;
   border: 0;
    cursor: pointer;
    font-weight: bold;
    color: #ffffff;
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
				<h1>Website Title</h1>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="req_eval_html.php"><i class="fas fa-dragon"></i>Request Evaluation</a>
				<a href="list_eval.php"><i class="fas fa-dragon"></i>View Evaluations</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
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
  				</tr>
                <?php foreach ($result as $row): array_map('htmlentities', $row); ?>
                <tr>
                <td><?php echo implode('</td><td>', $row); ?></td>
				<td> 
				<form action="index.html">
			<input type="submit" value="Remove" />
				</form>
				</td>
                </tr>
            <?php endforeach; ?>
				</table><br>
			</div>
		</div>
	</body>
</html>