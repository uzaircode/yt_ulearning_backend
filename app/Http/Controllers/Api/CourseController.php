<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    //return all the course list
    public function courseList() {
        try {
            $result = Course::select('name', 'thumbnail', 'lesson_num', 'price', 'id') -> get();

            return response()->json([
                'code' => 200,
                'message' => 'My course list is here',
                'data' => $result
            ], 200);
        } catch (\Throwable $throw) {
            return response()->json([
                'code' => 500,
                'msg' => "The column does not exist or you have a syntax error",
                'data' => $throw->getMessage(),
            ], 500);
        }
    }

    //return a course detail
    public function courseDetail(Request $request) {

        $id = $request->id;
        try {
            $result = Course::where('id', '=', $id)->select(
                'id',
                'name',
                'user_token',
                'description',
                'price',
                'lesson_num',
                'video_length',
                'thumbnail',
                ) -> get();

            return response()->json([
                'code' => 200,
                'message' => 'My course detail is here',
                'data' => $result
            ], 200);
        } catch (\Throwable $throw) {
            return response()->json([
                'code' => 500,
                'msg' => "The column does not exist or you have a syntax error",
                'data' => $throw->getMessage(),
            ], 500);
        }
    }
}