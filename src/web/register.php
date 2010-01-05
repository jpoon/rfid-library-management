<?php
include_once (dirname( __FILE__ ) . '\include\header.php');

if( $session->isUserLoggedIn() ) {
    echo "<p>We're sorry <b>$session->username</b>, but you've already registered. ";
} else if ( isset($_SESSION['regSuccess']) ) {
    /* Registration was successful */
    unset($_SESSION['regSuccess']);
    echo "<div class=\"title\">Registered!</div>";
    echo "<p>Thank you for taking the time to register, your information has been added to the database
             you may now <a href=\"login.php\">log in</a>.</p>";
} else {

?>

<div class="title">Register</div>
<form action="userProcess.php" method="POST">
    <input type="hidden" name="subjoin" value="1">
<?php       // Form
        include (dirname( __FILE__ ) . '\forms\createAccount.php');
?>
</form>

<?php
}
include (dirname( __FILE__ ) . '\include\footer.php');
?>
