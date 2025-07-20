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

require_once __DIR__ . '/utils/file_handler.php';
require_once __DIR__ . '/student.php';
require_once __DIR__ . '/classes/exception/invalid_grade_exception.php';

$pdo = require __DIR__ . '/config/database.php';

// Helper: get student by email
function getStudentByEmail($pdo, $email) {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Helper: get subject name => id map
function getSubjectNameIdMap($pdo) {
    $stmt = $pdo->query('SELECT subject_id, subject_name FROM subjects');
    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        $map[$row['subject_name']] = $row['subject_id'];
    }
    return $map;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['csv_file']['tmp_name'];
    try {
        $students = file_handler::load_students_from_csv($tmpName);
        $imported = 0;
        $skipped = 0;
        $errors = [];
        foreach ($students as $student) {
            $first_name = $student->firstname;
            $last_name = $student->lastname;
            $email = $student->email;
            // Check for duplicate email
            if (getStudentByEmail($pdo, $email)) {
                $skipped++;
                continue;
            }
            // Insert student
            if (addStudent($pdo, $first_name, $last_name, $email)) {
                $imported++;
            } else {
                $errors[] = "Failed to import $first_name $last_name ($email).";
            }
        }
        $msg = "<div style='color:green; text-align:center; margin-bottom:12px;'>Successfully imported $imported students.";
        if ($skipped > 0) $msg .= " Skipped $skipped duplicate(s).";
        $msg .= "</div>";
        if (!empty($errors)) {
            $msg .= "<div style='color:orange; text-align:left; margin-bottom:12px;'><ul style='margin:0 0 0 18px;'>";
            foreach ($errors as $err) $msg .= "<li>" . htmlspecialchars($err) . "</li>";
            $msg .= "</ul></div>";
        }
        $message = $msg;
    } catch (Exception $e) {
        $message = "<div style='color:red; text-align:center; margin-bottom:12px;'>Import failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Students</title>
    <link rel="stylesheet" href="asset/styles.css">
    <style>
        .import-container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px 24px 24px 24px; }
        .import-form { display: flex; flex-direction: column; gap: 18px; }
        .import-form input[type='file'] { padding: 8px; }
        .import-form button { padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: 600; cursor: pointer; }
        .import-form button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="import-container">
        <h1>Import Students from CSV</h1>
        <div style="margin-bottom: 12px; text-align: center;">
            <a href="asset/sample_students.csv" download>Download Sample CSV</a>
        </div>
        <?= $message ?>
        <form class="import-form" method="post" enctype="multipart/form-data">
            <label for="csv_file">Select CSV file:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
            <button type="submit">Import</button>
        </form>
        <div style="margin-top:18px;"><a href="index.php">&larr; Back to Student List</a></div>
    </div>
</body>
</html> 