<?php

/**
 * Minimal SMTP client.
 */

namespace app\core;

use RuntimeException;

class Mailer
{
    private const TIMEOUT = 10;

    public function __construct(
        private string $host,
        private int $port,
        private string $from,
        private string $fromName = 'Camagru',
    ) {
    }

    public static function fromEnv(): self
    {
        return new self(
            getenv('MAIL_HOST') ?: 'mailpit',
            (int) (getenv('MAIL_PORT') ?: 1025),
            getenv('MAIL_FROM') ?: 'no-reply@camagru.local',
        );
    }

    public function send(string $to, string $subject, string $html): void
    {
        // A line break in either would end the header, and everything after it
        // would be read as more headers: extra recipients, a forged subject.
        $this->rejectNewlines($to, 'recipient');
        $this->rejectNewlines($subject, 'subject');

        $socket = @stream_socket_client(
            sprintf('tcp://%s:%d', $this->host, $this->port),
            $errno,
            $error,
            self::TIMEOUT
        );

        if ($socket === false) {
            throw new RuntimeException(sprintf(
                'Cannot reach the mail server at %s:%d (%s)',
                $this->host,
                $this->port,
                $error
            ));
        }

        stream_set_timeout($socket, self::TIMEOUT);

        try {
            $this->expect($socket, '220');
            $this->command($socket, 'EHLO camagru', '250');
            $this->command($socket, sprintf('MAIL FROM:<%s>', $this->from), '250');
            $this->command($socket, sprintf('RCPT TO:<%s>', $to), '250');
            $this->command($socket, 'DATA', '354');

            // A line holding a single dot is what ends the message.
            fwrite($socket, $this->message($to, $subject, $html) . "\r\n.\r\n");
            $this->expect($socket, '250');

            $this->command($socket, 'QUIT', '221');
        } finally {
            fclose($socket);
        }
    }

    private function message(string $to, string $subject, string $html): string
    {
        $headers = [
            'From: ' . sprintf('%s <%s>', $this->fromName, $this->from),
            'To: <' . $to . '>',
            'Subject: ' . $this->encodeSubject($subject),
            'Date: ' . date(DATE_RFC2822),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];

        // One blank line separates headers from body.
        return implode("\r\n", $headers) . "\r\n\r\n" . $this->prepareBody($html);
    }

    /**
     * SMTP ends the body at a line containing only a dot, so a body line that
     * starts with one gets an extra dot, which the server strips back off.
     */
    private function prepareBody(string $html): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $html);

        foreach ($lines as $i => $line) {
            if (str_starts_with($line, '.')) {
                $lines[$i] = '.' . $line;
            }
        }

        return implode("\r\n", $lines);
    }

    // Headers are ASCII only, so anything else travels base64 encoded.
    private function encodeSubject(string $subject): string
    {
        if (preg_match('/[\x80-\xFF]/', $subject) !== 1) {
            return $subject;
        }

        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }

    private function command($socket, string $command, string $expected): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expect($socket, $expected);
    }

    private function expect($socket, string $expected): void
    {
        $response = '';

        // A multi-line reply repeats its code with a '-' until the final line,
        // which uses a space. EHLO always answers with several lines.
        while (($line = fgets($socket, 512)) !== false) {
            $response .= $line;

            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }

        if (!str_starts_with($response, $expected)) {
            throw new RuntimeException(sprintf(
                'SMTP: expected %s, got "%s"',
                $expected,
                trim($response)
            ));
        }
    }

    private function rejectNewlines(string $value, string $what): void
    {
        if (preg_match('/[\r\n]/', $value) === 1) {
            throw new RuntimeException(sprintf('Invalid %s: line breaks are not allowed.', $what));
        }
    }
}
