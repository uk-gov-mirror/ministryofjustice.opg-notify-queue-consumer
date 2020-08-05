<?php

declare(strict_types=1);

namespace NotifyQueueConsumer\Command\Model;

use InvalidArgumentException;

class SendToNotify
{
    protected string $id;
    protected string $uuid;
    protected string $filename;
    protected int $documentId;

    /**
     * @param array<string,string> $data
     */
    public function __construct(array $data)
    {
        $this->validate($data);

        $this->id = $data['id'];
        $this->uuid = $data['uuid'];
        $this->filename = $data['filename'];
        $this->documentId = (int)$data['documentId'];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    /**
     * @param array $data
     */
    private function validate(array &$data): void
    {
        $errors = [];

        if (empty($data['id'])) {
            $errors[] = 'Data doesn\'t contain an id';
        }

        if (empty($data['uuid'])) {
            $errors[] = 'Data doesn\'t contain a uuid';
        }

        if (empty($data['filename'])) {
            $errors[] = 'Data doesn\'t contain a filename';
        }

        if (empty($data['documentId']) || !is_numeric($data['documentId'])) {
            $errors[] = 'Data doesn\'t contain a numeric documentId';
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
    }
}
