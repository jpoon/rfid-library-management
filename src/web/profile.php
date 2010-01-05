<?php
include_once (dirname( __FILE__ ) . '\include\header.php');

if( !$session->isUserLoggedIn() ) {
    $path = $_SERVER['PHP_SELF'];
    $queryString = $_SERVER['QUERY_STRING'];
    $url = $path . "?" . $queryString;

    /* Set referrer page and forward user to login page */
    $_SESSION['url'] = $url;
    session_write_close();
    header("Location: login.php");
}

$queryString = $_SERVER['QUERY_STRING'];

switch ($queryString) {
    case "edit":
        print " <form action=\"userProcess.php\" method=\"POST\">
                    <input type=\"hidden\" name=\"subedit\" value=\"1\">";
        include (dirname( __FILE__ ) . '\forms\editAccount.php');
        print " </form>";
        break;
    case "holds":
    case "fines":
}


// HTML Footer
include (dirname( __FILE__ ) . '\include\footer.php');
?>