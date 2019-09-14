<?php

namespace App\Http\Controllers;

use App\Student;
use App\Course;
use App\Student_Course;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function showAllRecords()
    {
        $allStudents = \App\Student::all();

        $tableHeaders = array(
            1 => 'ID',
            2 => 'Name',
            3 => 'Email ID',
            4 => 'Phone Number',
            5 => 'Courses'
        );

        return view('/homePage', [
            'header' => $tableHeaders,
            'allStudents' => $allStudents
        ]);
    }
    
    public function insertNewRecord(Request $request)
    {
        $student = new Student;
        
        $student_course = new Student_Course;

        if(empty($request->name) || empty($request->emailId) || empty($request->phoneNo) || empty($request->courses))
        {
            return view('/errorPage', [
                'msg' => "No field are Mandatory!!!"
            ]);
        }

        $student->name = $request->name;
        $student->email_id = $request->emailId;
        $student->phone_no = $request->phoneNo;

        $student->save();
        $insertedId = $student->id;

        $coursesSelected = $request->courses;

        $courseIds = array();

        foreach($coursesSelected as $course)
        {
            $courseId = Course::select('id')->where('name', $course)->get();

            $courseIds[] = $courseId[0]["id"];
        }

        $dataToBeUpdate = array();

        foreach($courseIds as $courseId)
        {
            $student_course::insert([
                'student_id' => $insertedId,
                'course_id' => $courseId
            ]);
        }

        return view('/confirmInsert', ['id' => $insertedId]);
    }

    public function getEditId(Request $request, $id)
    {
        if(empty($id))
        {
            return view('/errorPage', [
                'msg' => "No ID found"
            ]);
        }

        return view('/editPage', [
            'id' => $id
        ]);
    }

    public function editRecord(Request $request)
    {
        $student = Student::find($request->id);

        if(empty($student))
        {
            return view('/errorPage', [
                'msg' => "ID not Found"
            ]);
        }

        if(empty($request->name) || empty($request->emailId) || empty($request->phoneNo) || empty($request->courses))
        {
            return view('/errorPage', [
                'msg' => "No field can be left Blank!!!"
            ]);
        }

        $student->name = $request->name;
        $student->email_id = $request->emailId;
        $student->phone_no = $request->phoneNo;

        $student->save();
        $coursesSelected = $request->courses;

        $courseIds = array();

        foreach($coursesSelected as $course)
        {
            $courseId = Course::select('id')->where('name', $course)->get();

            $courseIds[] = $courseId[0]["id"];
        }

        //dd($courseIds);

        foreach($courseIds as $courseId)
        {
            Student_Course::where('student_id', $request->id)->delete();
        }

        foreach($courseIds as $courseId)
        {
            Student_Course::insert([
                'student_id' => $request->id,
                'course_id' => $courseId
            ]);
        }

        return view('/editPageConfirm', [
            'id' => $request->id
        ]);
    }

    public function confirmDelete(Request $request, $id)
    {
        return view('/deletePageConfirm', [
            'id' => $id
        ]);
    }

    public function deleteRecord(Request $request)
    {
        $student = Student::find($request->id);

        if(empty($student))
        {
            return view('/errorPage', [
                'msg' => "ID not Found"
            ]);
        }
        
        Student_Course::where('student_id', $request->id)->delete();

        Student::where('id', $request->id)->delete();

        return view('/deletedRecord', [
            'id' => $request->id
        ]);
    }
}
