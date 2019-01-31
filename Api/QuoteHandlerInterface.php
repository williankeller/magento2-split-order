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
     * @param object $quote
     * @return array
     */
    public function normalizeQuotes($quote);

    /**
     * @param object $product
     * @param string $attributes
     * @return string
     */
    public function getProductAttributes($product, $attributes);

    /**
     * Collect list of data addresses.
     *
     * @param object $quote
     * @return array
     */
    public function collectAddressesData($quote);

    /**
     * @param object $quote
     * @param object $split
     * @return $this
     */
    public function setCustomerData($quote, $split);
 
    /**
     * Populate quotes with new data.
     *
     * @param object $quotes
     * @param object $split
     * @param object $item
     * @param array $addresses
     * @param string $payment
     * @return $this
     */
    public function populateQuote($quotes, $split, $item, $addresses, $payment);

    /**
     * Recollect order totals.
     *
     * @param object $quotes
     * @param object $item
     * @param object $quote
     * @param array $addresses
     * @return $this
     */
    public function recollectTotal($quotes, $item, $quote, $addresses);
 
    /**
     * @param object $quotes
     * @param object $quote
     * @param float $total
     */
    public function shippingAmount($quotes, $quote, $total = 0);
    
    /**
     * Set payment method.
     *
     * @param object $paymentMethod
     * @param object $split
     * @param string $payment
     * @return $this
     */
    public function setPaymentMethod($paymentMethod, $split, $payment);

    /**
     * Define checkout sessions.
     *
     * @param object $split
     * @param object $order
     * @param array $orderIds
     * @return $this
     */
    public function defineSessions($split, $order, $orderIds);
}
