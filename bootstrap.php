<?php

use App\Config\Configuration;
use App\Contracts\DataProviderInterface;
use App\Output\CommissionFormatter;
use App\Processors\TransactionProcessor;
use App\Providers\BinListProvider;
use App\Providers\ExchangeRateProvider;
use App\Providers\FileDataProvider;
use App\Services\CommissionCalculator;
use App\Services\CommissionRate;
use App\Services\EuCountriesSpecification;
use App\Services\Math;
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
            'defaultCommissionRate' => $_ENV['DEFAULT_COMMISSION_RATE'],
            'defaultMathScale' => $_ENV['DEFAULT_MATH_SCALE'],
            'commissionPrecision' => $_ENV['COMMISSION_PRECISION'],
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
    Math::class => function (ContainerInterface $container) {
        return new Math(
            $container->get(Configuration::class)->get('defaultMathScale')
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
            $container->get(BinListProvider::class),
            $container->get(Configuration::class)->get('defaultCommissionRate')
        );
    },
    CommissionCalculator::class => function (ContainerInterface $container) {
        return new CommissionCalculator(
            $container->get(ExchangeRateProvider::class),
            $container->get(CommissionRate::class),
            $container->get(Math::class)
        );
    },
    CommissionFormatter::class => function (ContainerInterface $container) {
        return new CommissionFormatter(
            $container->get(Configuration::class)->get('commissionPrecision')
        );
    },
    TransactionProcessor::class => function (ContainerInterface $container) {
        return new TransactionProcessor(
            $container->get(CommissionCalculator::class),
            $container->get(FileDataProvider::class),
            $container->get(CommissionFormatter::class)
        );
    },
    FileDataProvider::class => DI\create(FileDataProvider::class),
    DataProviderInterface::class => DI\get(FileDataProvider::class),
]);

$container = $containerBuilder->build();
return $container;
