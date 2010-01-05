<?php
require_once(dirname( __FILE__ ) . '\classes\session.class.php');

class SerialProcess{
	
	public function SerialProcess(){
		
		if (session_id() == "") {
			session_start();
		}

		include_once (dirname( __FILE__ ) . '\include\header.php');

		$queryString = $_SERVER['QUERY_STRING'];

		if (preg_match( '/scanBook/', $queryString) ) {
			$this->procScanBook($session);
		} else if( preg_match( '/getIDNum/', $queryString )){
			$this->procGetIDNum($session);
		} else if( preg_match( '/sendBookList/', $queryString )){
			$this->procSendBookList();
		}

		include (dirname( __FILE__ ) . '\include\footer.php');
	}
	
	private function procScanBook($session){
		
		$_SESSION['value_array'] = $session->form->getValueArray();
		session_write_close();
		
		$queryType = @$_GET['type'];
		echo "<h3>Please click below and scan the new book...<h3>";
		
		echo "<form name\"scanBook\" method=\"post\" action=\"serialProcess.php?getIDNum&type=".$queryType."\">
			  <input type=\"submit\" name=\"submit\" style='width: 350px; height: 100px' value=\"Click and Scan\" >
			  </form>";
		
		
	}
	
	private function procGetIDNum($session){
		
		$queryType = @$_GET['type'];
		
		shell_exec( "java PHPHandler requestRFIDTagId" );
		$idNum = (string) shell_exec( "java PHPHandler downloadRFID" );
		
		if( $queryType == "add" ){
			$session->form->setValue("rfid", $idNum );
			$_SESSION['value_array'] = $session->form->getValueArray();
			session_write_close();
			header( "Location: bookProcess.php?submitBookChanges&type=".$queryType );
		}
		else if( $queryType == "change" || $queryType == "remove" ){
			$book = new Book();
			$result = $book->getBookCallNo($idNum);
			$callNo = $result['CallNo'];
			header( "Location: bookProcess.php?setupChangeList&type=".$queryType."&callNo=".$callNo."");
		}
		else if( $queryType == "checkinout"){
			$book = new Book();
			$result = $book->getBookCallNo($idNum);
			$callNo = $result['CallNo'];
			header( "Location: librarianProcess.php?checkinout&s=1&callNo=".$callNo."");
		}
		
	}
	
	private function procSendBookList(){
		
		if( $_POST['count'] <= (MAX_DWNLD+1) ){
			$count = $_POST['count'] - 1;
		}
		else{
			$count = MAX_DWNLD;
		}
		
		
		
		$javaString = "java PHPHandler bookList ".$count." ".$_COOKIE['userId'];
		
		for( $i = 1; $i <= $count; $i += 1 ){
			if( isset($_POST[$i]) ){
				$javaString = $javaString." ".$_POST[$i];
			}
		}
		
		$results = shell_exec( $javaString );
		
		echo "<h1>Success!</h1>
			  <p><b>Your books have been downloaded to the handheld device.  Please <a href=\"userProcess.php\">sign out</a> and search for your books.</b></p>";
			  
	}
	
}

$serialProcess = new SerialProcess();
?>
