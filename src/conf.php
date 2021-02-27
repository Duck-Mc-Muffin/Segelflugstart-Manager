<?
// Database
const DB_SYSTEM = 'mysql';
const DB_HOST = 'database_host_adress';
const DB_NAME = 'database_name';
const DB_USER = 'database_user';
const DB_PASS = 'database_password';

// General
setlocale(LC_TIME, 'de_DE', 'german');
const FORCE_HTTPS = true;
const RESTRICT_MANUAL_ENTRY_ZONE = true; // Mode "false" not tested!
const RESTRICT_MANUAL_ENTRY_PLANNED = true; // Mode "false" not tested!
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
const EMAIL_SMTP_SECURE = 'ssl'; // Options: ssl|tls
const EMAIL_USERNAME = 'support@example.com';
const EMAIL_PASS = 'SMTP Password';
const EMAIL_FROM_MAIL = 'support@example.com';
const EMAIL_FROM_NAME = 'Segelflugstart-Manager';
const EMAIL_REPLY_MAIL = 'support@example.com';
const EMAIL_REPLY_NAME = 'Support';

// E-Mail cooldown
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