<?php

class course{

    public $course;
    public $school;
    public $title;
    public $description;
    public $examDate;
    public $examStart;
    public $examEnd;


    public function __construct($course, $school, $title, $description, $examDate, $examStart, $examEnd){
        $this->course = $course;
        $this->school = $school;
        $this->title = $title;
        $this->description = $description;
        $this->examDate = $examDate;
        $this->examStart = $examStart;
        $this->examEnd = $examEnd;
    }

    public function getCourse() {
        return $this->course;
    }

    public function getSchool() {
        return $this->school;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getExamDate() {
        return $this->examDate;
    }

    public function getExamStart() {
        return $this->examStart;
    }
    
    public function getExamEnd() {
        return $this->examEnd;
    }

}

?>