<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Core\Client;

use Amazon\Core\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class ClientFactory implements ClientFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Data
     */
    protected $coreHelper;

    /**
     * @var string
     */
    protected $instanceName;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ClientFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param Data                   $coreHelper
     * @param LoggerInterface        $logger
     * @param string                 $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Data $coreHelper,
        LoggerInterface $logger,
        $instanceName = '\\AmazonPay\\ClientInterface'
    ) {
        $this->objectManager = $objectManager;
        $this->coreHelper    = $coreHelper;
        $this->instanceName  = $instanceName;
        $this->logger        = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function create($scopeId = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        $config = [
            Data::AMAZON_SECRET_KEY  => $this->coreHelper->getSecretKey($scope, $scopeId),
            Data::AMAZON_ACCESS_KEY  => $this->coreHelper->getAccessKey($scope, $scopeId),
            Data::AMAZON_MERCHANT_ID => $this->coreHelper->getMerchantId($scope, $scopeId),
            Data::AMAZON_REGION      => $this->coreHelper->getRegion($scope, $scopeId),
            Data::AMAZON_SANDBOX     => $this->coreHelper->isSandboxEnabled($scope, $scopeId),
            Data::AMAZON_CLIENT_ID   => $this->coreHelper->getClientId($scope, $scopeId)
        ];

        $client = $this->objectManager->create($this->instanceName, ['amazonConfig' => $config]);

        if ($client instanceof LoggerAwareInterface && $this->coreHelper->isLoggingEnabled($scope, $scopeId)) {
            $client->setLogger($this->logger);
        }

        return $client;
    }
}
