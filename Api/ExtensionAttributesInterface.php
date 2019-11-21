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
 * Interface ExtensionAttributesInterface
 * @api
 */
interface ExtensionAttributesInterface
{
    /**
     * @var string
     */
    const QUANTITY_AND_STOCK_STATUS = 'quantity_and_stock_status';

    /**
     * Method to cover extra attributes which need a different load model.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     * @return bool|string
     */
    public function loadValue($product, $attributeCode);

    /**
     * Handle Stock attribute data.
     *
     * @param \Magento\Catalog\Api\Data\ProductExtensionInterface $attributes
     * @return string|float
     */
    public function quantityAndStockStatus($attributes);
}
