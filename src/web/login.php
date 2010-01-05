<?php
// Header
include_once (dirname( __FILE__ ) . '\include\header.php');

if ( $session->isUserLoggedIn() ) {
    if ( isset($_SESSION['url']) ) {
        $url = $_SESSION['url'];
        unset($_SESSION['url']);
        header('Location: '.$url);
    } else {
        header('Location: home.php');
    }
    exit;
}

?>
        <div class="title">Login</div>
        <form name="login" method="post" action="userProcess.php">
            <input type="hidden" name="sublogin" value="1">
<?php
                include (dirname( __FILE__ ) . '\forms\loginForm.php');
?>
        </form>


<?php
// Footer
include (dirname( __FILE__ ) . '\include\footer.php');
?>