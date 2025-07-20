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

// Handle add subject form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_name'])) {
    $subject_name = trim($_POST['subject_name']);
    if ($subject_name) {
        // Check if subject_name already exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM subjects WHERE subject_name = ?');
        $stmt->execute([$subject_name]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Subject name already exists.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO subjects (subject_name) VALUES (?)');
            if ($stmt->execute([$subject_name])) {
                header('Location: subject.php');
                exit;
            } else {
                $error = 'Failed to add subject.';
            }
        }
    } else {
        $error = 'Please enter a subject name.';
    }
}

// Fetch all subjects
$stmt = $pdo->query('SELECT subject_id, subject_name FROM subjects ORDER BY subject_id');
$subjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subject List</title>
    <link rel="stylesheet" href="asset/styles.css">
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
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px 24px 24px 24px; }
        .subject-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; background: #fff; font-size: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border-radius: 8px; overflow: hidden; }
        .subject-table th, .subject-table td { padding: 12px 10px; border-bottom: 1px solid #e0e0e0; }
        .subject-table th { background: #f4f6f8; color: #333; font-weight: 600; text-align: center; }
        .subject-table td:first-child { text-align: center; width: 120px; }
        .subject-table td:last-child { text-align: left; }
        .subject-table tbody tr:nth-child(even) { background: #f8fafc; }
        .subject-table tbody tr:hover { background: #e6f0ff; }
        .add-form { background: #f8fafc; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 18px 16px; margin-bottom: 18px; }
        .add-form label { display: block; margin-bottom: 6px; color: #555; font-weight: 500; }
        .add-form input { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; }
        .add-form button { width: 100%; padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: 600; cursor: pointer; }
        .add-form button:hover { background: #0056b3; }
        .error-msg { color: #e53935; margin-bottom: 12px; text-align: center; }
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
            <h1>Subject List</h1>
            <form class="add-form" method="post" action="subject.php">
                <h2 style="margin-top:0;">Add Subject</h2>
                <?php if ($error): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <label for="subject_name">Subject Name</label>
                <input type="text" id="subject_name" name="subject_name" maxlength="100" required>
                <button type="submit">Add Subject</button>
            </form>
            <table class="subject-table">
                <thead>
                    <tr>
                        <th>Subject ID</th>
                        <th>Subject Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subjects)): ?>
                        <tr><td colspan="2" style="text-align:center;">No subjects found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr style="cursor:pointer;" onclick="window.location='enrollment.php?subject_id=<?= urlencode($subject['subject_id']) ?>'">
                                <td><?= htmlspecialchars($subject['subject_id']) ?></td>
                                <td><a href="enrollment.php?subject_id=<?= urlencode($subject['subject_id']) ?>" style="color:inherit;text-decoration:underline;display:block;">
                                    <?= htmlspecialchars($subject['subject_name']) ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="index.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 