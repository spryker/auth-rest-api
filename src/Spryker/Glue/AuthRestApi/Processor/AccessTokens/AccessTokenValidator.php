<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi\Processor\AccessTokens;

use Generated\Shared\Transfer\OauthAccessTokenValidationRequestTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use InvalidArgumentException;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;
use Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToOauthClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenValidator implements AccessTokenValidatorInterface
{
    /**
     * @var string
     */
    protected const REQUEST_ATTRIBUTE_IS_PROTECTED = 'is-protected';

    /**
     * @var \Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToOauthClientInterface
     */
    protected $oauthClient;

    public function __construct(AuthRestApiToOauthClientInterface $oauthClient)
    {
        $this->oauthClient = $oauthClient;
    }

    public function validate(Request $request): ?RestErrorMessageTransfer
    {
        $isProtected = $request->attributes->get(static::REQUEST_ATTRIBUTE_IS_PROTECTED, false);
        $authorizationToken = $request->headers->get(AuthRestApiConfig::HEADER_AUTHORIZATION);

        if (!$authorizationToken && $isProtected) {
            return $this->createErrorMessageTransfer(
                AuthRestApiConfig::RESPONSE_DETAIL_MISSING_ACCESS_TOKEN,
                Response::HTTP_FORBIDDEN,
                AuthRestApiConfig::RESPONSE_CODE_FORBIDDEN,
            );
        }

        if (!$authorizationToken) {
            return null;
        }

        if (!$this->validateAccessToken($authorizationToken)) {
            return $this->createErrorMessageTransfer(
                AuthRestApiConfig::RESPONSE_DETAIL_INVALID_ACCESS_TOKEN,
                Response::HTTP_UNAUTHORIZED,
                AuthRestApiConfig::RESPONSE_CODE_ACCESS_CODE_INVALID,
            );
        }

        return null;
    }

    protected function createErrorMessageTransfer(
        string $detail,
        int $status,
        string $code
    ): RestErrorMessageTransfer {
        return (new RestErrorMessageTransfer())
            ->setDetail($detail)
            ->setStatus($status)
            ->setCode($code);
    }

    /**
     * @param string $authorizationToken
     *
     * @throws \InvalidArgumentException
     *
     * @return string|null
     */
    protected function extractToken(string $authorizationToken): ?string
    {
        $result = preg_split('/\s+/', $authorizationToken);
        if ($result === false) {
            throw new InvalidArgumentException('Not a valid token, cannot `preg_split()` it.');
        }

        return $result[1] ?? null;
    }

    /**
     * @param string $authorizationToken
     *
     * @throws \InvalidArgumentException
     *
     * @return string|null
     */
    protected function extractTokenType(string $authorizationToken): ?string
    {
        $result = preg_split('/\s+/', $authorizationToken);
        if ($result === false) {
            throw new InvalidArgumentException('Not a valid token, cannot `preg_split()` it.');
        }

        return $result[0] ?? null;
    }

    protected function validateAccessToken(string $authorizationToken): bool
    {
        $accessToken = $this->extractToken($authorizationToken);
        $type = $this->extractTokenType($authorizationToken);
        if (!$accessToken || !$type) {
            return false;
        }

        $authAccessTokenValidationRequestTransfer = new OauthAccessTokenValidationRequestTransfer();
        $authAccessTokenValidationRequestTransfer
            ->setAccessToken($accessToken)
            ->setType($type);

        return $this->oauthClient->validateOauthAccessToken($authAccessTokenValidationRequestTransfer)->getIsValid();
    }
}
