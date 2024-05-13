<?php

use App\Contracts\DataProviderInterface;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use App\Config\Configuration;
use App\Services\CommissionCalculator;
use App\Services\EuCountriesSpecification;
use App\Services\CommissionRateService;
use App\Providers\BinListProvider;
use App\Providers\ExchangeRateProvider;
use App\Processors\TransactionProcessor;
use App\Providers\FileDataProvider;
use Psr\Container\ContainerInterface;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    Configuration::class => function() {
        return new Configuration([
            'binListUrl' => $_ENV['API_BINLIST_URL'],
            'exchangeRateUrl' => $_ENV['API_EXCHANGE_RATE_URL'],
        ]);
    },
    Client::class => DI\create(Client::class),
    EuCountriesSpecification::class => DI\create(EuCountriesSpecification::class),

    BinListProvider::class => function (ContainerInterface $container) {
        $configuration = $container->get(Configuration::class);
        return new BinListProvider(
            $container->get(Client::class),
            $configuration->get('binListUrl')
        );
    },

    ExchangeRateProvider::class => function (ContainerInterface $container) {
        $configuration = $container->get(Configuration::class);
        return new ExchangeRateProvider(
            $container->get(Client::class),
            $configuration->get('exchangeRateUrl')
        );
    },

    CommissionRateService::class => DI\create(CommissionRateService::class)->constructor(
        DI\get(BinListProvider::class),
    ),

    CommissionCalculator::class => DI\create(CommissionCalculator::class)->constructor(
        DI\get(ExchangeRateProvider::class),
        DI\get(CommissionRateService::class)
    ),

    TransactionProcessor::class => DI\create(TransactionProcessor::class)->constructor(
        DI\get(CommissionCalculator::class),
        DI\get(FileDataProvider::class)
    ),

    FileDataProvider::class => DI\create(FileDataProvider::class),

    DataProviderInterface::class => DI\get(FileDataProvider::class),
]);

$container = $containerBuilder->build();
return $container;
