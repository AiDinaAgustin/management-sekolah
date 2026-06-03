<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ReportCardController extends Controller
{
    public function show($studentId)
    {
        return response()->json([
            'student_id' => $studentId,
            'grades' => [],
            'attendance_recap' => [],
        ]);
    }

    public function pdf($studentId)
    {
        return response()->json([
            'message' => 'PDF generation placeholder',
            'student_id' => $studentId,
        ]);
    }
}
