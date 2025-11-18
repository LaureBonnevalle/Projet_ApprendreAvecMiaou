<?php


class TimesModels {

    /** Function which allows you to retrieve either the complete url or just the end
     * @param string - $response - Either "all" or "end"
     *
     * @return string - A character string corresponding to the desired response.
     * 
     * Example:  var_dump(endUrl("all"));
    */
    function endUrl($response = "all") {
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            $url = "https";
        else
            $url = "http";
        $url .= $_SERVER['HTTP_HOST'];
        $url .= $_SERVER['REQUEST_URI'];
        $path = parse_url($url, PHP_URL_PATH);
        $pathFragments = explode('/', $path);
        if($response == "all")
            return $path;
        return end($pathFragments);
    }



    /** Method that allows you to add or remove a duration from a datetime
     * @param object - $dateTime - DateTime object
     * @param integer - $years - Number of years to add to the date
     * @param integer - $months - Number of months to add to the date
     * @param integer - $days - Number of days to add to the date
     * @param integer - $hours - Number of hours to add to the date
     * @param integer - $minutes - Number of minutes to add to the date
     * @param integer - $seconds - Number of seconds to add to the date
     * @param string - $oper - Addition or removal (add or sub), if other -> identical datetime in output
     * @param string - $format - Date output format
     *
     * @return string - Date in desired format
     * 
     * Example: 
     * $addAtDate = new DateTime(dateNow('Y-m-d H:i:s', 'Europe/Paris')); or $addAtDate = new DateTime('2020-01-31');
     * $newDate = addTimeToTime($addAtDate, 0, 0, 0, 0, 15, 0, "add", 'd-m-Y H:i:s'); // Add 15 minutes
     * $newDate = addTimeToTime($addAtDate, 0, 0, 0, 0, 15, 0, "sub", 'd-m-Y H:i:s'); // Sub 15 minutes
     * var_dump($newDate);
    */
    public function addTimeToTime($dateTime, $years, $months, $days, $hours, $minutes, $seconds, $oper = "add", $formatEnd = 'Y-m-d H:i:s') {
        // Si $dateTime est une chaîne, la convertir en item DateTime
        if (is_string($dateTime)) {
            $dateTime = new  DateTime($dateTime);
        }
 
        $text = 'P'.$years.'Y'.$months.'M'.$days.'DT'.$hours.'H'.$minutes.'M'.$seconds.'S';
        if($oper == 'add') {
            $dateTime->add(new DateInterval($text));
        }else if($oper == 'sub'){
            $dateTime->sub(new DateInterval($text));
        }
        else {
            $dateTime->format($formatEnd);
        }
        return $dateTime->format($formatEnd);
 
        // NOTE: Be careful when adding or removing months or years! 1 month === 31 days
        // Ex:  Add 1 month to: "2000-12-31" gives "2001-01-31"
        // But: Add 1 month to: "2001-01-31" gives "2001-03-03"
        // But: Add 1 month to: "2020-01-31" gives "2020-03-02" (Leap year)
    }

    public function dateNow(string $format = 'Y-m-d H:i:s', string $fuseau = 'Europe/Paris') {
        date_default_timezone_set($fuseau);
        $dateActuelle = date_create('now')->format($format);
        return $dateActuelle;
    }

