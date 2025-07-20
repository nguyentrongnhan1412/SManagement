<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../classes/student_record.php';
require_once __DIR__ . '/../classes/exception/invalid_grade_exception.php';

class StudentRecordTest extends TestCase
{
    public function testStudentCreation()
    {
        $student = new student_record(1, 'John', 'Doe', 'john@example.com');
        $this->assertEquals(1, $student->id);
        $this->assertEquals('John', $student->firstname);
        $this->assertEquals('Doe', $student->lastname);
        $this->assertEquals('john@example.com', $student->email);
        $this->assertEquals([], $student->grades);
    }

    public function testAddGradeEntryAndAverage()
    {
        $student = new student_record(2, 'Jane', 'Smith', 'jane@example.com');
        $student->add_grade_entry('Math', 95);
        $student->add_grade_entry('Science', 85);
        $this->assertEquals(['Math' => 95, 'Science' => 85], $student->grades);
        $this->assertEquals(90, $student->get_grade_average());
    }

    public function testGetLetterGrade()
    {
        $student = new student_record(3, 'Alice', 'Brown', 'alice@example.com', ['Math' => 92, 'Science' => 88]);
        $this->assertEquals('A', $student->get_letter_grade());
        $student = new student_record(4, 'Bob', 'White', 'bob@example.com', ['Math' => 82, 'Science' => 85]);
        $this->assertEquals('B', $student->get_letter_grade());
        $student = new student_record(5, 'Charlie', 'Green', 'charlie@example.com', ['Math' => 72, 'Science' => 75]);
        $this->assertEquals('C', $student->get_letter_grade());
        $student = new student_record(6, 'David', 'Black', 'david@example.com', ['Math' => 62, 'Science' => 65]);
        $this->assertEquals('D', $student->get_letter_grade());
        $student = new student_record(7, 'Eve', 'Gray', 'eve@example.com', ['Math' => 52, 'Science' => 55]);
        $this->assertEquals('F', $student->get_letter_grade());
    }

    public function testGetFullname()
    {
        $student = new student_record(8, 'Tom', 'Hanks', 'tom@example.com');
        $this->assertEquals('Tom Hanks', $student->get_fullname());
    }

    public function testGetStudentInfo()
    {
        $student = new student_record(9, 'Sam', 'Wilson', 'sam@example.com', ['Math' => 80, 'Science' => 90]);
        $info = $student->get_student_info();
        $this->assertStringContainsString('ID: 9', $info);
        $this->assertStringContainsString('Name: Sam Wilson', $info);
        $this->assertStringContainsString('Email: sam@example.com', $info);
        $this->assertStringContainsString('Average Grade: 85.00 (B)', $info);
        $this->assertStringContainsString('Math: 80', $info);
        $this->assertStringContainsString('Science: 90', $info);
    }
} 