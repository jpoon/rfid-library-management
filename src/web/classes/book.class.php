<?php

require_once (dirname( __FILE__ ) . '\..\include\constants.inc.php');
require_once (dirname( __FILE__ ) . '\..\classes\database.class.php');

class Book extends Database  {

    public function Book() {
        parent::Database();
    }

    public function Destroy() {
        parent::dbDisconnect();
    }

    public function CheckOut( $CallNo, $UserId ) {
    	$query = sprintf("SELECT adddate(curdate(), interval \"%s\" day)",
    						LOAN_LENGTH);

        $result = mysql_query($query)
            OR die ("Could not perform query : " . mysql_error());

        $dueDate= mysql_fetch_array($result);

        $query = sprintf("INSERT INTO %s (DueDate, CallNo, UserId) VALUES ( '%s', '%s', '%s')",
                            TBL_ONLOAN,
                            array_pop($dueDate),
                            $this->clean($CallNo),
                            $this->clean($UserId));

        mysql_query($query)
            OR die ("Could not perform query : " . mysql_error());

        return(mysql_insert_id());
    }
    
    public function CheckIn( $callNo ){
    	$query = "DELETE FROM onLoan WHERE CallNo = '$callNo'";
    	$result = mysql_query($query)
            OR die ("Could not perform query : " . mysql_error());
    }

    public function ViewCatalogue( ) {
        $query = sprintf("SELECT * FROM %s",
                            TBL_BOOKS);

        $result = mysql_query($query)
                OR die ("Could not perform query : " . mysql_error());

        echo "<p><table class=\"search\">
                <tr class=\"highLightRowCol\">
                    <td>#</td>
                    <td>Title</td>
                    <td>Author</td>
                    <td>Call No</td>
                    <td>Publisher</td>
                    <td>Status</td>
                    <td>ISBN</td>
                    <td>Shelf</td>
                    <td>&nbsp;</td>
                </tr>";

        $count = 1;
        while ( $row= mysql_fetch_array($result) ) {
            $title = $row["Title"];
            $author = $row["Author"];
            $callNo = $row["CallNo"];
            $publisher = $row["Publisher"];
            $state = $row["State"];
            $isbn = $row["ISBN"];
            $shelf = $row["shelf"];
            $modifyLink = "bookProcess.php?setupChangeList&callNo=".$callNo."&type=change";

            echo "<tr>
                    <td>$count</td>
                    <td>$title</td>
                    <td>$author</td>
                    <td>$callNo</td>
                    <td>$publisher</td>
                    <td>$state</td>
                    <td>$isbn</td>
                    <td>$shelf</td>
                    <td><a href=\"".$modifyLink."\">Modify</a></td>";
            $count++;
        }
        echo "</tr></table></p>";
    }
    
    public function viewMisplaced(){
    	
    	$query = "SELECT * FROM misplaced";

        $result = mysql_query($query)
                OR die ("Could not perform query : " . mysql_error());
                
        echo "<p><table class=\"search\">
                <tr class=\"highLightRowCol\">
                    <td>#</td>
                    <td>Title</td>
                    <td>Author</td>
                    <td>Call No</td>
                    <td>Status</td>
                    <td>Shelf</td>
                    <td>Found Shelf</td>
                    <td>&nbsp;</td>
                </tr>";

        $count = 1;
        while ( $row= mysql_fetch_array($result) ) {
        	
        	$bookquery = "SELECT * FROM Books WHERE CallNo = '".$row['CallNo']."'";
        	$books = mysql_query($bookquery);
        	$booksRow = mysql_fetch_array($books);
        	
            $title = $booksRow["Title"];
            $author = $booksRow["Author"];
            $callNo = $row["CallNo"];
            $state = $booksRow["State"];
            $shelf = $row["shelf"];
            $foundShelf = $row["foundShelf"];
            $foundLink = "bookProcess.php?foundMisplaced&callNo=$callNo&all=0";

            echo "<tr>
                    <td>$count</td>
                    <td>$title</td>
                    <td>$author</td>
                    <td>$callNo</td>
                    <td>$state</td>
                    <td>$shelf</td>
                    <td>$foundShelf</td>
                    <td><a href=\"bookProcess.php?foundMisplaced&callNo=$callNo\">Found</a></td>";
            $count++;
        }
        echo "</tr></table></p>";
        
        echo "<p><input type=\"button\" style='width: 150px' value=\"Found All\" onclick=\"location.href='bookProcess.php?foundMisplaced&callNo=0&all=1'\"";
    }

