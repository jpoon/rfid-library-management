<?php
// Database
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "123456");
define("DB_NAME", "Library");

// Table Types
define("TBL_USERS", "Users");
define("TBL_BOOKS", "Books");
define("TBL_BOOKS_FULLTEXT", "CallNo, Title, Author, Publisher, ISBN");
define("TBL_ONLOAN", "onLoan");
define("TBL_ONHOLD", "onHold");
define("TBL_SEARCH", "userSearchList");

// 3600secs = 1 hour
define("COOKIE_EXPIRY", 3600);

// Form Constants
define("MAX_LEN", 30);
define("MIN_LEN", 3);

define("MAX_DWNLD", 5 );
define("LOAN_LENGTH", 14);

// User Types
define("USER_STUDENT", "Student");
define("USER_LIBRARIAN", "Librarian");
define("USER_ADMIN", "Admin");

?>