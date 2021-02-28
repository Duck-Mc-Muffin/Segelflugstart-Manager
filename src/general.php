<?
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailer_Exception;

// Config file
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/conf.php';

// Session
session_start();
if (isset($_REQUEST["logout"]))
{
    session_destroy();
    if (!empty($_COOKIE['remember_me'])) setcookie('remember_me', '', time() - 1000, '/', $_SERVER["SERVER_NAME"], true, true);
    header("location: /index.php");
	exit;
}

// Force HTTPS
if (FORCE_HTTPS)
{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') header('Strict-Transport-Security: max-age=31536000');
    else
    {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
        exit;
    }
}

// App Exception
class AppException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

// Autoloader
require_once $_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php";
spl_autoload_register(function($class_name)
{
    // TODO: use namespaces
    include $_SERVER["DOCUMENT_ROOT"] . "/src/Entities/" . $class_name . '.php';
});

// Database connection
try
{
    $db = new PDO(DB_SYSTEM . ':host='. DB_HOST .';dbname=' . DB_NAME, DB_USER, DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ]);
}
catch (PDOException $ex)
{
    echo "<p>Fehler beim Verbinden mit der Datenbank.</p>";
    exit;
}

// ========================================================== Login ========================================================
function SetSessionUser($user)
{
    $_SESSION["user_id"] = $user->id;
    $_SESSION["user_obj"] = serialize($user);
}
function GetSessionUser()
{
    if (empty($_SESSION["user_obj"])) return false;
    else return unserialize($_SESSION["user_obj"]);
}
function Login($name, $password)
{
    global $db;
    $query = $db->prepare('SELECT * FROM user WHERE name = :name');
    $query->bindParam(':name', $name);
    $query->execute();
    if ($row = $query->fetch(PDO::FETCH_ASSOC))
    {
        if (empty($row["is_approved"]))
        {
            header('location: /login.php?acc_not_approved=true');
            exit;
        }
        if (password_verify($password, $row["password_hash"])) SetSessionUser(new User($row));
        else return false;
    }
    else return false;
    return true;
}
function LoginGoogle($token)
{
    $google_user_id = ValidateGoogleUserIDToken($token);
    if ($google_user_id === false)
    {
        $_SESSION["error"] = "Der Google-Token ist ungültig.";
        header('location: /login.php');
        exit;
    }

    global $db;
    $query = $db->prepare('SELECT * FROM user WHERE google_user_id = :google_user_id');
    $query->bindParam(':google_user_id', $google_user_id);
    $query->execute();
    if ($row = $query->fetch(PDO::FETCH_ASSOC))
    {
        if (empty($row["is_approved"]))
        {
            header('location: /login.php?acc_not_approved=true');
            exit;
        }
        SetSessionUser(new User($row));
        return true;
    }
    return false;
}
function ValidateGoogleUserIDToken($token)
{
    $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
    try
    {
        $payload = $client->verifyIdToken($token);
        if ($payload && $payload["aud"] == GOOGLE_CLIENT_ID) return $payload['sub'];
        else return false;
    }
    catch (Exception $ex) // TODO
    {
        $_SESSION["error"] = $ex->getMessage();
        header('location: /login.php');
        exit;
    }
    return false;
}
function LoginToken($column_name, $token, $token_ttl)
{
    $time = new DateTime();
    $time->sub(new DateInterval($token_ttl));

    global $db;
    $query = $db->prepare('SELECT * FROM user WHERE ' . $column_name . ' = :token AND ' . $column_name . '_time >= :token_time');
    $query->bindValue(':token', $token);
    $query->bindValue(':token_time', $time->format('Y-m-d H:i:s'));
    $query->execute();

    if ($row = $query->fetch(PDO::FETCH_ASSOC))
    {
        if (empty($row["is_approved"]))
        {
            header('location: /login.php?acc_not_approved=true');
            exit;
        }
        SetSessionUser(new User($row));
        return true;
    }
    return false;
}
function GenerateUserToken($column_name, $user_id)
{
    global $db;
    $token = bin2hex(random_bytes(32));
    $query = $db->prepare('UPDATE user SET ' . $column_name . ' = :token, ' . $column_name . '_time = :token_time WHERE id = :id');
    $query->bindParam(':id', $user_id);
    $query->bindValue(':token', $token);
    $query->bindValue(':token_time', (new DateTime())->format('Y-m-d H:i:s'));
    $query->execute();
    return $token;
}
function SetRememberMeCookie($user_id)
{
    $token = GenerateUserToken('remember_me_token', $user_id);
    $expire = new DateTime();
    $expire->add(new DateInterval(REMEMBER_ME_TOKEN_TTL));
    setcookie('remember_me', $token, $expire->getTimestamp(), '/', $_SERVER["SERVER_NAME"], true, true);
}
function CheckLogin($auto_login = true)
{
    $set_remember_me_cookie = !empty($_REQUEST["set_remember_me"]);
    if (!isset($_REQUEST["logout"]) && empty($_SESSION["user_id"]) && $auto_login)
    {
        if (!empty($_POST["google_user_id_token"])) // Google Login
        {
            if (!LoginGoogle($_POST["google_user_id_token"]))
            {
                header('location: /login.php?google_acc_not_found=true');
                exit;
            }
        }
        else if (!empty($_POST["name"]) && !empty($_POST["pw"])) // Regular Login
        {
            if (!Login($_POST["name"], $_POST["pw"]))
            {
                header('location: /login.php?wrong_credentials=true');
                exit;
            }
        }
        else if (!empty($_REQUEST["login_token"])) // E-Mail Token Login
        {
            if (!LoginToken('login_token', $_REQUEST["login_token"], LOGIN_TOKEN_TTL))
            {
                header('location: /login.php?invald_token=true');
                exit;
            }
        }
        else if (!empty($_COOKIE["remember_me"])) // Remember Me Token Login
        {
            if (LoginToken('remember_me_token', $_COOKIE["remember_me"], REMEMBER_ME_TOKEN_TTL))
            {
                // Update cookie
                $set_remember_me_cookie = true;
            }
        }
    }
    if ($set_remember_me_cookie) SetRememberMeCookie($_SESSION["user_id"]);
    return !empty($_SESSION["user_id"]);
}
// ===========================================================================================================

