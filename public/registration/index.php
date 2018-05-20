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

    include_once("../../admin/database_registration.php");
    //Initialise session; student enters name, ID, and other info
    if(isset($_POST['submit'])) {
        $checkFields = array('studentName' => $_POST['studentName'], 'studentID' => $_POST['studentID'], 'studentGradeLevel' => $_POST['studentGradeLevel'], 'phoneNumber' => $_POST['phoneNumber']);
        if(array_filter($checkFields)) {
            setcookie("csf_registration_details", json_encode($checkFields), time()+3600); //Stores cookie for 1 hour

            $statement = $databaseConnect->prepare("SELECT * FROM students WHERE student_id = ?"); //If student hasn't already submitted an application
            $statement->bind_param("s", $_POST['studentID']);
            $statement->execute();
            $statement->store_result();
            if(!already_submitted($databaseConnect)) {
                header("Location: ./prep.php");
            } else {
                echo "You have already submitted an application";
                header("Location: ./success.php");
            }
        }
    }
    ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" type="text/css" href="./css/main.css">
        <meta charset="utf-8">
        <title>CSF Registration</title>
	<meta name="viewport" content="width=device-width, initial-scale=.8, user-scalable=no">
    </head>
    <style>
        input[type=number], input[type=text], input[type=tel] {
	    width: 100%;
	}
    </style>
    <body>
        <header>
            <h1>CSF Registration</h1>
            <hr>
        </header>
        <?php
            if(admissions_open($databaseConnect)) {
                echo '
		<!--<span style="color: red">Note: You must login to your Aeries account, navigate to the <a href="http://parents.vistausd.org/Transcripts.aspx" target="_blank">transcripts page</a>, and ensure that both the <a href="example_sort.png" target="_blank">\'Sort by Date Descending\'</a> box is checked and the <a href="example_limit.png" target="_blank">\'Limit\'</a> field contains your current grade level before submitting your Parent Portal information.</span>-->
                <form action="index.php" method="post">
                    <table>
                        <th></th>
                        <th></th>
                        <tr>
                            <td>Legal Name: </td>
                            <td>
                                <input type="text" name="studentName" autocomplete="off" size="20px">
                            </td>
                        </tr>
                        <tr>
                            <td>Student ID: </td>
                            <td>
                                <input type="text" name="studentID" autocomplete="off" size="20px" placeholder="9-digit" minlength="9" maxlength="9">
                            </td>
                        </tr>
                        <tr>
                            <td>Grade: </td>
                            <td>
                                <input type="number" name="studentGradeLevel" autocomplete="off" size="20px" min="9" max="12">
                            </td>
                        </tr>
                        <tr>
                            <td>Phone Number: </td>
                            <td>
                                <input type="tel" name="phoneNumber" autocomplete="off" size="20px" maxlength="20">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="submit" name="submit" value="Submit">
                            </td>
                        </tr>
		    </table>
                </form>';
            } else {
                echo '<div style="color: red">CSF is not currently accepting applications</div>';
            }
        ?>
    </body>
</html>

