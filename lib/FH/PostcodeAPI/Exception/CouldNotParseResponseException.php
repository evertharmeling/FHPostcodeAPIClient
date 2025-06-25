<?php

namespace FH\PostcodeAPI\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * @author Gijs Nieuwenhuis <gijs.nieuwenhuis@freshheads.com>
 */
final class CouldNotParseResponseException extends \Exception implements PostcodeApiExceptionInterface
{
    private ResponseInterface $response;

    public function __construct(string $message, ResponseInterface $response)
    {
        parent::__construct($message);

        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
