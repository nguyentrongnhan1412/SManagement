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

require_once __DIR__ . '/../classes/exception/invalid_grade_exception.php';
require_once __DIR__ . '/../classes/exception/invalid_parameter_exception.php';

/**
 * Class grade_validator
 * Provides validation and cleaning utilities for student data, following Moodle conventions.
 */
class grade_validator {
    /**
     * Validates a grade score (0-100).
     *
     * @param mixed $grade The grade to validate
     * @return float The validated grade
     * @throws invalid_grade_exception If the grade is not between 0 and 100
     */
    public static function validate_grade_score($grade) {
        if (!is_numeric($grade) || $grade < 0 || $grade > 100) {
            throw new invalid_grade_exception($grade);
        }
        return (float)$grade;
    }

    /**
     * Validates an email address using Moodle-style patterns.
     *
     * @param string $email The email address to validate
     * @return string The validated email address
     * @throws invalid_parameter_exception If the email is invalid
     */
    public static function validate_email_address($email) {
        // Moodle uses a strict RFC 5322 pattern, but here we use a reasonable approximation
        $pattern = '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/';
        if (!is_string($email) || !preg_match($pattern, $email)) {
            throw new invalid_parameter_exception('email', 'Invalid email address format.');
        }
        return $email;
    }

    /**
     * Validates a student name using Moodle user name rules.
     *
     * @param string $name The name to validate
     * @return string The validated name
     * @throws invalid_parameter_exception If the name is invalid
     */
    public static function validate_student_name($name) {
        // Moodle allows letters, spaces, hyphens, apostrophes, and periods
        $pattern = "/^[A-Za-z\-' .]+$/";
        if (!is_string($name) || !preg_match($pattern, $name) || strlen($name) < 2 || strlen($name) > 100) {
            throw new invalid_parameter_exception('name', 'Invalid student name.');
        }
        return $name;
    }

    /**
     * Simulates Moodle's clean_param for basic types.
     *
     * @param mixed $value The value to clean
     * @param string $type The type to clean as (e.g., 'int', 'float', 'email', 'plain')
     * @return mixed The cleaned value
     * @throws invalid_parameter_exception If the value is invalid for the type
     */
    public static function clean_param($value, $type) {
        switch ($type) {
            case 'int':
                if (!is_numeric($value) || intval($value) != $value) {
                    throw new invalid_parameter_exception('int', 'Value must be an integer.');
                }
                return intval($value);
            case 'float':
                if (!is_numeric($value)) {
                    throw new invalid_parameter_exception('float', 'Value must be a float.');
                }
                return floatval($value);
            case 'email':
                return self::validate_email_address($value);
            case 'plain':
                // Remove tags and trim whitespace
                return trim(strip_tags($value));
            default:
                throw new invalid_parameter_exception('type', 'Unknown clean_param type.');
        }
    }
} 