    /** Method to calculate the time between two dates
     * @param string $dateFirst - Start date.
     * @param string $dateLast - End date.
     *
     * @return array - Different data usable in different formats
     * Example: 2 years 2 months 11 days 15 hours 37 minutes 42 seconds
    */
    function dateDiff(string $dateFirst, string $dateLast) : array{
        $dateIn = new DateTime($dateFirst);
        $dateOut = new DateTime($dateLast);
        $interval = $dateIn->diff($dateOut);

        $years = ""; $months = ""; $days = "";
        $hours = ""; $minutes = ""; $seconds = "";
        $shortTime = ""; $simplyTime = ""; $text = "";
        $array = [ $interval->y, $interval->m, $interval->d, $interval->h, $interval->i, $interval->s ];
        $newArray = []; $int = []; $tab = []; $int = ["ans", "mois"];

        $interval->y != 0 ? $years = $interval->y." ans" : $years = "";
        $interval->m != 0 ? $months = $interval->m." mois" : $months = "";
        if($interval->d != 0) {
            $days=$interval->d." jour"; $interval->d!=1 ? $days=$days."s" : $days=$days;
        }

        $interval->y != 0 && $interval->m != 0 && $interval->d != 0 ? $shortTime = $years.", ".$months." et ".$days : "";
        $interval->y == 0 && $interval->m == 0 && $interval->d == 0 ? $shortTime = false : "";
        $interval->y != 0 && $interval->m == 0 && $interval->d == 0 ? $shortTime = $years : "";
        $interval->y == 0 && $interval->m != 0 && $interval->d == 0 ? $shortTime = $months : "";
        $interval->y == 0 && $interval->m == 0 && $interval->d != 0 ? $shortTime = $days : "";
        $interval->y != 0 && $interval->m != 0 && $interval->d == 0 ? $shortTime = $years." et ".$months : "";
        $interval->y == 0 && $interval->m != 0 && $interval->d != 0 ? $shortTime = $months." et ".$days : "";
        $interval->y != 0 && $interval->m == 0 && $interval->d != 0 ? $shortTime = $years." et ".$days : "";

        if($interval->h != 0) {
            $hours=$interval->h." heure"; $interval->h!=1 ? $hours=$hours."s" : $hours=$hours;
        }
        if($interval->i != 0) {
            $minutes=$interval->i." minute"; $interval->i!=1 ? $minutes=$minutes."s" : $minutes=$minutes;
        }
        if($interval->s != 0) {
            $seconds=$interval->s." seconde"; $interval->s!=1 ? $seconds=$seconds."s" : $seconds=$seconds;
        }

        $interval->h != 0 && $interval->i == 0 && $interval->s == 0 ? $simplyTime = $hours : "";
        $interval->h == 0 && $interval->i != 0 && $interval->s == 0 ? $simplyTime = $minutes : "";
        $interval->h == 0 && $interval->i == 0 && $interval->s != 0 ? $simplyTime = $seconds : "";
        $interval->h != 0 && $interval->i != 0 && $interval->s != 0 ? $simplyTime = $hours.", ".$minutes." et ".$seconds : "";
        $interval->h == 0 && $interval->i == 0 && $interval->s == 0 ? $simplyTime = false : "";
        $interval->h != 0 && $interval->i != 0 && $interval->s == 0 ? $simplyTime = $hours." et ".$minutes : "";
        $interval->h == 0 && $interval->i != 0 && $interval->s != 0 ? $simplyTime = $minutes." et ".$seconds : "";
        $interval->h != 0 && $interval->i == 0 && $interval->s != 0 ? $simplyTime = $hours." et ".$seconds : "";

        for($i=0; $i<=count($array); $i++) {
            switch($i){
                case 2: $array[2] > 1 ? $int[] = "jours"    : $int[] = "jour";      break;
                case 3: $array[3] > 1 ? $int[] = "heures"   : $int[] = "heure";     break;
                case 4: $array[4] > 1 ? $int[] = "minutes"  : $int[] = "minute";    break;
                case 5: $array[5] > 1 ? $int[] = "secondes" : $int[] = "seconde";   break;
                default: break;
            }
        }

        for($i=0; $i<count($array); $i++) {
            $array[$i] != 0 || $array[$i] != "" ? $tab[] = $int[$i] : "";
            $array[$i] != 0 ? $newArray[] = $array[$i]." ".$int[$i] : "";
        }

        for($i=0; $i<count($newArray); $i++){
            $text = ($i == count($newArray)-1)
                ? $text." et " .$newArray[$i]
                    : (($i == 0) ? $text = $text.$newArray[$i]
                                    : $text = $text.", ".$newArray[$i]);
        }

        return $array = [
                            'dateIn'        => $dateFirst,
                            'dateOut'       => $dateLast,
                            'years'         => $interval->y,
                            'months'        => $interval->m,
                            'days'          => $interval->d,
                            'hours'         => $interval->h,
                            'minutes'       => $interval->i,
                            'secondes'      => $interval->s,
                            'onlyMinutes'   => $interval->h*60+$interval->i,
                            'onlySeconds'   => $interval->h*60*60 + $interval->i*60 + $interval->s,
                            'fullMonths'    => $interval->y*12 + $interval->m,
                            'fullDays'      => $interval->days,
                            'fullHours'     => $interval->days*24,
                            'fullMinutes'   => $interval->days*24*60,
                            'fullSeconds'   => $interval->days*24*60*60 + $interval->h*60*60 + $interval->i*60 + $interval->s,
                            'fullTime'      => $text,
                            'shortTime'     => $interval->days ." jours : ".$shortTime,
                            'simplyDay'     => $shortTime,
                            'simplyTime'    => $simplyTime,
        ];

        //$interval_1 = dateDiff('2021-01-18', '2022-02-19');
        //$interval_2 = dateDiff('06:30:00', '07:45:01');
        //$interval_3 = dateDiff('2021-03-18 00:00:00', '2022-08-06 01:06:27');
        //$interval_4 = dateDiff('1992-11-27 13:00:00', dateNow('Y-m-d H:i:s', 'Europe/Paris'));
        //$interval_5 = dateDiff('1978-08-19 03:45:00', '2022-02-18 23:20:46');

        //var_dump($interval_4);

        //var_dump("Between these two dates, ". $interval_3['fullTime']);
        //var_dump("Between these two dates, there is ". $interval_3['simplyDay']);
        //var_dump("Which represents a total of: ". $interval_3['fullDays']." days");
        //var_dump($interval_3['simplyTime']);
        //var_dump($interval_3['simplyTime']." represent: ". $interval_3['onlySeconds']." seconds");
        //var_dump("Between these two dates, a total of: ". $interval_3['fullHours']." hours");
        //var_dump("Between these two dates, a total of: ". $interval_3['fullMinutes']." minutes");
        //var_dump("Between these two dates, a total of: ". $interval_3['fullSeconds']." seconds");
        
        //$interval_1 = dateDiff('2021-01-18', '2022-02-19');
        //$interval_2 = dateDiff('06:30:00', '07:45:01');
        //$interval_3 = dateDiff('2021-03-18 00:00:00', '2022-08-06 01:06:27');
        //$interval_4 = dateDiff('1992-11-27 13:00:00', dateNow('Y-m-d H:i:s', 'Europe/Paris'));
        //$interval_5 = dateDiff('1978-08-19 03:45:00', '2022-02-18 23:20:46');

        //var_dump($interval_4);

        //var_dump("Entre ces deux dates, il s'est écoulé : ". $interval_3['fullTime']);
        //var_dump("Entre ces deux dates, il y a ". $interval_3['simplyDay']);
        //var_dump("Ce qui représente un total de : ". $interval_3['fullDays']." jours");
        //var_dump($interval_3['simplyTime']);
        //var_dump($interval_3['simplyTime']." représentent : ". $interval_3['onlySeconds']." secondes");
        //var_dump("Entre ces deux dates, un total de : ". $interval_3['fullHours']." heures");
        //var_dump("Entre ces deux dates, un total de : ". $interval_3['fullMinutes']." minutes");
        //var_dump("Entre ces deux dates, un total de : ". $interval_3['fullSeconds']." secondes");
    }
    
    function getElapsedTime() {
    if (isset($_SESSION['start_time'])) {
        $elapsed = time() - $_SESSION['start_time'];
        return $elapsed;
    }
    return 0;
    }
}