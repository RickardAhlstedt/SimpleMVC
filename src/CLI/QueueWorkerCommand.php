<?php

namespace SimpleMVC\CLI;

use SimpleMVC\Attribute\Command;
use SimpleMVC\Queue\QueueInterface;

#[Command('queue:work', 'Process jobs from the queue')]
class QueueWorkerCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function execute(array $args = []): int
    {
        $queue = $this->container->get(\SimpleMVC\Queue\DatabaseQueueDriver::class);
        $registry = $this->container->get(\SimpleMVC\Queue\JobRegistry::class);

        echo "Starting worker...\n";
        while (true) {
            $job = $queue->reserve();
            if ($job) {
                echo sprintf("[%s] Processing job #%s:%s\n", date("Y-m-d H:i:s"), $job['id'], $job['name']);

                $handler = $registry->resolve($job['name']);
                if ($handler) {
                    try {
                        if (is_string($job['payload'])) {
                            $job['payload'] = json_decode($job['payload'], true, 512, JSON_THROW_ON_ERROR);
                        }
                        $handler->handle($job['payload']);
                    } catch (\Throwable $e) {
                        echo sprintf("[%s] Exception: %s\n", date("Y-m-d H:i:s"), $e->getMessage());
                    }
                } else {
                    echo sprintf("[%s] Job (%s) not found!\n", date("Y-m-d H:i:s"), $job['name']);
                }
            } else {
                sleep(1);
            }
        }
        return 0;
    }
}
