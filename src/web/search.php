<?php
// Header
include_once (dirname( __FILE__ ) . '\include\header.php');
?>
        <form name="search" method="post" action="bookProcess.php">
            <input type="hidden" name="subsearch" value="1">
<?php
                include (dirname( __FILE__ ) . '\forms\search.php');
?>
        </form>


<?php
// Footer
include (dirname( __FILE__ ) . '\include\footer.php');
?>