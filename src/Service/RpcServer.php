<?php

namespace App\Service;

use PhpAmqpLib\Message\AMQPMessage;
use function GuzzleHttp\json_decode;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

class RpcServer
{
    private $logger;
    private $rpcKernel;
    private $req;
    private $res;
    private $mem;
    private $maxMem;
    private $body;
    private $route;
    private $request;
    public function __construct(LoggerInterface $logger)
    {
        global $kernel;
        $this->rpcKernel = clone $kernel;
        $this->logger = $logger;
        $this->maxMem = (int) ini_get('memory_limit');
        echo 'init: clone kernel.';
    }
    public function execute(AMQPMessage $message)
    {
        // handle message
        try {
            $this->body = json_decode($message->getBody(), true);

            $this->route = $this->body['route'];
            $this->request = $this->body['request'];
        } catch (\Exception $e) {
            $this->logger->error('AMQP:' . $message->getBody());
            return $e->getMessage();
        }

        try {
            $this->req = Request::create($this->route, 'POST', $this->request);
            $this->res = $this->rpcKernel->handle($this->req, 1, false);

            echo "\n handle: $this->route";
            $this->logger->info("\nhandle: $this->route" . $this->res->getContent());

            return json_decode($this->res->getContent(), true);
        } catch (\Exception $e) {
            $this->logger->error("\nerror: " . $e->getMessage(), $e->getTrace());
            echo "\n error: " . $e->getMessage();
            return $e->getMessage();
        } finally {
            $this->rpcKernel->reboot(null);
            $this->mem =round(memory_get_usage()/1048476, 0);
            echo "\n consume: [mem: $this->mem/$this->maxMem]";
            if ($this->mem/$this->maxMem > 0.8) {
                exit();
            }
        }
    }
}
