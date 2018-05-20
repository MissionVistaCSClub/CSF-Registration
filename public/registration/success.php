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

    //Displays applicant's application details
    include_once("../../admin/database_registration.php");

    if(!isset($_COOKIE['csf_registration_details'])) {
        header("Location: ./index.php");
    }

    $applicantDetails = json_decode($_COOKIE['csf_registration_details'], true);
    $statement = $databaseConnect->prepare("SELECT name, grade, student_id, phone, grade_info FROM students WHERE student_id = ?");
    $statement->bind_param("s", $applicantDetails['studentID']);
    $statement->execute();
    $studentInfo = $statement->get_result()->fetch_assoc();
    if(empty($studentInfo)) {
        header("Location: ./index.php");
    }
    ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>CSF Registration</title>
        <meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=.8, user-scalable=no">
    </head>
    <body>
        <header>
            <h1>Your Application Details</h1>
            <hr>
            <h3 style="color: green">Your application was submitted successfully</h3>
	    <p>Please take $5 to the Finance Office. Bring the receipt to Room 803 after school: Seniors - Jan 19, Juniors - Jan 22, Sophomores - Jan 23, Freshmen - Jan 24. Be sure you are checking your school email for updates regarding your application.</p>
	</header>
        <table>
            <th></th>
            <th></th>
            <tr>
                <td>Name: </td>
                <td>
                    <?php echo $studentInfo['name'] ?>
                </td>
            </tr>
            <tr>
                <td>Student ID: </td>
                <td>
                    <?php echo $studentInfo['student_id'] ?>
                </td>
            </tr>
            <tr>
                <td>Grade: </td>
                <td>
                    <?php echo $studentInfo['grade'] ?>
                </td>
            </tr>
            <tr>
                <td>Phone Number: </td>
                <td>
                    <?php echo $studentInfo['phone'] ?>
                </td>
            </tr>
            <tr>
                <td>Courses Used: </td>
                <td>
                    <table border="1">
                        <th>ID</th>
                        <th>Name</th>
                        <th>List</th>
                        <th>Points</th>
                        <?php
                            $gradeInfo = unserialize($studentInfo['grade_info']);
                            foreach($gradeInfo as $course) {
                                $statement = $databaseConnect->prepare("SELECT course_id, name, honours, list FROM courses WHERE course_id = ?");
                                $statement->bind_param("s", $course['course_id']);
                                $statement->execute();
                                $courseDetails = $statement->get_result()->fetch_array();
                                echo "<tr>
                                <td>" . $courseDetails['course_id'] . "</td>
                                <td>" . $courseDetails['name'] . "</td>
                                <td>" . $courseDetails['list'] . "</td>
                                <td>" . $course['grade'] . "</td>
                                </tr>
                                ";
                            }
                            ?>
                    </table>
                </td>
            </tr>
        </table>
        <br>
        <div>Refer to <a href="http://mvhs.vistausd.org/file/1380278131109/1380278114912/3985611883690913390.doc">this</a> file for point assignment rules</div>
        <div>If you believe there is a problem with your application or the site, please contact me <a href="mailto:r.keegan@me.com">here</a></div>
    </body>
</html>
