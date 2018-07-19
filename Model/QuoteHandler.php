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

namespace Magestat\SplitOrder\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magestat\SplitOrder\Api\QuoteHandlerInterface;
use Magestat\SplitOrder\Helper\Data as HelperData;

class QuoteHandler implements QuoteHandlerInterface
{
    /**
     * @var \Magento\Checkout\Model\Sessions
     */
    protected $checkoutSession;

    /**
     * @var \Magestat\SplitOrder\Helper\Data
     */
    protected $helperData;

    /**
     * @param CheckoutSession $checkoutSession
     * @param HelperData $helperData
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        HelperData $helperData
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helperData      = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeQuotes($quote)
    {
        $groups = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $groups[$item->getProduct()->getSku()][] = $item;
        }
        return $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function isSplittable($quotes)
    {
        // If module is active and order have more than one item.
        if ($this->helperData->isActive() && count($quotes) > 1) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function collectAddressesData($quote)
    {
        $billing = $quote->getBillingAddress()->getData();
        unset($billing['id']);
        unset($billing['quote_id']);

        $shipping = $quote->getShippingAddress()->getData();
        unset($shipping['id']);
        unset($shipping['quote_id']);

        return [
            'payment' => $quote->getPayment()->getMethod(),
            'billing' => $billing,
            'shipping' => $shipping
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerData($quote, $split)
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
     * {@inheritdoc}
     */
    public function populateQuote($split, $item, $addresses, $paymentMethod)
    {
        $this->recollectTotal($item, $split, $addresses);

        // Set payment method.
        $this->setPaymentMethod($split, $addresses['payment'], $paymentMethod);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function recollectTotal($item, $quote, $addresses, $shippingAmount = 0)
    {
        // Retrieve values.
        $tax      = $item->getData('tax_amount');
        $discount = $item->getData('discount_amount');

        $finalPrice = ($item->getPrice() * $item->getQty());

        // Set addresses.
        $quote->getBillingAddress()->setData($addresses['billing']);
        $quote->getShippingAddress()->setData($addresses['shipping']);

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
     * {@inheritdoc}
     */
    public function setPaymentMethod($split, $payment, $paymentMethod)
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
     * {@inheritdoc}
     */
    public function defineSessions($split, $order, $orderIds)
    {
        $this->checkoutSession->setLastQuoteId($split->getId());
        $this->checkoutSession->setLastSuccessQuoteId($split->getId());
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
        $this->checkoutSession->setOrderIds($orderIds);

        return $this;
    }
}
