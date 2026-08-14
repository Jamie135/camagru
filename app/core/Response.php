<?php

/**
 * This class represents the HTTP response.
 * It provides methods to manipulate the response sent to the client.
 */

namespace app\core;

class Response
{

    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }
}
