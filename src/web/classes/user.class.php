<?php

require_once (dirname( __FILE__ ) . '\..\include\constants.inc.php');
require_once (dirname( __FILE__ ) . '\..\classes\database.class.php');

class User extends Database  {

    public function User() {
        parent::Database();
    }

    public function Destroy() {
        parent::dbDisconnect();
    }

    /*
     * usernameAvailable
     * Returns true if the username is available for use, false otherwise.
     */
    public function usernameAvailable($username){
        $query = sprintf("SELECT Username FROM %s WHERE Username = '%s'",
            TBL_USERS,
            $this->clean($username) );
        $result = mysql_query($query)
            OR die ("Could not perform query : " . mysql_error());

        return (mysql_num_rows($result) == 0);
    }

    /*
     * verifyLogin
     * Returns true if username and password combination are correct, returns false otherwise.
     */
    public function verifyLogin($username, $password) {
        $query = sprintf("SELECT * FROM %s WHERE Username = '%s' AND Password = '%s'",
                    TBL_USERS,
                    $this->clean($username),
                    $this->clean(md5($password)));

        $result = mysql_query($query)
            OR die ("Could not perform query : " . mysql_error());

        if( mysql_num_rows($result) == 1 ) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * getUserInfo
     * Returns the result array from a mysql query asking for all information
     * stored regarding the given username. If query fails, NULL is returned.
     */
    public function getUserInfo($username) {
        $query = sprintf("SELECT * FROM %s WHERE username = '%s'",
                    TBL_USERS,
                    $this->clean($username));

        $result = mysql_query($query);

        if( $result || (mysql_numrows($result) == 1) ){
            return ( mysql_fetch_array($result) );
        } else {
            return NULL;
        }
    }
    
    public function getAllUsers(){
    	$query = sprintf( "SELECT * FROM %s", TBL_USERS );
    	$result = mysql_query($query)
    		OR die ("Could not perform query : " . mysql_error());
    		
    	return $result;
    	
    }

    /*
     * addNewUser
     * Adds a new user to the database returning the user's library
     * card number if successful.
     */
    public function addNewUser($firstname, $lastname, $username, $password, $email) {
        $query = sprintf("INSERT INTO %s (FirstName, LastName, Password, UserType, UserName, email)
                            VALUES ( '%s', '%s', '%s', 'Student', '%s', '%s')",
                            TBL_USERS,
                            $this->clean($firstname),
                            $this->clean($lastname),
                            $this->clean(md5($password)),
                            $this->clean($username),
                            $this->clean($email));

        mysql_query($query)
            OR die ("Could not perform query : " . mysql_error());

        return(mysql_insert_id());
    }

    public function editUser($firstname, $lastname, $password, $email, $cardNo) {
        $query = sprintf("UPDATE %s SET FirstName = '%s', LastName = '%s', Password = '%s', Email = '%s'
                            WHERE CardNo = '%s'",
                            TBL_USERS,
                            $this->clean($firstname),
                            $this->clean($lastname),
                            $this->clean(md5($password)),
                            $this->clean($email),
                            $this->clean($cardNo));
        mysql_query($query)
            OR die ("Could not perform query : " . mysql_error());

        return true;
    }
    
    public function clearUserSearchTable(){
    	mysql_query( "DELETE FROM userSearchList" )
    		OR die ("Could not perform query : " . mysql_error());
    }

    private function clean($input) {
        return mysql_real_escape_string($input);
    }
}
?>