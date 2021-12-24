<?
// Database connection
define('DB_SYSTEM', getenv('SEGELFLUG_DB_SYSTEM'));
define('DB_HOST', getenv('SEGELFLUG_DB_HOST'));
define('DB_NAME', getenv('SEGELFLUG_DB_NAME'));
define('DB_USER', getenv('SEGELFLUG_DB_USER'));

// Database password
$pass_file = getenv('SEGELFLUG_DB_PASS_FILE');
$pass = getenv('SEGELFLUG_DB_PASS');
if ($pass_file !== false)
{
    $pass = file_get_contents($pass_file);
    if ($pass === false) $pass = "";
}
define('DB_PASS', trim($pass));
unset($pass);

// General
const WEB_APP_TITLE = 'Segelflugstart-Manager'; // Visible on Welcome- and Login page
setlocale(LC_TIME, 'de_DE', 'german');
const INDEX_LANDING_PAGE = '/login.php';
const FORCE_HTTPS = true;
const RESTRICT_MANUAL_ENTRY_ZONE = true; // TODO: Mode "false" not tested!
const RESTRICT_MANUAL_ENTRY_PLANNED = true; // TODO: Mode "false" not tested!
const CALENDAR_DAY_SPAN = 14;

// Login
const APPROVE_ACCOUNTS_BY_DEFAULT = false;
const LOGIN_PW_MIN_LENGTH = 4;
const LOGIN_PW_ALGO = PASSWORD_DEFAULT; // PHP password_hash()
const LOGIN_TOKEN_TTL = 'P1D'; // PHP DateInterval (https://www.php.net/manual/en/dateinterval.construct.php)
const REMEMBER_ME_TOKEN_TTL = 'P2W'; // PHP DateInterval (https://www.php.net/manual/en/dateinterval.construct.php)

// Google-API
// Leave empty to remove Google-Sign-in feature (only visually)
const GOOGLE_CLIENT_ID = '';

// E-Mail
const EMAIL_HOST = 'smpt.example.server';
const EMAIL_PORT = 465;
const EMAIL_SMTP_SECURE = 'ssl';                    // Options: ssl|tls
const EMAIL_USERNAME = 'support@example.com';
const EMAIL_PASS = '';                              // SMTP password
const EMAIL_FROM_MAIL = 'support@example.com';
const EMAIL_FROM_NAME = 'Segelflugstart-Manager';
const EMAIL_REPLY_MAIL = 'support@example.com';
const EMAIL_REPLY_NAME = 'Support';

// E-Mail cool down
const EMAIL_LIMIT_TIME = 'PT3H'; // PHP DateInterval (https://www.php.net/manual/en/dateinterval.construct.php)
const EMAIL_LIMIT_AMOUNT = 3;

// Enlist-Zone
const ENLIST_ZONE_LATITUDE = 0;
const ENLIST_ZONE_LONGITUDE = 0;
const ENLIST_ZONE_RADIUS = 1000;

// Date format
// Pattern Rules: https://unicode-org.github.io/icu/userguide/format_parse/datetime/
$date_formatter_title = new IntlDateFormatter('de_DE', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
$date_formatter_title->setPattern('E d. MMM');
$date_formatter_calendar = new IntlDateFormatter('de_DE', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
$date_formatter_calendar->setPattern('E d. MMM');
$date_formatter_user_list = new IntlDateFormatter('de_DE', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
$date_formatter_user_list->setPattern('E d. MMM HH:mm');

// DB clean up script
const DB_CLEAN_NOT_APPROVED_TIME = 'P2D'; // PHP DateInterval (https://www.php.net/manual/en/dateinterval.construct.php)
const DB_CLEAN_INACTIVE_ACC_TIME = 'P6M'; // PHP DateInterval (https://www.php.net/manual/en/dateinterval.construct.php)

// Roles
const ATTENDANCE_ROLES = 
[
    [
        'name' => 'Schüler/Scheinpilot (sonstiges)',
        'symbol' => '',
        'bootstrap_color' => ''
    ],
    [
        'name' => 'Windenfahrer/Ausbilder',
        'symbol' => '<i title="Windenfahrer/Ausbilder" class="fas fa-truck-moving"></i>',
        'bootstrap_color' => 'warning'
    ],
    [
        'name' => 'Startleiter',
        'symbol' => '<i title="Startleiter" class="fas fa-phone"></i>',
        'bootstrap_color' => 'info'
    ],
    [
        'name' => 'Fluglehrer',
        'symbol' => '<i title="Fluglehrer" class="fas fa-chalkboard-teacher"></i>',
        'bootstrap_color' => 'primary'
    ],
    [
        'name' => 'Gast',
        'symbol' => '<i title="Gast" class="far fa-user"></i>',
        'bootstrap_color' => 'secondary'
    ]
];