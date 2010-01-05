<?php
require_once(dirname( __FILE__ ) . '\classes\book.class.php');
require_once(dirname( __FILE__ ) . '\classes\session.class.php');

class LibrarianProcess{

	private $book;
	private $form;

	public function LibrarianProcess() {
	    if (session_id() == "") {
		  session_start();
	    }
	    
        include_once (dirname( __FILE__ ) . '\include\header.php');
           	if ( isset($_COOKIE["auth"]) ) {
			if (($_COOKIE["auth"] == USER_LIBRARIAN) ||
             	($_COOKIE["auth"] == USER_ADMIN) ) {
		
				$this->book = new Book();
				//$this->session->form = new Form();
		
		        $queryString = $_SERVER['QUERY_STRING'];
		
		        if (preg_match( '/checkinout/', $queryString)) {
		            $this->procCheckInOut();
		        } elseif ( preg_match( '/viewCatalogue/', $queryString) ) {
		            $this->procViewCatalogue();
		        } elseif( preg_match( '/changeBookDetails/', $queryString ) ) {
		        	$this->procChangeBookDetails($session);
		        } elseif( preg_match('/confirmBookDetails/', $queryString ) ){
					$this->procConfirmBookDetails($session);
				} elseif( preg_match('/addNewBook/', $queryString) ){
					$this->procAddBook($session);
				} elseif( preg_match('/removeOrModifyBook/', $queryString ) ){
					$this->procRemoveOrModifyBook($session);
				} elseif( preg_match('/confirmBookRemove/', $queryString )){
					$this->procConfirmBookRemove($session);
				} elseif (preg_match('/findMisplaced/', $queryString )){
					$this->procFindMisplaced();
				} elseif( preg_match( '/viewUsers/', $queryString )){
					$this->procViewUsers();
				}
			}
		} else {
			echo "<h2>ERROR: 401 - Not Authenticated</h2>";
			echo "<p><b>You are not authenticated to view this page.</b><br />";
			echo "Please <a href=\"login.php\">login</a> with correct access rights.</p>";
			
		    $path = $_SERVER['PHP_SELF'];
		    $queryString = $_SERVER['QUERY_STRING'];
		    $url = $path . "?" . $queryString;
		
		    /* Set referrer page and forward user to login page */
		    $_SESSION['url'] = $url;
		    session_write_close();
		}
		include (dirname( __FILE__ ) . '\include\footer.php');
	}
	
	private function procViewCatalogue() {
		echo "<div class=\"title\">Complete Library Catalogue</div>";
        $this->book->ViewCatalogue();
	}
	
