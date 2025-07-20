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

require_once __DIR__ . '/student.php';
require_once __DIR__ . '/utils/utilities.php';

$pdo = require __DIR__ . '/config/database.php';
$students = getAllStudentRecords($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Student Management</title>
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
        .container {
            margin: 40px auto;
            max-width: 900px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 24px 24px 24px;
        }
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
        .student-table th.sorted-asc::after {
            content: " \25B2";
            font-size: 0.9em;
            color: #1976d2;
        }
        .student-table th.sorted-desc::after {
            content: " \25BC";
            font-size: 0.9em;
            color: #1976d2;
        }
        .no-results-row {
            text-align: center;
            color: #888;
            font-style: italic;
        }
        .student-table th, .student-table td { padding: 12px 10px; border-bottom: 1px solid #e0e0e0; }
        .student-table th { background: #f4f6f8; color: #333; font-weight: 600; text-align: center; }
        .student-table th:nth-child(2) { text-align: left; }
        .student-table td:first-child { text-align: center; width: 60px; }
        .student-table td:nth-child(2) { text-align: left; }
        .student-table td:nth-child(3) { text-align: left; }
        .student-table td:nth-child(4) { text-align: center; width: 120px; }
        .student-table td:nth-child(5) { text-align: center; width: 100px; }
        .student-table td:last-child { text-align: left; }
    </style>
</head>
<body>
    <nav class="sidebar">
        <h2>Dashboard</h2>
        <a href="index.php" class="active">Student List</a>
        <a href="subject.php">Subject List</a>
    </nav>
    <div class="dashboard-content">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 12px; flex-wrap: wrap;">
                <h1 style="margin-bottom: 0;">Student List</h1>
                <div style="display: flex; gap: 8px;">
                    <a class="add-link" href="add_student.php" style="margin: 0;">Add Student</a>
                    <a class="add-link" href="import_students.php" style="margin: 0;">Import Students</a>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap;">
                <div style="position: relative; width: 100%; max-width: 340px;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 1.1rem; pointer-events: none;">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="9" r="7" stroke="#aaa" stroke-width="2"/><line x1="14.4142" y1="14" x2="18" y2="17.5858" stroke="#aaa" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <input type="text" class="student-search" data-target-table="#student-table" placeholder="Search students..." style="padding: 10px 14px 10px 40px; border-radius: 24px; border: 1px solid #ccc; box-shadow: 0 2px 8px rgba(0,0,0,0.04); font-size: 1rem; width: 100%; outline: none; transition: border 0.2s, box-shadow 0.2s; background: #f8fafc;">
                </div>
            </div>
            <table class="student-table" id="student-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th class="sortable">Average Grade</th>
                    <th>Letter Grade</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($students)): ?>
                    <tr class="no-results-row"><td colspan="6">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr style="cursor:pointer;" onclick="window.location='grade.php?student_id=<?= urlencode($student->id) ?>'">
                            <td><?= htmlspecialchars($student->id) ?></td>
                            <td><?= htmlspecialchars($student->get_fullname()) ?></td>
                            <td><?= htmlspecialchars($student->email) ?></td>
                            <td><?= utilities::format_grade_display($student->get_grade_average()) ?></td>
                            <td class="grade-<?= htmlspecialchars($student->get_letter_grade()) ?>"><?= htmlspecialchars($student->get_letter_grade()) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="no-results-row" style="display:none;"><td colspan="6">No students match your search.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="utils/grade_manager.js"></script>
    <script>
    // Show/hide no-results row for filtering
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('student-search')) {
            var table = document.querySelector(e.target.dataset.targetTable);
            if (!table) return;
            var rows = Array.from(table.querySelectorAll('tbody tr:not(.no-results-row)'));
            var visibleRows = rows.filter(row => row.style.display !== 'none');
            var noResultsRow = table.querySelector('tr.no-results-row');
            if (noResultsRow) {
                noResultsRow.style.display = (visibleRows.length === 0) ? '' : 'none';
            }
        }
    });
    // Move sort icon to Average Grade by default
    document.addEventListener('DOMContentLoaded', function () {
        var table = document.getElementById('student-table');
        if (table) {
            var ths = table.querySelectorAll('th');
            ths.forEach((th, idx) => th.classList.remove('sorted-asc', 'sorted-desc'));
            // No sort icon on load, table is by default sorted by ID ascending (first column)
        }
    });
    </script>
</body>
</html>
