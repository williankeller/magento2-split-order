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
        $billingAddress = $quote->getBillingAddress()->getData();
        unset($billingAddress['id']);
        unset($billingAddress['quote_id']);

        return $billingAddress;
    }

    /**
     * @param object $quote
     * @return object
     */
    protected function _getShippingAddressData($quote)
    {
        $shippingAddress = $quote->getShippingAddress()->getData();
        unset($shippingAddress['id']);
        unset($shippingAddress['quote_id']);

        return $shippingAddress;
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
     * @param object $splittedQuote
     * @return $this
     */
    protected function _setCustomerData($quote, $splittedQuote)
    {
        $splittedQuote->setStoreId($quote->getStoreId());
        $splittedQuote->setCustomer($quote->getCustomer());
        $splittedQuote->setCustomerIsGuest($quote->getCustomerIsGuest());

        if ($quote->getCheckoutMethod() === \Magento\Quote\Api\CartManagementInterface::METHOD_GUEST) {
            $splittedQuote->setCustomerId(null);
            $splittedQuote->setCustomerEmail($quote->getBillingAddress()->getEmail());
            $splittedQuote->setCustomerIsGuest(true);
            $splittedQuote->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
        }
        return $this;
    }

    /**
     * Save quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param object $splittedQuote
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
    protected function _recollectTotal($item, $quote, $billing, $shipping)
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
        $shippingAmount = $quote->getShippingAddress()->getShippingAmount();

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
     * @param object $splittedQuote
     * @param string $paymentString
     * @return $this
     */
    protected function _setPaymentMethod($paymentMethod, $splittedQuote, $paymentString)
    {
        $splittedQuote->getPayment()->setMethod($paymentString);
        if ($paymentMethod) {
            $splittedQuote->getPayment()->setQuote($splittedQuote);
            $data = $paymentMethod->getData();
            $splittedQuote->getPayment()->importData($data);
        }
        return $this;
    }

    /**
     * Define checkout sessions.
     *
     * @param object $splittedQuote
     * @param object $order
     * @param array $orderIds
     * @return $this
     */
    protected function _defineSessions($splittedQuote, $order, $orderIds)
    {
        $this->checkoutSession->setLastQuoteId($splittedQuote->getId());
        $this->checkoutSession->setLastSuccessQuoteId($splittedQuote->getId());
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
        $paymentString   = $quote->getPayment()->getMethod();
        $billingAddress  = $this->_getBillingAddressData($quote);
        $shippingAddress = $this->_getShippingAddressData($quote);

        foreach ($quotes as $groups => $items) {
            // Init Quote Split.
            $splittedQuote = $this->quoteFactory->create();

            // Set all customer definition data.
            $this->_setCustomerData($quote, $splittedQuote);

            // Save splitted quote in order to have a quote item Id.
            $this->_toSaveQuote($splittedQuote);

            // Map quote items.
            foreach ($items as $item) {
                // Add item by item.
                $item->setId(null);
                $splittedQuote->addItem($item);
            }

            // Recollect order totals.
            $this->_recollectTotal($item, $splittedQuote, $billingAddress, $shippingAddress);

            // Set payment method.
            $this->_setPaymentMethod($paymentMethod, $splittedQuote, $paymentString);

            // Dispatch event as Magento standard once per each quote split.
            $this->eventManager->dispatch(
                'checkout_submit_before',
                ['quote' => $splittedQuote]
            );

            $this->_toSaveQuote($splittedQuote);
            $order = $subject->submit($splittedQuote);

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
        $this->_defineSessions($splittedQuote, $order, $orderIds);

        // Dispatch event.
        $this->eventManager->dispatch(
            'checkout_submit_all_after',
            ['orders' => $orders, 'quote' => $quote]
        );
        return $order->getId();
    }
}
