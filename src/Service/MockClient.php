<?php

namespace App\Service;

use Ramsey\Uuid\Uuid;
use OldSound\RabbitMqBundle\RabbitMq\RpcClient;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\json_decode;
use Psr\Log\LoggerInterface;

class MockClient
{
    private $rpcClient;
    private $logger;
    public function __construct(RpcClient $rpcClient, LoggerInterface $logger)
    {
        $this->rpcClient = $rpcClient;
        $this->logger = $logger;
    }

    public function request($service, $route, $request)
    {
        $rId = Uuid::uuid4()->toString();
        // dd($rId);
        $this->rpcClient->addRequest(json_encode([
            'route' => $route,
            'request' => $request,
        ]), $service, $rId, '', 5);

        $res = $this->rpcClient->getReplies()[$rId];
        if (is_string($res)) {
            $this->logger->error($res);
            throw new \Exception($res);
        }

        return $res;
    }
}
