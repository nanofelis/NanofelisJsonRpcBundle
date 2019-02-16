<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Response;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Validator\Constraints\RequestPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RpcResponseError extends JsonResponse
{
    const HTTP_ERROR_MAPPING = [
        AbstractRpcException::METHOD_NOT_FOUND => Response::HTTP_NOT_FOUND,
        AbstractRpcException::INVALID_REQUEST => Response::HTTP_BAD_REQUEST,
        AbstractRpcException::PARSE => Response::HTTP_INTERNAL_SERVER_ERROR,
        AbstractRpcException::INVALID_PARAMS => Response::HTTP_INTERNAL_SERVER_ERROR,
        AbstractRpcException::INTERNAL => Response::HTTP_INTERNAL_SERVER_ERROR,
    ];

    /**
     * @param AbstractRpcException $e
     * @param string[]             $headers
     */
    public function __construct(AbstractRpcException $e, array $headers = [])
    {
        $return = [
            'jsonrpc' => RequestPayload::JSON_RPC_VERSION,
            'error' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'data' => $e->getData(),
            ],
        ];
        $payload = $e->getPayload();

        if ($payload) {
            $return['id'] = $payload->getId();
        }

        parent::__construct($return, $this->getHttpStatus($e), $headers);
    }

    private function getHttpStatus(AbstractRpcException $e): int
    {
        return self::HTTP_ERROR_MAPPING[$e->getCode()] ?? Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
