<? namespace UserController;
use AppException;
use User;
use PDO;

require_once __DIR__ . '/../general.php';

// Actions
try
{
    switch ($_REQUEST["action"])
    {
        case 'insert': insert(); break;
        case 'update': update(); break;
        case 'delete': delete(); break;
    }
}
catch(AppException $ex)
{
    $_SESSION["error"] = $ex->getMessage();
}
switch ($_REQUEST["action"])
{
    case 'insert':
        header('location: /welcome.php');
        break;
    case 'update':
    case 'delete':
        header('location: /user.php');
        break;
}
exit;

/**
 * @throws AppException
 */
function insert()
{
    // Validate
    $name = trim($_POST["name"]); 
    if (empty($name)) throw new AppException("Der Name darf nicht leer sein.");
    
    // Username taken?
    global $db;
    $query = $db->prepare('SELECT count(*) AS user_count FROM user WHERE name = :name');
    $query->bindParam(':name', $name);
    $query->execute();
    if ($query->fetch(PDO::FETCH_ASSOC)["user_count"] > 0)
    {
        header('location: /welcome.php?user_name_taken=true');
        exit;
    }
    
    // E-Mail
    $password_email = $_POST["password_email"] ?? null;
    if (isset($password_email))
    {
        // E-Mail taken?
        global $db;
        $query = $db->prepare('SELECT count(*) user_count FROM user WHERE password_email = :password_email');
        $query->bindParam(':password_email', $password_email);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)["user_count"] > 0)
        {
            header('location: /welcome.php?password_email_taken=true');
            exit;
        }
    }
    
    // Password
    $password_hash = null;
    $google_user_id = null;
    if (!empty($_POST["google_user_id_token"]) && !empty(GOOGLE_CLIENT_ID))
    {
        // Validate
        $google_user_id = ValidateGoogleUserIDToken($_POST["google_user_id_token"]);
        if ($google_user_id === false) throw new AppException("Der Google-UserID-Token ist ungültig.");

        // Google-Acc already registered?
        global $db;
        $query = $db->prepare('SELECT count(*) AS user_count FROM user WHERE google_user_id = :google_user_id');
        $query->bindParam(':google_user_id', $google_user_id);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)["user_count"] > 0)
        {
            header('location: /welcome.php?google_id_taken=true');
            exit;
        }
    }
    else
    {
        // Validate
        if (empty($_POST["pw"]) || strlen($_POST["pw"]) < LOGIN_PW_MIN_LENGTH) throw new AppException("Das Passwort darf nicht kürzer als " . LOGIN_PW_MIN_LENGTH . " Zeichen sein.");

        // Hash password
        $password_hash = password_hash($_POST["pw"], LOGIN_PW_ALGO);
    }

    // Save user data
    $query = $db->prepare('INSERT INTO user(name, password_hash, password_email, google_user_id, is_approved)
                                    VALUES(:name, :password_hash, :password_email, :google_user_id, :is_approved)');
    $query->bindParam(':name', $name);
    $query->bindParam(':password_hash', $password_hash);
    $query->bindParam(':password_email', $password_email);
    $query->bindParam(':google_user_id', $google_user_id);
    $query->bindValue(':is_approved', (int)APPROVE_ACCOUNTS_BY_DEFAULT);
    $query->execute();

    // Save user data in session
    if (APPROVE_ACCOUNTS_BY_DEFAULT)
    {
        $new_user = User::GetByID($db->lastInsertId());
        if (empty($new_user)) throw new AppException("Automatischer Login nach dem Eintragen der Nutzerdaten ist fehlgeschlagen.");
        SetSessionUser($new_user);
        if (!empty($_REQUEST["set_remember_me"])) SetRememberMeCookie($new_user->id);
        header('location: /index.php');
    }
    else
    {
        header('location: /login.php?approval_notice=true');
    }
    exit;
}

/**
 * @throws AppException
 */
