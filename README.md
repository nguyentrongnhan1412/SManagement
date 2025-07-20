# Student Management System
This project serves as a beginner's result for PHP study

## Testing
- PHPUnit tests are located in the [`tests/`](./tests) directory.
- To run tests:
  ```bash
  ./vendor/bin/phpunit
  ```

## Project Structure
- `index.php` - Dashboard and student list
- `student.php` - Student CRUD logic
- `subject.php` - Subject management
- `enrollment.php` - Enroll/remove students in subjects
- `grade.php` - Enter/update grades
- `import_students.php` - Import students from CSV
- `add_student.php` - Add student form
- `classes/` - Core PHP classes and exceptions
- `utils/` - Utility scripts (file handling, validation, etc.)
- `asset/` - Static assets (CSS, sample CSV)
- `config/` - Database configuration
- `init.sql` - Database schema

## License
This project is licensed under the [GNU GPL v3](https://www.gnu.org/licenses/gpl-3.0.html). 