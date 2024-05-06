<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ExchangeRateProviderInterface;
use App\Exceptions\ExchangeRateRetrievalException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JsonException;

class ExchangeRateProvider implements ExchangeRateProviderInterface
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @throws ExchangeRateRetrievalException
     */
    public function getRate(string $currency): float
    {
        try {
            $response = $this->client->get('https://api.exchangeratesapi.io/latest');

            $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            return $data['rates'][$currency] ?? 1.0;
        } catch (RequestException | GuzzleException $e) {
            throw ExchangeRateRetrievalException::retrievalError();
        } catch (JsonException $e) {
            throw ExchangeRateRetrievalException::invalidJsonError();
        }
    }
}
