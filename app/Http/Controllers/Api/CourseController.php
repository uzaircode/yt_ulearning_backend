<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    //return all the course list
    public function courseList() {
        $result = Course::select('name', 'thumbnail', 'lesson_num', 'price', 'id') -> get();

        return response()->json([
            'code' => 200,
            'message' => 'My course list is here',
            'data' => $result
        ], 200);
    }
}