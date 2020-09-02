<?php

class section{

    public $course;
    public $section;
    public $day;
    public $start;
    public $end;
    public $instructor;
    public $venue;
    public $size;


    public function __construct($course, $section, $day, $start, $end, $instructor, $venue, $size){
        $this->course = $course;
        $this->section = $section;
        $this->day = $day;
        $this->start = $start;
        $this->end = $end;
        $this->instructor = $instructor;
        $this->venue = $venue;
        $this->size = $size;
    }

    public function getCourse() {
        return $this->course;
    }

    public function getSection() {
        return $this->section;
    }

    public function getDay() {
        return $this->day;
    }

    public function getStart() {
        return $this->start;
    }

    public function getEnd() {
        return $this->end;
    }

    public function getInstructor() {
        return $this->instructor;
    }
    
    public function getVenue() {
        return $this->venue;
    }

    public function getSize() {
        return $this->size;
    }

}

?>