    public function BookOnHold( $callNo, $userId ) {
        $query = sprintf("INSERT INTO %s (CallNo, UserId) VALUES ( '%s', '%s')",
                            TBL_ONHOLD,
                            $this->clean($callNo),
                            $this->clean($userId));

        mysql_query($query)
            OR die ("Could not perform query : " . mysql_error());

        return(mysql_insert_id());    
    }
    
    public function BookDetail( $callNo ) {    	
        $query = sprintf("SELECT * FROM %s WHERE CallNo = '%s'",
                            TBL_BOOKS,
                            $this->clean($callNo));                       

        $result = mysql_query($query)
                OR die ("Could not perform query : " . mysql_error());
        $numrows = mysql_num_rows($result);
        
        if ( $numrows == 0 ) {
        	echo "Unable to find specified book in catalogue.";
            return null;
        } else {
			return mysql_fetch_array($result);
        }
    }
    
    public function getBookCallNo( $rfid ) {
    	$query = sprintf(( "SELECT * FROM %s WHERE rfidNo = '%s' "),
    						TBL_BOOKS,
    						$this->clean($rfid));
    						
    	$result = mysql_query($query)
    		 OR die ("Could not perform query : " . mysql_error());
        $numrows = mysql_num_rows($result);
        
        if ( $numrows == 0 ) {
        	echo "Unable to find specified book in catalogue.";
            return null;
        } else {
			return mysql_fetch_array($result);
        }
    }
    
    public function getBookRFIDNo( $callNo ){
    	$query = sprintf(( "SELECT * FROM %s WHERE CallNo = '%s' "),
    						TBL_BOOKS,
    						$this->clean($callNo));
    						
    	$result = mysql_query($query)
    		 OR die ("Could not perform query : " . mysql_error());
        $numrows = mysql_num_rows($result);
        
        if ( $numrows == 0 ) {
        	echo "Unable to find specified book in catalogue.";
            return null;
        } else {
			return mysql_fetch_array($result);
        }
    }
    
    public function addSearch($rfid){
    	$query = sprintf(( "INSERT INTO %s (BookID) VALUES ('%s')"),
    						TBL_SEARCH,
    						$this->clean($rfid));
    						
    	$result = mysql_query($query);
    		
    	return $result;
    }
    
    public function Search( $keyword, $offset, $limit, $added, $addedCall) {
        $uri  = trim($_SERVER['PHP_SELF']);

        $query = sprintf("SELECT * FROM %s WHERE MATCH (%s) AGAINST ('%s' WITH QUERY EXPANSION)",
                            TBL_BOOKS,
                            TBL_BOOKS_FULLTEXT,
                            $this->clean($keyword));

        $numresults = mysql_query($query)
                OR die ("Could not perform query : " . mysql_error());
        $numrows = mysql_num_rows($numresults);

        echo "Found ". $numrows ." results for &quot;" . $keyword . "&quot;:";

        if ( $numrows == 0 ) {
            echo "<p>Suggestions:</p>
                  <ul>
                    <li>Make sure all words were spelled correctly.
                    <li>Try more general keywords.
                    <li>Try fewer keywords.
                  <ul>";
        } else {
	        $query .= " LIMIT $offset,$limit";
	        $result = mysql_query($query)
	                OR die ("Could not perform query : " . mysql_error());
                
            echo "<p><table class=\"search\">
                    <tr class=\"highLightRowCol\">
                        <td>#</td>
                        <td>Title</td>
                        <td>Author</td>
                        <td>Call No</td>
                        <td>Status</td>
                        <td>Request</td>
                       	<td>Find</td>
                    </tr>";

            $count = 1;
            while ( $row= mysql_fetch_array($result) ) {
                $title = $row["Title"];
                $author = $row["Author"];
                $callNo = $row["CallNo"];
                $state = $row["State"];
                $bookDetailLink = "$uri?bookDetail=1&s=$callNo";
                $bookRequestLink = "$uri?bookRequest=1&s=$callNo";
                $bookSearchLink = "userProcess.php?addSearch&callNo=".$callNo."&keyword=".$keyword."&s=".$offset."&limit=".$limit;
                
                if ( !isset($_COOKIE["auth"]) || $_COOKIE["auth"] == USER_STUDENT) {
                    switch($state) {
                    case "In Stacks":
                    	$state = "Available";
                        break;
                    case "In Reserve":
                    case "Returned":
                        $state = "Available";
                        break;
                    case "On Hold":
                    case "On Loan":
                        $state = "On Loan";
                        break;
                    }
                }

                echo "<tr>
                        <td>$count</td>
                        <td><a href=\"".$bookDetailLink."\">$title</a></td>
                        <td>$author</td>
                        <td>$callNo</td>
                        <td>$state</td>";
                if ( $state == "Missing" || $state == "Damaged" ) {
                    echo "<td>No copies available</td>";
                } else {
                    echo "<td><a href=\"".$bookRequestLink."\">Request first available copy</a></td>";
                }
                if( $state != "In Stacks" && $state != "Available"){
                	echo "<td>No copies available</td>";
                }
                else{
                	if( isset($_COOKIE['auth'] )) {
                		echo "<td><a href=\"".$bookSearchLink."\">Add book to search list</a>";
                	}
                	else {
                		echo "<td>To add to search list, please log in.";
                	}
                }
                	
                	if( $added == 1 && $callNo == $addedCall ){
                		echo "<br><font color=\"green\">Added to search list</font></td></tr>";
                	}
                	else if( $added == 2 && $callNo == $addedCall ){
                		echo "<br><font color=\"red\">Book already added!</font></td></tr>";
                	}
                	else{
                		echo "</td></tr>";
                	}
                $count++ ;
            }
            echo "</table></p>";
            
            if( isset($_COOKIE['auth'])){
            	echo "<p><input type=\"button\" value=\"View My Search List\" onclick=\"location.href='userProcess.php?searchList'\"></p>";
            }

            $currPage = (($offset/$limit) + 1);

            if ( $offset >= 1 ) {
                // PREV link
                $prevs=($offset-$limit);
                print "<a href=\"$uri?search=1&s=$prevs&limit=$limit&keyword=$keyword\">&lt;&lt; Prev ".$limit."</a>&nbsp&nbsp;";
            }

            // calculate number of pages requiring links
            $pages = intval($numrows/$limit);

            if ( $numrows%$limit ) {
                // has remainder so add one page
                $pages++;
            }

            // check to see if last page
            if (!((($offset+$limit)/$limit)==$pages) && $pages>1) {
                //NEXT link
                $news=$offset+$limit;
                echo "&nbsp;<a href=\"$uri?search=1&s=$news&limit=$limit&keyword=$keyword\">Next ".$limit." &gt;&gt;</a>";
            }
        }
    }

