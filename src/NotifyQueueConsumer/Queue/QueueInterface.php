<?php

declare(strict_types=1);

namespace NotifyQueueConsumer\Queue;

use Exception;

interface QueueInterface
{
    /**
     * @throws Exception
     * @return array|null
     */
    public function next(): ?array;

    /**
     * @throws Exception
     * @param string $id
     */
    public function delete(string $id): void;
}