function update()
{
    // Login status
    if (!CheckLogin(false)) throw new AppException("Du musst eingeloggt sein um Nutzerdaten bearbeiten zu können.");

    // Get user from DB
    if (empty($_POST["id"])) throw new AppException("Es wurde keine UserID für die Anfrage mitgesendet.");
    $user = User::GetByID($_POST["id"]);
    if (empty($user)) throw new AppException("Der Nutzer wurde nicht in der Datenbank gefunden (ID " . $_POST["id"] . ").");

    // Validate user privileges
    if ($_SESSION["user_id"] !== $user->id)
    {
        header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");
        exit;
    }

    // Token verification
    if (empty($_POST["csrf"]) || empty($_SESSION["user_data_form_csrf"])
        || $_SESSION["user_data_form_csrf"] != $_POST["csrf"]) throw new AppException("CSRF-Fehler");

    // Fill user data
    $new_user = clone $user;
    $new_user->fill($_POST);

    // Trim input
    $new_user->name = trim($new_user->name); 

    // Validate
    if (empty($new_user->name)) throw new AppException("Der Name darf nicht leer sein.");
    
    // Username taken?
    global $db;
    $query = $db->prepare('SELECT count(*) user_count FROM user WHERE name = :name AND id != :id');
    $query->bindParam(':name', $new_user->name);
    $query->bindParam(':id', $user->id);
    $query->execute();
    if ($query->fetch(PDO::FETCH_ASSOC)["user_count"] > 0)
    {
        header('location: /user.php?user_name_taken=true');
        exit;
    }
    
    // E-Mail taken?
    global $db;
    $query = $db->prepare('SELECT count(*) user_count FROM user WHERE password_email = :password_email AND id != :id');
    $query->bindParam(':password_email', $new_user->password_email);
    $query->bindParam(':id', $user->id);
    $query->execute();
    if ($query->fetch(PDO::FETCH_ASSOC)["user_count"] > 0)
    {
        header('location: /user.php?password_email_taken=true');
        exit;
    }
    
    // Google-Acc
    if (!empty($_POST["google_user_id_token"]) && !empty(GOOGLE_CLIENT_ID))
    {
        // Validate
        $google_user_id = ValidateGoogleUserIDToken($_POST["google_user_id_token"]);
        if ($google_user_id === false) throw new AppException("Der Google-UserID-Token ist ungültig.");

        // Google-Acc already registered?
        global $db;
        $query = $db->prepare('SELECT count(*) AS user_count FROM user WHERE google_user_id = :google_user_id AND id != :id');
        $query->bindParam(':id', $user->id);
        $query->bindParam(':google_user_id', $google_user_id);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)["user_count"] > 0)
        {
            header('location: /user.php?google_id_taken=true');
            exit;
        }

        // Set new Google-ID
        $query = $db->prepare('UPDATE user SET google_user_id = :google_user_id WHERE id = :id');
        $query->bindParam(':id', $user->id);
        $query->bindValue(':google_user_id', $google_user_id);
        $query->execute();
    }
    else if (isset($_POST["google_user_id_token"]) && empty($_POST["google_user_id_token"]))
    {
        // Password set?
        global $db;
        $query = $db->prepare("SELECT count(*) AS user_count
                                FROM user
                                WHERE id = :id
                                    AND password_hash IS NOT NULL
                                    AND password_hash != ''");
        $query->bindParam(':id', $user->id);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)["user_count"] == 0)
        {
            throw new AppException("Um die Verknüpfung mit dem Google-Account zu entfernen muss <em>vorher</em> ein Passwort gesetzt sein, sonst wäre ein Login nicht mehr möglich.");
        }

        // Remove Google-ID
        $query = $db->prepare('UPDATE user SET google_user_id = NULL WHERE id = :id');
        $query->bindParam(':id', $user->id);
        $query->execute();
    }
    
    // Update user data
    if (empty($_POST["password"]))
    {
        // Update
        $query = $db->prepare('UPDATE user SET name = :name, password_email = :password_email WHERE id = :id');
        $query->bindParam(':id', $user->id);
        $query->bindParam(':name', $new_user->name);
        $query->bindValue(':password_email', empty($new_user->password_email) ? null : $new_user->password_email);
        $query->execute();
    }
    else
    {
        // Hash password
        if (strlen($_POST["password"]) < LOGIN_PW_MIN_LENGTH) throw new AppException("Das Passwort darf nicht kürzer als " . LOGIN_PW_MIN_LENGTH . " Zeichen sein.");
        $password_hash = password_hash($_POST["password"], LOGIN_PW_ALGO);

        // Update
        $query = $db->prepare('UPDATE user SET name = :name, password_hash = :password_hash, password_email = :password_email WHERE id = :id');
        $query->bindParam(':id', $user->id);
        $query->bindParam(':name', $new_user->name);
        $query->bindParam(':password_hash', $password_hash);
        $query->bindValue(':password_email', empty($new_user->password_email) ? null : $new_user->password_email);
        $query->execute();
    }

    // Update session data
    $new_user = User::GetByID($user->id);
    if (empty($new_user))
    {
        session_destroy();
        throw new AppException("Aktualisieren der Session ist fehlgeschlagen.");
    }
    SetSessionUser($new_user);

    // Redirect
    header('location: /user.php?updated_notice=true');
    exit;
}

/**
 * @throws AppException
 */
function delete()
{
    throw new AppException("Dieses Feature wurde noch nicht implementiert.");
}