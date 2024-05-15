<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ExchangeRateProviderInterface;
use App\Exceptions\ExchangeRateRetrievalException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

class ExchangeRateProvider implements ExchangeRateProviderInterface
{
    private Client $client;
    private string $url;

    public function __construct(Client $client, string $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    /**
     * @throws ExchangeRateRetrievalException
     */
    public function getRate(string $currency): string
    {
        try {
            $response = $this->client->get($this->url);

            return $this->getResponseRate($response, $currency);
        } catch (RequestException | GuzzleException $e) {
            throw ExchangeRateRetrievalException::retrievalError();
        } catch (JsonException $e) {
            throw ExchangeRateRetrievalException::invalidJsonError();
        }
    }

    private function getResponseRate(ResponseInterface $response, string $currency): string
    {
        $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return (string) ($data['rates'][$currency] ?? 1.0);
    }
}