    /**
     * addNewBook
     * Creates a query with form submitted data, and submits to the books table
     * of the database.
     */
    public function addNewBook($title, $author, $publisher, $callNo, $bookState, $isbn, $rfid, $shelf){
    	$query = sprintf("INSERT INTO %s (CallNo, rfidNo, Title, Author, Publisher, ISBN, State, shelf)
                            VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
    						 TBL_BOOKS,
    						 $this->clean($callNo),
    						 $this->clean($rfid),
    						 $this->clean($title),
    						 $this->clean($author),
    						 $this->clean($publisher),
    						 $this->clean($isbn),
    						 $this->clean($bookState),
    						 $this->clean($shelf));

    	mysql_query($query)
    		OR die ("Could not perform query : " . mysql_error());

    	return (mysql_insert_id());
    }
    
    public function renewBook($callNo) {
    	/* get new due date */
    	$query = sprintf("SELECT adddate(curdate(), interval \"%s\" day)",
    						LOAN_LENGTH);
    	
    	$return = mysql_query($query)
    		OR die ("Could not perform query : " . mysql_error());

		$newDueDate = array_pop(mysql_fetch_array($return));

		/* update entry */
    	$query = sprintf("UPDATE %s SET dueDate = '%s' WHERE callNo = '%s'",
    						TBL_ONLOAN,
    						$this->clean($newDueDate),
    						$this->clean($callNo));
    	
    	$return = mysql_query($query)
    		OR die ("Could not perform query : " . mysql_error());
    		
    	return 1;
    }
    
   	public function viewOnLoan($userId) {
        $query = sprintf("SELECT * FROM %s WHERE UserId = '%s'",
                            TBL_ONLOAN,
                            $this->clean($userId));
        $result = mysql_query($query)
            OR die ("Could not perform query : " . mysql_error());

        $numBooks = mysql_num_rows($result);
        if ( $numBooks == 0 ) {
            echo "<p>No books on loan.</p>";
        } else {
            echo "<table class=\"search\">
                    <tr class=\"highLightRowCol\">
                        <td>#</td>
                        <td>Title</td>
                        <td>Author</td>
                        <td>Call No</td>
                        <td>Due Date</td>
                        <td>Tasks</td>
                    </tr>";

	        $count = 1;
	        while ( $row = mysql_fetch_array($result) ) {
	            $callNo = $row["CallNo"];
	            $dueDate = $row["DueDate"];
	
	            $bookQuery = sprintf("SELECT * FROM %s WHERE CallNo = '%s'",
	                            TBL_BOOKS,
	                            $callNo);
	
	            $bookResult = mysql_query($bookQuery);
	            $bookDetail = mysql_fetch_array($bookResult);
	
	            $bookTitle = $bookDetail["Title"];
	            $bookAuthor = $bookDetail["Author"];
	            $bookRenewLink = "bookProcess.php?bookRenew=1&s=$callNo";
	
	            echo "  <tr>
	                        <td>$count</td>
	                        <td>$bookTitle</td>
	                        <td>$bookAuthor</td>
	                        <td>$callNo</td>
	                        <td>$dueDate</td>
	                        <td><a href=\"".$bookRenewLink."\">Renew</a></td>
	                    </tr>";
	
	            $count++;
	        }
	        echo "</tr></table>";
        }
    }
    
