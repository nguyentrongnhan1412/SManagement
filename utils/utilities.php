<?php
// This file is part of the Student Management System
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Utilities class for Student Management System
 *
 * @package    local_grademanager
 * @category   utility
 * @copyright  2024 Student Management System
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../classes/exception/invalid_parameter_exception.php';

/**
 * Class utilities
 * Provides utility functions for student management system, following Moodle conventions.
 *
 * @package    local_grademanager
 * @category   utility
 * @copyright  2024 Student Management System
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utilities {
    /**
     * Format grade with 2 decimal places for display.
     *
     * @param float $grade The grade to format
     * @return string The formatted grade
     * @throws invalid_parameter_exception If grade is not numeric
     */
    public static function format_grade_display($grade) {
        if (!is_numeric($grade)) {
            throw new invalid_parameter_exception('grade', 'Grade must be numeric.');
        }
        return number_format((float)$grade, 2);
    }

    /**
     * Generate unique student ID using Moodle pattern.
     * Moodle typically uses: first letter of firstname + first letter of lastname + random number
     *
     * @param string $firstname The student's first name
     * @param string $lastname The student's last name
     * @return string The generated student ID
     * @throws invalid_parameter_exception If names are invalid
     */
    public static function generate_student_id($firstname, $lastname) {
        if (empty($firstname) || empty($lastname)) {
            throw new invalid_parameter_exception('firstname/lastname', 'First name and last name cannot be empty.');
        }
        
        // Clean and validate names
        $firstname = trim(strip_tags($firstname));
        $lastname = trim(strip_tags($lastname));
        
        if (strlen($firstname) < 1 || strlen($lastname) < 1) {
            throw new invalid_parameter_exception('firstname/lastname', 'Names must contain at least one character.');
        }
        
        // Get first letter of each name (Moodle pattern)
        $first_initial = strtoupper(substr($firstname, 0, 1));
        $last_initial = strtoupper(substr($lastname, 0, 1));
        
        // Generate random 4-digit number
        $random_number = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $first_initial . $last_initial . $random_number;
    }

    /**
     * Extract student data from CSV string.
     *
     * @param string $line The CSV line to parse
     * @return array The parsed student data
     * @throws invalid_parameter_exception If line is invalid
     */
    public static function parse_csv_line($line) {
        if (empty($line) || !is_string($line)) {
            throw new invalid_parameter_exception('line', 'CSV line must be a non-empty string.');
        }
        
        // Parse CSV line
        $data = str_getcsv($line);
        
        // Expected format: id, firstname, lastname, email, average, letter, grades
        if (count($data) < 4) {
            throw new invalid_parameter_exception('line', 'CSV line must contain at least id, firstname, lastname, and email.');
        }
        
        $result = [
            'id' => isset($data[0]) ? trim($data[0]) : '',
            'firstname' => isset($data[1]) ? trim($data[1]) : '',
            'lastname' => isset($data[2]) ? trim($data[2]) : '',
            'email' => isset($data[3]) ? trim($data[3]) : '',
            'average' => isset($data[4]) ? (float)trim($data[4]) : 0.0,
            'letter' => isset($data[5]) ? trim($data[5]) : '',
            'grades' => []
        ];
        
        // Parse grades if present
        if (isset($data[6]) && !empty($data[6])) {
            $grades_json = trim($data[6]);
            $grades = json_decode($grades_json, true);
            if (is_array($grades)) {
                $result['grades'] = $grades;
            }
        }
        
        return $result;
    }

    /**
     * Calculate grade statistics (min, max, average, median).
     *
     * @param array $grades Array of numeric grades
     * @return array Statistics array with min, max, average, median
     * @throws invalid_parameter_exception If grades array is invalid
     */
    public static function calculate_grade_statistics($grades) {
        if (!is_array($grades)) {
            throw new invalid_parameter_exception('grades', 'Grades must be an array.');
        }
        
        // Filter out non-numeric values
        $numeric_grades = array_filter($grades, 'is_numeric');
        
        if (empty($numeric_grades)) {
            return [
                'min' => 0,
                'max' => 0,
                'average' => 0,
                'median' => 0,
                'count' => 0
            ];
        }
        
        // Calculate statistics
        $min = min($numeric_grades);
        $max = max($numeric_grades);
        $average = array_sum($numeric_grades) / count($numeric_grades);
        
        // Calculate median
        sort($numeric_grades);
        $count = count($numeric_grades);
        $middle = floor($count / 2);
        
        if ($count % 2 == 0) {
            $median = ($numeric_grades[$middle - 1] + $numeric_grades[$middle]) / 2;
        } else {
            $median = $numeric_grades[$middle];
        }
        
        return [
            'min' => (float)$min,
            'max' => (float)$max,
            'average' => (float)$average,
            'median' => (float)$median,
            'count' => $count
        ];
    }

    /**
     * Simulate Moodle language strings.
     *
     * @param string $identifier The string identifier
     * @param string $component The component name (default: 'local_grademanager')
     * @return string The localized string
     */
    public static function get_string($identifier, $component = 'local_grademanager') {
        // Simulate Moodle's get_string function
        // In a real Moodle environment, this would load from language files
        
        $strings = [
            'local_grademanager' => [
                'student_added' => 'Student added successfully',
                'student_updated' => 'Student updated successfully',
                'student_deleted' => 'Student deleted successfully',
                'grade_updated' => 'Grade updated successfully',
                'invalid_grade' => 'Invalid grade value',
                'student_not_found' => 'Student not found',
                'file_upload_error' => 'File upload error',
                'csv_parse_error' => 'Error parsing CSV file',
                'backup_created' => 'Backup created successfully',
                'restore_completed' => 'Restore completed successfully'
            ],
            'core' => [
                'save' => 'Save',
                'cancel' => 'Cancel',
                'delete' => 'Delete',
                'edit' => 'Edit',
                'add' => 'Add',
                'search' => 'Search',
                'loading' => 'Loading...',
                'error' => 'Error',
                'success' => 'Success',
                'warning' => 'Warning',
                'student' => 'Student',
                'firstname' => 'First Name',
                'lastname' => 'Last Name',
                'email' => 'Email',
                'back' => 'Back',
                'to' => 'to',
                'list' => 'List'
            ]
        ];
        
        if (isset($strings[$component][$identifier])) {
            return $strings[$component][$identifier];
        }
        
        // Return the identifier if string not found (Moodle behavior)
        return $identifier;
    }

    /**
     * Clean user input following Moodle security practices.
     *
     * @param string $text The text to clean
     * @param string $type The type of cleaning to apply ('plain', 'html', 'email', 'url')
     * @return string The cleaned text
     * @throws invalid_parameter_exception If text is not a string
     */
    public static function clean_text($text, $type = 'plain') {
        if (!is_string($text)) {
            throw new invalid_parameter_exception('text', 'Text must be a string.');
        }
        
        switch ($type) {
            case 'plain':
                // Remove all HTML tags and trim whitespace
                return trim(strip_tags($text));
                
            case 'html':
                // Allow safe HTML tags only (Moodle's allowedtags)
                $allowed_tags = '<p><br><strong><em><u><ol><ul><li><h1><h2><h3><h4><h5><h6>';
                return trim(strip_tags($text, $allowed_tags));
                
            case 'email':
                // Clean email address
                $email = trim(strip_tags($text));
                return filter_var($email, FILTER_SANITIZE_EMAIL);
                
            case 'url':
                // Clean URL
                $url = trim(strip_tags($text));
                return filter_var($url, FILTER_SANITIZE_URL);
                
            case 'int':
                // Clean integer
                return (int)$text;
                
            case 'float':
                // Clean float
                return (float)$text;
                
            default:
                // Default to plain text cleaning
                return trim(strip_tags($text));
        }
    }
} 