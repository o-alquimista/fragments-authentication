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

namespace Fragments\Utility\SessionManagement;

use Fragments\Utility\SessionManagement\Init\SessionStrict;
use Fragments\Utility\Errors\SoftException;

/**
 * Session Utility
 *
 * Handles starting a session and regenerating
 * a new session ID while attempting to avoid
 * lost sessions due to unstable connections.
 */

/**
 * Session start
 *
 * Starts a session when this class is instantiated.
 *
 * If the session contains the flag 'destroyed',
 * we will check if it has expired. If it has,
 * all session variables will be wiped and a session
 * expired exception will be thrown.
 *
 * If it hasn't expired yet, an attempt to reset the
 * newly generated ID will be made.
 *
 * @author Douglas Silva <0x9fd287d56ec107ac>
 */
class Session
{
    public function __construct()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            return;
        }

        new SessionStrict;

        $isDestroyed = $this->isDestroyed();
        if ($isDestroyed === false) {
            return;
        }

        $isExpired = $this->isExpired();
        if ($isExpired === true) {
            return;
        }

        $isSetNewSessionID = $this->isSetNewSessionID();
        if ($isSetNewSessionID === true) {
            return;
        }

        session_commit();

        session_id(SessionTools::get('new_session_id'));

        new SessionStrict;
    }

    /**
     * Returns true if the current session contains the 'destroyed' flag.
     *
     * @return boolean
     */
    private function isDestroyed()
    {
        if (null === SessionTools::get('destroyed')) {
            return false;
        }

        return true;
    }

    /**
     * Wipes all session variables if the flag 'destroyed' has
     * been set for more than 5 minutes.
     *
     * @throws SoftException
     * @return boolean
     */
    private function isExpired()
    {
        try {
            if (SessionTools::get('destroyed') < time() - 300) {
                SessionTools::destroyAll();

                throw new SoftException();
            }

            return false;
        } catch(SoftException $err) {
            $err->sessionExpired();
        }
    }

    /**
     * Returns true if the session variable 'new_session_id' is set.
     *
     * @return boolean
     */
    private function isSetNewSessionID()
    {
        if (null === SessionTools::get('new_session_id')) {
            return false;
        }

        return true;
    }
}
