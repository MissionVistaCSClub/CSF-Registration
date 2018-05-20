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

    $databaseConnect = mysqli_connect("localhost", "root", "", "csf");
    
    function loggedIn() {
        session_start();
        if(isset($_SESSION['id'])) {
            $uid = $_SESSION['id'];
            $username = $_SESSION['username'];
            $accesslevel = $_SESSION['access-level'];
            $realname = $_SESSION['name'];
        } else {
            header("Location: login.php");
        }
    }

    function attendanceTotal($meetingId, $databaseConnect) {
        $attended = mysqli_num_rows(mysqli_query($databaseConnect, "SELECT student_id FROM attendance meeting_id = '$meetingId'"));
        return $attended;
    }

    function attendanceStudent($studentId, $meetingId, $databaseConnect) {
        $meetingDate = mysqli_fetch_array(mysqli_query($databaseConnect, "SELECT date FROM meetings WHERE id = '$meetingId'"));
        $dateInfo = date_parse_from_format('MM d, YYYY', $meetingDate['0']);
        $unixTimestamp = mktime('1', '55', '00', $dateInfo['month'], $dateInfo['day'], $dateInfo['year']); //UTC
        if($unixTimestamp > time()) {
            $attendance = '#38567F'; //Meeting hasn't happened yet
            return $attendance;
        } elseif(mysqli_num_rows(mysqli_query($databaseConnect, "SELECT id FROM attendance WHERE student_id = '$studentId' AND meeting_id = '$meetingId'"))>0) {
            $attendance = '#4BA33C'; //Student did attend
            return $attendance;
        } else {
            $attendance = '#C24040'; //Student didn't attend
            return $attendance;
        }
    }
    
    function nav_bar() {
        echo "
        <hr align='left'>
        <nav id='menu'>
            <ul>
                <li><a href='meetings.php'>Meetings Overview</a></li>
                <li><a href='students.php'>Students Overview</a></li>
                <li><a href='users.php'>Users Overview</a></li>
                <li><a href='logout.php'>Logout</a></li>
            </ul>
        </nav>
        ";
    }
    
    function nav_bar_public() {
        echo "
        <hr align='left'>
        <nav id='menu'>
            <ul>
                <li><a href='attendance.php'>Attendance</a></li>
                <li><a href='overview.php'>Meetings Overview</a></li>
            </ul>
        </nav>
        ";
    }
    
    function has_permission($permitted = array("")) {
        //Login method must be called first to start the session
        if($permitted['0'] == "") {
            $permitted = array("development", "advisor"); //Default access levels
        } else {
            array_push($permitted, "development", "advisor");
        }

        if(!isset($_SESSION['access-level'])) {
            $_SESSION['access-level'] = "";
        }

        if(in_array($_SESSION['access-level'], $permitted)) {
            return true;
        }
        return false;
    }
    ?>
