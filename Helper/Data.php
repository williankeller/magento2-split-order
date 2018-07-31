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

namespace Magestat\SplitOrder\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Check if module is active.
     *
     * @param null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->getConfig(
            'magestat_split_order/module/enabled',
            $storeId
        );
    }

    /**
     * Get attributes to split.
     *
     * @param null $storeId
     * @return array
     */
    public function getAttributes($storeId = null)
    {
        return $this->getConfig(
            'magestat_split_order/options/attributes',
            $storeId
        );
    }

    /**
     * Check if should split delivery.
     *
     * @param null $storeId
     * @return bool
     */
    public function getShippingSplit($storeId = null)
    {
        return (bool) $this->getConfig(
            'magestat_split_order/options/shipping',
            $storeId
        );
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
