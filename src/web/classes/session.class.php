    <?php
    require_once (dirname( __FILE__ ) . '\user.class.php');
    require_once (dirname( __FILE__ ) . '\book.class.php');
    require_once (dirname( __FILE__ ) . '\form.class.php');

    class Session
    {
        public $firstname;      // User Information
        public $lastname;
        public $username;
        public $email;
        public $form;           // Form Object

        private $user;
        private $userInfo;
        private $userType;
        private $cardNo;

        /* Class constructor */
        public function Session(){
        
            $this->user = new User();
            $this->form = new Form();

            if ( $this->isUserLoggedIn() ) {
                $this->userInfo = $this->user->getUserInfo($_COOKIE["username"]);
                $this->firstname  = $this->userInfo['FirstName'];
                $this->lastname  = $this->userInfo['LastName'];
                $this->username  = $this->userInfo['UserName'];
                $this->email  = $this->userInfo['Email'];
                $this->cardNo = $this->userInfo['CardNo'];
                $this->userType = $this->userInfo['UserType'];
            }
        }

        /*
        * isUserLoggedIn
        * Returns true if the user has been properly authenticated.
        */
        public function isUserLoggedIn(){
        if ( isset($_COOKIE["username"]) && isset($_COOKIE["auth"]) ) {
            return true;
        }
        return false;
    }

    /*
     * login
     * Verifies authenticity of login information returning true if
     * user successfully logged in, false otherwise
     */
    public function login($subuser, $subpass) {
        $usernameValid = $this->form->validate($subuser, "username");
        $passwordValid = $this->form->validate($subpass, "password");

        if ( $usernameValid && $passwordValid ) {
            if ( $this->user->verifyLogin($subuser, $subpass) ) {
                $this->userInfo = $this->user->getUserInfo($subuser);

                setcookie("username", $subuser, time() + COOKIE_EXPIRY);
                setcookie("auth", $this->userInfo['UserType'], time() + COOKIE_EXPIRY);
                setcookie("userId", $this->userInfo['CardNo'], time() + COOKIE_EXPIRY);
                
                $userType = $this->userInfo['UserType'];
                $userNum = (string) $this->userInfo['CardNo'];
                shell_exec( "java PHPHandler userAuth ".$userType." ".$userNum );
                

                return true;
            }
        }
        $this->form->setError("login", "* invalid login credientials");
        return false;
   }

   /*
    * logout
    * Logs user from website
    */
    public function logout() {
        setcookie ("username", "", time() - COOKIE_EXPIRY);
        setcookie ("auth", "", time() - COOKIE_EXPIRY);
        $this->user->clearUserSearchTable();
        
        $this->user->Destroy();
        session_destroy();
    }

    /*
     * register
     * Attempts to register user account. Returns true if succcessful, false otherwise
     */
    public function register($subfirst, $sublast, $subuser, $subpass1, $subpass2, $subemail) {
        $validFirst = $this->form->validate($subfirst, "firstname");
        $validLast  = $this->form->validate($sublast, "lastname");
        $validUname = $this->form->validate($subuser, "username");
        $validEmail = $this->form->validate($subemail, "email");

        if ( $subpass1 == $subpass2 ) {
            $validPassword = $this->form->validate($subpass1, "password");
        } else {
            $validPassword = false;
            $this->form->setError("password", "* Passwords do not match");
        }

        if ( $validFirst && $validLast && $validUname && $validPassword && $validEmail) {
            if ( $this->user->usernameAvailable($subuser) ) {
                $return = $this->user->addNewUser($subfirst, $sublast, $subuser, $subpass1, $subemail);
                if ( $return != 0 ) {
                    $this->userLibraryNo = $return;
                    return true;
                }
            } else {
                $this->form->setError("username", "* Username taken");
            }
        }
        return false;
    }

    /*
     * register
     * Attempts to register user account. Returns true if succcessful, false otherwise
     */
    public function editAccount($subfirst, $sublast, $subpass, $subemail) {
        $validFirst = $this->form->validate($subfirst, "firstname");
        $validLast  = $this->form->validate($sublast, "lastname");
        $validPassword = $this->form->validate($subpass, "password");
        $validEmail = $this->form->validate($subemail, "email");

        if ( $validFirst && $validLast && $validPassword && $validEmail) {
            if ( $this->user->editUser($subfirst, $sublast, $subpass, $subemail, $this->cardNo) ) {
                return true;
            }
        }
        return false;
    }
}

?>
