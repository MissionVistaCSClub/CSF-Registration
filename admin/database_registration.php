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

    $databaseConnect = mysqli_connect("localhost", "root", "", "csf-registration");

    function admissions_open($databaseConnect) {
        $attended = mysqli_fetch_array(mysqli_query($databaseConnect, "SELECT value FROM settings WHERE setting_id = 'admissions_open'"));
        if($attended[0] == 1) {
            return true;
        } else {
            return false;
        }
    }

    function already_submitted($databaseConnect) {
        $applicantDetails = (array)json_decode($_COOKIE['csf_registration_details']);

	$alreadySubmitted = $databaseConnect->prepare("SELECT * FROM students WHERE student_id = ?"); //If student hasn't already submitted an application
	$alreadySubmitted->bind_param("s", $applicantDetails['studentID']);
	$alreadySubmitted->execute();
	$alreadySubmitted->store_result();

	if($alreadySubmitted->num_rows != 0) {
	    return true;
	} else {
	    return false;
	}
    }

    function loggedIn() {
	session_start();

        if(isset($_SESSION['id'])) {
            $uid = $_SESSION['id'];
	    $username = $_SESSION['username'];
	    $realname = $_SESSION['name'];
	} else {
	    header("Location: login.php");
	}
    }
    ?>
