<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Nanofelis\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcInternalException;
use Nanofelis\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Exception\RpcServerErrorException;
use Nanofelis\JsonRpcBundle\Service\ServiceAnnotationReader;
use Nanofelis\JsonRpcBundle\Service\ServiceLocator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RpcRequestHandler
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var ServiceAnnotationReader
     */
    private $annotationReader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RpcRequestHandler constructor.
     *
     * @param ServiceLocator           $serviceLocator
     * @param ServiceAnnotationReader  $annotationReader
     * @param EventDispatcherInterface $eventDispatcher
     * @param NormalizerInterface      $normalizer
     */
    public function __construct(
        ServiceLocator $serviceLocator,
        ServiceAnnotationReader $annotationReader,
        EventDispatcherInterface $eventDispatcher,
        NormalizerInterface $normalizer
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->dispatcher = $eventDispatcher;
        $this->normalizer = $normalizer;
        $this->annotationReader = $annotationReader;
        $this->logger = new NullLogger();
    }

    /**
     * @required
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param RPCRequestPayload $payload
     *
     * @return array|bool|float|int|string
     *
     * @throws AbstractRpcException
     * @throws ExceptionInterface
     * @throws RpcMethodNotFoundException
     */
    public function handle(RPCRequestPayload $payload)
    {
        $service = $this->serviceLocator->find($payload);

        $this->dispatcher->dispatch(RpcBeforeMethodEvent::NAME, new RpcBeforeMethodEvent($payload, $service));

        $return = $this->execute($service, $payload);

        $contexts = $this->annotationReader->getDenormalizationContexts($service, $payload->getMethod());

        return $this->normalizer->normalize($return, null, $contexts);
    }

    /**
     * @param                   $service
     * @param RpcRequestPayload $payload
     *
     * @return mixed
     *
     * @throws RpcInvalidRequestException
     * @throws RpcServerErrorException
     */
    private function execute($service, RpcRequestPayload $payload)
    {
        $method = $payload->getMethod();

        try {
            return $service->$method(...array_values($payload->getParams() ?: []));
        } catch (\TypeError $e) {
            throw new RpcInvalidRequestException(sprintf('invalid types for method %s of service %s %s', $method, $payload->getServiceId(), $this->debug ? $e->getMessage() : ''));
        } catch (\InvalidArgumentException $e) {
            throw new RpcInvalidRequestException($e->getMessage(), $e);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[%s] %s.%s : %s', $e->getCode(), $payload->getServiceId(), $method, $e->getMessage()));

            throw new RpcServerErrorException($e->getMessage(), $e->getCode(), $e);
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('[%s] %s.%s : %s', $e->getCode(), $payload->getServiceId(), $method, $e->getMessage()));

            throw new RpcServerErrorException($this->debug ? $e->getMessage() : '', $e->getCode(), $e);
        }
    }
}
