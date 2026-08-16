<?php

/**
 * Render a template inside the email layout.
 */

namespace app\services;

use app\core\Mailer;
use app\core\View;
use Throwable;

abstract class TemplateMailer
{
    public function __construct(
        protected Mailer $mailer,
        protected View $view,
        protected string $appUrl,
    ) {
    }

    // new static(): each mailer gets an instance of itself, not of this class.
    public static function fromEnv(): static
    {
        $appUrl = getenv('APP_URL') ?: 'http://localhost:8080';

        return new static(Mailer::fromEnv(), new View(), rtrim($appUrl, '/'));
    }

    /**
     * A mail server that is down, or slow, or refusing us is not the sender's
     * problem: it is logged and reported as false, and whatever the request was
     * doing carries on.
     */
    protected function deliver(string $to, string $subject, string $template, array $params): bool
    {
        try {
            $this->mailer->send($to, $subject, $this->render($template, $params));

            return true;
        } catch (Throwable $e) {
            error_log(sprintf(
                'Mail "%s" to <%s> failed: %s: %s',
                $subject,
                $to,
                $e::class,
                $e->getMessage()
            ));

            return false;
        }
    }

    protected function render(string $template, array $params): string
    {
        return $this->view->renderPartial('emails/email_layout', [
            'content' => $this->view->renderPartial('emails/' . $template, $params),
        ]);
    }

    protected function url(string $path): string
    {
        return $this->appUrl . $path;
    }
}
