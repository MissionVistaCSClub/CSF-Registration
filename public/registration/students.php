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

    //Overview for Students
    include_once("../../admin/database_registration.php");
    loggedIn();
    ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>CSF Applicants</title>
    </head>
    <body>
        <header>
            <h1>CSF Applicants</h1>
	    <hr>
        </header>
        <br>
        <table align='center'>
            <tr>
                <th align='left'>Name</th>
                <th>Student ID</th>
		<th>Grade Level</th>
                <th>Full Application</th>
            </tr>
            <?php
		$i = 0;
                $result = mysqli_query($databaseConnect, "SELECT student_id, name, payment, grade FROM students ORDER BY grade ASC, name ASC");
                while($row = mysqli_fetch_array($result)) {
		    $i++;
                    $studentStudentId = $row['0'];
                    $studentName = $row['1'];
                    $studentPaymentStatus = $row['2'];
		    $studentGradeLevel = $row['3'];
                    echo "
                        <tr style='color: " , ($studentPaymentStatus > 0 ? " green" : " red") , "'>
                            <td>" . $studentName . "</td>
                            <td>" . $studentStudentId . "</td>
			    <td align='center'>" . $studentGradeLevel . "</td>
			    <td align='center'><a href='fullapp.php?id=" . $studentStudentId . "'>View</a></td>
			    ";
                }
                ?>
        </table>
	<br>
	<div align="center">
	    <a href="payment.php">Payment Check-in</a>
	    <br>
	    <a href="download.php">Download Applicant Records</a>
	</div>
	<br>
    </body>
</html>
