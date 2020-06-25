<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi\Dependency\Client;

use Generated\Shared\Transfer\OauthAccessTokenValidationRequestTransfer;
use Generated\Shared\Transfer\OauthAccessTokenValidationResponseTransfer;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Generated\Shared\Transfer\OauthResponseTransfer;
use Generated\Shared\Transfer\RevokeRefreshTokenResponseTransfer;

class AuthRestApiToOauthClientBridge implements AuthRestApiToOauthClientInterface
{
    /**
     * @var \Spryker\Client\Oauth\OauthClientInterface
     */
    protected $oauthClient;

    /**
     * @param \Spryker\Client\Oauth\OauthClientInterface $oauthClient
     */
    public function __construct($oauthClient)
    {
        $this->oauthClient = $oauthClient;
    }

    /**
     * @param \Generated\Shared\Transfer\OauthRequestTransfer $oauthRequestTransfer
     *
     * @return \Generated\Shared\Transfer\OauthResponseTransfer
     */
    public function processAccessTokenRequest(OauthRequestTransfer $oauthRequestTransfer): OauthResponseTransfer
    {
        return $this->oauthClient->processAccessTokenRequest($oauthRequestTransfer);
    }

    /**
     * @deprecated Use {@link \Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToOauthClientBridge::validateOauthAccessToken()} instead.
     *
     * @param \Generated\Shared\Transfer\OauthAccessTokenValidationRequestTransfer $authAccessTokenValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\OauthAccessTokenValidationResponseTransfer
     */
    public function validateAccessToken(
        OauthAccessTokenValidationRequestTransfer $authAccessTokenValidationRequestTransfer
    ): OauthAccessTokenValidationResponseTransfer {
        return $this->oauthClient->validateAccessToken($authAccessTokenValidationRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\OauthAccessTokenValidationRequestTransfer $authAccessTokenValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\OauthAccessTokenValidationResponseTransfer
     */
    public function validateOauthAccessToken(
        OauthAccessTokenValidationRequestTransfer $authAccessTokenValidationRequestTransfer
    ): OauthAccessTokenValidationResponseTransfer {
        return $this->oauthClient->validateOauthAccessToken($authAccessTokenValidationRequestTransfer);
    }

    /**
     * @param string $refreshTokenIdentifier
     * @param string $customerReference
     *
     * @return \Generated\Shared\Transfer\RevokeRefreshTokenResponseTransfer
     */
    public function revokeRefreshToken(string $refreshTokenIdentifier, string $customerReference): RevokeRefreshTokenResponseTransfer
    {
        return $this->oauthClient->revokeRefreshToken($refreshTokenIdentifier, $customerReference);
    }

    /**
     * @param string $customerReference
     *
     * @return \Generated\Shared\Transfer\RevokeRefreshTokenResponseTransfer
     */
    public function revokeAllRefreshTokens(string $customerReference): RevokeRefreshTokenResponseTransfer
    {
        return $this->oauthClient->revokeAllRefreshTokens($customerReference);
    }
}