	private function procCheckInOut() {
		echo "<div class=\"title\">Book Check-In/Check-Out</div>";
		
		$callNo = "";
		$rfid = "";
		
	    $s = @$_GET['s'];
	    switch ($s) {
	    	case 1:
				$_SESSION['value_array'] = $_POST;
				$this->form = new Form();
				if( isset( $_GET['callNo'] ) ){
					$callNo = @$_GET['callNo'];
				}
				else{
					$callNo = $this->form->getValue("callNo");
    				$rfid = $this->form->getValue("rfid");
				}

	    		break;
	    	case 2:
	    		// form has been submitted
	    		$callNo = @$_GET['callNo'];
	    		$rfid = @$_GET['rfid'];
	    		$newState = $_POST['state'];
	    		break;
	    }

		echo "<p><form name=\"checkinout\" method=\"post\" action=\"librarianProcess.php?checkinout&s=1\">
				<table>
					<tr>
						Please enter book information using either option 1 or 2:
					</tr>
					<tr>
						<td>
							<table class=\"checkout\">
								<tr>
									<td colspan=4 align=\"center\"><i>Option 1</i></td>
								</tr>
								<tr>
							        <td width=\"80\" align=\"right\">Call Number</td>
							        <td>:</td>
			        				<td><input name=\"callNo\" maxlength=\"30\" value=$callNo></td>
			        				<td><input type=\"submit\" name=\"submit\" value=\"Find\"></td>
							    </tr>
							</table>
						</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>
							<table class=\"checkout\">
								<tr>
									<td colspan=4 align=\"center\"><i>Option 2</i></td>
								</tr>
								<tr>
							        <td width=\"80\" align=\"right\">RFID</td>
							        <td>:</td>
			        				<td><input type=\"button\" value=\"Scan\" style='width: 200px' onclick=\"location.href='serialProcess.php?getIDNum&type=checkinout'\" /></td>
							    </tr>
							</table>
						</td>
					</tr>
				</table>
			</form></p>";

	    if ( !empty($s) ) {
	    	if (!empty($callNo)) {
	    		echo "<p>Searched for Call Number: $callNo</p>";
	    	} elseif (!empty($rfid)) {
	    		echo "<p>Searched for RFID: $rfid</p>";
	    	}
	    	
	        $book = $this->book->BookDetail($callNo);
	        
            if ($book == null) return;
            
			$title = $book["Title"];
	        $author = $book["Author"];
	        $callNo = $book["CallNo"];
	        $publisher = $book["Publisher"];
	        $state = $book["State"];
	        $isbn = $book["ISBN"];

    		if ($s == 2) { 	
				if ($state == $newState) {
					$output = "<div class=\"formError\">Changes NOT saved. Old and new book status are equal.</div>";
				} else if( $newState == "On Loan") {
					if( empty($_POST['userId']) ) $output = "<div class=\"formError\">Changes NOT saved. Enter a valid Card Number</div>";
							// sign book out under userId
							else {
								$this->book->checkOut($callNo, $_POST['userId']);
								$state = $newState;
								$output = "<div class=\"formSuccess\">Changes Saved.</div>";
							}
				} else {
					switch($newState) {
						case "In Stacks":
							$this->book->CheckIn($callNo);
							break;
						case "In Reserve":
							$this->book->CheckIn($callNo);
							break;
					}
					$state = $newState;
					$output = "<div class=\"formSuccess\">Changes Saved.</div>";
				}
			}	
			echo "	<p><table class=\"bookDetail\">
						<tr>
							<td class=\"highLightRowCol\" width=\"85px\">Title</td>
							<td>$title</td>
						</tr>
						<tr>
							<td class=\"highLightRowCol\">Author</td>
							<td>$author</td>
						</tr>
						<tr>
							<td class=\"highLightRowCol\">Call Number</td>
							<td>$callNo</td>
						</tr>
						<tr>
							<td class=\"highLightRowCol\">ISBN</td>
							<td>$isbn</td>
						</tr>
						<tr>
							<td class=\"highLightRowCol\">Publisher</td>
							<td>$publisher</td>
						</tr>
						<tr>
							<td class=\"highLightRowCol\">Status</td>
					        <td>";
			if( $state != "In Stacks" && $state != "In Reserve" && $state != "On Loan" ){
				echo "<b>$state: Cannot Check In or Out</b>";
			} else {
				echo "<form name=\"changeState\" method=\"post\" action=\"librarianProcess.php?checkinout&s=2&callNo=$callNo\">
					   <select name=\"state\">";
					        	
				if( $state == "In Stacks"){
					echo 			"<option selected value=\"In Stacks\">In Stacks</option>";
					echo 			"<option value=\"On Loan\">On Loan</option>";
				}
				else if( $state == "In Reserve"){
					echo 			"<option selected value=\"In Reserve\">In Reserve</option>";
					echo 			"<option value=\"On Loan\">On Loan</option>";
				}
				else if( $state == "On Loan"){
					echo			"<option selected value=\"On Loan\">On Loan</option>";
					echo 			"<option value=\"In Stacks\">In Stacks</option>";
				}
			
				echo "	</select>
						 &nbsp;";
				if( $state == "In Stacks" || $state == "In Reserve" ){
					echo "Card No: <input type=\"text\" style=\"width: 80px\" name=\"userId\">&nbsp;&nbsp;";
				}
				echo "<input type=\"submit\" name=\"submit\" value=\"Save\">";
				echo	"</form>";
				    	if ($s == 2) { 	
							echo $output;
						}			
			}
			echo "			</td>
						</tr>
					</table></p>";

        }
	}
	
	private function procAddBook($session){
		
		$queryType = "add";
		
		$shelves = $this->book->getShelfDescription();
		
		echo "<h2>Add New Book</h2>
			  <p>Please enter the book details:</p>";
	    
	    include (dirname( __FILE__ ) . '\forms\changeBookDetails.php');
	    		
	}
	
	private function procChangeBookDetails($session){
		
		$queryType = "change";
		
		$shelves = $this->book->getShelfDescription();
		
		if( $session->form->getValue("callNo") == "" ){
			 echo "<h2>ERROR: Book Not Found!</h2>
			 		<p><b>Please check inputted information and <a href=\"librarianProcess.php?removeOrModifyBook&type=change\">try again</a>.</b></p>";
		}
		else{
			echo "<h2>Modify Book</h2>
				  <p>Please enter the book details:</p>";
		    
		    include (dirname( __FILE__ ) . '\forms\changeBookDetails.php');
		}		
	}
	
