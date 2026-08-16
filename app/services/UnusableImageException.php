<?php

/**
 * Thrown when the submitted image is the problem, not the server.
 * The message is written to be shown to whoever sent it.
 */

namespace app\services;

use RuntimeException;

class UnusableImageException extends RuntimeException
{
}
