<?php

namespace Magestat\SplitOrder\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

/**
 * Class AttributeQty
 * Options source to manage the stock.
 */
class AttributeQty implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'qty' => __('Stock Quantity (Inventory value)'),
            'status' => __('Stock Status (In or Out of stock)')
        ];
    }
}
