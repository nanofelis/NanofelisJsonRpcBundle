<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\JsonRpcBundle\Exception\RpcParseException;
use Nanofelis\JsonRpcBundle\Response\RpcResponseError;
use Symfony\Component\HttpFoundation\Request;

class RpcRequestParser
{
    /**
     * @param int $maxBatchSize maximum number of requests accepted in a single batch call,
     *                          0 to disable the limit
     */
    public function __construct(private int $maxBatchSize = 0)
    {
    }

    public function parse(Request $request): RpcPayload
    {
        try {
            $data = $this->getPostData($request);

            return $this->getRpcPayload($data);
        } catch (AbstractRpcException $e) {
            $payload = new RpcPayload();
            $payload->addRpcResponse(new RpcResponseError($e));

            return $payload;
        }
    }

    /**
     * @return mixed[]
     *
     * @throws RpcParseException|RpcInvalidRequestException
     */
    private function getPostData(Request $request): array
    {
        try {
            $data = json_decode((string) $request->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new RpcParseException(previous: $e);
        }
        if (!\is_array($data)) {
            throw new RpcInvalidRequestException();
        }

        return $data;
    }

    /**
     * @param array<string|int,mixed> $data
     *
     * @throws RpcInvalidRequestException
     */
    private function getRpcPayload(mixed $data): RpcPayload
    {
        $payload = new RpcPayload();

        if (!array_is_list($data)) {
            $payload->addRpcRequest($this->getRpcRequest($data));

            return $payload;
        }

        // the spec requires a batch to be an array with at least one value; an empty one is a
        // batch-level failure, answered with a single response rather than an array of them
        if ([] === $data) {
            throw new RpcInvalidRequestException();
        }

        $payload->setIsBatch(true);

        $batchSize = \count($data);

        // an oversized batch is a batch-level failure too, so this keeps propagating to parse()
        if ($this->maxBatchSize > 0 && $batchSize > $this->maxBatchSize) {
            throw new RpcInvalidRequestException(\sprintf('batch size %d exceeds the maximum of %d', $batchSize, $this->maxBatchSize));
        }

        foreach ($data as $subData) {
            $subData = (array) $subData;

            // once the batch itself is recognised, a malformed entry only invalidates itself:
            // its siblings must still run, and it gets its own error response
            try {
                $payload->addRpcRequest($this->getRpcRequest($subData));
            } catch (AbstractRpcException $e) {
                $payload->addRpcResponse(new RpcResponseError($e, $this->extractId($subData)));
            }
        }

        return $payload;
    }

    /**
     * @param array<string|int,mixed> $data
     *
     * @throws RpcInvalidRequestException
     */
    private function getRpcRequest(array $data): RpcRequest
    {
        // guarded: a non-object batch entry has no 'jsonrpc' key at all, and reading it blindly
        // raises an "Undefined array key" warning before the invalid-request error is returned
        if (RpcRequest::JSON_RPC_VERSION !== ($data['jsonrpc'] ?? null)) {
            throw new RpcInvalidRequestException();
        }
        $methodParts = explode('.', $data['method'] ?? '');

        if (2 !== \count($methodParts)) {
            throw new RpcInvalidRequestException();
        }

        // RpcRequest types its id string|int|null, so a float or array id would raise a TypeError
        // under strict_types — not an AbstractRpcException, hence an HTTP 500 rather than an error
        if (null !== ($data['id'] ?? null) && null === $this->extractId($data)) {
            throw new RpcInvalidRequestException('id must be a string or an integer');
        }

        return new RpcRequest(
            serviceKey: $methodParts[0],
            methodKey: $methodParts[1],
            id: $this->extractId($data),
            params: $data['params'] ?? null
        );
    }

    /**
     * Best-effort id extraction for error responses: the spec mandates a null id whenever the id
     * of a malformed request cannot be determined.
     *
     * @param array<string|int,mixed> $data
     */
    private function extractId(array $data): string|int|null
    {
        $id = $data['id'] ?? null;

        return \is_string($id) || \is_int($id) ? $id : null;
    }
}
