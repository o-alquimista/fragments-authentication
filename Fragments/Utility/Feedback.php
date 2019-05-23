<?php

namespace Fragments\Utility\Feedback;

use Fragments\Utility\Errors\SoftException;

/**
 * Feedback Utility
 *
 * Retrieves, formats and returns feedback messages. To create new
 * feedback types, a new <Type>Feedback class extending from Feedback
 * must be created, and it must contain two properties: $feedbackType (string)
 * and $feedbackText (array). An example for the ID (key) of a feedback
 * message: FEEDBACK_USERNAME_EMPTY. In addition, the styling must be written
 * at /public_html/css/style.css.
 *
 * @author Douglas Silva <0x9fd287d56ec107ac>
 */
abstract class Feedback
{
    private $feedbackID;

    private $type;

    /**
     * Sets the feedback ID and retrieves its type.
     *
     * @param string $feedbackID The identifier of a feedback message
     */
    public function __construct($feedbackID)
    {
        $this->feedbackID = $feedbackID;

        $this->type = $this->getType();
    }

    /**
     * Retrieves a feedback message.
     *
     * @throws SoftException
     * @return string
     */
    public function get()
    {
        $message = $this->feedbackText[$this->feedbackID];

        try {
            if (is_null($message)) {
                throw new SoftException($this->feedbackID);
            }
        } catch(SoftException $err) {
            $message = $err->invalidFeedbackID();
        }

        $message = $this->format($message);

        return $message;
    }

    private function getType()
    {
        return $this->feedbackType;
    }

    /**
     * Applies styling to a feedback message
     *
     * @param string $message
     * @return string
     */
    private function format($message)
    {
        ob_start();

        echo "<div class='alert alert-" . $this->type . "'>
            " . $message . "
            </div>";

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}

class DangerFeedback extends Feedback
{
    protected $feedbackType = 'danger';

    protected $feedbackText = array(
        'EXCEPTION_SESSION_EXPIRED' => 'This session has expired',
    );
}

class WarningFeedback extends Feedback
{
    protected $feedbackType = 'warning';

    protected $feedbackText = array(
        'FEEDBACK_USERNAME_EMPTY' => 'Username was left empty',
        'FEEDBACK_USERNAME_LENGTH' => 'Minimum username length is 4 characters',
        'FEEDBACK_PASSWORD_EMPTY' => 'Password was left empty',
        'FEEDBACK_PASSWORD_LENGTH' => 'Minimum password length is 8 characters',
        'FEEDBACK_NOT_REGISTERED' => 'Invalid credentials',
        'FEEDBACK_INCORRECT_PASSWD' => 'Invalid credentials',
        'FEEDBACK_USERNAME_TAKEN' => 'Username already taken',
    );
}

class SuccessFeedback extends Feedback
{
    protected $feedbackType = 'success';

    protected $feedbackText = array(
        'FEEDBACK_REGISTRATION_COMPLETE' => 'Registration complete',
    );
}