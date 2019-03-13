<?php

namespace App\Service;

use Transbank\Webpay\Configuration;
use Transbank\Webpay\Webpay;

/**
 * Class TransactionService
 * @package App\Service
 * @author Guillermo Quinteros <gu.quinteros@gmail.com>
 */
class TransactionService
{
    /**
     * @var []
     */
    private $settings;

    /**
     * TransactionService constructor.
     * @param $settings
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        $configuration = new Configuration();
        $configuration->setEnvironment($this->settings['environment']);
        $configuration->setCommerceCode($this->settings['commerce_code']);
        $configuration->setPrivateKey($this->settings['private_key']);
        $configuration->setPublicCert($this->settings['public_cert']);
        $configuration->setWebpayCert($this->settings['webpay_cert']);

        return $configuration;
    }

    /**
     * @return \Transbank\Webpay\WebPayNormal
     */
    public function getTransaction()
    {
        return (new Webpay($this->getConfiguration()))->getNormalTransaction();
    }

    /**
     * @return \Transbank\Webpay\WebPayNormal
     */
    public function getTestTransaction()
    {
        return (new Webpay(Configuration::forTestingWebpayPlusNormal()))->getNormalTransaction();
    }
}
