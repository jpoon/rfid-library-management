<?php
/**
 * Form.php
 *
 * The Form class is meant to simplify the task of keeping
 * track of errors in user submitted forms and the form
 * field values that were entered correctly.
 */

class Form
{
    public $num_errors;   		    // The number of errors in submitted form

    private $values = array();  	// Holds submitted form field values
    private $errors = array();  	// Holds submitted form error messages

    /* Class constructor */
    public function Form(){
        if( isset($_SESSION['value_array']) ) {
            $this->values = $_SESSION['value_array'];
            unset($_SESSION['value_array']);
        }

        if( isset($_SESSION['error_array']) ) {
            $this->errors = $_SESSION['error_array'];
            $this->num_errors = count($this->errors);
            unset($_SESSION['error_array']);
        } else {
            $this->num_errors = 0;
        }
    }

    /*
     * setError
     * Records new form error given the form field name and the error message attached to it.
     */
    public function setError($field, $errmsg){
        $this->errors[$field] = $errmsg;
        $this->num_errors = count($this->errors);
    }

    /*
     * setValue
     * Records the value typed into the given form field by the user.
     */
    public function setValue($field, $value){
        $this->values[$field] = $value;
    }

    /*
     * value
     * Returns the value attached to the given field, if none exists, the empty string is returned.
     */
    public function getValue($field){
        if(array_key_exists($field,$this->values)){
            return htmlspecialchars(stripslashes($this->values[$field]));
        } else {
            return "";
        }
    }

    /*
     * error
     * Returns the error message attached to the given field, if none exists, the empty string is returned.
     */
    public function getError($field){
        if( array_key_exists($field, $this->errors) ){
            return "<font size=\"2\" color=\"#ff0000\">".$this->errors[$field]."</font>";
        } else {
            return "";
        }
    }

    /*
     * getErrorArray
     * Returns the array of error messages
     */
    public function getErrorArray(){
        return $this->errors;
    }
    
    public function getValueArray(){
    	return $this->values;
    }

    /*
     * validate
     */
    public function validate($input, $field) {
        switch( $field ) {
            case "firstname":
            case "lastname":
                return $this->validName($input, $field);
            case "username":
                return $this->validUsername($input, $field);
            case "password":
                return $this->validPassword($input, $field);
            case "email":
                return $this->validEmail($input, $field);
            case "title":
            	return $this->validBookEntity($input, $field);
            case "author":
            	return $this->validBookEntity($input, $field);
            case "publisher":
            	return $this->validBookEntity($input, $field);
            case "callNo":
            	return $this->validBookEntity($input, $field);
            case "isbn":
            	return $this->validISBN($input, $field);
            case "shelf":
            	return $this->validBookEntity($input, $field);
            default:
                // should not arrive here
                die( $field );
                return false;
        }
    }

    private function validName($name, $errorField)
    {
        $name = trim($name);

        if (empty($name)) {
            $this->setError($errorField, "* Name not entered");
            return false;
        } elseif (strlen($name) > MAX_LEN) {
            $this->setError($errorField, "* Name too long");
            return false; // to long
        } elseif (strlen($name) < MIN_LEN) {
            $this->setError($errorField, "* Name too short");
            return false; //toshort
        } elseif(!ereg("^[A-Za-z]+$", $name)) { //only A-Z, a-z are allowed
            $this->setError($errorField, "* Name contains invalid characters");
            return false;
        }
        return true;
    }


    private function validUsername($username, $field)
    {
        $username = trim($username);

        if (empty($username)) {
            $this->setError($field, "* Username not entered");
            return false;
        } elseif (strlen($username) > MAX_LEN) {
            $this->setError($field, "* Username too long");
            return false;
        } elseif (strlen($username) < MIN_LEN) {
            $this->setError($field, "* Username too short");
            return false;
        } elseif (!ereg("^[A-Za-z0-9_\-]+$", $username)) {
            //only A-Z, a-z and 0-9 are allowed
            $this->setError($field, "* Username contains invalid characters");
            return false;
        }
        return true;
    }

    private function validPassword($pass, $field)
    {
        $pass = trim($pass);

        if (empty($pass)) {
            $this->setError($field, "* Password not entered");
            return false;
        } elseif ( strlen($pass) > MAX_LEN) {
            $this->setError($field, "* Password too long");
            return false;
        } elseif (strlen($pass) < MIN_LEN) {
            $this->setError($field, "* Password too short");
            return false;
        } elseif (!ereg("^[A-Za-z0-9_\-]+$", $pass)) {
            $this->setError($field, "* Password contains invalid characters");
            return false;
        }
        return true;
    }

    private function validEmail($email, $field)
    {
        if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email))
        {
            // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
            $this->setError($field, "* Invalid e-mail address");
            return false;
        }

        $email_array = explode("@", $email);
        $local_array = explode(".", $email_array[0]);
        for ($i = 0; $i < sizeof($local_array); $i++) {
            if (!ereg("^(([A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~-][A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
                $local_array[$i])) {
                $this->setError($field, "* Invalid e-mail address");
                return false;
            }
        }
        if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
            $domain_array = explode(".", $email_array[1]);
            if (sizeof($domain_array) < 2) {
                $this->setError($field, "* Invalid e-mail address");
                return false; // Not enough parts to domain
            }
            for ($i = 0; $i < sizeof($domain_array); $i++) {
                if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
                    $this->setError($field, "* Invalid e-mail address");
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Used to check Title, Author, and Publisher for non-empty strings.
     */
    public function validBookEntity($input, $field){
    	if(empty($input)){
    		$this->setError($field, "* Error: Field is Empty");
            return false;
    	}
		return true;
    }
    
    /**
     * Used to check ISBN Number.
     * Number should be in the format of:
     * -10 digits and 4 dashes
     * -Does not start with a dash or end with a dash.
     * -May have the last character as an x or X
     */
    public function validISBN($input, $field) {
    	if(empty($input)){
    		$this->setError($field, "* Error: Field is Empty");
            return false;
    	}
    	elseif( !preg_match( '/^(?=[-0-9xX ]{13}$)(?:[0-9]+[- ]){3}[0-9]*[xX0-9]$/', $input ) ) {
    		$this->setError($field, "* Error: Invalid ISBN format.  Use 10 or 13 digits and 4 dashes.");
    		return false;
    	}
    	else return true;
    }
}
?>