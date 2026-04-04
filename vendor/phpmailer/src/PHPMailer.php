<?php
namespace PHPMailer\PHPMailer;

class PHPMailer
{
    public $CharSet = 'UTF-8';
    public $ContentType = 'text/plain';
    public $isHTML = false;
    public $Mailer = 'mail';
    public $Host = 'localhost';
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = 'tls';
    public $Port = 587;
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $to = [];
    public $ReplyTo = [];

    public function isMail()
    {
        $this->Mailer = 'mail';
    }

    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    public function setFrom($address, $name = '')
    {
        $this->From = $address;
        $this->FromName = $name;
        return true;
    }

    public function addAddress($address, $name = '')
    {
        $this->to[] = trim($address);
        return true;
    }

    public function addReplyTo($address, $name = '')
    {
        $this->ReplyTo = [trim($address), $name];
        return true;
    }

    public function isHTML($isHtml = true)
    {
        $this->isHTML = (bool) $isHtml;
    }

    public function send()
    {
        if ($this->Mailer === 'mail') {
            return $this->sendMail();
        }
        if ($this->Mailer === 'smtp') {
            return $this->sendSMTP();
        }
        throw new Exception('Unsupported mailer: ' . $this->Mailer);
    }

    protected function sendMail()
    {
        if (empty($this->to)) {
            throw new Exception('No recipient provided.');
        }

        $to = implode(', ', $this->to);
        $headers = [];
        $headers[] = 'From: ' . $this->FromName . ' <' . $this->From . '>';

        if (!empty($this->ReplyTo)) {
            $headers[] = 'Reply-To: ' . $this->ReplyTo[0];
        }

        $headers[] = 'MIME-Version: 1.0';
        if ($this->isHTML) {
            $headers[] = 'Content-Type: text/html; charset=' . $this->CharSet;
            $body = $this->Body;
        } else {
            $headers[] = 'Content-Type: text/plain; charset=' . $this->CharSet;
            $body = $this->AltBody ?: $this->Body;
        }

        return mail($to, $this->Subject, $body, implode("\r\n", $headers));
    }

    protected function sendSMTP()
    {
        if (empty($this->Host)) {
            throw new Exception('SMTP host not set.');
        }

        $transport = $this->SMTPSecure === 'ssl' ? 'ssl://' : '';
        $remote_socket = $transport . $this->Host . ':' . $this->Port;
        $timeout = 30;

        $socket = @stream_socket_client($remote_socket, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            throw new Exception('SMTP connect failed: ' . $errstr . ' (' . $errno . ')');
        }

        $this->smtpGetResponse($socket, 220);
        $this->smtpSendCommand($socket, 'EHLO localhost', 250);

        if ($this->SMTPSecure === 'tls') {
            $this->smtpSendCommand($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('Failed to start TLS encryption.');
            }
            $this->smtpSendCommand($socket, 'EHLO localhost', 250);
        }

        if ($this->SMTPAuth) {
            $this->smtpSendCommand($socket, 'AUTH LOGIN', 334);
            $this->smtpSendCommand($socket, base64_encode($this->Username), 334);
            $this->smtpSendCommand($socket, base64_encode($this->Password), 235);
        }

        $this->smtpSendCommand($socket, 'MAIL FROM:<' . $this->From . '>', 250);
        foreach ($this->to as $recipient) {
            $this->smtpSendCommand($socket, 'RCPT TO:<' . $recipient . '>', [250, 251]);
        }

        $this->smtpSendCommand($socket, 'DATA', 354);

        $headers = [];
        $headers[] = 'From: ' . $this->FromName . ' <' . $this->From . '>';
        if (!empty($this->ReplyTo)) {
            $headers[] = 'Reply-To: ' . $this->ReplyTo[0];
        }
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: ' . ($this->isHTML ? 'text/html' : 'text/plain') . '; charset=' . $this->CharSet;
        $headers[] = 'Subject: ' . $this->Subject;
        $headers[] = 'Date: ' . date('r');

        $body = $this->isHTML ? $this->Body : ($this->AltBody ?: $this->Body);
        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
        $message = str_replace("\n", "\r\n", $message);
        $message = preg_replace('/^\./m', '..', $message);

        fwrite($socket, $message . "\r\n.\r\n");
        $this->smtpGetResponse($socket, 250);
        $this->smtpSendCommand($socket, 'QUIT', 221);
        fclose($socket);

        return true;
    }

    protected function smtpSendCommand($socket, $command, $expectedCodes)
    {
        fwrite($socket, $command . "\r\n");
        return $this->smtpGetResponse($socket, $expectedCodes);
    }

    protected function smtpGetResponse($socket, $expectedCodes)
    {
        if (!is_array($expectedCodes)) {
            $expectedCodes = [$expectedCodes];
        }

        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expectedCodes)) {
            throw new Exception('SMTP Error: ' . trim($response));
        }

        return $response;
    }
}
