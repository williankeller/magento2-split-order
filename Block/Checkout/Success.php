<?php

/**
 * A Magento 2 module named Magestat/SplitOrder
 * Copyright (C) 2018 Magestat
 *
 * This file included in Magestat/SplitOrder is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Magestat\SplitOrder\Block\Checkout;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderFactory;
    
    protected $order;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_orderConfig     = $orderConfig;
        $this->_isScopePrivate  = true;
        $this->httpContext      = $httpContext;
        $this->orderFactory     = $orderFactory;
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }

    public function getOrderArray()
    {
        $checkoutSession = $this->_checkoutSession->getQuoteSplitted();
        $splittedOrders = explode(';', $checkoutSession);

        if (count($splittedOrders) <= 1) {
            return false;
        }

        return $this->prepareOrders($splittedOrders);
    }

    /**
     * Prepares block data
     *
     * @return false|array
     */
    public function prepareOrders($quotes, $result = [])
    {
        foreach ($quotes as $quote) {
            $order = $this->getLastRealOrders($quote);

            $result[] = [
                'is_order_visible' => $this->isVisible($order),
                'view_order_url' => $this->getUrl(
                    'sales/order/view/',
                    ['order_id' => $order->getEntityId()]
                ),
                'print_url' => $this->getUrl(
                    'sales/order/print',
                    ['order_id' => $order->getEntityId()]
                ),
                'can_print_order' => $this->isVisible($order),
                'can_view_order'  => $this->canViewOrder($order),
                'order_id'  => $order->getIncrementId()
            ];
        }
        return $result;
    }
    
    /**
     * Get order instance based on last order ID
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getLastRealOrders($orderId)
    {
        if ($this->order !== null && $orderId == $this->order->getIncrementId()) {
            return $this->order;
        }
        $this->order = $this->orderFactory->create();
        if ($orderId) {
            $this->order->loadByIncrementId((int) $orderId);
        }
        return $this->order;
    }
}
