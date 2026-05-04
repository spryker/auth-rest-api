<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AuthRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\RefreshTokensStorefrontResource;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Oauth\OauthClientInterface;
use Spryker\Glue\AuthRestApi\Api\Storefront\Exception\AuthRestApiExceptionFactory;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;

class RefreshTokensStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string KEY_REFRESH_TOKEN = 'refreshToken';

    /**
     * @uses \Spryker\Shared\PersistentCart\PersistentCartConfig::PERSISTENT_CART_ANONYMOUS_PREFIX
     */
    protected const string ANONYMOUS_CUSTOMER_REFERENCE_PREFIX = 'anonymous:';

    public function __construct(
        protected OauthClientInterface $oauthClient,
        protected AuthRestApiExceptionFactory $exceptionFactory = new AuthRestApiExceptionFactory(),
    ) {
    }

    protected function processPost(mixed $data): mixed
    {
        return $this->processRefreshTokenGrant($data);
    }

    // Revocation is idempotent by design: the OAuth client silently succeeds for
    // unknown or cross-user tokens to avoid leaking token ownership information.
    // See legacy RefreshTokensRevoker which has the same shape (no error handling).

    protected function processDelete(): mixed
    {
        $customerReference = $this->resolveCustomerReference();
        $refreshToken = (string)$this->getUriVariable(static::KEY_REFRESH_TOKEN);

        if ($refreshToken === AuthRestApiConfig::COLLECTION_IDENTIFIER_CURRENT_USER) {
            $this->oauthClient->revokeAllRefreshTokens($customerReference);

            return null;
        }

        $this->oauthClient->revokeRefreshToken($refreshToken, $customerReference);

        return null;
    }

    protected function processRefreshTokenGrant(RefreshTokensStorefrontResource $resource): RefreshTokensStorefrontResource
    {
        $oauthRequestTransfer = (new OauthRequestTransfer())
            ->setGrantType(AuthRestApiConfig::CLIENT_GRANT_REFRESH_TOKEN)
            ->setRefreshToken($resource->refreshToken)
            // Propagate the anonymous customer identifier so post-auth plugins can
            // merge the guest cart into the customer cart on token refresh. The prefix
            // matches the customer reference stored on the guest quote.
            ->setCustomerReference($this->resolveAnonymousCustomerReference());

        $oauthResponseTransfer = $this->oauthClient->processAccessTokenRequest($oauthRequestTransfer);

        if ($oauthResponseTransfer->getIsValid() === false) {
            throw $this->exceptionFactory->createInvalidRefreshTokenException();
        }

        $resource->accessToken = $oauthResponseTransfer->getAccessToken();
        $resource->tokenType = $oauthResponseTransfer->getTokenType();
        $resource->expiresIn = $oauthResponseTransfer->getExpiresIn();
        $resource->refreshToken = $oauthResponseTransfer->getRefreshToken();
        $resource->idCompanyUser = $oauthResponseTransfer->getIdCompanyUser();

        return $resource;
    }

    protected function resolveAnonymousCustomerReference(): ?string
    {
        $anonymousCustomerUniqueId = $this->getRequest()
            ->headers
            ->get(AuthRestApiConfig::HEADER_ANONYMOUS_CUSTOMER_UNIQUE_ID);

        if ($anonymousCustomerUniqueId === null) {
            return null;
        }

        return static::ANONYMOUS_CUSTOMER_REFERENCE_PREFIX . $anonymousCustomerUniqueId;
    }

    protected function resolveCustomerReference(): string
    {
        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createMissingAccessTokenException();
        }

        return $this->getCustomerReference();
    }
}
