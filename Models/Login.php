<?php

    /* all functions that connect to database should be
    tied to an abstract class that contains the connection
    object and a connection constructor shared with all
    child classes */

    require_once '../Utils/Text.php';
    require_once '../Utils/Connection.php';
    require_once '../Controllers/Session.php';
    require_once '../Utils/InputValidation.php';

    interface UserExistsInterface {

        public function isUserRegistered($username);

    }

    class UserExists implements UserExistsInterface {

        protected $connection;
        public $feedbackText;

        public function __construct() {
            $connect = new DatabaseConnection;
            $this->connection = $connect->getConnection();
        }

        public function isUserRegistered($username) {
            $stmt = $this->connection->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $resultStmt = $stmt->get_result();
            if ($resultStmt->num_rows == 0) {
                $feedbackMessage = Text::get('FEEDBACK_NOT_REGISTERED');
                $feedbackReady = WarningFormat::format($feedbackMessage);
                $this->feedbackText = $feedbackReady;
                return FALSE;
            }
            return TRUE;
        }

    }

    interface PasswordVerifyInterface {

        public function VerifyPassword($username, $passwd);

    }

    class PasswordVerify implements PasswordVerifyInterface {

        protected $connection;
        public $feedbackText;

        public function __construct() {
            $connect = new DatabaseConnection;
            $this->connection = $connect->getConnection();
        }

        public function VerifyPassword($username, $passwd) {
            $stmt = $this->connection->prepare("SELECT hash FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $resultStmt = $stmt->get_result();
            while ($result = $resultStmt->fetch_object()) {
                $hash = $result->hash;
            }

            if (!password_verify($passwd, $hash)) {
                $feedbackMessage = Text::get('FEEDBACK_INCORRECT_PASSWD');
                $feedbackReady = WarningFormat::format($feedbackMessage);
                $this->feedbackText = $feedbackReady;
                return FALSE;
            }
            return TRUE;
        }

    }

    interface SessionDataInterface {

        public function setSessionVariables($username);

    }

    class SessionData implements SessionDataInterface {

        protected $connection;

        public function __construct() {
            $connect = new DatabaseConnection;
            $this->connection = $connect->getConnection();
        }

        public function setSessionVariables($username) {
            $stmt = $this->connection->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $resultStmt = $stmt->get_result();

            $this->generateNewSessionID();

            while ($result = $resultStmt->fetch_object()) {
                $_SESSION['login'] = "";
                $_SESSION['username'] = $result->username;
            }
            return TRUE;
        }

        protected function generateNewSessionID() {
            $newID = new SessionRegenerateID;
            $newID->regenerate();
        }

    }

    interface FormValidationInterface {

        public function validate($username, $passwd);

    }

    class FormValidation implements FormValidationInterface {

        public $feedbackText = array();

        public function validate($username, $passwd) {
            $UsernameValidation = new UsernameValidation;
            $PasswordValidation = new PasswordValidation;

            $UsernameEmpty = $UsernameValidation->isEmpty($username);
            if ($UsernameEmpty == TRUE) {
                $feedbackMsg = Text::get('FEEDBACK_USERNAME_EMPTY');
                $feedbackFormat = WarningFormat::format($feedbackMsg);
                $this->feedbackText[] = $feedbackFormat;
            }

            $PasswordEmpty = $PasswordValidation->isEmpty($passwd);
            if ($PasswordEmpty == TRUE) {
                $feedbackMsg = Text::get('FEEDBACK_PASSWORD_EMPTY');
                $feedbackFormat = WarningFormat::format($feedbackMsg);
                $this->feedbackText[] = $feedbackFormat;
            }

            foreach ($this->feedbackText as $entry) {
                if (!is_null($entry)) {
                    return FALSE;
                }
            }

            return TRUE;
        }

    }

?>
