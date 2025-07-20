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
require_once __DIR__ . '/utils/utilities.php';
// add_student.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link rel="stylesheet" href="asset/styles.css">
</head>
<body>
    <div class="container">
    <h1><?= utilities::get_string('add', 'core') ?> <?= utilities::get_string('student', 'core') ?></h1>
    <form action="student.php" method="post">
        <label for="first_name"><?= utilities::get_string('firstname', 'core') ?>:</label>
        <input type="text" id="first_name" name="first_name" required>
        <label for="last_name"><?= utilities::get_string('lastname', 'core') ?>:</label>
        <input type="text" id="last_name" name="last_name" required>
        <label for="email"><?= utilities::get_string('email', 'core') ?>:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit"><?= utilities::get_string('add', 'core') ?> <?= utilities::get_string('student', 'core') ?></button>
    </form>
    <a href="index.php"><?= utilities::get_string('back', 'core') ?> <?= utilities::get_string('to', 'core') ?> <?= utilities::get_string('student', 'core') ?> <?= utilities::get_string('list', 'core') ?></a>
    </div>
</body>
</html> 