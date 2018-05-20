<?php
/* CSF Registration - Handles the registration process for CSF.
Part of the CSF Check-in service (dependency).
Copyright (C) 2017-2018 Ryan Keegan
	
This program is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the
Free Software Foundation; either version 3, or (at your option) any
later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; see the file LICENSE.  If not see
<http://www.gnu.org/licenses/>.  */

    //Enter Aeries Parent Portal credentials and determine eligibility
    include_once("../../admin/database_registration.php");

    if(!isset($_COOKIE['csf_registration_details']) || !admissions_open($databaseConnect)) {
        header("Location: ./index.php");
    }

    if(already_submitted($databaseConnect)) {
    	header("Location: ./success.php");
    }

    if(isset($_POST['submit'])) {
        //Pass parent portal username, password, and the applicant's name/grade level into custom function; returns 2D array with class ids (first index) and grade as number 4-0 (A 3, B 1, C 0, D -1, F -1) (second index)

        $studentInfo = (array)json_decode($_COOKIE['csf_registration_details']);
        $studentName = $studentInfo['studentName'];
	$studentGrade = $studentInfo['studentGradeLevel'];
	$useOldGrades = mysqli_fetch_array(mysqli_query($databaseConnect, "SELECT value FROM settings WHERE setting_id = 'use_old_grades'"))[0];  //Determines whether or not grades from previous grade level can be used
	$coursesJSON = shell_exec("python scraper.py \"" . $_POST['username'] . "\" \"" . $_POST['password'] . "\" \"" . $studentName . "\" \"" . $studentGrade . "\" \"". $useOldGrades . "\"");
        $courses = json_decode(substr($coursesJSON, 0, strrpos($coursesJSON, "*")), true);      //Seperate JSON from student id
        $GLOBALS['returnedStudentID'] = substr($coursesJSON, strrpos($coursesJSON, "*")+1, 9);  //Seperate student id from JSON
        $GLOBALS['duplicateCourses'] = json_decode(substr($coursesJSON, strrpos($coursesJSON, "^")+1), true);

        $GLOBALS['coursesUsed'] = array(); //Stores courses used in meeting eligibility requirements (contains five courses)
        $GLOBALS['coursesExtraMax'] = 2;   //Max number of courses you can get honours/AP credit for
	$GLOBALS['coursesExtraUsed'] = 0;  //Number of courses that have recieved extra credit
        $GLOBALS['maxCoursesUsed'] = 5;    //Maxed courses that can be used when determining eligibility
        $GLOBALS['pointsIRequired'] = 4;   //Points required from list 1
        $GLOBALS['pointsIIRequired'] = 7;  //Points required from list 2
        $GLOBALS['pointsRequired'] = 10;   //Points required from list 3

        //Verifies that the grades returned for a given student matches the applicant's student id
        function verify_identity() {
            $studentInfo = (array)json_decode($_COOKIE['csf_registration_details']);
            if($GLOBALS['returnedStudentID'] == $studentInfo['studentID']) {
              return true;
            } else {
              return false;
            }
        }

        //Alogrithim

        //Filter out PE and other inelegible courses from array
        function filter_courses($courseList = array(), $databaseConnect) {
            for($i=0; $i<sizeof($courseList); $i++) {
                $courseID = $courseList[$i]['course_id'];
                if(in_array($courseID, $GLOBALS['duplicateCourses']) || mysqli_num_rows(mysqli_query($databaseConnect, "SELECT course_id FROM courses WHERE course_id = '$courseID'")) == 0) {
                    //If course is not in courses table then it's not in any of the lists so remove it
                    array_splice($courseList, $i, 1);
                    $i--;
                }
            }

            return $courseList;
        }

        //Account for honours/AP credit
        function grade_value(&$course, $databaseConnect) {
            $courseID = $course['course_id'];
            $query = mysqli_query($databaseConnect, "SELECT honours FROM courses WHERE course_id='$courseID'");
	    
            //Don't add extra point if course is D/F, already have for 2 courses, or if it's not H/AP
            if(mysqli_fetch_row($query)[0] == 1 && $GLOBALS['coursesExtraUsed'] < $GLOBALS['coursesExtraMax'] && $course['grade'] > -1) {
                $GLOBALS['coursesExtraUsed']++;
		$course['grade']++;
                return $course['grade'];
            } else {
	        return $course['grade'];
	    }
        }

        //Check list and point requirements
        function verify_grades($courseList = array(), $databaseConnect) {
            //Precondition: None of the grades are a D/F (-1 points)
            foreach($courseList as $course) {
                if($course['grade'] == -1) {
                    return false;
                }
            }

            $courseListI = array();
            $courseListII = array();
            $courseListIII = array();

            foreach($courseList as $course) {
                //Order by list
                $courseID = $course['course_id'];
                $listType = mysqli_fetch_row(mysqli_query($databaseConnect, "SELECT list FROM courses WHERE course_id='$courseID'")); //Query DB for list type
                switch($listType[0]) {
                    case 1:
                        array_push($courseListI, $course);
                        break;
                    case 2:
                        array_push($courseListII, $course);
                        break;
                    case 3:
                        array_push($courseListIII, $course);
                        break;
                }
            }

            //Order each list by highest grade
            usort($courseListI, function($a, $b) {
                return $b['grade'] - $a['grade'];
            });

            usort($courseListII, function($a, $b) {
                return $b['grade'] - $a['grade'];
            });

            usort($courseListIII, function($a, $b) {
                return $b['grade'] - $a['grade'];
            });

            $coursePointsI = 0;   //Number of points from courses in list 1
            $coursePointsII = 0;  //Number of points from courses in list 2
            $coursePoints = 0;    //Number of points from courses in list 3

            //For list 1
            $list1ID = 0;
            while(($coursePointsI < $GLOBALS['pointsIRequired'] || empty($courseListII) || $courseListI[$list1ID]['grade'] >= $courseListII[0]['grade']) && ($list1ID < count($courseListI) && $list1ID < $GLOBALS['maxCoursesUsed'])) {
                //Get point value for course
                $coursePointsI += grade_value($courseListI[$list1ID], $databaseConnect);
                array_push($GLOBALS['coursesUsed'], $courseListI[$list1ID]);
                $list1ID++;
                if($list1ID >= count($courseListI)) {
                    break;
                }
            }

            //For list 2
            $list2ID = 0;
            while(($coursePointsII < $GLOBALS['pointsIIRequired'] || empty($courseListIII) || $courseListII[$list2ID]['grade'] >= $courseListIII[0]['grade']) && ($list2ID < count($courseListII) && ($list2ID + $list1ID) < $GLOBALS['maxCoursesUsed'])) {
                //Get point value for course
                $coursePointsII += grade_value($courseListII[$list2ID], $databaseConnect);
                array_push($GLOBALS['coursesUsed'], $courseListII[$list2ID]);
                $list2ID++;
                if($list2ID >= count($courseListII)) {
                    break;
                }
            }

            $coursePoints = $coursePointsI + $coursePointsII;

            //For list 3
            $list3ID = 0;
            while($coursePoints < $GLOBALS['pointsRequired'] && ($list3ID < count($courseListIII) && ($list3ID + $list2ID + $list1ID) < $GLOBALS['maxCoursesUsed'])) {
                //Get point value for course
		$coursePoints += grade_value($courseListIII[$list3ID], $databaseConnect); 
                array_push($GLOBALS['coursesUsed'], $courseListIII[$list3ID]);
                $list3ID++;
            }

            if($coursePoints >= $GLOBALS['pointsRequired'] && $coursePointsI >= $GLOBALS['pointsIRequired'] && ($coursePointsI + $coursePointsII) >= $GLOBALS['pointsIIRequired']) {
                return true;
            } else {
                return false;
            }
        }

        //No more than 5 grades
        //First step: order courses by grade value (account for honours which will require querying courses table)
        //Second step: Get max number of points from list 1 while meeting 4 point requirement, continue to list 2 if the highest point value is met, etc.
	if(isset($coursesJSON) && strpos($coursesJSON, "Error: Grades mismatched") !== false) {
	    echo "<div style='color: red'>The last 8 grades returned do not match your submitted grade level. Ensure that both the <a href='example_limit.png' target='_blank'>'Limit' field</a> on the transcripts page of Parent Portal includes your current grade level and the <a href='example_sort.png' target='_blank'>'Sort by Date Descending'</a> box is checked. If you had a free period last term (or received less than eight grades for whatever reason) you must visit room 803 for manual application review.</div>";
	} else if(isset($coursesJSON) && strpos($coursesJSON, "Error: Incorrect grade limit") !== false) {
	    echo "<div style='color: red'>The 'Limit' field on the transcripts page does not span from 9th grade to your current grade level.</div>";
	} else if(empty($courses)) {
            echo "<div style='color: red'>Aeries didn't return anything. Your username and/or password are likely wrong.</div>";
	} else if(!verify_identity()) {
            echo "<div style='color: red'>Aeries returned a student ID of " . $GLOBALS['returnedStudentID'] . " which doesn't match what you provided. Ensure the student ID and first name you provided for your application match what is on your Aeries account.</div>";
        } else {
            $courses = filter_courses($courses, $databaseConnect); //Filter out non-qualifying courses

            if(verify_identity() && verify_grades($courses, $databaseConnect) && admissions_open($databaseConnect)) {
                if(!already_submitted($databaseConnect)) {
                    $gradeInfo = serialize($GLOBALS['coursesUsed']);
		    $applicantDetails = (array)json_decode($_COOKIE['csf_registration_details']);

                    $statement = $databaseConnect->prepare("INSERT INTO students (name, student_id, grade, phone, grade_info) VALUES (?, ?, ?, ?, ?)");
                    $statement->bind_param("sssss", $applicantDetails['studentName'], $applicantDetails['studentID'], $applicantDetails['studentGradeLevel'], $applicantDetails['phoneNumber'], $gradeInfo);
                    $statement->execute();
                    header("Location: ./success.php");
                } else {
                    echo "<div style='color: red'>You have already submitted your application</div>";
                }
            } else {
                echo "<div style='color: red'>You do not meet the membership requirements. If you believe this to be an error please contact the <a href='mailto:joystraub@vistausd.org'>club advisor</a>.</div>";
            }
        }
    }
    ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>CSF Registration</title>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="./css/main.css">
	<meta name="viewport" content="width=device-width, initial-scale=.8, user-scalable=no">
    </head>
    <body>
        <header>
            <h1>Aeries Parent Portal Link</h1>
            <hr>
        </header>
        <form action="eligibility.php" method="post">
            <table>
                <th></th>
                <th></th>
                <tr>
                    <td>Aeries Username: </td>
                    <td>
                        <input type="text" name="username" spellcheck="false">
                    </td>
                </tr>
                <tr>
                    <td>Password: </td>
                    <td>
                        <input type="password" name="password">
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" value="Submit" name="submit">
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>
