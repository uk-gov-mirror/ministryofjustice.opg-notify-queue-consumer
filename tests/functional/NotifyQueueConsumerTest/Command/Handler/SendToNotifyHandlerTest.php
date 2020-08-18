<?php

declare(strict_types=1);

namespace NotifyQueueConsumerTest\Functional\Command\Handler;

use Alphagov\Notifications\Client;
use Http\Discovery\Psr17FactoryDiscovery;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use NotifyQueueConsumer\Command\Handler\SendToNotifyHandler;
use PHPUnit\Framework\TestCase;
use NotifyQueueConsumer\Command\Model\SendToNotify;
use Http\Mock\Client as MockHttpClient;
use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Psr7 as Psr7;

class SendToNotifyHandlerTest extends TestCase
{
    private const TEST_FILE_PATH = __DIR__ . '/../../../../fixtures/sample_doc.pdf';
    private Filesystem $filesystem;
    private SendToNotifyHandler $handler;
    private Client $notifyClient;
    private MockHttpClient $mockHttpClient;

    public function setUp(): void
    {
        // These services are defined in src/bootstrap/services.php and are included in tests/bootstrap.php
        global $config, $filesystem, $notifyClient;

        parent::setUp();

        $this->mockHttpClient = new MockHttpClient();
        $this->filesystem = $filesystem;
        $this->notifyClient = new Client(
            [
                'apiKey' => $config['notify']['api_key'],
                'httpClient' => $this->mockHttpClient,
            ]
        );
//        $this->notifyClient = $notifyClient;
        $this->handler = new SendToNotifyHandler($this->filesystem, $this->notifyClient);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @throws FileNotFoundException
     */
    public function testHandleSuccess(): void
    {
        $headers = unserialize(
            'a:12:{s:28:"Access-Control-Allow-Headers";a:1:{i:0;s:26:"Content-Type,Authorization";}s:28:"Access-Control-Allow-Methods";a:1:{i:0;s:19:"GET,PUT,POST,DELETE";}s:27:"Access-Control-Allow-Origin";a:1:{i:0;s:1:"*";}s:12:"Content-Type";a:1:{i:0;s:16:"application/json";}s:4:"Date";a:1:{i:0;s:29:"Tue, 18 Aug 2020 14:37:32 GMT";}s:6:"Server";a:1:{i:0;s:5:"nginx";}s:25:"Strict-Transport-Security";a:1:{i:0;s:35:"max-age=31536000; includeSubdomains";}s:11:"X-B3-Spanid";a:1:{i:0;s:16:"892676658bf4165d";}s:12:"X-B3-Traceid";a:1:{i:0;s:16:"892676658bf4165d";}s:17:"X-Vcap-Request-Id";a:1:{i:0;s:36:"d93873ae-f25d-40dc-5127-fe1ccccaeaba";}s:14:"Content-Length";a:1:{i:0;s:3:"112";}s:10:"Connection";a:1:{i:0;s:10:"keep-alive";}}'
        );
        $response = Psr17FactoryDiscovery::findResponseFactory()
            ->createResponse(201, 'Created')
            ->withBody(
                Psr7\stream_for(
                    '{"id":"a392de5f-6f1b-458b-a12f-027c9ad2928d","postage":"second",'
                    . '"reference":"da94ad24f89389845dbad530ff3a88a8"}' . "\n"
                )
            );

        foreach ($headers as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
        }

        $this->mockHttpClient->addResponse($response);

        $content = file_get_contents(self::TEST_FILE_PATH);
        $destination = basename(self::TEST_FILE_PATH);
        $this->filesystem->put($destination, $content);
        $command = SendToNotify::fromArray(
            [
                'id' => '123',
                'uuid' => md5(__METHOD__ . '_' . microtime() . '_' . rand(1000, 10000000)),
                'filename' => $destination,
                'documentId' => '1234',
            ]
        );

        $updateDocumentStatus = $this->handler->handle($command);

        self::assertNotEmpty($updateDocumentStatus->getNotifyId());
        self::assertNotEmpty($updateDocumentStatus->getNotifyStatus());
    }
}
