<?php

namespace FH\PostcodeAPI;

use FH\PostcodeAPI\Exception\CouldNotParseResponseException;
use FH\PostcodeAPI\Exception\InvalidApiKeyException;
use FH\PostcodeAPI\Exception\ServerErrorException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Client library for postcodeapi.nu 2.0 web service.
 *
 * @author Gijs Nieuwenhuis <gijs.nieuwenhuis@freshheads.com>
 * @author Evert Harmeling <evert@freshheads.com>
 */
class Client
{
    public const string POSTCODES_SORT_DISTANCE = 'distance';
    private string $url = 'https://api.postcodeapi.nu';
    private string $version = 'v2';
    private ClientInterface $httpClient;


    public function __construct(ClientInterface $httpClient, ?string $url = null)
    {
        if (null !== $url) {
            $this->url = $url;
        }

        $this->httpClient = $httpClient;
    }

    /**
     * @return \stdClass
     */
    public function getAddresses(?string $postcode = null, ?string $number = null, int $from = 0)
    {
        return $this->get('/addresses/', [
            'postcode' => $postcode,
            'number' => $number,
            'from' => $from
        ]);
    }

    /**
     * @return \stdClass
     */
    public function getAddress(string $id)
    {
        return $this->get(sprintf('/addresses/%s', $id));
    }

    /**
     * @return \stdClass
     */
    public function getPostcodeDataByPostcode(string $postcode)
    {
        return $this->get('/postcodes/' . $postcode);
    }

    /**
     * @return \stdClass
     */
    public function getPostcodesByCoordinates(string $latitude, string $longitude, string $sort = self::POSTCODES_SORT_DISTANCE)
    {
        return $this->get('/postcodes/', [
            'coords' => [
                'latitude' => $latitude,
                'longitude' => $longitude
            ],
            'sort' => $sort
        ]);
    }

    /**
     * @return \stdClass
     *
     * @throws ClientExceptionInterface
     */
    private function get(string $path, array $params = [])
    {
        $request = $this->createHttpGetRequest($this->buildUrl($path), $params);

        $response = $this->httpClient->sendRequest($request);

        return $this->parseResponse($response, $request);
    }

    private function buildUrl(string $path): string
    {
        return sprintf('%s/%s%s', $this->url, $this->version, $path);
    }

    private function createHttpGetRequest(string $url, array $params = []): Request
    {
        $url .= (count($params) > 0 ? '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986) : '');

        return new Request('GET', $url);
    }

    /**
     * @return \stdClass
     *
     * @throws CouldNotParseResponseException
     * @throws ServerErrorException
     * @throws InvalidApiKeyException
     */
    private function parseResponse(ResponseInterface $response, RequestInterface $request)
    {
        $result = json_decode((string) $response->getBody()->getContents(), false);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CouldNotParseResponseException('Could not parse response', $response);
        }

        if (property_exists($result, 'error')) {
            switch ($result->error) {
                case 'API key is invalid.':
                    throw new InvalidApiKeyException('API key is invalid');
                case 'An unknown server error occured.':
                    throw ServerErrorException::fromRequest($request);
            }
        }

        return $result;
    }
}
