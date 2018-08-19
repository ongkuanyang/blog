<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/helper/paths.php";
require_once WEBROOT . "/helper/database.php";
$header_welcome = "";
$authenticated_controls = "";
$account_controls = <<<EOTHTML
<li class="right" id="login"><a href="/login.php">Log In</a></li>
<li class="right" id="signup"><a href="/signup.php">Sign Up</a></li>
EOTHTML;
if (isset($_SESSION['username'])) {
    $header_welcome = 'Hi ' . $_SESSION['name'] . ' (' . $_SESSION['username'] . ')!!!';
    $authenticated_controls = <<<EOTHTML
    <li><a href="/index.php?myposts">My Posts</a></li>
    <li><a href="/posts/addpost.php">New Post</a></li>
EOTHTML;
    $account_controls = <<<EOTHTML
    <li class="right" id="logout"><a href="/logout.php">Log Out</a></li>
EOTHTML;
}

?>
<ul id="navbar">
    <li><a href="/index.php">Home</a></li>
    <li><a href="/about.php">About</a></li>
    <?php echo $authenticated_controls; ?>
    <?php echo $account_controls; ?>
</ul> 
<div id="welcome"><?php echo $header_welcome ?></div>
