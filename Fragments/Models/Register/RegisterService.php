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

use Fragments\Utility\Server\Request;
use Fragments\Models\Register\FormValidation;
use Fragments\Models\Register\CredentialHandler;
use Fragments\Models\Register\User;

/**
 * Register service
 *
 * @author Douglas Silva <0x9fd287d56ec107ac>
 */
class RegisterService
{
    private $username;

    private $passwd;

    public function __construct()
    {
        $this->username = $this->clean(Request::post('username'));
        $this->passwd = Request::post('passwd');
    }

    public function register()
    {
        $formInput = new FormValidation($this->username, $this->passwd);

        if ($formInput->validate() === false) {
            return false;
        }

        $credential = new CredentialHandler($this->username, $this->passwd);

        if ($credential->usernameAvailable() === false) {
            return false;
        }

        $hashedPassword = $credential->hashPassword();

        $user = new User($this->username, $hashedPassword);
        $user->createUser();

        return true;
    }

    private function clean($input)
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);

        return $input;
    }
}