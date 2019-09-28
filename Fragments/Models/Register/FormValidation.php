<?php

/**
 * Copyright 2019 Douglas Silva (0x9fd287d56ec107ac)
 *
 * This file is part of Fragments.
 *
 * Fragments is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Fragments.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Fragments\Models\Register;

use Fragments\Utility\Feedback\WarningFeedback;

/**
 * Input validation
 *
 * Performs validation of form data, but should never
 * have to use a data mapper.
 *
 * @author Douglas Silva <0x9fd287d56ec107ac>
 */
class FormValidation
{
    public $feedbackText = array();

    private $username;

    private $passwd;

    public function __construct($username, $passwd)
    {
        $this->username = $username;
        $this->passwd = $passwd;
    }

    public function validate()
    {
        $validationUsername = $this->validateUsername();
        $validationPassword = $this->validatePassword();

        if ($validationUsername && $validationPassword === true) {
            return true;
        }

        return false;
    }

    private function validateUsername()
    {
        if (empty($this->username)) {
            $feedback = new WarningFeedback('FEEDBACK_USERNAME_EMPTY');
            $this->feedbackText[] = $feedback->get();

            return false;
        }

        if (strlen($this->username) < 4 or strlen($this->username) > 15) {
            $feedback = new WarningFeedback('FEEDBACK_USERNAME_LENGTH');
            $this->feedbackText[] = $feedback->get();

            return false;
        }

        if (!preg_match('/^(?!.*__.*)[a-zA-Z0-9_]+$/', $this->username)) {
            $feedback = new WarningFeedback('FEEDBACK_USERNAME_INVALID');
            $this->feedbackText[] = $feedback->get();

            return false;
        }

        return true;
    }

    private function validatePassword()
    {
        if (empty($this->passwd)) {
            $feedback = new WarningFeedback('FEEDBACK_PASSWORD_EMPTY');
            $this->feedbackText[] = $feedback->get();

            return false;
        }

        if (strlen($this->passwd) <= 7) {
            $feedback = new WarningFeedback('FEEDBACK_PASSWORD_LENGTH');
            $this->feedbackText[] = $feedback->get();

            return false;
        }

        return true;
    }
}
