<?php
  session_start(); // must be before any output

  if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;

	session_start(); // must be before any output
	if(isset($_POST) & !empty($_POST))
	{
	  if(isset($_POST['csrf_token']))
	  {
		  if($_POST['csrf_token'] == $_SESSION['csrf_token'])
		  {
			  echo"token recognized";
		  }
		  else
		  {
			  $errors[] = "Issues With Token";
		  }
	  }
	  $maximum_time = 5;
	  if (isset($_SESSION['csrf_token_time']))
	  {
		  $token_time = $_SESSION['csrf_token_time'];
		  if(($token_time + $maximum_time) >= time())
		  {
			  unset($_SESSION['csrf_token_time']);
			  unset($_SESSION['csrf_token']);
			  echo 'token expired';
		  }
		  else
		  {
			  $errors[] =  "all good";
		  }
	  }
	}
	$token = md5(uniqid(rand(), true));
	$_SESSION['csrf_token'] = $token;
	$_SESSION['csrf_token_time'] = time();
}
?>

<!DOCTYPE html>
<html>
	<head>
		<style>
			form {
			display: flex;
			flex-wrap: wrap;
			justify-content: center;
			padding-top: 20px;
		}
		form label {
			display: flex;
			justify-content: center;
			align-items: center;
			width: 50px;
		height: 50px;
			background-color: #435165;
			color: #ffffff;
		}
		form input[type="password"], .register form input[type="text"], .register form input[type="email"] {
			width: 310px;
			height: 50px;
			border: 1px solid #dee0e4;
			margin-bottom: 20px;
			padding: 0 15px;
		}
		form input[type="submit"] {
			width: 100%;
			padding: 15px;
			margin-top: 20px;
			background-color: #435165;
		border: 0;
			cursor: pointer;
			font-weight: bold;
			color: #ffffff;
			transition: background-color 0.2s;
		}
		form input[type="submit"]:hover {
		background-color: #435165;
			transition: background-color 0.2s;
		}
		</style>
		<meta charset="utf-8">
		<title>Request Evalutation Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
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
<div>
<form action="req_eval.php" method="POST" class="signup-form" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
  <label for="topic">Topic</label><br><br>
  <input type="text" id="topic" name="topic"><br><br>
  <label for="body">Body</label><br><br>
  <input type="text" id="body" name="body"><br><br>
  <input type="hidden" name="MAX_FILE_SIZE" value="512000" />
  <input name="userfile" type="file" />
  <input type="submit" value="Submit">
</form>
 </div>
</body>
</html>