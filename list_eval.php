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
					<th>Remove</th>
  				</tr>
                <?php foreach ($result as $key => $row): array_map('htmlentities', $row); ?>
                <tr>
                <td><?php echo implode('</td><td>', $row); ?></td>
				<td> <form action="index.html">
			<select id="list" value="Contact">
				<?php
					// Get all the categories from category table
					$sql = "SELECT * FROM `accounts` where id=2";
					$all_categories = mysqli_query($con,$sql);
					// use a while loop to fetch data
					// from the $all_categories variable
					// and individually display as an option
					$category = mysqli_fetch_array($all_categories,MYSQLI_ASSOC);
				?>
                <option value="<?php echo $category["id"];
                    // The value we usually set is the primary key
                ?>">
                    <?php echo $category["email"];
                        // To show the category name to the user
                    ?>
                </option>
                <option value="<?php echo $category["id"];
                    // The value we usually set is the primary key
                ?>">
                    <?php echo $category["username"];
                        // To show the category name to the user
                    ?>
                </option>
			</select>
			<td> 
			<input type="submit" value="Contact" id="contact"/>
				<td>
			<input type="submit" value="Remove" id="remove"/>
				</form>
				</td>
                </tr>
            <?php endforeach; ?>
				</table><br>
			</div>
		</div>
	</body>
</html>