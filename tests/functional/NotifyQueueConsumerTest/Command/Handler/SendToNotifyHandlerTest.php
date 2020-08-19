<?php

declare(strict_types=1);

namespace NotifyQueueConsumerTest\Functional\Command\Handler;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use NotifyQueueConsumer\Command\Handler\SendToNotifyHandler;
use NotifyQueueConsumer\Queue\DuplicateMessageException;
use PHPUnit\Framework\TestCase;
use NotifyQueueConsumer\Command\Model\SendToNotify;
use VCR\VCR;

class SendToNotifyHandlerTest extends TestCase
{
    private const TEST_FILE_PATH = __DIR__ . '/../../../../fixtures/sample_doc.pdf';
    private Filesystem $filesystem;
    private SendToNotifyHandler $handler;

    public function setUp(): void
    {
        // These services are defined in src/bootstrap/services.php and are included in tests/bootstrap.php
        global $filesystem, $notifyClient;

        parent::setUp();
        VCR::turnOn();

        $this->filesystem = $filesystem;
        $this->handler = new SendToNotifyHandler($this->filesystem, $notifyClient);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        VCR::turnOff();
    }

    /**
     * @throws FileNotFoundException
     */
    public function testHandleSuccess(): void
    {
        VCR::insertCassette('test_sendtonotifyhandler_handle_success.yml');
        $content = file_get_contents(self::TEST_FILE_PATH);
        $destination = basename(self::TEST_FILE_PATH);

        $this->filesystem->put($destination, $content);

        $command = SendToNotify::fromArray(
            [
                'id' => '123',
                'uuid' => 'test-handle-success-20200818175308',
                'filename' => $destination,
                'documentId' => '1234',
            ]
        );

        $updateDocumentStatus = $this->handler->handle($command);

        VCR::eject();

        self::assertNotEmpty($updateDocumentStatus->getNotifyId());
        self::assertNotEmpty($updateDocumentStatus->getNotifyStatus());
    }

    /**
     * @throws FileNotFoundException
     */
    public function testDuplicateUuidThrowsExceptionFailure(): void
    {
        self::expectException(DuplicateMessageException::class);

        VCR::insertCassette('test_sendtonotifyhandler_duplicate_uuid_throws_exception_failure.yml');
        $content = file_get_contents(self::TEST_FILE_PATH);
        $destination = basename(self::TEST_FILE_PATH);
        $this->filesystem->put($destination, $content);

        // Contains a uuid we sent previously
        $command = SendToNotify::fromArray(
            [
                'id' => '123',
                'uuid' => 'test-handle-success-20200818175307',
                'filename' => $destination,
                'documentId' => '1234',
            ]
        );

        $this->handler->handle($command);

        VCR::eject();
    }
}
