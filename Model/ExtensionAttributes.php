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

use Magestat\SplitOrder\Helper\Data as HelperData;
use Magestat\SplitOrder\Api\ExtensionAttributesInterface;

/**
 * Class ExtensionAttributes
 * @package Magestat\SplitOrder\Model
 */
class ExtensionAttributes implements ExtensionAttributesInterface
{
    /**
     * @var \Magestat\SplitOrder\Helper\Data
     */
    private $helperData;

    /**
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @inheritdoc
     */
    public function loadValue($product, $attributeCode)
    {
        /** @var \Magento\Catalog\Api\Data\ProductExtensionInterface $attributes */
        $attributes = $product->getExtensionAttributes();
        if ($attributeCode === self::QUANTITY_AND_STOCK_STATUS) {
            return (string) $this->quantityAndStockStatus($attributes);
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function quantityAndStockStatus($attributes)
    {
        if ($this->helperData->getQtyType() === 'qty') {
            return $attributes->getStockItem()->getQty();
        }
        if ($this->helperData->getBackorder() && $attributes->getStockItem()->getQty() < 1) {
            return 'out';
        }
        return ($attributes->getStockItem()->getIsInStock()) ? 'in' : 'out';
    }
}
