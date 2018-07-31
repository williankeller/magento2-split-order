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

use Magento\Quote\Model\QuoteManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Event\ManagerInterface;
use Magestat\SplitOrder\Api\QuoteHandlerInterface;

class SplitQuote
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
     * @var \Magestat\SplitOrder\Api\QuoteHandlerInterface
     */
    protected $quoteHandler;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteFactory $quoteFactory
     * @param ManagerInterface $eventManager
     * @param QuoteHandlerInterface $quoteHandler
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        QuoteFactory $quoteFactory,
        ManagerInterface $eventManager,
        QuoteHandlerInterface $quoteHandler
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory    = $quoteFactory;
        $this->eventManager    = $eventManager;
        $this->quoteHandler = $quoteHandler;
    }

    /**
     * Places an order for a specified cart.
     * 
     * @param QuoteManagement $subject
     * @param callable $proceed
     * @param int $cartId
     * @param string $payment
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundPlaceOrder(QuoteManagement $subject, callable $proceed, $cartId, $payment = null)
    {
        $quote = $this->quoteRepository->getActive($cartId);

        // Separate all items in quote into new quotes.
        if (($quotes = $this->quoteHandler->normalizeQuotes($quote)) === false) {
            return $result = $proceed($cartId, $payment);
        }
        // Collect list of data addresses.
        $addresses = $this->quoteHandler->collectAddressesData($quote);

        foreach ($quotes as $items) {
            // Init Quote Split.
            $split = $this->quoteFactory->create();

            // Set all customer definition data.
            $this->quoteHandler->setCustomerData($quote, $split);

            // Save splitted quote in order to have a quote item Id.
            $this->toSaveQuote($split);

            // Map quote items.
            foreach ($items as $item) {
                // Add item by item.
                $item->setId(null);
                $split->addItem($item);
            }
            // Recollect order totals.
            $this->quoteHandler->populateQuote($quotes, $split, $item, $addresses, $payment);

            // Dispatch event as Magento standard once per each quote split.
            $this->eventManager->dispatch(
                'checkout_submit_before',
                ['quote' => $split]
            );

            $this->toSaveQuote($split);
            $order = $subject->submit($split);

            $orders[] = $order;
            $orderIds[$order->getId()] = $order->getIncrementId();

            if (null == $order) {
                throw new LocalizedException(
                    __('An error occurred on the server. Please try to place the order again.')
                );
            }
        }
        // Disable origin quote.
        $quote->setIsActive(false);
        // To save quote.
        $this->toSaveQuote($quote);

        // Define checkout sessions.
        $this->quoteHandler->defineSessions($split, $order, $orderIds);

        // Dispatch event.
        $this->eventManager->dispatch(
            'checkout_submit_all_after',
            ['orders' => $orders, 'quote' => $quote]
        );
        return $order->getId();
    }

    /**
     * Save quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return $this
     */
    protected function toSaveQuote($quote)
    {
        $this->quoteRepository->save($quote);

        return $this;
    }
}
