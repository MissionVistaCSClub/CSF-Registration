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

    session_start();
    if(isset($_SESSION['id'])) {
        session_destroy();
        echo "You have been logged out.";
        header("Location: login.php");
    } else {
        header("Location: index.php");
    }
    ?>
<!DOCTYPE html>
<html>
    <body>
        <p>
            <a href="login.php" target="_top">Click here</a> if you are not automatically redirected.
        </p>
    </body>
</html>