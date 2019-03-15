<?php

namespace Tests;

use App\Application\Document\DocumentStatusInterface;
use Faker\Factory;
use Ramsey\Uuid\Uuid;

class TestDataFactory extends BaseTest
{

    public static function generateDocuments(int $amount)
    {
        $faker = Factory::create();
        $uuid4 = Uuid::uuid4();

        for ($i=0; $i < $amount; $i++) {
            $item = [
                'uuid' => $uuid4->getBytes(),
                'status' => DocumentStatusInterface::UPLOADED,
                'name' => $faker->regexify('[1-9][0-9][a-z]{9,14}') . '.txt',
                'tag' => $faker->slug,
                'storage' => 'local',
                'path' => $faker->imageUrl()
            ];

            self::$db->insert('storage_document', $item);
        }
    }

}