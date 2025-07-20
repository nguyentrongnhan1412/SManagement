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

// Get subject_id from query string
$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : null;
if (!$subject_id) {
    die('No subject selected.');
}

// Fetch subject name
$stmt = $pdo->prepare('SELECT subject_name FROM subjects WHERE subject_id = ?');
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();
if (!$subject) {
    die('Subject not found.');
}
$subject_name = $subject['subject_name'];

// Handle enroll form submission
$enroll_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['student_id']) && isset($_POST['enroll'])) {
        $student_id = $_POST['student_id'];
        // Check if already enrolled
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND subject_id = ?');
        $stmt->execute([$student_id, $subject_id]);
        if ($stmt->fetchColumn() > 0) {
            $enroll_msg = '<div style="color:#e53935; text-align:center; margin-bottom:12px;">Student is already enrolled in this subject.</div>';
        } else {
            $stmt = $pdo->prepare('INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)');
            if ($stmt->execute([$student_id, $subject_id])) {
                header('Location: enrollment.php?subject_id=' . urlencode($subject_id));
                exit;
            } else {
                $enroll_msg = '<div style="color:#e53935; text-align:center; margin-bottom:12px;">Failed to enroll student.</div>';
            }
        }
    } elseif (isset($_POST['remove_student_id']) && isset($_POST['remove'])) {
        $remove_student_id = $_POST['remove_student_id'];
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE student_id = ? AND subject_id = ?');
        if ($stmt->execute([$remove_student_id, $subject_id])) {
            $enroll_msg = '<div style="color:#1ca21c; text-align:center; margin-bottom:12px;">Student removed from enrollment.</div>';
            header('Location: enrollment.php?subject_id=' . urlencode($subject_id));
            exit;
        } else {
            $enroll_msg = '<div style="color:#e53935; text-align:center; margin-bottom:12px;">Failed to remove student.</div>';
        }
    }
}

// Fetch enrolled students
$stmt = $pdo->prepare('
    SELECT s.student_id, s.first_name, s.last_name, s.email
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    WHERE e.subject_id = ?
    ORDER BY s.last_name, s.first_name
');
$stmt->execute([$subject_id]);
$students = $stmt->fetchAll();

// Fetch students not enrolled in this subject
$stmt = $pdo->prepare('
    SELECT student_id, first_name, last_name, email
    FROM students
    WHERE student_id NOT IN (
        SELECT student_id FROM enrollments WHERE subject_id = ?
    )
    ORDER BY last_name, first_name
');
$stmt->execute([$subject_id]);
$not_enrolled_students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrolled Students - <?= htmlspecialchars($subject_name) ?></title>
    <link rel="stylesheet" href="../asset/styles.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
        }
        .sidebar {
            width: 220px;
            background: #222d32;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 32px 0 0 0;
            min-height: 100vh;
            box-shadow: 2px 0 8px rgba(0,0,0,0.04);
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 32px;
            font-size: 1.4rem;
            letter-spacing: 1px;
            color: #fff;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 16px 32px;
            font-size: 1.08rem;
            transition: background 0.2s;
            border-left: 4px solid transparent;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #1a2226;
            border-left: 4px solid #007bff;
        }
        .dashboard-content {
            flex: 1;
            padding: 0;
            background: #f4f6f8;
        }
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px 24px 24px 24px; }
        .student-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; background: #fff; font-size: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border-radius: 8px; overflow: hidden; }
        .student-table th, .student-table td { padding: 12px 10px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        .student-table th { background: #f4f6f8; color: #333; font-weight: 600; }
        .student-table tbody tr:nth-child(even) { background: #f8fafc; }
        .student-table tbody tr:hover { background: #e6f0ff; }
        .enroll-form { background: #f8fafc; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 18px 16px; margin-bottom: 18px; }
        .enroll-form label { display: block; margin-bottom: 6px; color: #555; font-weight: 500; }
        .enroll-form select { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; }
        .enroll-form button { width: 100%; padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: 600; cursor: pointer; }
        .enroll-form button:hover { background: #0056b3; }
        .remove-form { display: inline; }
        .remove-btn { background: #e53935; color: #fff; border: none; border-radius: 4px; padding: 6px 14px; font-size: 0.98rem; cursor: pointer; margin-left: 8px; transition: background 0.2s; }
        .remove-btn:hover { background: #b71c1c; }
        @media (max-width: 900px) {
            .container { max-width: 98vw; }
            .sidebar { width: 140px; font-size: 0.95rem; }
        }
        @media (max-width: 700px) {
            body { flex-direction: column; }
            .sidebar { flex-direction: row; width: 100vw; height: 60px; min-height: unset; padding: 0; }
            .sidebar h2 { display: none; }
            .sidebar a { flex: 1; text-align: center; padding: 18px 0; font-size: 1rem; border-left: none; border-bottom: 4px solid transparent; }
            .sidebar a.active, .sidebar a:hover { background: #1a2226; border-left: none; border-bottom: 4px solid #007bff; }
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <h2>Dashboard</h2>
        <a href="index.php">Student List</a>
        <a href="subject.php" class="active">Subject List</a>
    </nav>
    <div class="dashboard-content">
        <div class="container">
            <h1>Enrolled Students</h1>
            <h2 style="margin-top:0; color:#1976d2;">Subject: <?= htmlspecialchars($subject_name) ?></h2>
            <?= $enroll_msg ?>
            <form class="enroll-form" method="post" action="enrollment.php?subject_id=<?= urlencode($subject_id) ?>">
                <label for="student_id">Enroll a Student</label>
                <select id="student_id" name="student_id" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($not_enrolled_students as $student): ?>
                        <option value="<?= htmlspecialchars($student['student_id']) ?>">
                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['email'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="enroll" <?= empty($not_enrolled_students) ? 'disabled' : '' ?>>Enroll Student</button>
            </form>
            <?php if (empty($students)): ?>
                <div style="text-align:center; color:#888; margin:32px 0;">No students are enrolled in this subject.</div>
            <?php else: ?>
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td>
                                    <form class="remove-form" method="post" action="enrollment.php?subject_id=<?= urlencode($subject_id) ?>" onsubmit="return confirm('Remove this student from the subject?');">
                                        <input type="hidden" name="remove_student_id" value="<?= htmlspecialchars($student['student_id']) ?>">
                                        <button type="submit" name="remove" class="remove-btn">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <a href="subject.php">Back to Subject List</a>
        </div>
    </div>
</body>
</html> 