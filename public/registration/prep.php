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

<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>CSF Registration</title>
	<meta name="viewport" content="width=device-width, initial-scale=.8, user-scalable=yes">
	<style>
	    td {
	        padding-bottom: 10px;
	    }
	    img {
	        width: 50vw;
	        max-width: 700px;
	    }
	</style>
    </head>
    <body>
        <header>
            <h1>CSF Registration</h1>
            <hr>
	    <span style="color: red">Please complete the following before entering your Aeries information: </span>
        </header>
        <table>
	    <th></th>
            <th></th>
	    <tr>
		<td>
		    <span>Step 1. Login to your <a href="http://parents.vistausd.org/Transcripts.aspx" target="_blank">Parent Portal</a> account and navigate to the 'Transcripts' page</span>
		</td>
	    </tr>
	    <tr>
		<td>
		    <span>Step 2. Modify the 'Limit' field so that it spans from 9th grade to your current grade level.</span>
		</td>
		<td>
                    <a href="example_limit.png"><img src="example_limit.png"></a>
		</td>
	    </tr>
	    <tr>
		<td>
		    <span>Step 3. Check the box titled 'Sort by Date Decending' and uncheck the box titled 'Sort by Subject'.</span>
		</td>
		<td>
		    <a href="example_sort.png"><img src="example_sort.png"></a>
                </td>
	    </tr>
	    <tr>
		<td>
		    <a href="eligibility.php">Continue</a>
		</td>
	    </tr>
	</table>
    </body>
</html>

