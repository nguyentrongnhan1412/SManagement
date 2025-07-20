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
 * Class grade_manager
 * Manages student records and grade calculations following Moodle conventions.
 *
 * @package    student-management
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_manager {
    /**
     * @var array Array of student_record objects
     */
    private $students = array();

    /**
     * grade_manager constructor.
     * Initializes an empty students array.
     */
    public function __construct() {
        $this->students = array();
    }

    /**
     * Adds a student record to the system.
     * 
     * @param student_record $student The student record to add
     * @return bool True if student was added successfully, false otherwise
     * @throws invalid_parameter_exception If student parameter is not a student_record object
     * @throws moodle_exception For any Moodle-related error
     */
    public function add_student_record($student) {
        try {
            if (!($student instanceof student_record)) {
                throw new invalid_parameter_exception('student', 'Parameter must be a student_record object');
            }
            
            // Check if student with same ID already exists
            foreach ($this->students as $existing_student) {
                if ($existing_student->id === $student->id) {
                    return false; // Student already exists
                }
            }
            
            $this->students[] = $student;
            return true;
        } catch (moodle_exception $e) {
            throw $e;
        }
    }

    /**
     * Finds a student by their ID.
     * 
     * @param int $id The student ID to search for
     * @return student_record The student record if found
     * @throws invalid_parameter_exception If ID is not a positive integer
     * @throws student_not_found_exception If student is not found
     * @throws moodle_exception For any Moodle-related error
     */
    public function find_student_by_id($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new invalid_parameter_exception('id', 'ID must be a positive integer');
            }
            
            foreach ($this->students as $student) {
                if ($student->id == $id) {
                    return $student;
                }
            }
            
            throw new student_not_found_exception($id);
        } catch (moodle_exception $e) {
            throw $e;
        }
    }

    /**
     * Returns all students in the system.
     * 
     * @return student_record[] Array of student_record objects
     */
    public function get_all_students() {
        return $this->students;
    }

    /**
     * Calculates the average grade for all students in the system.
     * 
     * @return float The average grade across all students, or 0 if no students
     */
    public function get_class_average() {
        if (empty($this->students)) {
            return 0.0;
        }
        
        $total_average = 0;
        $students_with_grades = 0;
        
        foreach ($this->students as $student) {
            $student_average = $student->get_grade_average();
            if ($student_average > 0) {
                $total_average += $student_average;
                $students_with_grades++;
            }
        }
        
        return $students_with_grades > 0 ? $total_average / $students_with_grades : 0.0;
    }

    /**
     * Returns the top performing students based on their average grades.
     * 
     * @param int $limit The maximum number of top students to return (default: 5)
     * @return student_record[] Array of student_record objects sorted by average grade (highest first)
     * @throws invalid_parameter_exception If limit is not a positive integer
     * @throws moodle_exception For any Moodle-related error
     */
    public function get_top_students($limit = 5) {
        try {
            if (!is_numeric($limit) || $limit <= 0) {
                throw new invalid_parameter_exception('limit', 'Limit must be a positive integer');
            }
            
            // Create a copy of students array to avoid modifying the original
            $students_copy = $this->students;
            
            // Sort students by average grade (highest first)
            usort($students_copy, function($a, $b) {
                return $b->get_grade_average() <=> $a->get_grade_average();
            });
            
            // Return only the top N students
            return array_slice($students_copy, 0, $limit);
        } catch (moodle_exception $e) {
            throw $e;
        }
    }

    /**
     * Returns the total number of students in the system.
     * 
     * @return int The number of students
     */
    public function count_students() {
        return count($this->students);
    }

    /**
     * Removes a student from the system by ID.
     * 
     * @param int $id The student ID to remove
     * @return bool True if student was removed successfully, false if not found
     * @throws invalid_parameter_exception If ID is not a positive integer
     * @throws student_not_found_exception If student is not found
     * @throws moodle_exception For any Moodle-related error
     */
    public function remove_student($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new invalid_parameter_exception('id', 'ID must be a positive integer');
            }
            
            foreach ($this->students as $key => $student) {
                if ($student->id == $id) {
                    unset($this->students[$key]);
                    $this->students = array_values($this->students); // Re-index array
                    return true;
                }
            }
            
            throw new student_not_found_exception($id);
        } catch (moodle_exception $e) {
            throw $e;
        }
    }

    /**
     * Updates an existing student record.
     * 
     * @param int $id The student ID to update
     * @param string $firstname New first name
     * @param string $lastname New last name
     * @param string $email New email address
     * @return bool True if student was updated successfully, false if not found
     * @throws invalid_parameter_exception If parameters are invalid
     * @throws student_not_found_exception If student is not found
     * @throws moodle_exception For any Moodle-related error
     */
    public function update_student($id, $firstname, $lastname, $email) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new invalid_parameter_exception('id', 'ID must be a positive integer');
            }
            
            if (empty($firstname) || empty($lastname) || empty($email)) {
                throw new invalid_parameter_exception('firstname/lastname/email', 'First name, last name, and email cannot be empty');
            }
            
            foreach ($this->students as $student) {
                if ($student->id == $id) {
                    $student->firstname = $firstname;
                    $student->lastname = $lastname;
                    $student->email = $email;
                    return true;
                }
            }
            
            throw new student_not_found_exception($id);
        } catch (moodle_exception $e) {
            throw $e;
        }
    }

    /**
     * Gets students with grades below a certain threshold.
     * 
     * @param float $threshold The grade threshold (default: 70.0)
     * @return student_record[] Array of student_record objects with grades below threshold
     * @throws invalid_parameter_exception If threshold is not a valid number
     * @throws moodle_exception For any Moodle-related error
     */
    public function get_students_below_threshold($threshold = 70.0) {
        try {
            if (!is_numeric($threshold)) {
                throw new invalid_parameter_exception('threshold', 'Threshold must be a valid number');
            }
            
            $below_threshold = array();
            
            foreach ($this->students as $student) {
                if ($student->get_grade_average() < $threshold && $student->get_grade_average() > 0) {
                    $below_threshold[] = $student;
                }
            }
            
            return $below_threshold;
        } catch (moodle_exception $e) {
            throw $e;
        }
    }

    /**
     * Gets the class statistics including total students, average grade, and grade distribution.
     * 
     * @return array Array containing class statistics
     */
    public function get_class_statistics() {
        $total_students = $this->count_students();
        $class_average = $this->get_class_average();
        
        $grade_distribution = array(
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
            'F' => 0
        );
        
        foreach ($this->students as $student) {
            $letter_grade = $student->get_letter_grade();
            if (isset($grade_distribution[$letter_grade])) {
                $grade_distribution[$letter_grade]++;
            }
        }
        
        return array(
            'total_students' => $total_students,
            'class_average' => $class_average,
            'grade_distribution' => $grade_distribution
        );
    }
} 