<?php
// This file is part of Student Management System
//
// Student Management System is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Student Management System is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Student Management System.  If not, see <http://www.gnu.org/licenses/>.

require_once __DIR__ . '/moodle_exception.php';

/**
 * Exception thrown for general parameter validation errors.
 * Follows Moodle's exception handling patterns.
 */
class invalid_parameter_exception extends moodle_exception {
    /**
     * invalid_parameter_exception constructor.
     * @param string $param The parameter name or description
     * @param string|null $debug Additional debug info (optional)
     */
    public function __construct($param, $debug = null) {
        $message = 'Invalid parameter: ' . htmlspecialchars($param) . '.';
        $errorcode = 'invalidparameter';
        if ($debug) {
            $message .= ' [Debug: ' . $debug . ']';
        }
        parent::__construct($message, $errorcode);
    }
}
