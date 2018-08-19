<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/helper/paths.php";
require_once WEBROOT . "/helper/database.php";
require_once WEBROOT . '/library/HTMLPurifier.auto.php';

/*
   In a post, only the post content can contain html. However this validation is NOT done when
   inserting a post to database. Instead, htmlspecialchars is used to escape html special
   characters when displaying things like post title.
   Postcont can be directly output as it is purified with HTMLPurifier.
   
*/
						
// create HTML Purifier
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
session_start();

$error = ""; // Error to be displayed if form content is invalid

if (!isset($_SESSION["username"])) { // if not loggged in redirect
    header("location: /login.php?redirect=" .
		   urlencode($_SERVER['REQUEST_URI']));
    die();
} else { // logged in
	$name = $_SESSION["name"];
	$userid = $_SESSION["userID"];
	if (isset($_POST["postcont"])) {

		// Extracting form content

		$postcont = $purifier->purify($_POST["postcont"]);
		$posttitle = $_POST["posttitle"];
		$postdesc = $_POST["postdesc"];
		date_default_timezone_set('Asia/Singapore');
		$mysql_date_now = date("Y-m-d H:i:s");
		$author = $_SESSION["userID"];
		if ($postcont === "" || $posttitle === "") {
			$error = "Error. Post and Post Title cannot be empty. Try again.";
		} else {
			
			// Inserting post into database
			
			$conn = db_connect();
			$stmt = $conn->prepare('INSERT INTO posts ' .
								   '(postTitle, postDesc, postCont, postDate, author) ' .
								   'VALUES (?, ?, ?, ?, ?)');
			$stmt->bind_param('ssssi', $posttitle, $postdesc, $postcont,
							  $mysql_date_now, $author);
			$stmt->execute();

			if ($stmt->error) {
				$error = 'Database erorr. Please try again. <br />' .
						 nl2br(htmlentities($stmt->error));
				$stmt->close();
				$conn->close();
			} else {
				$id = $conn->insert_id;
				$stmt->close();
				$conn->close();
				header("Location: /posts/viewpost.php?id=$id");
				die();
			}
		}
	}
}


?>

<!DOCTYPE html>
<html>
	<head>
		<title>New Post</title>
		<link href="/css/main.css" type="text/css" rel="stylesheet" />
		<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
		<script type="text/javascript">
		 tinymce.init({selector: '#postcont', plugins: 'lists link', target_list: false});
		</script>
	</head>
	<body>
		<header id="header">
			<?php require_once '/srv/http/views/header.php' ?>
		</header>
		<main>
			<div class="error">
				<?php echo htmlspecialchars($error) ?>
			</div>
			<form method="post" action="addpost.php">
				<label>
					Title:
					<input type="text" id="posttitle" name="posttitle" required />
				</label>
				<label>
					Description:
					<input type="text" id="postdesc" name="postdesc" required />
				</label>
				<label>
					Enter Post here:
					<textarea id="postcont" name="postcont"></textarea>
				</label>
				<input type="submit" value="Post" />
			</form>
		</main>
	</body>
</html>
