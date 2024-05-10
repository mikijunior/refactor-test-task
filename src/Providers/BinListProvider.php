<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\BinProviderInterface;
use App\Exceptions\BinDataRetrievalException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

class BinListProvider implements BinProviderInterface
{
    private Client $client;
    private string $url;

    public function __construct(Client $client, string $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    /**
     * @Problems:
     * Rate Limiting: The API limits requests to 5 per hour.
     * Empty Values: Some fields might be empty, and the country code could be an empty string.
     *
     * @throws BinDataRetrievalException
     */
    public function getCountryCode(string $bin): string
    {
        try {
            $response = $this->client->get($this->url . $bin);
            return $this->getResponseCountryCode($response);
        } catch (RequestException | GuzzleException $e) {
            throw BinDataRetrievalException::retrievalError();
        } catch (JsonException $e) {
            throw BinDataRetrievalException::invalidJsonError();
        }
    }

    private function getResponseCountryCode(ResponseInterface $response): string
    {
        $data = json_decode((string)$response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        return $data->country->alpha2 ?? '';
    }
}
