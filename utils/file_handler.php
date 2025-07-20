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

require_once __DIR__ . '/grade_validator.php';
require_once __DIR__ . '/../classes/student_record.php';
require_once __DIR__ . '/../classes/exception/invalid_parameter_exception.php';
require_once __DIR__ . '/../classes/exception/student_not_found_exception.php';
require_once __DIR__ . '/../classes/exception/moodle_exception.php';
require_once __DIR__ . '/utilities.php';

/**
 * Class file_handler
 * Handles CSV and backup operations for student data, following Moodle conventions.
 */
class file_handler {
    /**
     * Saves an array of student_record objects to a CSV file in Moodle export format.
     *
     * @param student_record[] $students Array of student_record objects
     * @param string $filename The CSV filename to save to
     * @return void
     * @throws invalid_parameter_exception If parameters are invalid
     * @throws moodle_exception On file write errors
     */
    public static function save_students_to_csv($students, $filename) {
        if (!is_array($students) || empty($filename)) {
            throw new invalid_parameter_exception('students/filename', 'Invalid students array or filename.');
        }
        $fp = @fopen($filename, 'w');
        if (!$fp) {
            throw new moodle_exception('Failed to open file for writing: ' . htmlspecialchars($filename));
        }
        // Moodle export header
        fputcsv($fp, ['id', 'firstname', 'lastname', 'email', 'average', 'letter', 'grades']);
        foreach ($students as $student) {
            if (!($student instanceof student_record)) continue;
            $grades_str = json_encode($student->grades);
            fputcsv($fp, [
                $student->id,
                $student->firstname,
                $student->lastname,
                $student->email,
                utilities::format_grade_display($student->get_grade_average()),
                $student->get_letter_grade(),
                $grades_str
            ]);
        }
        fclose($fp);
    }

    /**
     * Loads student records from a CSV file, validating each row.
     *
     * @param string $filename The CSV filename to load from
     * @return student_record[] Array of validated student_record objects
     * @throws invalid_parameter_exception If filename is invalid
     * @throws moodle_exception On file read or validation errors
     */
    public static function load_students_from_csv($filename) {
        if (empty($filename) || !file_exists($filename)) {
            throw new invalid_parameter_exception('filename', 'File does not exist.');
        }
        $fp = @fopen($filename, 'r');
        if (!$fp) {
            throw new moodle_exception('Failed to open file for reading: ' . htmlspecialchars($filename));
        }
        $students = [];
        $header = fgetcsv($fp);
        // Detect format: new (firstname,lastname,email) or old (id,firstname,lastname,email,average,letter,grades)
        if ($header === false) {
            fclose($fp);
            throw new moodle_exception('CSV file is empty or invalid.');
        }
        $is_simple = (count($header) === 3 && strtolower($header[0]) === 'firstname' && strtolower($header[1]) === 'lastname' && strtolower($header[2]) === 'email');
        while (($row = fgetcsv($fp)) !== false) {
            try {
                if ($is_simple) {
                    list($firstname, $lastname, $email) = $row;
                    $id = null;
                    $grades = [];
                } else {
                    list($id, $firstname, $lastname, $email, $average, $letter, $grades_json) = $row;
                    $grades = json_decode($grades_json, true);
                    if (!is_array($grades)) $grades = [];
                }
                $firstname = grade_validator::validate_student_name($firstname);
                $lastname = grade_validator::validate_student_name($lastname);
                $email = grade_validator::validate_email_address($email);
                if (!empty($grades)) {
                    foreach ($grades as $subject => $score) {
                        $grades[$subject] = grade_validator::validate_grade_score($score);
                    }
                }
                $students[] = new student_record($id, $firstname, $lastname, $email, $grades);
            } catch (Exception $e) {
                // Skip invalid rows, or optionally log error
                continue;
            }
        }
        fclose($fp);
        return $students;
    }

    /**
     * Exports a grade report in Moodle-style CSV format.
     *
     * @param student_record[] $students Array of student_record objects
     * @param string $filename The CSV filename to export to
     * @return void
     * @throws invalid_parameter_exception If parameters are invalid
     * @throws moodle_exception On file write errors
     */
    public static function export_grade_report($students, $filename) {
        if (!is_array($students) || empty($filename)) {
            throw new invalid_parameter_exception('students/filename', 'Invalid students array or filename.');
        }
        $fp = @fopen($filename, 'w');
        if (!$fp) {
            throw new moodle_exception('Failed to open file for writing: ' . htmlspecialchars($filename));
        }
        // Moodle-style grade report header
        fputcsv($fp, ['id', 'fullname', 'email', 'average', 'letter', 'grades']);
        foreach ($students as $student) {
            if (!($student instanceof student_record)) continue;
            $grades_str = json_encode($student->grades);
            fputcsv($fp, [
                $student->id,
                $student->get_fullname(),
                $student->email,
                utilities::format_grade_display($student->get_grade_average()),
                $student->get_letter_grade(),
                $grades_str
            ]);
        }
        fclose($fp);
    }

    /**
     * Simulates Moodle backup functionality by serializing student data.
     *
     * @param mixed $data The data to backup (array or object)
     * @return string The serialized backup string
     * @throws invalid_parameter_exception If data is not serializable
     */
    public static function backup_student_data($data) {
        if (!is_array($data) && !is_object($data)) {
            throw new invalid_parameter_exception('data', 'Data must be array or object.');
        }
        $backup = @serialize($data);
        if ($backup === false) {
            throw new invalid_parameter_exception('data', 'Failed to serialize data.');
        }
        return $backup;
    }
} 