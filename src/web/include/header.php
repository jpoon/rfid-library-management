<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>EECE 375/474 - Group 9</title>
    <link rel="stylesheet" type="text/css" href="styles/main.css" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="styles/dropDown.css" media="screen,projection" />
    <link rel="icon" href="../images/favicon.ico" type="image/x-icon"/>
</head>

<body>
<?php
if (session_id() == "") {
    session_start();
}

require_once (dirname( __FILE__ ) . '\..\classes\session.class.php');
$session = new Session;
?>

<img src="images/header.jpg" width="800px" height="100px" alt="Header"/>


<table width="800" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
    <tr>
        <td>
            <div id="navigation">
            	<ul>
            		<li><a href="home.php" class="parent">Home</a></li>
            	</ul>
            	<ul>
            		<li><a href="#" class="parent">Catalogue</a>
            		<ul>
                        <li><a href="search.php">Search</a></li>
                    </ul>
                    </li>
            	</ul>
            	<ul>
            		<li><a href="#" class="parent">My Account</a>
            		<ul>
            			<li><a href="profile.php?edit">View/Edit Profile</a></li>
                        <li><a href="bookProcess.php?getOnLoan">Items Out</a></li>
                        <?php if( isset($_COOKIE["auth"])){
                        	echo "<li><a href=\"userProcess.php?searchList\">My Search List</a></li>";
                        } ?>
                        
            		</ul>
            		</li>
            	</ul>
<?php
	if ( isset($_COOKIE["auth"]) ) {
		if ( ($_COOKIE["auth"] == USER_LIBRARIAN) ||
             ($_COOKIE["auth"] == USER_ADMIN) ) {
?>            	
            	<ul>
            		<li><a href="#" class="parent">Librarian</a>
            		<ul>            		
            			<li><a href="librarianProcess.php?checkinout">Check-in/Check-out</a></li>
						<li><a href="librarianProcess.php?viewCatalogue">View Catalogue</a></li>
						<li><a href="librarianProcess.php?findMisplaced">View Misplaced</a></li>
						<li><a href="librarianProcess.php?viewUsers">View Users</a></li>
                        <li><a href="librarianProcess.php?addNewBook">Add to Catalogue</a></li>  
                        <li><a href="librarianProcess.php?removeOrModifyBook&type=change">Modify Catalogue</a></li>  
                        <li><a href="librarianProcess.php?removeOrModifyBook&type=remove">Remove Item</a></li>    
            		</ul>
            		</li>
            	</ul>
<?php
		}
	}
?>
            </div>
        </td>
        <td align="right" valign="bottom">
            <?php
                if ( $session->isUserLoggedIn() ) {
                    echo "Welcome ".$session->username."! [<a href=\"userProcess.php\">Logout</a>]";
                } else {
                    echo "Welcome Guest! [<a href=\"login.php\">Login</a> | <a href=\"register.php\">Register</a>]";
                }
            ?>
        </td>
    </tr>
</table>

<div class="content">