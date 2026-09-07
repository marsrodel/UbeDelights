<?php

function mail_load_config()
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/mail_config.php';
    }
    return $config;
}

function mail_using_placeholder_credentials($config = null)
{
    $config = $config ?? mail_load_config();
    $user = strtolower(trim((string) ($config['smtp_user'] ?? '')));
    $pass = trim((string) ($config['smtp_pass'] ?? ''));

    if ($user === '' || $pass === '') {
        return true;
    }

    return strpos($user, 'your-email') !== false
        || strpos($user, 'example.com') !== false
        || strpos($pass, 'your-app-password') !== false
        || strpos($pass, 'your-password') !== false;
}

function mail_is_dev_mode($config = null)
{
    $config = $config ?? mail_load_config();
    return !empty($config['dev_mode']) || mail_using_placeholder_credentials($config);
}

function mail_log_dev_otp($toEmail, $subject, $otp = '')
{
    $dir = __DIR__ . '/storage';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $line = sprintf(
        "[%s] to=%s subject=%s otp=%s\n",
        date('Y-m-d H:i:s'),
        $toEmail,
        $subject,
        $otp !== '' ? $otp : '(see email body)'
    );
    @file_put_contents($dir . '/otp_dev.log', $line, FILE_APPEND);
}

/**
 * @return array{success:bool,message?:string,dev_mode?:bool}
 */
function mail_send_message($toEmail, $subject, $body, $devOtp = null)
{
    $config = mail_load_config();

    if (empty($config['enabled'])) {
        return ['success' => false, 'message' => 'Email is not configured. Update server/mail_config.php'];
    }

    // Local / unfinished SMTP setup: do not attempt Gmail auth with placeholders.
    if (mail_is_dev_mode($config)) {
        mail_log_dev_otp($toEmail, $subject, is_string($devOtp) ? $devOtp : '');
        return [
            'success' => true,
            'dev_mode' => true,
            'message' => 'Dev mode: OTP generated locally (SMTP not configured).',
        ];
    }

    $fromEmail = $config['from_email'];
    $fromName = $config['from_name'];

    if (!empty($config['smtp_host']) && !empty($config['smtp_user'])) {
        $result = mail_send_smtp($toEmail, $subject, $body, $config);
        if ($result['success']) {
            return $result;
        }

        // Clearer message when Gmail rejects login
        $msg = $result['message'] ?? 'SMTP send failed';
        if (stripos($msg, 'auth') !== false) {
            $msg .= ' Check MAIL_USER / MAIL_PASS in the project .env — MAIL_PASS must be a Gmail App Password (16 characters), not your normal Gmail password.';
        }
        return ['success' => false, 'message' => $msg];
    }

    $headers = "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (@mail($toEmail, $subject, $body, $headers)) {
        return ['success' => true];
    }

    return ['success' => false, 'message' => 'Failed to send email. Check mail_config.php SMTP settings or enable dev_mode.'];
}

function mail_send_smtp($toEmail, $subject, $body, $config)
{
    $host = $config['smtp_host'];
    $port = (int) $config['smtp_port'];
    $secure = strtolower((string) ($config['smtp_secure'] ?? 'tls'));
    $user = $config['smtp_user'];
    $pass = $config['smtp_pass'];
    $fromEmail = $config['from_email'];
    $fromName = $config['from_name'];

    $remote = ($secure === 'ssl') ? "ssl://{$host}" : $host;
    $socket = @stream_socket_client("{$remote}:{$port}", $errno, $errstr, 20, STREAM_CLIENT_CONNECT);

    if (!$socket) {
        return ['success' => false, 'message' => "SMTP connection failed: {$errstr}"];
    }

    stream_set_timeout($socket, 20);

    $read = function () use ($socket) {
        $data = '';
        while ($line = fgets($socket, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $write = function ($cmd) use ($socket) {
        fwrite($socket, $cmd . "\r\n");
    };

    $greeting = $read();
    if (strpos($greeting, '220') === false) {
        fclose($socket);
        return ['success' => false, 'message' => 'SMTP server did not greet properly'];
    }

    $write('EHLO localhost');
    $ehlo = $read();
    if (strpos($ehlo, '250') === false) {
        $write('HELO localhost');
        $read();
    }

    if ($secure === 'tls') {
        $write('STARTTLS');
        $tls = $read();
        if (strpos($tls, '220') === false) {
            fclose($socket);
            return ['success' => false, 'message' => 'SMTP STARTTLS failed'];
        }

        $cryptoOk = @stream_socket_enable_crypto(
            $socket,
            true,
            STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT
        );
        if ($cryptoOk !== true) {
            $cryptoOk = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }
        if ($cryptoOk !== true) {
            fclose($socket);
            return ['success' => false, 'message' => 'SMTP TLS handshake failed'];
        }

        $write('EHLO localhost');
        $read();
    }

    $write('AUTH LOGIN');
    $authPrompt = $read();
    if (strpos($authPrompt, '334') === false) {
        fclose($socket);
        return ['success' => false, 'message' => 'SMTP AUTH not accepted'];
    }

    $write(base64_encode($user));
    $read();
    $write(base64_encode($pass));
    $auth = $read();
    if (strpos($auth, '235') === false) {
        fclose($socket);
        return ['success' => false, 'message' => 'SMTP authentication failed'];
    }

    $write("MAIL FROM:<{$fromEmail}>");
    $fromResp = $read();
    if (strpos($fromResp, '250') === false) {
        fclose($socket);
        return ['success' => false, 'message' => 'SMTP MAIL FROM rejected'];
    }

    $write("RCPT TO:<{$toEmail}>");
    $rcpt = $read();
    if (strpos($rcpt, '250') === false && strpos($rcpt, '251') === false) {
        fclose($socket);
        return ['success' => false, 'message' => 'SMTP recipient rejected'];
    }

    $write('DATA');
    $dataResp = $read();
    if (strpos($dataResp, '354') === false) {
        fclose($socket);
        return ['success' => false, 'message' => 'SMTP DATA not accepted'];
    }

    $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
    $message = "From: {$encodedFromName} <{$fromEmail}>\r\n";
    $message .= "To: <{$toEmail}>\r\n";
    $message .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= $body . "\r\n.";
    $write($message);
    $result = $read();

    $write('QUIT');
    fclose($socket);

    if (strpos($result, '250') !== false) {
        return ['success' => true];
    }

    return ['success' => false, 'message' => 'SMTP send failed'];
}
