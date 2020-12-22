<?php


namespace App\Helpers;


class Helper
{
    public static function displayFloats($value, $type='crypto')
    {
        if ($type == 'cash'){
            return number_format($value,2,'.',' ');
        }
        $decimas = explode('.', $value);
        if (isset($decimas[1])) {
            if (intval($decimas[1]) == 0) {
                return $decimas[0];
            }
        }
        return $value;
    }

    public static function getFirstRecordNumber($limit=50)
    {
       return ( request()->get('page') == null) ? 1 :  (((request()->get('page') -1) * 50) +1 );
    }
}
