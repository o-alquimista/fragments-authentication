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

namespace App\Models\Register;

use App\Models\Register\DataMappers\CredentialHandlerMapper;
use Fragments\Utility\Feedback;

/**
 * Credential handler
 *
 * Tasks that concern the treatment of login credentials
 *
 * @author Douglas Silva <0x9fd287d56ec107ac>
 */
class CredentialHandler
{
    private $username;

    private $passwd;

    public function __construct($username, $passwd)
    {
        $this->username = $username;
        $this->passwd = $passwd;
    }

    public function usernameAvailable()
    {
        $storage = new CredentialHandlerMapper;
        $matchingRows = $storage->retrieveCount($this->username);

        if ($matchingRows >= 1) {
            Feedback::add(
                'warning',
                'Username already taken'
            );

            return false;
        }

        return true;
    }

    public function hashPassword()
    {
        $hash = password_hash($this->passwd, PASSWORD_DEFAULT);

        return $hash;
    }
}