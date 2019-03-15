<?php

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use App\Application\Token\CreateTokenCommand;
use App\Application\Token\CreateTokenHandler;
use App\Application\Document\DocumentStoreCommand;
use App\Application\Document\DocumentStoreHandler;
use App\Infrastructure\Tactitian\ContainerLocator;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use Micheh\Cache\CacheUtil;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use App\Infrastructure\Flysystem\StorageFactory;
use League\Flysystem\Adapter\Local;
use App\Application\Document\DocumentStoreMiddleware;
use App\Infrastructure\Db\Document\DocumentRepository;
use App\Infrastructure\Enqueue\Document\FsDocumentProducer;
use Enqueue\Fs\FsConnectionFactory;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Manager;
use App\Application\Document\DocumentStatusCommand;
use App\Application\Document\DocumentStatusHandler;
use App\Infrastructure\Db\Document\DocumentQuery;
use App\Application\Document\DocumentSingleCommand;
use App\Application\Document\DocumentSingleHandler;
use App\Application\Document\DocumentListHandler;
use App\Application\Document\DocumentListCommand;
use App\Infrastructure\Paginator\PagerfantaPaginator;
use App\Infrastructure\Paginator\QueryParametersParser;
use App\Infrastructure\Paginator\CursorPaginator;
use App\Application\Document\DocumentDeleteCommand;
use App\Application\Document\DocumentDeleteHandler;
use Doctrine\DBAL\DriverManager;
use App\Application\Console\Processor\StoreDocumentProcessor;
use App\Infrastructure\Db\Document\DocumentStatusQuery;

$container = $app->getContainer();

$container['token.create.handler'] = function ($container) {
    return new CreateTokenHandler($container['settings']['scopes']);
};

$container['document.store.handler'] = function ($container) {
    return new DocumentStoreHandler(
        $container['storage.adapter.factory'],
        $container['document.repository'],
        $container['enqueueStorageProducer']
    );
};

$container['document.status.handler'] = function ($container) {
    return new DocumentStatusHandler($container['document.query']);
};

$container['document.single.handler'] = function ($container) {
    return new DocumentSingleHandler(
        $container['storage.adapter.factory'],
        $container['document.query']
    );
};

$container['document.list.handler'] = function ($container) {
    return new DocumentListHandler(
        $container['document.query'],
        $container['document.status.query'],
        $container['query.parameters.parser']
    );
};

$container['document.delete.handler'] = function ($container) {
    return new DocumentDeleteHandler(
        $container['storage.adapter.factory'],
        $container['document.query'],
        $container['document.repository']
    );
};

$container['document.repository'] = function ($container) {
    return new DocumentRepository($container['db']);
};

$container['document.query'] = function ($container) {
    return new DocumentQuery($container['db'], $container['paginator']);
};

$container['document.status.query'] = function ($container) {
    return new DocumentStatusQuery($container['db'], $container['paginator']);
};

$container['storage.adapter.factory'] = function ($container) {
    return new StorageFactory($container, $container['settings']['storage']);
};

$container['storage.local.adapter'] = function ($container) {
    $reflector = new ReflectionClass(Local::class);
    return $reflector->newInstanceArgs($container['settings']['storage']['local']['args']);
};

$container['commandBus'] = function ($container) {
    $inflector = new HandleInflector();

    $map = [
        CreateTokenCommand::class => 'token.create.handler',
        DocumentStoreCommand::class => 'document.store.handler',
        DocumentStatusCommand::class => 'document.status.handler',
        DocumentSingleCommand::class => 'document.single.handler',
        DocumentListCommand::class => 'document.list.handler',
        DocumentDeleteCommand::class => 'document.delete.handler'
    ];

    $locator = new ContainerLocator($container, $map);

    $nameExtractor = new ClassNameExtractor();

    $commandHandlerMiddleware = new CommandHandlerMiddleware(
        $nameExtractor,
        $locator,
        $inflector
    );

    $storeDocumentMiddleware = new DocumentStoreMiddleware($container['document.repository']);

    return new CommandBus([$storeDocumentMiddleware, $commandHandlerMiddleware]);
};

$container['fractal'] = function () {
    $serializer = new DataArraySerializer();
    $fractal = new Manager();
    $fractal->setSerializer($serializer);

    if (isset($_GET['include'])) {
        $fractal->parseIncludes($_GET['include']);
    }

    return $fractal;
};

$container['logger'] = function () {
    $logger = new Logger('api.storage');

    $formatter = new LineFormatter(
        '[%datetime%] [%level_name%]: %message% %context%\n',
        null,
        true,
        true
    );

    /* Log to timestamped files */
    $rotating = new RotatingFileHandler(__DIR__ . '/../../runtime/api.log', 0, Logger::DEBUG);
    $rotating->setFormatter($formatter);
    $logger->pushHandler($rotating);

    return $logger;
};

$container['cache'] = function () {
    return new CacheUtil;
};

$container['db'] = function ($container) {
    $settings = $container->get('settings');

    return DriverManager::getConnection($settings['db']);
};

$container['enqueueStorageProducer'] = function ($container) {
    $settings = $container['settings']['queue'];
    $queue = $settings['name'];
    unset($settings['name']);

    return new FsDocumentProducer($queue, new FsConnectionFactory($settings));
};

$container['query.parameters.parser'] = function () {
    return new QueryParametersParser();
};

$container['paginator.pagerfanta'] = function ($container) {
    return new PagerfantaPaginator(
        $container['db']
    );
};

$container['paginator.cursor'] = function ($container) {
    return new CursorPaginator(
        $container['db']
    );
};

$container['paginator'] = $container['paginator.pagerfanta'];

$container['store.document.processor'] = function ($container) {
    return new StoreDocumentProcessor(
        $container['storage.adapter.factory'],
        $container['document.repository'],
        $container['logger']
    );
};