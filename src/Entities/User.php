<?
class User extends Entity
{
    public $id;
    public $name;
    public $password_email;
    public $google_user_id;
    public $login_token_time;
    public $remember_me_token_time;
    public $is_approved;
    public $is_moderator;

    const ENCODE_USER_INP = ["name", "password_email"];

    function getFullName()
    {
        return $this->name;
    }

    /**
     * @param $alias
     * @param $prefix
     * @return string
     */
    public static function SQL($alias, $prefix): string
    {
        return $alias . ".id AS " . $prefix . "_id," .
                $alias . ".name AS " . $prefix . "_name," .
                $alias . ".password_email AS " . $prefix . "_password_email," .
                $alias . ".google_user_id AS " . $prefix . "_google_user_id," .
                $alias . ".login_token_time AS " . $prefix . "_login_token_time," .
                $alias . ".remember_me_token_time AS " . $prefix . "_remember_me_token_time," .
                $alias . ".is_approved AS " . $prefix . "_is_approved," .
                $alias . ".is_moderator AS " . $prefix . "_is_moderator," .
                $alias . ".updated_at AS " . $prefix . "_updated_at," .
                $alias . ".inserted_at AS " . $prefix . "_inserted_at";
    }
}