<?php
namespace Login;

include_once 'db_connect.php';
include_once 'functions.php';
 
sec_session_start(); // Our custom secure way of starting a PHP session.

class LoginProcessor
{
    private $username;
    private $password;
    private $mysqli;
    private $user_data;

    public function __construct($username, $password, $mysqli)
    {
        $this->username = $username;
        $this->password = $password;
        $this->mysqli = $mysqli;
        $this->user_data = array(
            "user_id" => "",
            "db_password" => "",
            "db_password_salt" => "",
            "db_password_salt" => "",
        );
    }

    public function login()
    {
        if ($data = $this->fetch_user_data()) {
            $this->user_data['user_id'] = $data['user_id'];
            $this->user_data['db_password'] = $data['db_password'];
            $this->user_data['db_password_salt'] = $data['db_password_salt'];

            // If the user exists we check if the account is locked
            // from too many login attempts
            if ($this->checkbrute() == true) {

                // Account is locked
                // Send an email to user saying their account is locked or something
                return false;
            } else {

                // hash the password with the unique salt.
                $this->password = hash('sha512', $this->password . $this->user_data['db_password_salt']);

                // Check if the password in the database matches
                // the password the user submitted.
                if ($this->user_data['db_password'] == $this->password) {

                    // Password is correct!
                    // Get the user-agent string of the user.
                    $user_browser = $_SERVER['HTTP_USER_AGENT'];

                    // XSS protection as we might print this value
                    $this->user_data['user_id'] = preg_replace("/[^0-9]+/", "", $this->user_data['user_id']);
                    $_SESSION['user_id'] = $this->user_data['user_id'];

                    // XSS protection as we might print this value
                    $this->username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $this->username);
                    $_SESSION['username'] = $this->username;
                    $_SESSION['login_string'] = hash('sha512', $this->password . $user_browser);

                    // Login successful.
                    return true;
                } else {

                    // Password is not correct
                    // We record this attempt in the database
                    $now = time();
                    $query = "INSERT INTO login_attempts(user_id, time)
                              VALUES ('{$this->user_data['user_id']}', '$now')";
                    $this->mysqli->query($query);

                    return false;
                }
            }
        } else {
            return false;
        }
    }

    private function fetch_user_data()
    {
        $query = "SELECT id, password, salt FROM members WHERE username = ? LIMIT 1";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param('s', $this->username);  // Bind "$username" to parameter.
            $stmt->execute();    // Execute the prepared query.
            $stmt->store_result();

            // get variables from result.
            $stmt->bind_result($user_id, $db_password, $salt);
            $stmt->fetch();

            if ($stmt->num_rows == 1) {
                return array (
                    "user_id" => $user_id,
                    "db_password" => $db_password,
                    "db_password_salt" => $salt
                );
            }
        }

        return false;
    }

    private function checkbrute() {
        // Get timestamp of current time
        $now = time();

        // All login attempts are counted from the past 2 hours.
        $valid_attempts = $now - (2 * 60 * 60);

        $query = "SELECT time FROM login_attempts WHERE user_id = ? AND time > '$valid_attempts'";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param('i', $this->user_data['user_id']);

            // Execute the prepared query.
            $stmt->execute();
            $stmt->store_result();

            // If there have been more than 5 failed logins
            if ($stmt->num_rows > 5) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
}