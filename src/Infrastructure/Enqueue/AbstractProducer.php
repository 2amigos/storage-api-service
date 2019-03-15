<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Enqueue;

use Interop\Queue\ConnectionFactory;
use Interop\Queue\Context;
use Interop\Queue\Queue;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProducer
{
    /**
     * @var OptionsResolver
     */
    protected $resolver;
    /**
     * @var Queue
     */
    private $queue;
    /**
     * @var Context
     */
    private $context;

    /**
     * AbstractQueue constructor.
     *
     * @param string $queue
     * @param ConnectionFactory $factory
     */
    public function __construct(string $queue, ConnectionFactory $factory)
    {
        $this->context = $factory->createContext();
        $this->queue = $this->context->createQueue($queue);
        $this->resolver = new OptionsResolver();
        $this->configureOptions();
    }

    /**
     * @param array $document
     * @param int|null $delay
     */
    abstract public function send(array $document, ?int $delay): void;

    /**
     * @return Queue
     */
    protected function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * @return Context
     */
    protected function getContext(): Context
    {
        return $this->context;
    }

    /**
     * Here is where we configure the options for documents.
     */
    protected function configureOptions(): void
    {
    }
}
