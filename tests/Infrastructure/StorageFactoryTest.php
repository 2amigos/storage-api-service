<?php

namespace Tests\Infrastructure;

use http\Exception\InvalidArgumentException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Tests\BaseTest;

class StorageFactoryTest extends BaseTest
{

    public function testShouldReturnInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $factory = self::$container['storage.adapter.factory'];

        $nonExistingAdapterName = '';
        $factory->fromAdapterName($nonExistingAdapterName);
    }

    public function testShouldReturnValidFilesystem()
    {
        $factory = self::$container['storage.adapter.factory'];

        $nonExistingAdapterName = 'local';
        $filesystem = $factory->fromAdapterName($nonExistingAdapterName);

        $isFilesystem = $filesystem instanceof Filesystem;
        $isCorrectAdapter = $filesystem->getAdapter() instanceof Local;

        $this->assertTrue($isFilesystem);
        $this->assertTrue($isCorrectAdapter);
    }

}