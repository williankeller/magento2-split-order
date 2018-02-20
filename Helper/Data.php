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

use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    const CONFIG_PATH_MODULE_ENABLED = 'magestat_splitorder/splitorder/enable';

    /**
     * Check if module is enabled.
     *
     * @return boolean
     */
    public function getConfigModuleEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_MODULE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
