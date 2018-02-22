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

namespace Magestat\SplitOrder\Plugin;

class QuoteManagement
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory    = $quoteFactory;
        $this->eventManager    = $eventManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param object $quote
     * @return object
     */
    protected function _getBillingAddressData($quote)
    {
        $payment = $quote->getBillingAddress()->getData();
        unset($payment['id']);
        unset($payment['quote_id']);

        return $payment;
    }

    /**
     * @param object $quote
     * @return object
     */
    protected function _getShippingAddressData($quote)
    {
        $shipping = $quote->getShippingAddress()->getData();
        unset($shipping['id']);
        unset($shipping['quote_id']);

        return $shipping;
    }

    /**
     * Separate all items in quote into new quotes.
     *
     * @param object $quote
     * @return array
     */
    protected function _normalizeQuotes($quote)
    {
        $groups = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $groups[$item->getProduct()->getSku()][] = $item;
        }
        return $groups;
    }

    /**
     * @param object $quote
     * @param object $split
     * @return $this
     */
    protected function _setCustomerData($quote, $split)
    {
        $split->setStoreId($quote->getStoreId());
        $split->setCustomer($quote->getCustomer());
        $split->setCustomerIsGuest($quote->getCustomerIsGuest());

        if ($quote->getCheckoutMethod() === \Magento\Quote\Api\CartManagementInterface::METHOD_GUEST) {
            $split->setCustomerId(null);
            $split->setCustomerEmail($quote->getBillingAddress()->getEmail());
            $split->setCustomerIsGuest(true);
            $split->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
        }
        return $this;
    }

    /**
     * Save quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param object $split
     * @return $this
     */
    protected function _toSaveQuote($quote)
    {
        $this->quoteRepository->save($quote);

        return $this;
    }
 
    /**
     * Recollect order totals.
     *
     * @param object $item
     * @param object $quote
     * @param object $billing
     * @param object $shipping
     * @param float $shippingAmount
     * @return $this
     */
    protected function _recollectTotal($item, $quote, $billing, $shipping, $shippingAmount = 0)
    {
        // Retrieve values.
        $tax        = $item->getData('tax_amount');
        $discount   = $item->getData('discount_amount');
        $itemPrice  = $item->getPrice();
        $itemQty    = $item->getQty();
        $finalPrice = ($itemPrice * $itemQty);

        // Set addresses.
        $quote->getBillingAddress()->setData($billing);
        $quote->getShippingAddress()->setData($shipping);

        // Add shipping amount if product is not virual.
        if ($quote->hasVirtualItems() === false) {
            $shippingAmount = $quote->getShippingAddress()->getShippingAmount();
        }

        // Recollect totals into the quote.
        foreach ($quote->getAllAddresses() as $address) {
            // Build grand total.
            $grandTotal = (($finalPrice + $shippingAmount + $tax) - $discount);

            $address->setBaseSubtotal($finalPrice);
            $address->setSubtotal($finalPrice);
            $address->setDiscountAmount($discount);
            $address->setTaxAmount($tax);
            $address->setBaseTaxAmount($tax);
            $address->setBaseGrandTotal($grandTotal);
            $address->setGrandTotal($grandTotal);
        }
        return $this;
    }
 
    /**
     * Set payment method.
     *
     * @param object $paymentMethod
     * @param object $split
     * @param string $payment
     * @return $this
     */
    protected function _setPaymentMethod($paymentMethod, $split, $payment)
    {
        $split->getPayment()->setMethod($payment);
        if ($paymentMethod) {
            $split->getPayment()->setQuote($split);
            $data = $paymentMethod->getData();
            $split->getPayment()->importData($data);
        }
        return $this;
    }

    /**
     * Define checkout sessions.
     *
     * @param object $split
     * @param object $order
     * @param array $orderIds
     * @return $this
     */
    protected function _defineSessions($split, $order, $orderIds)
    {
        $this->checkoutSession->setLastQuoteId($split->getId());
        $this->checkoutSession->setLastSuccessQuoteId($split->getId());
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
        $this->checkoutSession->setOrderIds($orderIds);

        return $this;
    }

    /**
     * * Places an order for a specified cart.
     *
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param \Magestat\SplitOrder\Plugin\callable $proceed
     * @param int $cartId The cart ID.
     * @param PaymentInterface|null $paymentMethod
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundPlaceOrder(
        \Magento\Quote\Model\QuoteManagement $subject,
        callable $proceed,
        $cartId,
        $paymentMethod = null
    ) {
        $quote = $this->quoteRepository->getActive($cartId);

        // Separate all items in quote into new quotes.
        $quotes = $this->_normalizeQuotes($quote);
        if (count($quotes) < 2) {
            // Proceed as default if items is default number.
            return $result = $proceed($cartId, $paymentMethod);
        }

        // Get data from addresses.
        $pmethod  = $quote->getPayment()->getMethod();
        $payment  = $this->_getBillingAddressData($quote);
        $shipping = $this->_getShippingAddressData($quote);

        foreach ($quotes as $groups => $items) {
            // Init Quote Split.
            $split = $this->quoteFactory->create();

            // Set all customer definition data.
            $this->_setCustomerData($quote, $split);

            // Save splitted quote in order to have a quote item Id.
            $this->_toSaveQuote($split);

            // Map quote items.
            foreach ($items as $item) {
                // Add item by item.
                $item->setId(null);
                $split->addItem($item);
            }

            // Recollect order totals.
            $this->_recollectTotal($item, $split, $payment, $shipping);

            // Set payment method.
            $this->_setPaymentMethod($paymentMethod, $split, $pmethod);

            // Dispatch event as Magento standard once per each quote split.
            $this->eventManager->dispatch(
                'checkout_submit_before',
                ['quote' => $split]
            );

            $this->_toSaveQuote($split);
            $order = $subject->submit($split);

            $orders[] = $order;
            $orderIds[$order->getId()] = $order->getIncrementId();

            if (null == $order) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('An error occurred on the server. Please try to place the order again.')
                );
            }
        }
        // Disable origin quote.
        $quote->setIsActive(false);
        // To save quote.
        $this->_toSaveQuote($quote);

        // Define checkout sessions.
        $this->_defineSessions($split, $order, $orderIds);

        // Dispatch event.
        $this->eventManager->dispatch(
            'checkout_submit_all_after',
            ['orders' => $orders, 'quote' => $quote]
        );
        return $order->getId();
    }
}
