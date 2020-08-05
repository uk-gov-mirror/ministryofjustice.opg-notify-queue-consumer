<?php

declare(strict_types=1);

namespace NotifyQueueConsumer\Command\Model;

use InvalidArgumentException;

class UpdateDocumentStatus
{
    protected int $documentId;
    protected string $notifyId;
    protected string $notifyStatus;

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(array $data)
    {
        $this->validate($data);

        $this->documentId = (int)$data['documentId'];
        $this->notifyId = $data['notifyId'];
        $this->notifyStatus = $data['notifyStatus'];
    }

    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    public function getNotifyId(): string
    {
        return $this->notifyId;
    }

    public function getNotifyStatus(): string
    {
        return $this->notifyStatus;
    }

    /**
     * @param array $data
     */
    private function validate(array &$data): void
    {
        $errors = [];

        if (empty($data['documentId']) || !is_numeric($data['documentId'])) {
            $errors[] = 'Data doesn\'t contain a numeric documentId';
        }

        if (empty($data['notifyId'])) {
            $errors[] = 'Data doesn\'t contain a notifyId';
        }

        if (empty($data['notifyStatus'])) {
            $errors[] = 'Data doesn\'t contain a notifyStatus';
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
    }
}
