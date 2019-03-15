<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Flysystem;

use http\Exception\InvalidArgumentException;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

final class StorageFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var array
     */
    private $config;

    /**
     * StorageFactory constructor.
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * @param string $storage
     * @return Filesystem
     */
    public function fromAdapterName(string $storage): Filesystem
    {
        if (array_key_exists($storage, $this->config)) {
            $adapter = $this->container->get($this->config[$storage]['adapter']);
            return new Filesystem($adapter);
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unable to find storage adapter for "%s"',
                $storage
            )
        );
    }
}
