<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AuthRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\AccessTokensStorefrontResource;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\AuthRestApi\AuthRestApiClientInterface;
use Spryker\Glue\AuthRestApi\Api\Storefront\Exception\AuthRestApiExceptionFactory;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;

class AccessTokensStorefrontProcessor extends AbstractStorefrontProcessor
{
    /**
     * @uses \Spryker\Shared\PersistentCart\PersistentCartConfig::PERSISTENT_CART_ANONYMOUS_PREFIX
     */
    protected const string ANONYMOUS_CUSTOMER_REFERENCE_PREFIX = 'anonymous:';

    public function __construct(
        protected AuthRestApiClientInterface $authRestApiClient,
        protected AuthRestApiExceptionFactory $exceptionFactory = new AuthRestApiExceptionFactory(),
    ) {
    }

    protected function processPost(mixed $data): mixed
    {
        return $this->processPasswordGrant($data);
    }

    protected function processPasswordGrant(AccessTokensStorefrontResource $resource): AccessTokensStorefrontResource
    {
        $oauthRequestTransfer = (new OauthRequestTransfer())
            ->setGrantType(AuthRestApiConfig::CLIENT_GRANT_PASSWORD)
            ->setUsername($resource->username)
            ->setPassword($resource->password)
            // Propagate the anonymous customer identifier so post-auth plugins can
            // merge the guest cart into the customer cart on login. The prefix matches
            // the customer reference stored on the guest quote — see
            // CartsRestApi\Plugin\ControllerBeforeAction\SetAnonymousCustomerIdControllerBeforeActionPlugin
            // which fulfils the same role on the legacy Glue stack.
            ->setCustomerReference($this->resolveAnonymousCustomerReference());

        $oauthResponseTransfer = $this->authRestApiClient->createAccessToken($oauthRequestTransfer);

        if ($oauthResponseTransfer->getIsValid() === false) {
            throw $this->exceptionFactory->createInvalidLoginException();
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
}
