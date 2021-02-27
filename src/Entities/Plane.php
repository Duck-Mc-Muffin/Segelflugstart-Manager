<?

class Plane extends Entity
{
    public $id;
    public $model;
    public $lfz;
    public $wkz;
    public $alias;
    public $available;

    public static function SQL($alias, $prefix)
    {
        return $alias . ".id AS " . $prefix . "_id," .
                $alias . ".model AS " . $prefix . "_model," .
                $alias . ".lfz AS " . $prefix . "_lfz," .
                $alias . ".wkz AS " . $prefix . "_wkz," .
                $alias . ".alias AS " . $prefix . "_alias," .
                $alias . ".available AS " . $prefix . "_available," .
                $alias . ".updated_at AS " . $prefix . "_updated_at," .
                $alias . ".inserted_at AS " . $prefix . "_inserted_at";
    }
}