<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Response;

use Nanofelis\JsonRpcBundle\Request\RpcRequestPayload;
use Nanofelis\JsonRpcBundle\Validator\Constraints\RequestPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RpcResponseOK extends JsonResponse
{
    /**
     * RpcResponseOK constructor.
     *
     * @param array|bool|float|int|string $data
     * @param RpcRequestPayload           $payload
     * @param array                       $headers
     */
    public function __construct($data, RpcRequestPayload $payload, array $headers = [])
    {
        $return = [
            'jsonrpc' => RequestPayload::JSON_RPC_VERSION,
            'result' => $data,
        ];

        if ($payloadId = $payload->getId()) {
            $return['id'] = $payloadId;
        }

        parent::__construct($return, Response::HTTP_OK, $headers);
    }
}
