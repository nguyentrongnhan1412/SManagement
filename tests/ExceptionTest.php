<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../classes/exception/moodle_exception.php';
require_once __DIR__ . '/../classes/exception/invalid_parameter_exception.php';
require_once __DIR__ . '/../classes/exception/student_not_found_exception.php';
require_once __DIR__ . '/../classes/exception/invalid_grade_exception.php';

class ExceptionTest extends TestCase
{
    public function testMoodleException()
    {
        $e = new moodle_exception('Test error', 'testcode');
        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals('Test error', $e->getMessage());
        $this->assertEquals('testcode', (new ReflectionClass($e))->getProperty('errorcode')->getValue($e));
    }

    public function testInvalidParameterException()
    {
        $e = new invalid_parameter_exception('param', 'debug info');
        $this->assertStringContainsString('Invalid parameter: param.', $e->getMessage());
        $this->assertStringContainsString('Debug: debug info', $e->getMessage());
        $this->assertEquals('invalidparameter', (new ReflectionClass($e))->getProperty('errorcode')->getValue($e));
    }

    public function testStudentNotFoundException()
    {
        $e = new student_not_found_exception(123, 'debug info');
        $this->assertStringContainsString('The requested student (ID: 123) was not found.', $e->getMessage());
        $this->assertStringContainsString('Debug: debug info', $e->getMessage());
        $this->assertEquals('studentnotfound', (new ReflectionClass($e))->getProperty('errorcode')->getValue($e));
    }

    public function testInvalidGradeException()
    {
        $e = new invalid_grade_exception(150, 'debug info');
        $this->assertStringContainsString('Invalid grade: 150. Grade must be between 0 and 100.', $e->getMessage());
        $this->assertStringContainsString('Debug: debug info', $e->getMessage());
        $this->assertEquals('invalidgrade', (new ReflectionClass($e))->getProperty('errorcode')->getValue($e));
    }
} 