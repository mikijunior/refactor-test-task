<?php

use App\Config\Configuration;
use App\Contracts\DataProviderInterface;
use App\Processors\TransactionProcessor;
use App\Providers\BinListProvider;
use App\Providers\ExchangeRateProvider;
use App\Providers\FileDataProvider;
use App\Services\CommissionCalculator;
use App\Services\CommissionRate;
use App\Services\EuCountriesSpecification;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    Configuration::class => function () {
        return new Configuration([
            'binListUrl' => $_ENV['API_BINLIST_URL'],
            'exchangeRateUrl' => $_ENV['API_EXCHANGE_RATE_URL'],
        ]);
    },
    Client::class => DI\create(Client::class),
    EuCountriesSpecification::class => DI\create(EuCountriesSpecification::class),
    BinListProvider::class => function (ContainerInterface $container) {
        return new BinListProvider(
            $container->get(Client::class),
            $container->get(Configuration::class)->get('binListUrl')
        );
    },
    ExchangeRateProvider::class => function (ContainerInterface $container) {
        return new ExchangeRateProvider(
            $container->get(Client::class),
            $container->get(Configuration::class)->get('exchangeRateUrl')
        );
    },
    CommissionRate::class => function (ContainerInterface $container) {
        return new CommissionRate(
            $container->get(BinListProvider::class)
        );
    },
    CommissionCalculator::class => function (ContainerInterface $container) {
        return new CommissionCalculator(
            $container->get(ExchangeRateProvider::class),
            $container->get(CommissionRate::class),
            2
        );
    },
    TransactionProcessor::class => function (ContainerInterface $container) {
        return new TransactionProcessor(
            $container->get(CommissionCalculator::class),
            $container->get(FileDataProvider::class)
        );
    },
    FileDataProvider::class => DI\create(FileDataProvider::class),
    DataProviderInterface::class => DI\get(FileDataProvider::class),
]);

$container = $containerBuilder->build();
return $container;
