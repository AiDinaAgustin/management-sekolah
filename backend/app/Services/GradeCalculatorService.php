<?php

namespace App\Services;

class GradeCalculatorService
{
    public function calculate(float $tugas, float $uts, float $uas): float
    {
        return round(($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4), 2);
    }
}
