<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/helper/paths.php";
require_once WEBROOT . "/helper/database.php";
session_start();
	
if (!isset($_SESSION["username"])) { // If not logged in, redirect
    header("location: /login.php?redirect=" .
	   urlencode($_SERVER['REQUEST_URI']));
    die();
}

// Check if id is set and is an integer
if (!isset($_GET["id"]) || ! filter_var($_GET["id"], FILTER_VALIDATE_INT)) {
    die("Invalid Request");
}

// Check if id is a valid post

$id = $_GET["id"];
$conn = db_connect();
$stmt = $conn->prepare('SELECT postTitle, author FROM posts WHERE postID=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($postTitle, $author);

if (! $stmt->fetch()) { // If post is not found in database
    die("Post not found");
}
if ($author !== $_SESSION['userID']) { // If post author is not current account
    die("You do not have permissions to delete this post.");
}
$postTitle = json_encode($postTitle);
$stmt->close();

// All validation passed, i.e. user is logged in and is deleting his own post.
// If user has not confirmed deletion, show dialog.
// If user clicks ok, call page again with GET parameter 'confirm'

if (!isset($_GET['confirm'])) { // Show confirmation dialog
   echo <<<EOTJAVASCRIPT
<script type="text/javascript">
    if (confirm("Are you sure you want to delete " + $postTitle)) {
      window.location.href = '?confirm&id=' + '$id';
    } else {
      window.location.href = '/posts/viewpost.php?id=' + '$id';
    }
</script>
EOTJAVASCRIPT;
}

if (isset($_GET['confirm'])) { //Confirmed, proceed to delete post
    $conn->query('DELETE FROM posts WHERE postID=' . $id);
    if (! $conn->error) { // Post deleted, alert and redirect back to index
	echo <<<EOTJAVASCRIPT
<script type="text/javascript">
    alert("Post deleted.");
    window.location.href = '/index.php';
</script>
EOTJAVASCRIPT;

    } else { // Deletion fail, alert and redirect back to post page
	echo <<<EOTJAVASCRIPT
<script type="text/javascript">
    alert("Post could not be deleted.");
    window.location.href = '/posts/viewpost.php?id=' + '$id';
</script>
EOTJAVASCRIPT;
    }
}
$conn->close();

?>


