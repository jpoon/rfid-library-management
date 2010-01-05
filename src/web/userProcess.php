<?php
/**
 * userProcess.php
 *
 * The Process class is meant to simplify the task of processing
 * user submitted forms, redirecting the user to the correct
 * pages if errors are found, or if form is successful, either
 * way. Also handles the logout procedure.
 *
 */
require_once(dirname( __FILE__ ) . '\classes\session.class.php');

class UserProcess
{
    private $session;

    /* Class constructor */
    public function UserProcess() {
    	if (session_id() == "") {
		  session_start();
	    }

        $this->session = new Session;
        
        $queryString = $_SERVER['QUERY_STRING'];
        
        if (isset($_POST['sublogin'])) {
            /* User submitted login form */
            $this->procLogin();
        } else if (isset($_POST['subjoin']) ) {
            /* User submitted registration form */
            $this->procRegister();
        } else if (isset($_POST['subedit'])) {
            /* User submitted edit account form */
            $this->procEditAccount();
        } else if( preg_match( '/addSearch/', $queryString)){
        	$this->procAddSearch();
        } else if( preg_match( '/searchList/', $queryString )){
        	$this->procSearchList();
        } else if ( $this->session->isUserLoggedIn() ) {
            /* User logout */
            $this->procLogout();
        }
        else {
             header("Location: login.php");
        }
    }

    /*
     * procLogin
     * Processes the user submitted login form and attempts to log user into system.
     * If errors are found, user is redirected back to login page.
     */
    private function procLogin(){
        /* Login attempt */
        $retval = $this->session->login($_POST['username'], $_POST['password']);

        if(!$retval){
            /* Login failed */
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = $this->session->form->getErrorArray();
            session_write_close();
        }
        header("Location: login.php");
    }

    /*
     * procLogout
     * Logs user out of system
     */
    private function procLogout(){
        $retval = $this->session->logout();
        header("Location: home.php");
    }

    /*
     * procRegister
     * Processes the registration form. If errors are found, the user is redirected
     * back to the registration page to correct errors, else the user is directed to
     * login page.
     */
    private function procRegister(){
        /* Registration attempt */
        $retval = $this->session->register($_POST['firstname'], $_POST['lastname'], $_POST['username'], $_POST['password1'], $_POST['password2'], $_POST['email']);

        if( $retval ){
            /* Registration Successful */
            $_SESSION['regSuccess'] = true;
        } else {
            /* Error found with form */
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = $this->session->form->getErrorArray();
        }
        session_write_close();
        header("Location: register.php");
    }

    /*
     * procEditAccount
     * Processes the form. The user will be redirected to the View/Edit Profile page.
     * If errors were found with the form, the 'error_array' is filled with the errors
     */
    private function procEditAccount(){
        /* Account edit attempt */
        $retval = $this->session->editAccount($_POST['firstname'], $_POST['lastname'], $_POST['password'], $_POST['email']);

        if( $retval ){
            /* Account edit successful */
            $_SESSION['editSuccess'] = true;
        } else {
            /* Error found with form */
            $_SESSION['error_array'] = $this->session->form->getErrorArray();
        }
        session_write_close();
        header("Location: profile.php?edit");
   }
   
   private function procAddSearch(){
   	
   		if(isset($_GET['callNo'])) $callNo = @$_GET['callNo'];
   		if(isset($_GET['keyword'])) $keyword = @$_GET['keyword'];
   		if(isset($_GET['s'])) $offset = @$_GET['s'];
   		if(isset($_GET['limit'])) $limit = @$_GET['limit'];
   		
   		
   		if(isset($_GET['detail'])) {
   			$detail = @$_GET['detail'];
   		}
   		else $detail = 0;
   		
   		$book = new Book();
   		$rfid = $book->getBookRFIDNo($callNo);
   		$rfidNo = $rfid['rfidNo'];
   		$result = $book->addSearch($rfidNo);
   		
   		if( $result == true){
   			if( $detail == 1){
   				header( "Location: bookProcess.php?bookDetail=1&s=$callNo&added=1" );
   			}
	   		else{
	   			header( "Location: bookProcess.php?search&s=$offset&keyword=$keyword&limit=$limit&added=1&callNo=$callNo" );
	   		}
   		}
   		else if( $result == false ){
   			if( $detail == 1){
   				header( "Location: bookProcess.php?bookDetail=1&s=$callNo&added=0" );
   			}
	   		else{
	   			header( "Location: bookProcess.php?search&s=$offset&keyword=$keyword&limit=$limit&added=2&callNo=$callNo" );
	   		}
   		}
   		
   }
   
   private function procSearchList(){
   	
   		include_once( dirname( __FILE__ ) . '\include\header.php');
   		
   		echo "<h2>My Search List</h2>
   			  <p>Below are the books you have chosen to search for.  You may choose <b>up to five books</b> to search for at a time.  <b>Only the first five books will be downloaded!</b></p>
   			  <p>Please click select the books you wish to choose for and click \"Download\" when you are ready.</p>";
   		
   		$book = new Book();	  
   		$book->viewSearchList();
   			  
   		include_once( dirname( __FILE__ ) . '\include\footer.php');
   }
}

/* Initialize process */
$process = new UserProcess;

?>
