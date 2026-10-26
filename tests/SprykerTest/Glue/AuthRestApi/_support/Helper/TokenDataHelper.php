<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Glue\AuthRestApi\Helper;

use Codeception\Module;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\RestAccessTokensAttributesTransfer;
use Generated\Shared\Transfer\RestRefreshTokensAttributesTransfer;
use PHPUnit\Framework\Assert;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;
use SprykerTest\ApiPlatform\Helper\ApiRequestHelperTrait;
use SprykerTest\Shared\Customer\Helper\CustomerDataHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * The customer the token resources authenticate, and the token requests themselves.
 *
 * Tokens are obtained the way a client obtains them - by posting credentials to `/access-tokens` -
 * so a refresh-token test runs against a token pair the stack itself issued rather than a
 * fabricated one.
 */
class TokenDataHelper extends Module
{
    use ApiRequestHelperTrait;

    protected const string URI_ACCESS_TOKENS = '/access-tokens';

    protected const string URI_REFRESH_TOKENS = '/refresh-tokens';

    protected const string HEADER_AUTHORIZATION = 'Authorization';

    protected const string TOKEN_TYPE_BEARER = 'Bearer';

    /**
     * Long enough and mixed enough to pass the registration policy the confirmed-customer fixture
     * runs through, and reused by the token request so the grant sees the password it was created
     * with.
     */
    public const string CUSTOMER_PASSWORD = '$SecureP@ssw0rd123#';

    public function arrangeConfirmedCustomer(): CustomerTransfer
    {
        return $this->getCustomerDataHelper()->haveConfirmedCustomer([
            CustomerTransfer::PASSWORD => static::CUSTOMER_PASSWORD,
            CustomerTransfer::NEW_PASSWORD => static::CUSTOMER_PASSWORD,
        ]);
    }

    protected function getCustomerDataHelper(): CustomerDataHelper
    {
        /** @var \SprykerTest\Shared\Customer\Helper\CustomerDataHelper $customerDataHelper */
        $customerDataHelper = $this->getModule('\\' . CustomerDataHelper::class);

        return $customerDataHelper;
    }

    /**
     * @return array<string, mixed> The `access-tokens` attributes of a freshly issued token pair.
     */
    public function arrangeIssuedTokenPair(): array
    {
        return $this->issueTokenPair($this->arrangeConfirmedCustomer());
    }

    /**
     * Several pairs for one customer, so a revocation that covers all of them has more than one
     * token to prove it on - with a single pair the outcome is indistinguishable from revoking
     * that one token.
     *
     * @return array<int, array<string, mixed>> The `access-tokens` attributes of each issued pair.
     */
    public function arrangeIssuedTokenPairs(int $count): array
    {
        $customerTransfer = $this->arrangeConfirmedCustomer();
        $issuedTokenPairs = [];

        for ($issued = 0; $issued < $count; $issued++) {
            $issuedTokenPairs[] = $this->issueTokenPair($customerTransfer);
        }

        return $issuedTokenPairs;
    }

    /**
     * @return array<string, mixed>
     */
    protected function issueTokenPair(CustomerTransfer $customerTransfer): array
    {
        $response = $this->postAccessToken((string)$customerTransfer->getEmail(), static::CUSTOMER_PASSWORD);

        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode(), (string)$response->getContent());

        return $this->readAttributes($response);
    }

    /**
     * @param array<string, string> $headers
     */
    public function postAccessToken(string $username, string $password, array $headers = []): Response
    {
        return $this->handleApiRequest(
            'POST',
            static::URI_ACCESS_TOKENS,
            $this->encodeJsonApiBody(AuthRestApiConfig::RESOURCE_ACCESS_TOKENS, [
                RestAccessTokensAttributesTransfer::USERNAME => $username,
                RestAccessTokensAttributesTransfer::PASSWORD => $password,
            ]),
            $headers,
        );
    }

    /**
     * Exchanges a refresh token for a new pair.
     */
    public function postRefreshToken(string $refreshToken): Response
    {
        return $this->handleApiRequest(
            'POST',
            static::URI_REFRESH_TOKENS,
            $this->encodeJsonApiBody(AuthRestApiConfig::RESOURCE_REFRESH_TOKENS, [
                RestRefreshTokensAttributesTransfer::REFRESH_TOKEN => $refreshToken,
            ]),
        );
    }

    /**
     * Revokes one refresh token, authenticated with the access token that came with it. Pass no
     * access token for the unauthenticated case.
     */
    public function deleteRefreshToken(string $refreshToken, ?string $accessToken = null): Response
    {
        return $this->handleApiRequest(
            'DELETE',
            sprintf('%s/%s', static::URI_REFRESH_TOKENS, rawurlencode($refreshToken)),
            null,
            $accessToken === null ? [] : [static::HEADER_AUTHORIZATION => static::TOKEN_TYPE_BEARER . ' ' . $accessToken],
        );
    }

    /**
     * Revokes every refresh token the access token's customer holds.
     */
    public function deleteAllRefreshTokens(string $accessToken): Response
    {
        return $this->deleteRefreshToken(AuthRestApiConfig::COLLECTION_IDENTIFIER_CURRENT_USER, $accessToken);
    }

    /**
     * @return array<string, mixed>
     */
    protected function readAttributes(Response $response): array
    {
        /** @var array{data: array{attributes: array<string, mixed>}} $document */
        $document = json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $document['data']['attributes'];
    }
}
