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

/**
 * Simulated Moodle Exception for Student Management System
 *
 * This class mimics the moodle_exception structure for demonstration/testing purposes.
 */
class moodle_exception extends Exception {
    /**
     * @var string Optional error code
     */
    protected $errorcode;

    /**
     * moodle_exception constructor.
     * @param string $message
     * @param string $errorcode
     * @param Exception|null $previous
     */
    public function __construct($message, $errorcode = '', Exception $previous = null) {
        $this->errorcode = $errorcode;
        parent::__construct($message, 0, $previous);
    }

    /**
     * Outputs a simple error page (simulating Moodle's error output)
     */
    public function print_error_page() {
        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head><meta charset="UTF-8"><title>Moodle Exception</title>';
        echo '<link rel="stylesheet" href="asset/styles.css">';
        echo '</head><body>';
        echo '<div class="container" style="max-width:500px;margin-top:60px;text-align:center;">';
        echo '<h1 style="color:#e53935;">An error occurred</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($this->getMessage()) . '</p>';
        if ($this->errorcode) {
            echo '<p><strong>Error code:</strong> ' . htmlspecialchars($this->errorcode) . '</p>';
        }
        echo '<a href="index.php">Back to Student List</a>';
        echo '</div></body></html>';
    }
}

// Example usage (uncomment to test):
// throw new moodle_exception('Simulated Moodle error', 'simulatederror');