// Views & Templates
function RenderFlightDayBtn($str, $flight_day = null)
{
    ?><div class="text-center py-3">
        <a class="btn btn-outline-secondary btn-sm" href="/index.php<?= empty($flight_day) ? '' : '?flight_day=' . $flight_day->format('Y-m-d') ?>">
            <i class="fas fa-arrow-left"></i> <?= $str ?>
        </a>
    </div><?
}
function RenderAttendanceAll($list)
{
    include $_SERVER["DOCUMENT_ROOT"] . '/src/templates/attendance_all.php';
}
function RenderAttendanceByPlane($list, $plane)
{
    include $_SERVER["DOCUMENT_ROOT"] . '/src/templates/attendance_by_plane.php';
}
function RenderAttendanceForm($planes, $flight_day, $att = null, $plane_selection = [], $is_manual = false)
{
    include $_SERVER["DOCUMENT_ROOT"] . '/src/templates/attendance_form.php';
}
function RenderAttendanceTable($caption, $list, $plane_selection = [])
{
    include $_SERVER["DOCUMENT_ROOT"] . '/src/templates/attendance_table.php';
}

// Send E-Mail
function SendMail($user_id, $to_mail, $to_name, $subject, $template_file, $data, $debug_level = 0)
{
    $err = null;
    try
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->Port = EMAIL_PORT;
        $mail->Username = EMAIL_USERNAME;
        $mail->Password = EMAIL_PASS;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = EMAIL_SMTP_SECURE;
        $mail->SMTPDebug = $debug_level;

        $mail->setFrom(EMAIL_FROM_MAIL, EMAIL_FROM_NAME);
        $mail->addReplyTo(EMAIL_REPLY_MAIL, EMAIL_REPLY_NAME);
        $mail->addAddress($to_mail, $to_name);
        $mail->Subject = $subject;

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        ob_start();
        require $_SERVER["DOCUMENT_ROOT"] . '/src/templates/' . $template_file;
        $msg_html = ob_get_clean();
        $mail->msgHTML($msg_html, $_SERVER["DOCUMENT_ROOT"]);

        if (!$mail->send()) $err = $mail->ErrorInfo;
    }
    catch(PHPMailer_Exception $ex)
    {
        $err = $ex->getMessage();
    }
    catch(Exception $ex)
    {
        $err = $ex->getMessage();
    }
    
    global $db;
    $query = $db->prepare('INSERT INTO mail_log(to_mail, user_id, to_name, subject, error, inserted_at)
                                        VALUES(:to_mail, :user_id, :to_name, :subject, :error, :inserted_at)');
    $query->bindParam(':user_id', $user_id);
    $query->bindParam(':to_mail', $to_mail);
    $query->bindParam(':to_name', $to_name);
    $query->bindParam(':subject', $subject);
    $query->bindParam(':error', $err);
    $query->bindValue(':inserted_at', (new DateTime())->format('Y-m-d H:i:s'));
    $query->execute();

    return empty($err);
}