    public function modifyBook($title, $author, $publisher, $callNo, $bookState, $isbn, $rfid, $shelf){
    	$query = sprintf("UPDATE %s SET rfidNo = '%s', Title = '%s', Author = '%s', 
    						Publisher = '%s', ISBN = '%s', State = '%s', shelf = '%s' WHERE CallNo = '%s'", 
    						 TBL_BOOKS,
    						 $this->clean($rfid),
    						 $this->clean($title),
    						 $this->clean($author),
    						 $this->clean($publisher),
    						 $this->clean($isbn),
    						 $this->clean($bookState),
    						 $this->clean($shelf),
    						 $this->clean($callNo));
    						
    	mysql_query($query)
    		OR die ("Could not perform query : " . mysql_error());
    		
    	return true;
    }
    
    public function removeBook($callNo){
    	$query = sprintf("DELETE FROM %s WHERE CallNo = '%s'", TBL_BOOKS, $this->clean($callNo));
    	
    	mysql_query($query)
    		OR die ("Could not perform query : " . mysql_error());
    		
    	return true;
    	
    }
    
    public function viewSearchList(){
    	$query = "SELECT * FROM userSearchList";
    	$rfidNums = mysql_query($query)
    		OR die ("Could not perform query : " . mysql_error());
    		
    		
    	echo "<p><form name=\"searchList\" method=\"post\" action=\"serialProcess.php?sendBookList\">
    		  <table class=\"search\">
                <tr class=\"highLightRowCol\">
                    <td>&nbsp;</td>
                    <td>Title</td>
                    <td>Author</td>
                    <td>Call No</td>
                    <td>Publisher</td>
                    <td>ISBN</td>
                    <td>Shelf</td>
                </tr>";
    	
    	$count = 1;
    	while( $rfidRow = mysql_fetch_array($rfidNums) ){
    		
    		$rfid = $rfidRow["BookID"];
    		$result = mysql_query("SELECT * FROM Books WHERE rfidNo = '$rfid'");
    		
    		$info = mysql_fetch_array($result);
    		
    		$title = $info["Title"];
            $author = $info["Author"];
            $callNo = $info["CallNo"];
            $publisher = $info["Publisher"];
            $isbn = $info["ISBN"];
            $shelf = $info["shelf"];

			echo "<tr>
					<td><input type=\"checkbox\" name=\"$count\" value=\"$rfid\"";
					if( $count <= MAX_DWNLD ) echo "checked"; echo "></td>";
            echo   "<td>$title</td>
                    <td>$author</td>
                    <td>$callNo</td>
                    <td>$publisher</td>
                    <td>$isbn</td>
                    <td>$shelf</td>";
                    
           $count++;
        }
        echo "</tr>
        	  <input type=\"hidden\" name=\"count\" value=\"$count\" >
        	  </table>
        	  <p><td><input type=\"submit\" name=\"submit\" style='width: 150px' value=\"Download List\" />&nbsp;
        	  <input type=\"button\" style='width: 150px' value=\"Go back to Search\" onclick=\"location.href='search.php'\" /></td><p>
        	  </form></p>";
        	  
        
    }
    
    public function clearMisplaced($all, $callNo){
    	if( $all == 1 ){
    		mysql_query("DELETE FROM misplaced")
    			OR die ("Could not perform query : " . mysql_error());
    	}
    	else{
    		mysql_query("DELETE FROM misplaced WHERE callNo = '$callNo'")
    			OR die ("Could not perform query : " . mysql_error());
    	}
    }
    
    public function getShelfDescription(){
    	$query = "SELECT description FROM shelves";
    	$result = mysql_query($query)
    		OR die ("Could not perform query : " . mysql_error());
    	
    	return $result;
    }

    private function clean($input) {
        return mysql_real_escape_string($input);
    }
}
?>