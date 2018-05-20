######################################################################
# CSF Registration - Handles the registration process for CSF.
# Part of the CSF Check-in service (dependency).
# Copyright (C) 2017-2018 Ryan Keegan
#	
# This program is free software; you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by the
# Free Software Foundation; either version 3, or (at your option) any
# later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; see the file LICENSE.  If not see
# <http://www.gnu.org/licenses/>.
######################################################################

import requests
from lxml import html
import sys
import re
import os

username = sys.argv[1]
password = sys.argv[2]
studentName = sys.argv[3]
studentGradeLevel = sys.argv[4]
useOldGrades = sys.argv[5]      #Boolean; determines whether or not grade from previous year can be used
coursesReturned = 8		#Number of courses returned to the end user


payload = {				#Keys for connecting to Aeries
	"checkCookiesEnabled": "true",
	"checkSilverlightSupport": "true",
	"checkMobileDevice": "false",
	"checkStandaloneMode": "false",
        "checkTabletDevice": "false",
	"portalAccountUsername": username,
	"portalAccountPassword": password,
        "submit": ""
}

session_requests = requests.session()

login_url = "http://parents.vistausd.org/LoginParent.aspx"
result = session_requests.get(login_url)
tree = html.fromstring(result.text)


result = session_requests.post(
	login_url,
	data = payload,
	headers = dict(referer=login_url)
)

url = 'http://parents.vistausd.org/Default.aspx'
result = session_requests.get(
	url,
	headers = dict(referer = url)
)
tree = html.fromstring(result.content)
students = tree.xpath('//div[@id="Sub_7"]/a')					#Stores all a tags in 'Change Student' drop down on Parent Portal

for student in students:
	if(studentName.split(" ")[0] in student.text_content() and "[INACTIVE]" not in student.text_content()): #Find best fit for student's first name
		url = 'http://parents.vistausd.org/' + student.attrib['href'];	#Visit url to change student session to best fit
		break;

result = session_requests.get(
	url,
	headers = dict(referer = url)
)

#Parse transcripts once student has been selected
url = 'http://parents.vistausd.org/Transcripts.aspx'
result = session_requests.get(
	url,
	headers = dict(referer = url)
)
tree = html.fromstring(result.content)
courseInfo = tree.xpath('//td[@class="Data"]/text()')	#Only stores tags that follow '<td class="Data"' pattern. If this program stops working this
                                                        #is likely the culprit. Load the transcripts page and see if the tags have changed.
studentInfo = tree.xpath('//span[@class="list-data"]/text()')
gradeLimit = tree.xpath('//input/@value')
courseIDs = []		#Stores course IDs
grades = []		#Stores grades
gradeLevel = []         #Stores grade level for course

def is_ascii(text):	#Determines whether or not a character is ASCII. Used to filter contents that meet the 4 character limit but aren't a course id
    if isinstance(text, unicode):
        try:
            text.encode('ascii')
        except UnicodeEncodeError:
            return False
    else:
        try:
            text.decode('ascii')
        except UnicodeDecodeError:
            return False
    return True

def score(x):		#Maps each grade to a point value that is used when computing eligibility. A '-1' makes you inelegible for CSF
	return {		#This doesn't account for honours. That is done in eligibility.php
		'A': 3,
		'B': 1,
		'C': 0,
		'D': -1,
		'F': -1
	}.get(x, -1)

for element in courseInfo:
	if(len(element) == 4 and "." not in element and "/" not in element and is_ascii(element)):
		#Filters Course IDs
		courseIDs.append(element)
	if(("A" in element or "B" in element or "C" in element or "D" in element or "F" in element) and ((len(element) == 2 and ("-" in element or "+" in element)) or len(element) == 1)):
		#Filters Grades
		grades.append(element)
        if(len(element) >= 1 and ("9" in element or "10" in element or "11" in element or "12" in element) and element.isdigit()):
                #Get grade level for course
                gradeLevel.append(element)

for element in gradeLimit:
        if(len(gradeLimit) >= 10 and "Limit " in element):
                #Make sure grade limit has correct span
                if(("Limit (9-" not in element or studentGradeLevel not in element) and not (bool(int(useOldGrades)) and str(int(studentGradeLevel)-1) in element)):
                        print "Error: Incorrect grade limit"
                        sys.exit()

#Go through all courses and ensure they are from the same grade level
i = 0
for i in xrange(8):
        if(studentGradeLevel not in gradeLevel[i] and not (bool(int(useOldGrades)) and str(int(studentGradeLevel)-1) in gradeLevel[i] and int(studentGradeLevel)-1 >= 9)):
                print "Error: Grades mismatched"
                sys.exit()

#Begin formatting JSON that will be returned
print("[")
for x in range (0, coursesReturned):
	print("{\"course_id\":\"" + courseIDs[x] + "\"" + ", \"grade\":" + str(score(grades[x][:1])) + "}")
	if(x<(coursesReturned-1)):
		print(",")
print("]")

#What is printed to the console is decoded and used as a 2D array in eligibility.php
#The first key is a normal index (0, 1, 2, etc.) and the second key is either the grade or course id (associative)
#Ex: $array[0]['grade']		Where 0 refers to the first entry and grade refers to the point value for the course


#Return student ID to verify student's identity
for element in studentInfo:
	if(len(element) == 9 and not re.search('[a-zA-Z]', element) and "/" not in element):
		print("*" + element)

#Find duplicate courses
gradeIndex = 0
coursesUnique = []		#First semester taking course
coursesDuplicate = []	#Second semester taking course (2 semesters = 1 term)
coursesRetake = []		#Third semester taking course (indicates retake)
while(gradeIndex < len(grades)):							#Each course should having a matching grade (fix for garbled course data at the end of course ids array)
	if(not courseIDs[gradeIndex] in coursesUnique):			#First time taking course
		coursesUnique.append(courseIDs[gradeIndex])
	elif(not courseIDs[gradeIndex] in coursesDuplicate):	#There can be duplicate courses (same course over two semesters)
		coursesDuplicate.append(courseIDs[gradeIndex])
	elif(not courseIDs[gradeIndex] in coursesRetake):		#More than two grades for the same course indicates retake
		coursesRetake.append(courseIDs[gradeIndex])
	gradeIndex += 1


#Return duplicate courses in the form of JSON
print("^{")
x = 0
for course in coursesRetake:
	print("\"course_id\":\"" + course + "\"")
	if(x<(len(coursesRetake)-1)):
		print(",")
	x += 1
print("}")
