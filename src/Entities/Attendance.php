<?
class Attendance extends Entity
{
    public $id;
    public $flight_day;
    public $user_id;
    public $time;
    public $pos_longitude;
    public $pos_latitude;
    public $manual_entry;
    public $is_planned;
    public $role;
    public $first;

    const ENCODE_USER_INP = ["manual_entry"];
    const IGNORE_AUTOSET = ["user"];
    public $user;

    function __construct($arr, $prefix = null)
    {
        parent::__construct($arr, $prefix);
    }

    function fill($arr, $prefix = null)
    {
        parent::fill($arr, $prefix);
        if (is_string($this->flight_day)) $this->flight_day = DateTime::createFromFormat('Y-m-d', $this->flight_day);
        if (is_a($this->flight_day, 'DateTime')) $this->flight_day->setTime(0, 0, 0, 0);
        $this->time = $this->parseTime($this->time, $this->flight_day);
    }

    /**
     * @param $str
     * @param null $day
     * @return DateTime|null
     */
    public static function parseTime($str, $day = null): ?DateTime
    {
        $time = is_a($day, 'DateTime') ? clone $day : new DateTime();
        $time_arr = explode(':', $str);
        if (empty($time_arr) || $time_arr[0] === "") return null;
        else
        {
            $time->setTime($time_arr[0], $time_arr[1] ?? 0, $time_arr[2] ?? 0, $time_arr[3] ?? 0);
            return $time;
        }
    }

    /**
     * @return string
     */
    public function GetNameAndSymbols(): string
    {
        $str = $this->user_id == $_SESSION["user_id"] ? '<a class="" href="/edit.php?id=' . $this->id . '"><i class="far fa-edit"></i></a> ' : '';
        $str .= empty($this->manual_entry) ? $this->user->getFullName() : $this->manual_entry . ' <small>(von ' . $this->user->getFullName() . ')</small>';
        $str .= empty($this->first) ? '' : ' <i class="far fa-frown"></i>';
        $str .= empty(ATTENDANCE_ROLES[$this->role]) ? '' : ' ' . ATTENDANCE_ROLES[$this->role]["symbol"];
        return $str;
    }

    /**
     * @param $alias
     * @param $prefix
     * @return string
     */
    public static function SQL($alias, $prefix): string
    {
        return $alias . ".id AS " . $prefix . "_id," .
                $alias . ".flight_day AS " . $prefix . "_flight_day," .
                $alias . ".user_id AS " . $prefix . "_user_id," .
                $alias . ".time AS " . $prefix . "_time," .
                $alias . ".pos_longitude AS " . $prefix . "_pos_longitude," .
                $alias . ".pos_latitude AS " . $prefix . "_pos_latitude," .
                $alias . ".manual_entry AS " . $prefix . "_manual_entry," .
                $alias . ".is_planned AS " . $prefix . "_is_planned," .
                $alias . ".role AS " . $prefix . "_role," .
                $alias . ".first AS " . $prefix . "_first," .
                $alias . ".updated_at AS " . $prefix . "_updated_at," .
                $alias . ".inserted_at AS " . $prefix . "_inserted_at";
    }
}