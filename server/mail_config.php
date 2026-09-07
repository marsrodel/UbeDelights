<?php
// UbeDelights mail settings.
//
// Credentials are read from the git-ignored .env at the project root so no
// secret lives in this committed file. To send real OTP emails, add these to
// .env (App Password, NOT your normal Gmail password):
//
//     MAIL_USER=youraccount@gmail.com
//     MAIL_PASS=abcdefghijklmnop
//     MAIL_FROM_NAME=UbeDelights
//
// Gmail App Password: Google Account -> Security -> 2-Step Verification -> App passwords
//
// With those set, real sending turns on automatically. With them missing, the
// app stays in dev mode and shows the OTP on screen instead of emailing it.

if (!function_exists('ubedelights_mail_env')) {
    function ubedelights_mail_env(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }

        if ($value === false || $value === null) {
            static $parsed = null;
            if ($parsed === null) {
                $parsed = [];
                $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
                if (is_readable($path)) {
                    foreach ((array) file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                        $line = trim($line);
                        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                            continue;
                        }
                        [$k, $v] = explode('=', $line, 2);
                        $k = trim($k);
                        $v = trim($v);
                        if (
                            (substr($v, 0, 1) === '"' && substr($v, -1) === '"')
                            || (substr($v, 0, 1) === "'" && substr($v, -1) === "'")
                        ) {
                            $v = substr($v, 1, -1);
                        }
                        $parsed[$k] = $v;
                    }
                }
            }
            $value = isset($parsed[$key]) ? $parsed[$key] : $default;
        }

        return (string) $value;
    }
}

$mailUser = trim(ubedelights_mail_env('MAIL_USER'));

// Google shows App Passwords as "abcd efgh ijkl mnop" — the spaces are only for
// readability and must not be sent to the SMTP server, so strip all whitespace.
$mailPass = preg_replace('/\s+/', '', ubedelights_mail_env('MAIL_PASS'));

return [
    'dev_mode' => false,
    'enabled' => true,
    'smtp_host' => ubedelights_mail_env('MAIL_HOST', 'smtp.gmail.com'),
    'smtp_port' => (int) ubedelights_mail_env('MAIL_PORT', '465'),
    'smtp_secure' => strtolower(ubedelights_mail_env('MAIL_ENCRYPTION', ubedelights_mail_env('MAIL_SECURE', 'ssl'))),
    'smtp_user' => $mailUser,
    'smtp_pass' => $mailPass,
    'from_email' => ubedelights_mail_env('MAIL_FROM', $mailUser),
    'from_name' => ubedelights_mail_env('MAIL_FROM_NAME', 'UbeDelights'),
];