	private function procRemoveOrModifyBook($session){
		
		$type = @$_GET['type'];
		
		if( $type == "remove" ) echo "<div class=\"title\">Remove Book From Catalogue</div>";
		else echo "<div class=\"title\">Modify Book From Catalogue</div>";
		
		echo "<p><form name=\"checkinout\" method=\"post\" action=\"bookProcess.php?setupChangeList&type=".$type."\">
				<input type=\"hidden\" name=\"mode\" value=\"1\">
				<table>
					<tr>
						Please enter book information using either option 1 or 2:
					</tr>
					<tr>
						<td>
							<table class=\"checkout\">
								<tr>
									<td colspan=4 align=\"center\"><i>Option 1</i></td>
								</tr>
								<tr>
							        <td width=\"80\" align=\"right\">Call Number</td>
							        <td>:</td>
			        				<td><input name=\"callNo\" maxlength=\"30\" value=".$session->form->getValue("callNo")."></td>
			        				<td><input type=\"submit\" name=\"submit\" value=\"Find\"></td>
							    </tr>
							</table>
						</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>
							<table class=\"checkout\">
								<tr>
									<td colspan=4 align=\"center\"><i>Option 2</i></td>
								</tr>
								<tr>
							        <td width=\"80\" align=\"right\">RFID</td>
							        <td>:</td>
			        				<td><input type=\"button\" value=\"Scan\" style='width: 200px' onclick=\"location.href='serialProcess.php?getIDNum&type=".$type."'\" /></td>
							    </tr>
							</table>
						</td>
					</tr>
				</table>
			</form></p>";
	}
	
	/**
	 * procConfirmed
	 * Once the user determines the details are set
	 * this function is used to call the appropriate database function
	 * to make changes on the database.
	 */
	private function procConfirmBookDetails($session){
		
		$_SESSION['value_array'] = $session->form->getValueArray();
		session_write_close();
		
		$queryType = @$_GET['type'];
		if( $queryType == "add") $url = "serialProcess.php?scanBook&type=".$queryType;
		else if( $queryType == "change") $url = "bookProcess.php?submitBookChanges&type=".$queryType;
		
		include ( dirname( __FILE__ ) . '\forms\confirmBook.php');
		
		echo "<table class=\"forms\">";
		echo "<form name=\"confirmBook\" method=\"post\" action=\"".$url."\">
       		  <tr>
       		  		<td width=\"130\">&nbsp;</td>
	        		<td><input type=\"submit\" name=\"submit\" value=\"Confirm\" style='width: 140px'/>&nbsp;";
	        	
	    if( $queryType == 'add') $type = "addNewBook";
	    else $type = "changeBookDetails";
	            
	    echo "		<input type=\"button\" value=\"Edit\" style='width: 140px' onclick=\"location.href='librarianProcess.php?".$type."&callNo=".$session->form->getValue("callNo")." '\" /></td>";
	    echo "</tr>";
		echo "</form>";
		echo "</table>";
		
	}
	
	private function procConfirmBookRemove($session){
		
		$callNo = @$_GET['callNo'];
		
		if( $callNo == "" ){
			 echo "<h2>ERROR: Book Not Found!</h2>
			 		<p><b>Please check inputted information and <a href=\"librarianProcess.php?removeOrModifyBook&type=remove\">try again</a>.</b></p>";
		}
		else{
					
			include ( dirname( __FILE__ ) . '\forms\confirmRemove.php');
			
			echo "<table class=\"forms\">";
			echo "<form name=\"removeBook\" method=\"post\" action=\"bookProcess.php?submitBookChanges&callNo=".$callNo."&type=remove\">
	       		  <input type=\"hidden\" name=\"subConfirmed\" value=\"1\">
	       		  <tr>
        				<td width=\"130\">&nbsp;</td>
				        <td>
				            <input type=\"submit\" name=\"submit\" value=\"Remove Book\" style='width: 140px'/>
				            &nbsp;
				            <input type=\"button\" value=\"Cancel\" style='width: 140px' onclick=\"location.href='librarianProcess.php?removeOrModifyBook&type=remove'\" />
				        </td>
    			  </tr>";
			echo "</form>";
			echo "</table>";
		}

	}
	
	private function procFindMisplaced(){
		
		echo "<h2>Misplaced Books</h2>
			  <p>Listed here are all of the misplaced books that have been found.  They
			     are located on the shelf listed as \"Found Shelf.\"</p>";
			     
		$this->book->viewMisplaced();
		
	}
	
	private function procViewUsers(){
		echo "<h2>View All Users</h2>";
		echo "<p>Below is a list of all library users registered</p>";
		
		$users = new User();
		$result = $users->getAllUsers();
		
		echo "<p><table class=\"search\">
                    <tr class=\"highLightRowCol\">
                        <td>#</td>
                        <td>Name</td>
                        <td>Username</td>
                        <td>e-mail</td>
                        <td>Type</td>
                        <td>Card Number</td>
                    </tr>";
                    
        $count = 1;
        while( $row = mysql_fetch_array($result) ){
        	
        	$lastname = $row['LastName'];
        	$firstname = $row['FirstName'];
        	$username = $row['UserName'];
        	$email = $row['Email'];
        	$type = $row['UserType'];
        	$cardNo = $row['CardNo'];
        	
        	echo "<tr>
        			<td>$count</td>
        			<td>$lastname, $firstname</td>
        			<td>$username</td>
        			<td>$email</td>
        			<td>$type</td>
        			<td>$cardNo</td>
        		</tr>";
        	
        	$count++;
        }
        
        echo "</table>";
	}
}

$process = new LibrarianProcess;

?>
