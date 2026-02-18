<?php

if (!function_exists('DateThai')) {
    /**
     * Convert English date to Thai format
     * 
     * @param string $strDate (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
     * @return string
     */
    function DateThai($strDate)
    {
        if (!$strDate || $strDate == '0000-00-00' || $strDate == '0000-00-00 00:00:00') {
            return "-";
        }

        $strYear = date("Y", strtotime($strDate)) + 543;
        $strMonth = date("n", strtotime($strDate));
        $strDay = date("j", strtotime($strDate));
        $strHour = date("H", strtotime($strDate));
        $strMinute = date("i", strtotime($strDate));
        $strSeconds = date("s", strtotime($strDate));
        $strMonthCut = array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
        $strMonthThai = $strMonthCut[$strMonth];

        // Return only Date if time is 00:00:00
        if ($strHour == '00' && $strMinute == '00') {
            return "$strDay $strMonthThai $strYear";
        }

        return "$strDay $strMonthThai $strYear $strHour:$strMinute";
    }
}
