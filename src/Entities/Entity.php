<?
class Entity
{
    const IGNORE_AUTOSET = [];
    const ENCODE_USER_INP = [];
    public $updated_at;
    public $inserted_at;

    function __construct($arr, $prefix = null)
    {
        $this->fill($arr, $prefix);
    }

    function fill($arr, $prefix = null)
    {
        foreach($arr as $key => $value)
        {
            if (!empty($prefix) && strpos($key, $prefix) !== 0) continue;
            $this->autoSetter($key, $value, $prefix);
        }
        if (is_string($this->updated_at)) $this->updated_at = DateTime::createFromFormat('Y-m-d H:i:s', $this->updated_at);
        if (is_string($this->inserted_at)) $this->inserted_at = DateTime::createFromFormat('Y-m-d H:i:s', $this->inserted_at);
    }

    public static function GetByID($id)
    {
        global $db;
        $query = $db->prepare("SELECT * FROM " . strtolower(static::class) . " WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        if ($row = $query->fetch(PDO::FETCH_ASSOC)) return new static($row);
        else return null;
    }

    function autoSetter($key, $value, $prefix = null)
    {
        if (!empty($prefix)) $key = substr($key, strlen($prefix));
        if (!in_array($key, $this::IGNORE_AUTOSET)
            && property_exists(get_class($this), $key)) $this->$key = in_array($key, static::ENCODE_USER_INP) ? htmlspecialchars($value) : $value;
    }
}