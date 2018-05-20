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

    //Check-in for students who have paid club dues
    include_once("../../admin/database_registration.php");
    loggedIn();

    if(isset($_POST['student_id'])) {
    	$studentStudentId = $_POST['student_id'];
        $statement = $databaseConnect->prepare("SELECT id FROM students WHERE student_id = ?");
        $statement->bind_param('s', $studentStudentId);
        $statement->execute();
        $studentExists = $statement->get_result()->fetch_array();
        
        if(!empty($studentExists)) {
            $statement = $databaseConnect->prepare("UPDATE students SET payment = '1' WHERE student_id = ?");
            $statement->bind_param("s", $studentStudentId);
            $statement->execute();
            echo $studentStudentId . " paid";
        } else {
          echo "<div style='color: red'>Student ID is not in DB</div>";
        }
    }
    ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>CSF Registration Payment</title>
        <script type="text/javascript">
            window.onload=function() {
                var select = document.getElementById('student_id');
                select.focus();
                select.select();
            };
        </script>
    </head>
    <body>
        <header>
            <h1>CSF Registration Payment</h1>
        </header>
        <form action="payment.php" method="post" style="margin-bottom: 1em">
            <div>
                Student ID: <input type="text" name="student_id" id="student_id" size="20px" onFocus="this.select()" minlength="9" maxlength="9">
                <input type="submit" name="add" value="Add">
            </div>
        </form>
    </body>
</html>
