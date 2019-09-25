<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Glue\AuthRestApi\Helper;

use Codeception\Exception\ModuleException;
use Codeception\Module;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Generated\Shared\Transfer\OauthResponseTransfer;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;
use SprykerTest\Glue\Testify\Helper\GlueRest;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;
use SprykerTest\Shared\Testify\Helper\ModuleLocatorTrait;

class AuthRestApiHelper extends Module
{
    use LocatorHelperTrait;
    use ModuleLocatorTrait;

    /**
     * @var \SprykerTest\Glue\Testify\Helper\GlueRest|null
     */
    protected $glueRestProvider;

    /**
     * @inheritdoc
     */
    public function _initialize(): void
    {
        $this->glueRestProvider = $this->getGlueRestProvider();
    }

    /**
     * Specification:
     * - Sets bearer token.
     *
     * @part json
     *
     * @param string $token
     *
     * @return void
     */
    public function amAuthorizedGlueUser(string $token): void
    {
        $this->glueRestProvider->amBearerAuthenticated($token);
    }

    /**
     * Specification:
     * - Sets X-Anonymous-Customer-Unique-Id header.
     *
     * @part json
     *
     * @param string $value
     *
     * @return void
     */
    public function amUnauthorizedGlueUser(string $value): void
    {
        $this->glueRestProvider->haveHttpHeader('X-Anonymous-Customer-Unique-Id', $value);
    }

    /**
     * Specification:
     * - Authorizes customer and returns OauthResponseTransfer.
     * - Returns OauthResponseTransfer with error if authorization failed.
     *
     * @part json
     *
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     * @param string|null $anonymousCustomerReference
     *
     * @return \Generated\Shared\Transfer\OauthResponseTransfer
     */
    public function haveAuthorizationToGlue(CustomerTransfer $customerTransfer, $anonymousCustomerReference = null): OauthResponseTransfer
    {
        $oauthRequestTransfer = (new OauthRequestTransfer())
            ->setGrantType('password')
            ->setUsername($customerTransfer->getEmail())
            ->setPassword($customerTransfer->getNewPassword());

        if ($anonymousCustomerReference) {
            $oauthRequestTransfer->setCustomerReference($anonymousCustomerReference);
        }

        return $this->getLocator()
            ->authRestApi()
            ->facade()
            ->processAccessToken($oauthRequestTransfer);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     *
     * @return \SprykerTest\Glue\Testify\Helper\GlueRest
     */
    protected function getGlueRestProvider(): GlueRest
    {
        foreach ($this->getModules() as $module) {
            if ($module instanceof GlueRest) {
                return $module;
            }
        }

        throw new ModuleException('AuthRestApi', 'The module requires GlueRest.');
    }
}
