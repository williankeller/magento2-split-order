<?php

namespace Magestat\SplitOrder\Block\Checkout;

use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Class Success
 * Overriding Magento One page success
 */
class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param Config $orderConfig
     * @param HttpContext $httpContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $orderConfig,
            $httpContext,
            $data
        );
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return bool|array
     */
    public function getOrderArray()
    {
        $splitOrders = $this->checkoutSession->getOrderIds();
        $this->checkoutSession->unsOrderIds();

        if (empty($splitOrders) || count($splitOrders) <= 1) {
            return false;
        }
        return $splitOrders;
    }
}
