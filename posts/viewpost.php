<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/helper/paths.php";
require_once WEBROOT . "/helper/database.php";
session_start();
$userID = "";

if (isset($_SESSION["username"])) {
   $userID = $_SESSION['userID'];
}

if (!isset($_GET["id"]) || ! filter_var($_GET["id"], FILTER_VALIDATE_INT)) {
    die("Invalid Request");
}

$id = $_GET["id"];
$conn = db_connect();
$stmt = $conn->prepare('SELECT posts.postID, posts.postTitle, posts.postDesc, ' .
		       'posts.postCont, posts.postDate, posts.author, users.name ' .
		       'FROM posts LEFT JOIN users ' .
		       'ON posts.author=users.userID ' .
		       'WHERE posts.postID=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($postID, $posttitle, $postdesc, $postcont, $postdate, $authorID, $authorname);

if (! $stmt->fetch()) {
    die("Post not found");
}

$stmt->close();
$conn->close();

$posttitle = htmlspecialchars($posttitle);
$postdesc = htmlspecialchars($postdesc);
$postdate = htmlspecialchars($postdate);
$authorname = htmlspecialchars($authorname);

if ($userID === $authorID) { // Display form controls if user is author
    $post_controls = "<a href='/posts/editpost.php?id=$postID'>Edit</a>" .
		     "<a href='/posts/deletepost.php?id=$postID'>Delete</a>";
} else {
    $post_controls = "";
}


$content = <<<EOTHTML
<div class="post" id="$postID">
    <h1 class="postTitle">$posttitle</h1>
    <div class="postdet">
         Posted by: $authorname on $postdate
    </div>
    <div class="postControl">
        $post_controls
    </div>
    <div class="postCont">
       $postcont
    </div>
</div>
EOTHTML;
?>

<!DOCTYPE html>
<html>
    <head>
	<title><?php echo $posttitle; ?></title>
	<link href="/css/main.css" type="text/css" rel="stylesheet" />
    </head>
    <body>
	<header id="header">
	    <?php require_once '/srv/http/views/header.php' ?>
	</header>
	<main>
	    <?php echo $content; ?>
	</main>
    </body>
</html>w







