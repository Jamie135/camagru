<?php

/**
 * Tells the author of a photo that somebody has commented on it, unless they
 * have turned that off in their settings.
 */

namespace app\services;

class CommentMailer extends TemplateMailer
{
    private const EXCERPT = 300;

    public function sendNewComment(
        string $email,
        string $author,
        string $commenter,
        int $photoId,
        string $body,
    ): bool {
        return $this->deliver($email, sprintf('%s commented on your photo', $commenter), 'new-comment', [
            'username' => $author,
            'commenter' => $commenter,
            'body' => $this->excerpt($body),
            'url' => $this->url('/photos/' . $photoId),
            'settingsUrl' => $this->url('/profile'),
        ]);
    }

    private function excerpt(string $body): string
    {
        if (mb_strlen($body) <= self::EXCERPT) {
            return $body;
        }

        return mb_substr($body, 0, self::EXCERPT) . '…';
    }
}
