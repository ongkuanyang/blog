<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/helper/paths.php";
require_once WEBROOT . "/helper/database.php";
session_start();
$error = "";
$message = "";
$action = '/login.php';

// Check for redirect GET parameter. If set, display a message.
if (isset($_GET["redirect"])) {
    $action = '/login.php?redirect=' . urlencode($_GET["redirect"]);
    $message = 'You need to login to view this page.';
}

if (isset($_POST["user"])) { // Check form entries
    $conn = db_connect();
    $user = $_POST["user"];
    $pass = $_POST["pass"];

    if ($user === "" || $pass === "") {
	$error = "Not all fields were entered.";
    } else {
	$stmt = $conn->prepare('SELECT username, name, userID, password ' .
			       'FROM users WHERE username=?');
	$stmt->bind_param('s', $user);
	$stmt->execute();

	if ($stmt->error) {
	    $error = "Database error. Please try again. <br />" .
		     nl2br(htmlentities($stmt->error));
	    $stmt->close();
	    $conn->close();
	} else {
	    $stmt->bind_result($username, $name, $userID, $hash);

	    if ($stmt->fetch() && password_verify($pass, $hash)) {
		$_SESSION['username'] = $username;
		$_SESSION['name'] = $name;
		$_SESSION['userID'] = $userID;
		$stmt->close();
		$conn->close();
		if (isset($_GET["redirect"])) {
		    header('Location:' . $_GET["redirect"]);
		    die();
		} else {
		    header('Location:' . '/index.php');
		    die();
		}
	    } else {
		$stmt->close();
		$conn->close();
		$error = "Username/Password is invalid. Retry.";
	    }
	    
	}
    }
}		
?>
<!DOCTYPE html>
<html>
    <head>
	<title>Blog - Sign In</title>
	<link href="/css/main.css" type="text/css" rel="stylesheet" />
    </head>
    <body>
	<header id="header">
	    <?php require_once '/srv/http/views/header.php' ?>
	</header>

	<main>
	    <div id="error"><?php echo $error; ?></div>
	    <div id="message"><?php echo $message; ?></div>
	    <form method="post" action="<?php echo $action ?>">
		<label for="user">
		    Username:
		</label>
		<input id="user" type="text" name="user" autofocus required />
		<label for="password">
		    Password:
		</label>
		<input id="password" type="password" name="pass" required />
		<input type="submit" value="Sign in" />
	    </form>

	    <div id="signup">
		Don't have an account? <a href="/signup.php">Sign up now.</a>
	    </div>
	</main>
    </body>
</html>
