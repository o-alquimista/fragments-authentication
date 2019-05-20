<?php

/**
 *
 * Login Model
 *
 */

namespace Fragments\Models\Login;

use Fragments\Utility\Connection\DatabaseConnection;
use Fragments\Utility\Feedback\WarningFeedback;
use Fragments\Utility\Requests\ServerRequest;
use Fragments\Utility\Session\RegenerateSessionID;
use Fragments\Utility\SessionTools\SessionData;

class LoginService {

    /**
     * Holds feedback messages
     * @var array $feedbackText
     */

    public $feedbackText = array();

    /**
     * Holds the database connection object
     * @var object $connection
     */

    private $connection;

    private $username;

    private $passwd;

    public function __construct() {

        $connection = new DatabaseConnection;
        $this->connection = $connection->getConnection();

        $this->username = $this->clean(ServerRequest::post('username'));
        $this->passwd = ServerRequest::post('passwd');

    }

    public function login() {

        $input = new FormValidation($this->username, $this->passwd);

        if ($input->validate() === FALSE) {

            $this->getFeedback($input);

            return FALSE;

        }

        $user = new User($this->connection, $this->username);

        if ($user->isRegistered() === FALSE) {

            $this->getFeedback($user);

            return FALSE;

        }

        $credentials = new CredentialHandler(
            $this->connection, $this->username, $this->passwd
        );

        if ($credentials->verifyPassword() === FALSE) {

            $this->getFeedback($credentials);

            return FALSE;

        }

        $authentication = new Authentication($this->connection, $this->username);

        $authentication->login();

        return TRUE;

    }

    private function getFeedback($object) {

        $this->feedbackText = array_merge(
            $this->feedbackText,
            $object->feedbackText
        );

    }

    private function clean($input) {

        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);

        return $input;

    }

}

class FormValidation {

    public $feedbackText = array();

    private $username;

    private $passwd;

    public function __construct($username, $passwd) {

        $this->username = $username;
        $this->passwd = $passwd;

    }

    public function validate() {

        $validationUsername = $this->validateUsername();
        $validationPassword = $this->validatePassword();

        if ($validationUsername && $validationPassword === TRUE) {

            return TRUE;

        }

        return FALSE;

    }

    private function validateUsername() {

        if (empty($this->username)) {

            $feedback = new WarningFeedback('FEEDBACK_USERNAME_EMPTY');
            $this->feedbackText[] = $feedback->get();

            return FALSE;

        }

        return TRUE;

    }

    private function validatePassword() {

        if (empty($this->passwd)) {

            $feedback = new WarningFeedback('FEEDBACK_PASSWORD_EMPTY');
            $this->feedbackText[] = $feedback->get();

            return FALSE;

        }

        return TRUE;

    }

}

class User {

    public $feedbackText = array();

    protected $connection;

    protected $username;

    public function __construct($connection, $username) {

        $this->connection = $connection;

        $this->username = $username;

    }

    public function isRegistered() {

        $storage = new UserMapper($this->connection, $this->username);

        $matchingRows = $storage->retrieveCount();

        if ($matchingRows == 0) {

            $feedback = new WarningFeedback('FEEDBACK_NOT_REGISTERED');
            $this->feedbackText[] = $feedback->get();

            return FALSE;

        }

        return TRUE;

    }

}

class UserMapper extends User {

    public function retrieveCount() {

        $query = "SELECT COUNT(id) FROM users WHERE username = :username";

        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        return $stmt->fetchColumn();

    }

}

class CredentialHandler {

    public $feedbackText = array();

    protected $connection;

    protected $username;

    protected $passwd;

    public function __construct($connection, $username, $passwd) {

        $this->connection = $connection;

        $this->username = $username;
        $this->passwd = $passwd;

    }

    public function verifyPassword() {

        $storage = new CredentialHandlerMapper(
            $this->connection, $this->username, $this->passwd
        );

        $hash = $storage->retrieveHash();

        if (!password_verify($this->passwd, $hash)) {

            $feedback = new WarningFeedback('FEEDBACK_INCORRECT_PASSWD');
            $this->feedbackText[] = $feedback->get();

            return FALSE;

        }

        return TRUE;

    }

}

class CredentialHandlerMapper extends CredentialHandler {

    public function retrieveHash() {

        $query = "SELECT hash FROM users WHERE username = :username";

        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        $registry = $stmt->fetchObject();
        $hash = $registry->hash;

        return $hash;

    }

}

class Authentication {

    public $feedbackText = array();

    protected $connection;

    protected $username;

    public function __construct($connection, $username) {

        $this->connection = $connection;

        $this->username = $username;

    }

    public function login() {

        new RegenerateSessionID;

        $storage = new AuthenticationMapper($this->connection, $this->username);

        $data = $storage->retrieve();

        SessionData::set('login', TRUE);
        SessionData::set('username', $data->username);

    }

}

class AuthenticationMapper extends Authentication {

    public function retrieve() {

        $query = "SELECT * FROM users WHERE username = :username";

        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        $data = $stmt->fetchObject();

        return $data;

    }

}

?>
