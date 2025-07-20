<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../classes/grade_manager.php';
require_once __DIR__ . '/../classes/student_record.php';
require_once __DIR__ . '/../classes/exception/invalid_parameter_exception.php';
require_once __DIR__ . '/../classes/exception/student_not_found_exception.php';

class GradeManagerTest extends TestCase
{
    public function testAddAndFindStudentRecord()
    {
        $manager = new grade_manager();
        $student = new student_record(1, 'John', 'Doe', 'john@example.com');
        $this->assertTrue($manager->add_student_record($student));
        $found = $manager->find_student_by_id(1);
        $this->assertEquals($student, $found);
    }

    public function testAddDuplicateStudentReturnsFalse()
    {
        $manager = new grade_manager();
        $student1 = new student_record(1, 'John', 'Doe', 'john@example.com');
        $student2 = new student_record(1, 'Jane', 'Smith', 'jane@example.com');
        $manager->add_student_record($student1);
        $this->assertFalse($manager->add_student_record($student2));
    }

    public function testFindStudentByIdThrowsOnInvalidId()
    {
        $this->expectException(invalid_parameter_exception::class);
        $manager = new grade_manager();
        $manager->find_student_by_id('abc');
    }

    public function testFindStudentByIdThrowsOnNotFound()
    {
        $this->expectException(student_not_found_exception::class);
        $manager = new grade_manager();
        $manager->find_student_by_id(999);
    }

    public function testGetAllStudentsAndCount()
    {
        $manager = new grade_manager();
        $student1 = new student_record(1, 'John', 'Doe', 'john@example.com');
        $student2 = new student_record(2, 'Jane', 'Smith', 'jane@example.com');
        $manager->add_student_record($student1);
        $manager->add_student_record($student2);
        $this->assertCount(2, $manager->get_all_students());
        $this->assertEquals(2, $manager->count_students());
    }

    public function testRemoveStudent()
    {
        $manager = new grade_manager();
        $student = new student_record(1, 'John', 'Doe', 'john@example.com');
        $manager->add_student_record($student);
        $this->assertTrue($manager->remove_student(1));
        $this->assertCount(0, $manager->get_all_students());
    }

    public function testRemoveStudentThrowsOnInvalidId()
    {
        $this->expectException(invalid_parameter_exception::class);
        $manager = new grade_manager();
        $manager->remove_student('abc');
    }

    public function testRemoveStudentThrowsOnNotFound()
    {
        $this->expectException(student_not_found_exception::class);
        $manager = new grade_manager();
        $manager->remove_student(999);
    }

    public function testGetClassAverageAndTopStudents()
    {
        $manager = new grade_manager();
        $student1 = new student_record(1, 'John', 'Doe', 'john@example.com', ['Math' => 90, 'Science' => 80]);
        $student2 = new student_record(2, 'Jane', 'Smith', 'jane@example.com', ['Math' => 100, 'Science' => 90]);
        $manager->add_student_record($student1);
        $manager->add_student_record($student2);
        $this->assertEquals(90, $manager->get_class_average());
        $top = $manager->get_top_students(1);
        $this->assertCount(1, $top);
        $this->assertEquals($student2, $top[0]);
    }
} 