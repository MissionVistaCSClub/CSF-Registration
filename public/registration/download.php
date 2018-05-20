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

    //Export attendance for meetings
    include_once("../../admin/database_registration.php");
    loggedIn();

    $result = $databaseConnect->query("SELECT name, student_id, grade, payment, grade_info FROM students ORDER BY grade ASC, name ASC");
    if (!$result) die("Couldn't fetch records");
    $headers = array("Student Name", "Student ID", "Grade Level", "Payment", "Courses");
    $fp = fopen('php://output', 'w');
    if ($fp && $result) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="Applicants.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        fputcsv($fp, $headers);
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $studentId = $row['0'];
	    $row['4'] = json_encode(unserialize($row['4']));
            //array_push($row, $studentName['0']);
            fputcsv($fp, array_values($row));
        }
        die;
    }
    ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Download CSV</title>
    </head>
</html>
