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
	if ($admin == 0)
	{
	}
	else
	{
		$query = "SELECT id, id_user, header, comment, url, contact FROM evaluations";
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
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<style>
select{
    text-align-last:right;
    padding-right: 29px;
    direction: rtl;
	width:120px;   

}
			td {
    word-break: break-all;
    word-break: break-word;
}
		#list{
			font-size: 10px;
			text-align: center;
			font-weight: bold;
			appearance:listbox;
			width: 100%;
			background-color: #90EE90;
			border: 1;
			border-color: black;
	}
		#see_pic{
			width: 100%;
			background-color: #90EE90;
			border: 0;
			cursor: pointer;
			font-weight: bold;
			transition: background-color 0.2s;
	}
		#remove{
			width: 100%;
			background-color: #ff6961;
			border: 0;
			cursor: pointer;
			font-weight: bold;
			transition: background-color 0.2s;
	}
		#contact{
			width: 100%;
			background-color: #FFD580;
			border: 0;
			cursor: pointer;
			font-weight: bold;
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
	<?php 
	if (isset($_SESSION['correct']) & !empty($_SESSION['correct'])){echo "<p class='alert alert-success'>". $_SESSION['correct'] . " </p>"; $_SESSION['correct'] = NULL;}
	if (isset($_SESSION["error"]) & !empty($_SESSION["error"])) {echo "<p class='alert alert-danger'>". $_SESSION["error"] . " </p>"; $_SESSION['error'] = NULL;}
	?>
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
					<th>Contact</th>
					<th>Resolved</th>
  				</tr>
                <?php foreach ($result as $row): array_map('htmlentities', $row); ?>
                <tr>
                <td><?php echo $row['id_user'];?></td>
                <td><?php echo $row['header'];?></td>
                <td><?php echo $row['comment'];?></td>
				<?php 
					if ($row['url'] == "None")
					{
						?>
						<td><?php echo "None";?></td>
						<td> 
						<?php
					}
					else
					{
						?>
					<form action="show_image.php" method="POST">
						<input type="hidden" name="description" value=<?php echo $row['header'];?> id="description" hidden>
						<input type="hidden" name="image" value=<?php echo $row['url'];?> id="image" hidden>
						<td><input type="submit" value="See Picture" id="see_pic"></td>
					</form>
						<td> 
						<?php
					}
					?>
				<input type="submit" value=<?php echo $row['contact'];?> id="contact"/>
					<td>
			<form action="remove_eval.php" method="POST">
				<input type="hidden" name="remove" value=<?php echo $row['id'];?> id="remove" />
				<input class="button" name="submit_button" value="Remove" type="submit" id="remove"/>
			</form>
				</td>
                </tr>
            <?php endforeach; ?>
				</table><br>
			</div>
		</div>
	</body>
</html>