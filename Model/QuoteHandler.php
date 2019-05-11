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

use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magestat\SplitOrder\Api\QuoteHandlerInterface;
use Magestat\SplitOrder\Helper\Data as HelperData;

/**
 * @package Magestat\SplitOrder\Model
 */
class QuoteHandler implements QuoteHandlerInterface
{
    /**
     * @var \Magento\Checkout\Model\Session\Proxy
     */
    private $checkoutSession;

    /**
     * @var \Magestat\SplitOrder\Helper\Data
     */
    private $helperData;

    /**
     * @param CheckoutSession $checkoutSession
     * @param HelperData $helperData
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        HelperData $helperData
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeQuotes($quote)
    {
        // if module is active.
        if (!$this->helperData->isActive()) {
            return false;
        }
        $attributes = $this->helperData->getAttributes();
        if (empty($attributes)) {
            return false;
        }

        $groups = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            $attribute = $this->getProductAttributes($product, $attributes);
            if (empty($attribute)) {
                // Kill function if attribute is null.
                return false;
            }
            $groups[$attribute][] = $item;
        }
        // If order have more than one item.
        if (count($groups) > 1) {
            return $groups;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductAttributes($product, $attributes)
    {
        $attribute = $product->getResource()
            ->getAttribute($attributes)
            ->getFrontend()
            ->getValue($product);

        return $attribute;
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

        if ($quote->getCheckoutMethod() === CartManagementInterface::METHOD_GUEST) {
            $split->setCustomerId(null);
            $split->setCustomerEmail($quote->getBillingAddress()->getEmail());
            $split->setCustomerIsGuest(true);
            $split->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function populateQuote($quotes, $split, $items, $addresses, $payment)
    {
        $this->recollectTotal($quotes, $items, $split, $addresses);
        // Set payment method.
        $this->setPaymentMethod($split, $addresses['payment'], $payment);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function recollectTotal($quotes, $items, $quote, $addresses)
    {
        $tax = 0.0;
        $discount = 0.0;
        $finalPrice = 0.0;

        foreach ($items as $item) {
            // Retrieve values.
            $tax += $item->getData('tax_amount');
            $discount += $item->getData('discount_amount');

            $finalPrice += ($item->getPrice() * $item->getQty());
        }

        // Set addresses.
        $quote->getBillingAddress()->setData($addresses['billing']);
        $quote->getShippingAddress()->setData($addresses['shipping']);

        // Add shipping amount if product is not virual.
        $shipping = $this->shippingAmount($quotes, $quote);

        // Recollect totals into the quote.
        foreach ($quote->getAllAddresses() as $address) {
            // Build grand total.
            $grandTotal = (($finalPrice + $shipping + $tax) - $discount);

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
    public function shippingAmount($quotes, $quote, $total = 0.0)
    {
        // Add shipping amount if product is not virual.
        if ($quote->hasVirtualItems() === true) {
            return $total;
        }
        $shippingTotals = $quote->getShippingAddress()->getShippingAmount();

        // If not, set shipping to one order only.
        if (!$this->helperData->getShippingSplit()) {
            static $process = 1;

            if ($process > 1) {
                // Set zero price to next orders.
                $quote->getShippingAddress()->setShippingAmount($total);
                return $total;
            }
            $process ++;

            return $shippingTotals;
        }
        if ($shippingTotals > 0) {
            // Divide shippinto to each order.
            $total = (float) ($shippingTotals / count($quotes));
            $quote->getShippingAddress()->setShippingAmount($total);
        }
        return $total;
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
