<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/helper/paths.php";
require_once WEBROOT . "/helper/database.php";
session_start();
$posts_per_page = 5;
$userID = "";
$mypostsonly = ""; // mysql WHERE CLAUSE that will be set if GET myposts is set

if (isset($_SESSION['username'])) {
    $userID = $_SESSION['userID'];
    $username = $_SESSION['username'];
}
if (isset($_GET['myposts'])) { //display user's post only
    if (! $userID) { // if not logged in, redirect
	header('location: /login.php?redirect=' .
	       urlencode($_SERVER['REQUEST_URI']));
	die();
    } else { // set mysql WHERE clause
	$mypostsonly = ' WHERE author=' . $userID;
    }
}

// Count posts to calculate pages

$conn = db_connect();
$result = $conn->query('SELECT COUNT(*) FROM posts' . $mypostsonly);
$row = $result->fetch_row();
$post_count = (int) $row[0];

if ($post_count === 0) {
    $maximum_pages = 1;
} else {
    $maximum_pages = ceil($post_count / $posts_per_page);
}

// SET current page

if (!isset($_GET['p']) ||  ! filter_var($_GET['p'], FILTER_VALIDATE_INT) || $_GET['p'] <= 0) {
    $current_page = 1;
} elseif ($_GET['p'] > $maximum_pages) {
    $current_page = $maximum_pages;
} else {
    $current_page = $_GET['p'];
}

// first post for LIMIT clause
$start = ($current_page - 1) * $posts_per_page;

if ($post_count === 0) {
    $content = '<p>There are no posts to display.</p>';
} else { // Retrieve posts
    $content = ""; // posts content that will be echoed in <main> element
    $query = 'SELECT * FROM posts LEFT JOIN users ' .
	     'ON posts.author=users.userID ' .
	     $mypostsonly .
	     ' ORDER BY postDate DESC, postID ' .
	     'LIMIT ' . $start . ', ' . $posts_per_page;

    $result = $conn->query($query);
    if (!$result) die($conn->error . "<br \>" . htmlspecialchars($query));
    $rows = $result->num_rows;

    for ($j = 0 ; $j < $rows ; ++$j) { // LOOP THROUGH POSTS
	$result->data_seek($j);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$postID = $row['postID'];
	$postTitle = htmlspecialchars($row['postTitle']);
	$postCont = $row['postCont'];
	$postDate = htmlspecialchars($row['postDate']);
	$author = htmlspecialchars($row['name']);
	$authusername = $row['username'];
	$post_controls = "";
	// Display post controls if user is author of post
	if ($username && $username === $authusername) {
	    $post_controls = "<a href='/posts/editpost.php?id=$postID'>Edit</a>" .
			     "<a href='/posts/deletepost.php?id=$postID'>Delete</a>";
	}

	// Generate post html
	
	$content .= <<<EOTHTML
<div class="post" id="$postID">
    <h1 class="postTitle"><a href="/posts/viewpost.php?id=$postID">$postTitle</a></h1>
    <div class="postdet">
        Posted by: $author on $postDate
    </div>
    <div class="postControl">
        $post_controls
    </div>
    <div class="postCont">
        $postCont
    </div>
</div>

EOTHTML;
    }
    $result->close();
}

$conn->close();

// PAGE NAVIGATION

// Generate previous page link
if ($current_page > 1) {
    $pagenav = '<a href="?p=' . ($current_page - 1) . '">Prev</a>';
} else {
    $pagenav = "";
}

// Generate pages links: first and last page and 2 pages around current page
foreach(range(1, $maximum_pages) as $page) {
    // Check if we're on the current page in the loop
    if($page == $current_page){
        $pagenav .= '<span class="currentpage">' . $page . '</span>';
    } elseif ($page == 1 || $page == $maximum_pages || ($page >= $current_page - 2 && $page <= $current_page + 2)) {
        $pagenav .= '<a href="?p=' . $page . '">' . $page . '</a> ';
    }
}

if ($current_page < $maximum_pages) {
    $pagenav .= '<a href="?p=' . ($current_page + 1) . '">Next</a>';
}

?>

<!DOCTYPE html>
<html>
    <head>
	<title>Blog - Main Page</title>
	<link href="/css/main.css" type="text/css" rel="stylesheet" />
    </head>
    <body>
	<header id="header">
	    <?php require_once '/srv/http/views/header.php' ?>
	</header>
	<main>
	    <?php echo $content; ?>
	</main>
	<footer>
	    Pages: 
	    <?php echo $pagenav; ?>
	</footer>
    </body>
</html>
