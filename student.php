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

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/student_record.php';
require_once __DIR__ . '/utils/utilities.php';

function getAllStudents($pdo) {
    $stmt = $pdo->query('SELECT * FROM students ORDER BY student_id');
    return $stmt->fetchAll();
}

function getStudentById($pdo, $id) {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function addStudent($pdo, $first_name, $last_name, $email) {
    $stmt = $pdo->prepare('INSERT INTO students (first_name, last_name, email) VALUES (?, ?, ?)');
    return $stmt->execute([$first_name, $last_name, $email]);
}

function updateStudent($pdo, $id, $first_name, $last_name, $email) {
    $stmt = $pdo->prepare('UPDATE students SET first_name = ?, last_name = ?, email = ? WHERE student_id = ?');
    return $stmt->execute([$first_name, $last_name, $email, $id]);
}

function deleteStudent($pdo, $id) {
    $stmt = $pdo->prepare('DELETE FROM students WHERE student_id = ?');
    return $stmt->execute([$id]);
}

/**
 * Fetch all students as student_record objects with grades.
 *
 * @param PDO $pdo
 * @return student_record[]
 */
function getAllStudentRecords($pdo) {
    $students = [];
    $stmt = $pdo->query('SELECT * FROM students ORDER BY student_id');
    while ($row = $stmt->fetch()) {
        // Fetch grades for this student
        $grades = [];
        $gradeStmt = $pdo->prepare('SELECT s.subject_name, g.grade_value FROM grades g JOIN subjects s ON g.subject_id = s.subject_id WHERE g.student_id = ?');
        $gradeStmt->execute([$row['student_id']]);
        while ($g = $gradeStmt->fetch()) {
            $grades[$g['subject_name']] = $g['grade_value'];
        }
        $students[] = new student_record($row['student_id'], $row['first_name'], $row['last_name'], $row['email'], $grades);
    }
    return $students;
}

/**
 * Fetch a single student as a student_record object with grades.
 *
 * @param PDO $pdo
 * @param int $id
 * @return student_record|null
 */
function getStudentRecordById($pdo, $id) {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $grades = [];
    $gradeStmt = $pdo->prepare('SELECT s.subject_name, g.grade_value FROM grades g JOIN subjects s ON g.subject_id = s.subject_id WHERE g.student_id = ?');
    $gradeStmt->execute([$id]);
    while ($g = $gradeStmt->fetch()) {
        $grades[$g['subject_name']] = $g['grade_value'];
    }
    return new student_record($row['student_id'], $row['first_name'], $row['last_name'], $row['email'], $grades);
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['first_name'], $_POST['last_name'], $_POST['email'])
) {
    $pdo = require __DIR__ . '/config/database.php';
    $first_name = utilities::clean_text($_POST['first_name'], 'plain');
    $last_name = utilities::clean_text($_POST['last_name'], 'plain');
    $email = utilities::clean_text($_POST['email'], 'email');
    if ($first_name && $last_name && $email) {
        addStudent($pdo, $first_name, $last_name, $email);
    }
    header('Location: index.php');
    exit;
} 