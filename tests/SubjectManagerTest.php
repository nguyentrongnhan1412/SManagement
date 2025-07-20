<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../classes/subject_manager.php';
require_once __DIR__ . '/../classes/exception/invalid_parameter_exception.php';

class SubjectManagerTest extends TestCase
{
    public function testAddAndListSubjects()
    {
        $manager = new subject_manager();
        $this->assertTrue($manager->add_subject('MATH101', 'Mathematics'));
        $this->assertTrue($manager->add_subject('ENG202', 'English Literature'));
        $subjects = $manager->list_subjects();
        $this->assertCount(2, $subjects);
        $this->assertEquals('MATH101', $subjects[0]['subject_id']);
        $this->assertEquals('Mathematics', $subjects[0]['subject_name']);
        $this->assertEquals('ENG202', $subjects[1]['subject_id']);
        $this->assertEquals('English Literature', $subjects[1]['subject_name']);
    }

    public function testAddDuplicateSubjectReturnsFalse()
    {
        $manager = new subject_manager();
        $this->assertTrue($manager->add_subject('SCI101', 'Science'));
        $this->assertFalse($manager->add_subject('SCI101', 'Science'));
    }

    public function testRemoveSubject()
    {
        $manager = new subject_manager();
        $manager->add_subject('HIST101', 'History');
        $this->assertTrue($manager->remove_subject('HIST101'));
        $this->assertCount(0, $manager->list_subjects());
    }

    public function testRemoveNonexistentSubjectReturnsFalse()
    {
        $manager = new subject_manager();
        $this->assertFalse($manager->remove_subject('NONEXIST'));
    }

    public function testAddSubjectThrowsOnInvalidId()
    {
        $this->expectException(invalid_parameter_exception::class);
        $manager = new subject_manager();
        $manager->add_subject('!', 'Invalid');
    }

    public function testAddSubjectThrowsOnInvalidName()
    {
        $this->expectException(invalid_parameter_exception::class);
        $manager = new subject_manager();
        $manager->add_subject('BIO101', '');
    }
} 