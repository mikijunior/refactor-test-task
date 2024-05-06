<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\BinProviderInterface;
use App\Exceptions\BinDataRetrievalException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JsonException;

class BinListProvider implements BinProviderInterface
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
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
            $response = $this->client->get('https://lookup.binlist.net/' . $bin);
            $data = json_decode((string)$response->getBody(), false, 512, JSON_THROW_ON_ERROR);

            return $data->country->alpha2 ?? '';
        } catch (RequestException | GuzzleException $e) {
            throw BinDataRetrievalException::retrievalError();
        } catch (JsonException $e) {
            throw BinDataRetrievalException::invalidJsonError();
        }
    }
}
