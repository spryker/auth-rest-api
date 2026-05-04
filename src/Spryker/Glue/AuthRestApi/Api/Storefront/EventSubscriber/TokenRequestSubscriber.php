<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AuthRestApi\Api\Storefront\EventSubscriber;

use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\ApiPlatform\Attribute\ApiType;
use Spryker\Client\AuthRestApi\AuthRestApiClientInterface;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\OauthToken;
use Spryker\Glue\AuthRestApi\Processor\Logger\AuditLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handles the OAuth 2.0 `/token` endpoint which speaks the RFC 6749 wire format
 * (`application/x-www-form-urlencoded` request body, plain JSON response with snake_case
 * keys) instead of JSON:API. Intercepts the request before Symfony routing and returns
 * the response produced by {@see OauthToken}, bypassing the API Platform pipeline entirely.
 */
#[ApiType(types: ['storefront'])]
class TokenRequestSubscriber implements EventSubscriberInterface
{
    protected const string TOKEN_PATH = '/token';

    protected const int PRIORITY_BEFORE_ROUTER = 100;

    /**
     * @uses \Spryker\Shared\PersistentCart\PersistentCartConfig::PERSISTENT_CART_ANONYMOUS_PREFIX
     */
    protected const string ANONYMOUS_CUSTOMER_REFERENCE_PREFIX = 'anonymous:';

    public function __construct(
        protected AuthRestApiClientInterface $authRestApiClient,
    ) {
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', static::PRIORITY_BEFORE_ROUTER],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->getMethod() !== Request::METHOD_POST) {
            return;
        }

        if (rtrim($request->getPathInfo(), '/') !== static::TOKEN_PATH) {
            return;
        }

        $oauthRequestTransfer = (new OauthRequestTransfer())
            ->fromArray($request->request->all(), true)
            // Propagate the anonymous customer identifier so post-auth plugins can
            // merge the guest cart into the customer cart on login/refresh. The prefix
            // matches the customer reference stored on the guest quote.
            ->setCustomerReference($this->resolveAnonymousCustomerReference($request));

        $event->setResponse(
            (new OauthToken($this->authRestApiClient, new AuditLogger()))
                ->createAccessToken($oauthRequestTransfer),
        );
    }

    protected function resolveAnonymousCustomerReference(Request $request): ?string
    {
        $anonymousCustomerUniqueId = $request->headers->get(AuthRestApiConfig::HEADER_ANONYMOUS_CUSTOMER_UNIQUE_ID);

        if ($anonymousCustomerUniqueId === null) {
            return null;
        }

        return static::ANONYMOUS_CUSTOMER_REFERENCE_PREFIX . $anonymousCustomerUniqueId;
    }
}
