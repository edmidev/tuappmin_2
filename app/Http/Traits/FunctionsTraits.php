<?php

namespace App\Http\Traits;

trait FunctionsTraits
{
    public static function dateDiffInDays($date1, $date2) 
    {
        // Calculating the difference in timestamps
        $diff = strtotime($date2) - strtotime($date1);
    
        // 1 day = 24 hours
        // 24 * 60 * 60 = 86400 seconds
        return round($diff / 86400);
    }

    public static function dateDiffInMonths($date1, $date2) 
    {
        $d1 = new \DateTime($date2);
        $d2 = new \DateTime($date1);                      
        $Months = $d2->diff($d1);
        $howeverManyMonths = (($Months->y) * 12) + ($Months->m);

        return $howeverManyMonths;
    }
}