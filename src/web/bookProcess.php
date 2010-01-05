<?php
require_once(dirname( __FILE__ ) . '\classes\book.class.php');
require_once(dirname( __FILE__ ) . '\classes\form.class.php');
/**
 * BookProcess.php
 *
 * This file is used to perform operations to process librarian processes.
 * Error checking on submissions.
 * Setting session variables for reference on other pages.
 * Calling other helper processes.
 *
 */

class BookProcess{

	private $book;
	private $form;
	private $session;
	
	public function BookProcess(){
		
		if (session_id() == "") {
			session_start();
		}

		include_once (dirname( __FILE__ ) . '\include\header.php');
	  
		$this->book = new Book();
		$this->form = new Form();
		$this->session = new Session();

		$queryString = $_SERVER['QUERY_STRING'];

		if (preg_match( '/search/', $queryString) || isset($_POST['subsearch']) ) {
			$this->procSearch();
		} elseif ( preg_match( '/getOnLoan/', $queryString) ) {
			$this->procGetOnLoan();
		} elseif ( preg_match( '/bookDetail/', $queryString) ) {
			$this->procBookDetail();
		} elseif ( preg_match( '/bookRequest/', $queryString) ) {
			$this->procBookRequest();
		} elseif ( preg_match( '/bookRenew/', $queryString) ) {
			$this->procBookRenew();
		} elseif( preg_match( '/changeBookDetails/', $queryString) ){
			/*User to add a book to catalogue */
			$this->procChangeBookDetails();
		} elseif( preg_match('/submitBookChanges/', $queryString) ){
			$this->procSubmitBookChanges($session);
		} elseif( preg_match('/setupChangeList/', $queryString) ){
			$this->procSetupChangeList();
		} elseif( preg_match('/foundMisplaced/', $queryString )){
			$this->procFoundMisplaced();
		}

		include (dirname( __FILE__ ) . '\include\footer.php');
	}

	private function procSearch() {
		echo "<div class=\"title\">Search Results</div>";

		//offset
		$s = @$_GET['s'];
		if ( empty($s) ) {
			$s = 0;
		}
		//search keyword
		$keyword = @$_GET['keyword'];
		if ( empty($keyword) ) {
			$keyword = $_POST['keyword'];
		}

		//limit
		$limit = @$_GET['limit'];
		if ( empty($limit) ) {
			$limit = $_POST['records'];
		}
		
		if( isset($_GET['added'] ) ){
			$added = @$_GET['added'];
		} else {
			$added = 0;
		}
		
		if( isset($_GET['callNo'] ) ){
			$callNo = @$_GET['callNo'];
		} else {
			$callNo = 0;
		}

		$this->book->Search($keyword, $s, $limit, $added, $callNo );
	}

    private function procBookRequest(){
		$uri  = trim($_SERVER['PHP_SELF']);
		 
		$callNo = @$_GET['s'];
		if ( empty($callNo) ) {
			die("error: procBookDetail - no call no");
		}

        if (isset($_COOKIE["userId"])) {
            $book = $this->book->BookOnHold($callNo, $_COOKIE["userId"]);
        }
        echo $book;
	}
    
	private function procBookDetail(){
		echo "<div class=\"title\">Book Details</div>";
		 
		$uri  = trim($_SERVER['PHP_SELF']);
		 
		$callNo = @$_GET['s'];
		if ( empty($callNo) ) {
			die("error: procBookDetail - no call no");
		}

		$book = $this->book->BookDetail($callNo);

		$title = $book["Title"];
		$author = $book["Author"];
		$callNo = $book["CallNo"];
		$publisher = $book["Publisher"];
		$state = $book["State"];
		$isbn = $book["ISBN"];
		$shelf = $book["shelf"];
		$bookRequestLink = "$uri?bookRequest=1&s=$callNo";
		$bookSearchLink = "userProcess.php?addSearch&detail=1&callNo=$callNo";

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
		<td class=\"highLightRowCol\">Publisher</td>
		<td>$publisher</td>
		</tr>
		<tr>
		<td class=\"highLightRowCol\">Status</td>
		<td>$state</td>
		</tr>
		<tr>
		<td class=\"highLightRowCol\">ISBN</td>
		<td>$isbn</td>
		</tr>
		<tr>
		<td class=\"highLightRowCol\">Shelf</td>
		<td>$shelf</td>
		</tr>
		<tr>
		<td class=\"highLightRowCol\">Requests</td>";
		if ( $state == "Missing" || $state == "Damaged" ) {
			echo "<td>No copies available</td></tr>";
		} else {
			echo "<td><a href=\"".$bookRequestLink."\">Request first available copy</a></td></tr>";
		}
		echo "<tr><td class=\"highLightRowCol\">Search</td>";
		
		if( $state != "In Stacks" ){
			echo "<td>No copies available</td>";
		}
		else{
			if( isset( $_COOKIE['auth'] ) ) echo "<td><a href=\"$bookSearchLink\">Add book to search list</a>";
			else echo "<td>To add to search list, please log in.";
		}
			  
		
		if( isset( $_GET['added']) ){
			if( @$_GET['added'] == 1 ){
				echo "<br><font color=\"green\">Added to search list</font></td>";
			}
			else if( @$_GET['added'] == 0 ){
				echo "<br><font color=\"red\">Book already added to your list!</font></td>";
			}
		}
		
		echo "</tr></table></p>";
		if( isset($_COOKIE['auth'])){
			echo "<p><input type=\"button\" value=\"View My Search List\" onclick=\"location.href='userProcess.php?searchList'\"></p>";
		}
		
	}

