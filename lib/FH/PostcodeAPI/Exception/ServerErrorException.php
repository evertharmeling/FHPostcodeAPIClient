<?php

namespace FH\PostcodeAPI\Exception;

use Psr\Http\Message\RequestInterface;

/**
 * @author Evert Harmeling <evert@freshheads.com>
 */
class ServerErrorException extends \Exception implements PostcodeApiExceptionInterface
{
    private RequestInterface $request;

    /**
     * @return ServerErrorException
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return ServerErrorException
     */
    public static function fromRequest(RequestInterface $request)
    {
        $self = new self();
        $self->setRequest($request);

        return $self;
    }
}
