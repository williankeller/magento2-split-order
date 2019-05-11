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

namespace Magestat\SplitOrder\Api;

/**
 * @api
 */
interface QuoteHandlerInterface
{
    /**
     * Separate all items in quote into new quotes.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool|array False if not split, or an array of array of split items
     */
    public function normalizeQuotes($quote);

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributes
     * @return mixed
     */
    public function getProductAttributes($product, $attributes);

    /**
     * Collect list of data addresses.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function collectAddressesData($quote);

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote $split
     * @return \Magestat\SplitOrder\Api\QuoteHandlerInterface
     */
    public function setCustomerData($quote, $split);
 
    /**
     * Populate quotes with new data.
     *
     * @param array $quotes
     * @param \Magento\Quote\Model\Quote $split
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @param array $addresses
     * @param string $payment
     * @return \Magestat\SplitOrder\Api\QuoteHandlerInterface
     */
    public function populateQuote($quotes, $split, $items, $addresses, $payment);

    /**
     * Recollect order totals.
     *
     * @param array $quotes
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $addresses
     * @return \Magestat\SplitOrder\Api\QuoteHandlerInterface
     */
    public function recollectTotal($quotes, $items, $quote, $addresses);
 
    /**
     * @param array $quotes
     * @param \Magento\Quote\Model\Quote $quote
     * @param float $total
     */
    public function shippingAmount($quotes, $quote, $total = 0.0);
    
    /**
     * Set payment method.
     *
     * @param string $paymentMethod
     * @param \Magento\Quote\Model\Quote $split
     * @param string $payment
     * @return \Magestat\SplitOrder\Api\QuoteHandlerInterface
     */
    public function setPaymentMethod($paymentMethod, $split, $payment);

    /**
     * Define checkout sessions.
     *
     * @param \Magento\Quote\Model\Quote $split
     * @param \Magento\Sales\Model\Order $order
     * @param array $orderIds
     * @return \Magestat\SplitOrder\Api\QuoteHandlerInterface
     */
    public function defineSessions($split, $order, $orderIds);
}