	/**
	 * procAddBook
	 * This function is used to submit a book from the default form
	 * Validates the needed entries to determine if there are errors.
	 * Sets appropriate session variables for handling, and redirects to
	 * addbook.php to continue.
	 */
	private function procChangeBookDetails(){
		
		$queryType = @$_GET['type'];
		
		$validTitle = $this->form->validate($_POST['title'], "title");
		$validAuthor = $this->form->validate($_POST['author'], "author");
		$validPublisher = $this->form->validate($_POST['publisher'], "publisher");
		$validCallNo = $this->form->validate($_POST['callNo'], "callNo");
		$validISBN = $this->form->validate($_POST['isbn'], "isbn");
		$validShelf = $this->form->validate($_POST['shelf'], "shelf");
		
		if( $validTitle && $validAuthor && $validPublisher
		&& $validCallNo && $validISBN && $validShelf){
			/* Registration Successful */
			$url = "librarianProcess.php?confirmBookDetails&type=" . $queryType;
		} else {
			/* Error found with form */
			$_SESSION['error_array'] = $this->form->getErrorArray();
			$url = "librarianProcess.php?changeBookDetails&type=" . $queryType;
			if( $queryType == "add"){
				$url = "librarianProcess.php?addNewBook";
			}
			
		}
		$_SESSION['value_array'] = $_POST;
		session_write_close();
		
		header("Location: " . $url);
	}

	private function procBookRenew() {
		$callNo = @$_GET['s'];

		$renew = $this->book->renewBook($callNo);
		if ($renew) {
			$renewMsg = "Book with call number $callNo successfully renewed";
			$formStatus = "formSuccess";
		} else {
			$renewMsg = "Error renewing book with call number: $callNo";
			$formStatus = "formError";
		}
		$this->procGetOnLoan($renewMsg, $formStatus);
	}
	
	private function procGetOnLoan($renewMsg = "", $formStatus = "formSuccess") {
		if( !$this->session->isUserLoggedIn() ) {
		    $path = $_SERVER['PHP_SELF'];
		    $queryString = $_SERVER['QUERY_STRING'];
		    $url = $path . "?" . $queryString;
		
		    /* Set referrer page and forward user to login page */
		    $_SESSION['url'] = $url;
		    session_write_close();
		    header("Location: login.php");
		}
		
		print	"<div class=\"title\">Items on Loan</div>";
		if (!empty($renewMsg)) {
			echo "<div class=\"$formStatus\"><p>$renewMsg</p></div>";
		}
		$this->book->viewOnLoan($_COOKIE["userId"]);
	}
	
	private function procSubmitBookChanges($session){
		
		$type = @$_GET['type'];
		
		if( $type == 'change'){
			$this->book->modifyBook($session->form->getValue("title"),
									$session->form->getValue("author"),
									$session->form->getValue("publisher"),
									$session->form->getValue("callNo"),
									$session->form->getValue("state"),
									$session->form->getValue("isbn"),
									$session->form->getValue("rfid"),
									$session->form->getValue("shelf"));
		}
		else if( $type == 'add' ){
			
			$this->book->addNewBook($session->form->getValue("title"),
									$session->form->getValue("author"),
									$session->form->getValue("publisher"),
									$session->form->getValue("callNo"),
									$session->form->getValue("state"),
									$session->form->getValue("isbn"),
									$session->form->getValue("rfid"),
									$session->form->getValue("shelf"));
		}
		else if( $type == 'remove' ){
			$callNo = @$_GET['callNo'];
			$this->book->removeBook($callNo);
		}
		
		echo "<h3>Success</h3>";
		if( $type == "add" ){
			echo "<p>Your the book has been successfully added to the database.</p>";
		}
		elseif( $type == "change" ){
			echo "<p>Your changes to the book have been successfully submitted to the database.</p>";
		}
		elseif( $type == "remove"){
			echo "<p>Your the book has been successfully removed to the database.</p>";
		}
		
	}
	
	private function procSetupChangeList(){
		
		$type = @$_GET['type'];
		if( isset($_POST['mode']) ) $callNo = $_POST['callNo'];
		else $callNo = @$_GET['callNo'];
				
		$book = $this->book->BookDetail($callNo);
		
		$title = $book["Title"];
		$author = $book["Author"];
		$callNo = $book["CallNo"];
		$publisher = $book["Publisher"];
		$state = $book["State"];
		$isbn = $book["ISBN"];
		$rfid = $book["rfidNo"];
		$shelf = $book["shelf"];
		
		$bookDetail = array( "title" => $title,
							 "author" => $author,
							 "callNo" => $callNo,
							 "publisher" => $publisher,
							 "state" => $state,
							 "isbn" => $isbn,
							 "rfid" => $rfid,
							 "shelf" => $shelf );
							 
		
		$_SESSION['value_array'] = $bookDetail;
		session_write_close();
		
		if($type == "change"){
			header( "Location: librarianProcess.php?changeBookDetails&callNo=".$callNo );
		}
		elseif( $type == "remove" ){
			header( "Location: librarianProcess.php?confirmBookRemove&callNo=".$callNo );
		}
		
	}
	
	private function procFoundMisplaced(){
		
		$all = @$_GET['all'];
		$callNo = @$_GET['callNo'];
		
		$this->book->clearMisplaced($all, $callNo);
		
		header( "Location: librarianProcess.php?findMisplaced" );
	}
	
}

$process = new BookProcess();

?>
