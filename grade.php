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

$pdo = require __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/utilities.php';

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
if (!$student_id) {
    die('No student selected.');
}

// Fetch student info
$stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
$stmt->execute([$student_id]);
$student = $stmt->fetch();
if (!$student) {
    die('Student not found.');
}

// Fetch enrolled subjects
$stmt = $pdo->prepare('SELECT s.subject_id, s.subject_name FROM enrollments e JOIN subjects s ON e.subject_id = s.subject_id WHERE e.student_id = ?');
$stmt->execute([$student_id]);
$subjects = $stmt->fetchAll();

// Fetch current grades
$stmt = $pdo->prepare('SELECT subject_id, grade_value FROM grades WHERE student_id = ?');
$stmt->execute([$student_id]);
$grades = [];
foreach ($stmt->fetchAll() as $row) {
    $grades[$row['subject_id']] = $row['grade_value'];
}

// Handle form submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grades']) && is_array($_POST['grades'])) {
    foreach ($_POST['grades'] as $subject_id => $grade_value) {
        if ($grade_value === '' || !is_numeric($grade_value) || $grade_value < 0 || $grade_value > 100) {
            $msg = '<div style="color:#e53935; text-align:center; margin-bottom:12px;">' . utilities::get_string('invalid_grade') . '</div>';
            break;
        }
    }
    if (!$msg) {
        foreach ($_POST['grades'] as $subject_id => $grade_value) {
            // Check if grade exists
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM grades WHERE student_id = ? AND subject_id = ?');
            $stmt->execute([$student_id, $subject_id]);
            if ($stmt->fetchColumn() > 0) {
                // Update
                $stmt = $pdo->prepare('UPDATE grades SET grade_value = ? WHERE student_id = ? AND subject_id = ?');
                $stmt->execute([$grade_value, $student_id, $subject_id]);
            } else {
                // Insert
                $stmt = $pdo->prepare('INSERT INTO grades (student_id, subject_id, grade_value) VALUES (?, ?, ?)');
                $stmt->execute([$student_id, $subject_id, $grade_value]);
            }
        }
        $msg = '<div style="color:#1ca21c; text-align:center; margin-bottom:12px;">' . utilities::get_string('grade_updated') . '</div>';
        // Refresh grades
        $stmt = $pdo->prepare('SELECT subject_id, grade_value FROM grades WHERE student_id = ?');
        $stmt->execute([$student_id]);
        $grades = [];
        foreach ($stmt->fetchAll() as $row) {
            $grades[$row['subject_id']] = $row['grade_value'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter Grades - <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></title>
    <link rel="stylesheet" href="asset/styles.css">
    <style>
        body { display: flex; min-height: 100vh; margin: 0; }
        .sidebar { width: 220px; background: #222d32; color: #fff; display: flex; flex-direction: column; padding: 32px 0 0 0; min-height: 100vh; box-shadow: 2px 0 8px rgba(0,0,0,0.04); }
        .sidebar h2 { text-align: center; margin-bottom: 32px; font-size: 1.4rem; letter-spacing: 1px; color: #fff; }
        .sidebar a { color: #fff; text-decoration: none; padding: 16px 32px; font-size: 1.08rem; transition: background 0.2s; border-left: 4px solid transparent; }
        .sidebar a.active, .sidebar a:hover { background: #1a2226; border-left: 4px solid #007bff; }
        .dashboard-content { flex: 1; padding: 0; background: #f4f6f8; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px 24px 24px 24px; }
        .grade-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; background: #fff; font-size: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border-radius: 8px; overflow: hidden; }
        .grade-table th, .grade-table td { padding: 12px 10px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        .grade-table th { background: #f4f6f8; color: #333; font-weight: 600; }
        .grade-table tbody tr:nth-child(even) { background: #f8fafc; }
        .grade-table tbody tr:hover { background: #e6f0ff; }
        .save-btn { width: 100%; padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: 600; cursor: pointer; margin-top: 18px; }
        .save-btn:hover { background: #0056b3; }
        .student-info { margin-bottom: 18px; text-align: center; }
        .student-info strong { color: #1976d2; }
        @media (max-width: 700px) { .container { max-width: 98vw; padding: 18px 4vw 18px 4vw; } }
    </style>
</head>
<body>
    <nav class="sidebar">
        <h2>Dashboard</h2>
        <a href="index.php">Student List</a>
        <a href="subject.php">Subject List</a>
    </nav>
    <div class="dashboard-content">
        <div class="container">
            <h1>Enter Grades</h1>
            <?= $msg ?>
            <div class="student-info">
                <div><strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></strong></div>
                <div><?= htmlspecialchars($student['email']) ?></div>
            </div>
            <?php if (empty($subjects)): ?>
                <div style="text-align:center; color:#888; margin:32px 0;">This student is not enrolled in any subjects.</div>
            <?php else: ?>
            <form method="post" action="grade.php?student_id=<?= urlencode($student_id) ?>">
                <table class="grade-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade (0-100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                            <td>
                                <input type="number" name="grades[<?= htmlspecialchars($subject['subject_id']) ?>]" min="0" max="100" step="1" pattern="\\d*" inputmode="numeric" value="<?= isset($grades[$subject['subject_id']]) ? htmlspecialchars($grades[$subject['subject_id']]) : '' ?>" style="width:90px; padding:6px; border-radius:4px; border:1px solid #ccc;">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="save-btn">Save Grades</button>
            </form>
            <?php endif; ?>
            <a href="index.php">Back to Student List</a>
        </div>
    </div>
</body>
</html> 