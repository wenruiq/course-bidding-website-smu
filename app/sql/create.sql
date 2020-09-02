--
-- Create Database "BIOS"
--
drop schema if exists BIOS;
create schema BIOS;
use BIOS;

create table course (
	course varchar(100) not null primary key,
	school varchar(100) not null,
	title varchar(100) not null, #title must not exceed 100 characters
	description varchar(1000) not null, #description must not exceed 1000 characters
	examdate date not null,
	examstart time not null,
	examend time not null
);

create table section (
	course varchar(100) not null,
    section varchar(3) not null, #section must not exceed 3 characters (S99 is the highest)
    day tinyint not null CHECK (day BETWEEN 1 AND 7), #day ranges from 1-7
    start time not null,
    end time not null,
    instructor varchar(100), #instructor must not exceed 100 characters
    venue varchar(100), #venue must not exceed 100 characters
    size int not null,
    constraint section_pk primary key(course, section)
);

create table student (
	userid varchar(128) not null primary key, #username must not exceed 128 characters
    password varchar(128) not null, #password must not exceed 128 characters
    name varchar(100) not null, #name must not exceed 100 characters
    school varchar(1000) not null,
    edollar decimal(5,2) not null #assume edollar only goes from 0-999 with a maximum of 2 decimal places
);

create table prerequisite (
	course varchar(100) not null,
    prerequisite varchar(100) not null,
    constraint prerequisite_pk primary key(course, prerequisite)
);

create table course_completed (
	userid varchar(128) not null,
    code varchar(100) not null,
    constraint course_completed_pk primary key(userid, code)
);

create table bid (
	userid varchar(128) not null,
    amount decimal(5,2) not null, #bid must be a positive number with not more than 2 decimal places
    code varchar(100) not null,
    section varchar(3) not null, #section must not exceed 3 characters (S99 is the highest)
    constraint bid_pk primary key(userid, code, section)
);

create table admin (
	userid varchar(128) not null primary key,
    password varchar(128) not null
);

create table round (
    roundnum varchar(128) not null primary key,
    status varchar(128) not null
);

CREATE TABLE bids_rejected (
	userid VARCHAR(128) NOT NULL ,
	amount DECIMAL(5,2) NOT NULL ,
	code VARCHAR(100) NOT NULL ,
	section VARCHAR(3) NOT NULL
);

CREATE TABLE bids_rejected_2 (
	userid VARCHAR(128) NOT NULL ,
	amount DECIMAL(5,2) NOT NULL ,
	code VARCHAR(100) NOT NULL ,
	section VARCHAR(3) NOT NULL
);

CREATE TABLE successful_bids (
	userid VARCHAR(128) NOT NULL ,
	amount DECIMAL(5,2) NOT NULL ,
	code VARCHAR(100) NOT NULL ,
	section VARCHAR(3) NOT NULL
);

CREATE TABLE successful_bids_2 (
	userid VARCHAR(128) NOT NULL ,
	amount DECIMAL(5,2) NOT NULL ,
	code VARCHAR(100) NOT NULL ,
	section VARCHAR(3) NOT NULL
);

CREATE TABLE minimum_bid_value (
	code VARCHAR(100) NOT NULL ,
    section VARCHAR(3) NOT NULL,
    amount DECIMAL(5,2) NOT NULL
);

