<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/helper/paths.php";
require_once WEBROOT . "/helper/database.php";

session_start();
// ERRORS TO BE DISPLAYED
$name_error = $username_error = $password_error = $email_error = "";
$form_error = "";
// Initialising variables that are default values in form
// So that previous values are displayed if error occured
$username = "";
$password = "";
$name = "";
$email = "";

if (isset($_POST["username"])) { // If form is filled

    $conn = db_connect();
    $name = $_POST["name"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];

    // Validate form entries: validate functions return error string or empty string if no error
    $name_error = validate_name($name);
    $username_error = validate_username($conn, $username);
    $password_error = validate_password($password);
    $email_error = validate_email($email);

    // If form entries are invalid
    if ($name_error . $username_error . $password_error . $email_error . $form_error !== "") {
	$form_error .= "One or more fields were not entered correctly. Try again.";
	$conn->close();
    } else {	
	// hash password
	$hashedpass = password_hash($password, PASSWORD_DEFAULT);
	
	// Insert into database
	$stmt = $conn->prepare('INSERT INTO users (username, password, name, email) ' .
			       'VALUES(?, ?, ?, ?)');
	$stmt->bind_param('ssss', $username, $hashedpass, $name, $email);
	$stmt->execute();
	
	if ($stmt->error) {
	    $form_error = "Database error. Please try again. <br />" .
			  nl2br(htmlentities($stmt->error));
	    $stmt->close();
	    $conn->close();
	} else {
	    $stmt->close();
	    $conn->close();
	    header('Location:' . '/login.php');
	    die();
	}
    }
}

// VALIDATION functions

function validate_name($name) {
    if ($name === "") {
	return 'It is compulsory to enter your name.';
    }
    if (strlen($name) > 255) {
	return 'Your name cannot be longer than 255 characters.';
    }
    if ($name === "Chuck Norris") {
	return 'Database is dead.';
    }

    return "";
}

function validate_password($password) {
    if ($password === "") {
	return 'It is compulsory to enter a password.';
    }

    return "";
}

function validate_username($conn, $username) {
    if ($username === "") {
	return 'It is compulsory to enter a username.';
    }
    if (strlen($username) > 32) {
	return 'Username cannot be longer than 32 characters.';
    }

    $stmt = $conn->prepare('SELECT userID FROM users WHERE username=?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($userID);

    if ($stmt->fetch()) {
	return 'Username already taken. Try again.';

    }

    return "";
}


function validate_email($email) {
    if ($email === "") {
	return 'It is compulsory to enter your email address.';
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
	return "";
    } else {
	return 'Enter a valid email address.';
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
	<title>Blog - New Account</title>
	<link href="/css/main.css" type="text/css" rel="stylesheet" />
    </head>
    <body>
	<header id="header">
	    <?php require_once '/srv/http/views/header.php' ?>
	</header> 	
	<header>
	    <h1>Create An Account</h1>
	</header>
	<main>
	    <div id="form_error"><?php echo $form_error; ?></div>

	    <form method="post" action="signup.php">
		<label for="name">
		    Name:
		</label>
		<input id="name" type="text" value="<?php echo htmlspecialchars($name); ?>"
		       name="name" autofocus maxlength="255" required />
		<div class="error">
		    <?php echo $name_error; ?>
		</div>

		<label for="username">
		    Username:
		</label>
		<input id="username" type="text" value="<?php echo htmlspecialchars($username); ?>"
		       name="username" maxlength="32" required />
		<div class="error">
		    <?php echo $username_error; ?>
		</div>

		<label for="password">
		    Password:
		</label>
		<input id="password" type="password" name="password" required />
		<div class="error">
		    <?php echo $password_error; ?>
		</div>

		<label for="email">
		    Email:
		</label>
		<input id="email" type="email" value="<?php echo htmlspecialchars($email); ?>"
		       name="email" maxlength="255" required />
		<div class="error">
		    <?php echo $email_error; ?>
		</div>
		
		<input type="submit" value="Create Account" />
	    </form>
	</main>
    </body>
</html>
