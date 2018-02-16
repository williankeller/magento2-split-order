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

use Magento\Quote\Api\Data\PaymentInterface;

class QuoteManagement extends \Magento\Quote\Model\QuoteManagement
{

    /**
     * {@inheritdoc}
     */
    public function placeOrder($cartId, PaymentInterface $paymentMethod = null)
    {
        $quote = $this->quoteRepository->getActive($cartId);

        // Get data from default Quote.
        $paymentString   = $quote->getPayment()->getMethod();
        $billingAddress  = $quote->getBillingAddress()->getData();
        $shippingAddress = $quote->getShippingAddress()->getData();
        $customerEmail   = $quote->getBillingAddress()->getEmail();

        // Remove addresses IDs.
        unset($billingAddress['id']);
        unset($billingAddress['quote_id']);
        unset($shippingAddress['id']);
        unset($shippingAddress['quote_id']);

        foreach ($quote->getAllItems() as $item) {
            // Init Quote Split.
            $quoteSplit = $this->quoteFactory->create();
            $quoteSplit->setStoreId($quote->getStoreId());
            $quoteSplit->setCustomer($quote->getCustomer());
            $quoteSplit->setCustomerIsGuest($quote->getCustomerIsGuest());

            if ($quote->getCheckoutMethod() === self::METHOD_GUEST) {
                $quoteSplit->setCustomerEmail($customerEmail);
                $quoteSplit->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
            }

            // Save quoteSplit in order to have a quote ID for item.
            $this->quoteRepository->save($quoteSplit);

            // Add item and init Id to be added to quoteSplit collection.
            $item->setId(null);
            $quoteSplit->addItem($item);

            // Set addresses.
            $quoteSplit->getBillingAddress()->setData($billingAddress);
            $quoteSplit->getShippingAddress()->setData($shippingAddress);

            // Set original payment method.
            $quoteSplit->getPayment()->setMethod($paymentString);
            if ($paymentMethod) {
                $paymentMethod->setChecks([
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
                ]);
                $quoteSplit->getPayment()->setQuote($quoteSplit);

                $data = $paymentMethod->getData();
                $quoteSplit->getPayment()->importData($data);
            }

            // Sets whether the cart is still active.
            $quoteSplit->setIsActive(true);

            // Recollect totals into the quote.
            $quoteSplit->collectTotals()->save();

            // Dispatch this event as Magento standard once per each quote split.
            $this->eventManager->dispatch('checkout_submit_before', ['quote' => $quoteSplit]);
            $this->quoteRepository->save($quoteSplit);

            // Submit quote.
            $order = $this->submit($quoteSplit);

            $orders[] = $order;
            $orderIds[] = $order->getId();

            if (null == $order) {
                throw new LocalizedException(
                    __('An error occurred on the server. Please try to place the order again.')
                );
            }
        }
        // Disable origin quote.
        $quote->setIsActive(false);
        // To save quote.
        $this->quoteRepository->save($quote);

        $this->checkoutSession->setLastQuoteId($quoteSplit->getId());
        $this->checkoutSession->setLastSuccessQuoteId($quoteSplit->getId());
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());

        $this->checkoutSession->setQuoteSplitted(implode(';', $orderIds));

        $this->eventManager->dispatch('checkout_submit_all_after', ['orders' => $orders, 'quote' => $quote]);
        return $order->getId();
    }

}
