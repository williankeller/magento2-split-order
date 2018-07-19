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
     * Check if quotes should be split.
     *
     * @param object $quotes
     * @return bool
     */
    public function isSplittable($quotes);

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
     * @param object $split
     * @param object $item
     * @param array $addresses
     * @param string $paymentMethod
     * @return $this
     */
    public function populateQuote($split, $item, $addresses, $paymentMethod);

    /**
     * Recollect order totals.
     *
     * @param object $item
     * @param object $quote
     * @param array $addresses
     * @param float $shippingAmount
     * @return $this
     */
    public function recollectTotal($item, $quote, $addresses, $shippingAmount = 0);
 
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
