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
 * Class student_record
 * Represents a student and their grades in the Student Management System.
 *
 * @package    student-management
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_record {
    /**
     * @var int Student ID
     */
    public $id;

    /**
     * @var string First name
     */
    public $firstname;

    /**
     * @var string Last name
     */
    public $lastname;

    /**
     * @var string Email address
     */
    public $email;

    /**
     * @var array Grades array (subject => score)
     */
    public $grades = array();

    /**
     * student_record constructor.
     *
     * @param int $id Student ID
     * @param string $firstname First name
     * @param string $lastname Last name
     * @param string $email Email address
     * @param array $grades (optional) Grades array (subject => score)
     */
    public function __construct($id, $firstname, $lastname, $email, $grades = array()) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->grades = $grades;
    }

    /**
     * Adds a grade entry for a subject.
     *
     * @param string $subject Subject name
     * @param float $score Grade score
     * @return void
     * @throws invalid_grade_exception If the grade is not between 0 and 100
     */
    public function add_grade_entry($subject, $score) {
        try {
            if (!is_numeric($score) || $score < 0 || $score > 100) {
                throw new invalid_grade_exception($score, 'Grade must be between 0 and 100');
            }
            $this->grades[$subject] = $score;
        } catch (invalid_grade_exception $e) {
            throw $e;
        }
    }

    /**
     * Calculates the average grade.
     *
     * @return float Average grade, or 0 if no grades
     */
    public function get_grade_average() {
        if (empty($this->grades)) {
            return 0;
        }
        return array_sum($this->grades) / count($this->grades);
    }

    /**
     * Returns the letter grade based on the average.
     *
     * @return string Letter grade (A, B, C, D, F)
     */
    public function get_letter_grade() {
        $avg = $this->get_grade_average();
        if ($avg >= 90) {
            return 'A';
        } elseif ($avg >= 80) {
            return 'B';
        } elseif ($avg >= 70) {
            return 'C';
        } elseif ($avg >= 60) {
            return 'D';
        } else {
            return 'F';
        }
    }

    /**
     * Returns formatted student information.
     *
     * @return string Formatted info
     */
    public function get_student_info() {
        $info = "ID: {$this->id}\n";
        $info .= "Name: " . $this->get_fullname() . "\n";
        $info .= "Email: {$this->email}\n";
        $info .= "Average Grade: " . number_format($this->get_grade_average(), 2) . " ({$this->get_letter_grade()})\n";
        $info .= "Grades:\n";
        foreach ($this->grades as $subject => $score) {
            $info .= "  $subject: $score\n";
        }
        return $info;
    }

    /**
     * Returns the full name of the student.
     *
     * @return string Full name
     */
    public function get_fullname() {
        return $this->firstname . ' ' . $this->lastname;
    }
} 