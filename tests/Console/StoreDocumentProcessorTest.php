<?php

namespace Tests\Console;

use App\Application\Console\Processor\StoreDocumentProcessor;
use App\Application\Document\DocumentStatusInterface;
use App\Application\Document\DocumentStoreCommand;
use Enqueue\Fs\FsConnectionFactory;
use Faker\Factory;
use Ramsey\Uuid\Uuid;
use Slim\Http\UploadedFile;
use Tests\BaseTest;
use Enqueue\Client\Config;

class StoreDocumentProcessorTest extends BaseTest
{

    private $processor;
    private $context;
    private $uuid;
    private $document;

    public function setUp()
    {
        $faker = Factory::create();

        $this->document = new UploadedFile(
            __DIR__ . '/../files/test.txt',
            'test.txt',
            'text/plain',
            filesize(__DIR__ . '/../files/test.txt')
        );

        $repository = self::$container->get('document.repository');

        $uuid4 = Uuid::uuid4();
        $this->uuid = $uuid4->toString();
        $repository->create([
            'uuid' => $uuid4->getBytes(),
            'name' => $faker->regexify('[1-9][0-9][a-z]{9,14}') . '.txt',
            'tag' => $faker->slug,
            'status' => DocumentStatusInterface::PROCESSING,
            'storage' => 'local',
        ]);

        $settings = self::$container['settings']['queue'];
        $factory = new FsConnectionFactory($settings);
        $this->context = $factory->createContext();

        $this->processor = new StoreDocumentProcessor(
            self::$container->get('storage.adapter.factory'),
            self::$container->get('document.repository'),
            self::$container->get('logger')
        );


    }

    public function testProcessingQueueDocumentSuccess()
    {
        $query = self::$container->get('document.query');
        $data = $query->findOneByUuidAndTag($this->uuid);
        $data['document'] = $this->document;

        $message = $this->context->createMessage(
            json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            [
                Config::PROCESSOR => 'enqueue.storage.processor'
            ]
        );

        $this->processor->process($message, $this->context);

        $document = $query->findOneByUuidAndTag($data['uuid']);

        $this->assertEquals(DocumentStatusInterface::UPLOADED, $document['status']);

        $basePath = self::$container->get('settings')['storage']['local']['args']['root'];
        $filePath = $basePath . $document['name'];

        $this->assertFileExists($filePath);
    }

}