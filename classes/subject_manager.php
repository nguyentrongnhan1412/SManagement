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

require_once __DIR__ . '/exception/invalid_parameter_exception.php';
require_once __DIR__ . '/exception/moodle_exception.php';

/**
 * Class subject_manager
 * Manages subjects in the Student Management System, following Moodle conventions.
 */
class subject_manager {
    /**
     * @var array List of subjects (each subject is an associative array with 'subject_id' and 'subject_name')
     */
    private $subjects = array();

    /**
     * Adds a new subject to the system.
     *
     * @param string $subject_id The subject ID (unique)
     * @param string $subject_name The subject name
     * @return bool True if added successfully, false if subject_id already exists
     * @throws invalid_parameter_exception If parameters are invalid
     */
    public function add_subject($subject_id, $subject_name) {
        $subject_id = $this->validate_subject_id($subject_id);
        $subject_name = $this->validate_subject_name($subject_name);
        foreach ($this->subjects as $subject) {
            if ($subject['subject_id'] === $subject_id) {
                return false; // Subject already exists
            }
        }
        $this->subjects[] = [
            'subject_id' => $subject_id,
            'subject_name' => $subject_name
        ];
        return true;
    }

    /**
     * Removes a subject by subject_id.
     *
     * @param string $subject_id The subject ID
     * @return bool True if removed, false if not found
     * @throws invalid_parameter_exception If subject_id is invalid
     */
    public function remove_subject($subject_id) {
        $subject_id = $this->validate_subject_id($subject_id);
        foreach ($this->subjects as $key => $subject) {
            if ($subject['subject_id'] === $subject_id) {
                unset($this->subjects[$key]);
                $this->subjects = array_values($this->subjects); // Re-index array
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the list of all subjects.
     *
     * @return array Array of subjects, each with 'subject_id' and 'subject_name'
     */
    public function list_subjects() {
        return $this->subjects;
    }

    /**
     * Validates a subject ID (alphanumeric, 2-10 chars).
     *
     * @param string $subject_id The subject ID
     * @return string The validated subject ID
     * @throws invalid_parameter_exception If subject_id is invalid
     */
    private function validate_subject_id($subject_id) {
        if (!is_string($subject_id) || !preg_match('/^[A-Za-z0-9]{2,10}$/', $subject_id)) {
            throw new invalid_parameter_exception('subject_id', 'Invalid subject ID.');
        }
        return strtoupper($subject_id);
    }

    /**
     * Validates a subject name (letters, spaces, hyphens, 2-100 chars).
     *
     * @param string $subject_name The subject name
     * @return string The validated subject name
     * @throws invalid_parameter_exception If subject_name is invalid
     */
    private function validate_subject_name($subject_name) {
        if (!is_string($subject_name) || !preg_match("/^[A-Za-z\-' .]{2,100}$/", $subject_name)) {
            throw new invalid_parameter_exception('subject_name', 'Invalid subject name.');
        }
        return trim($subject_name);
    }
} 