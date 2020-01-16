<?php

namespace Magestat\SplitOrder\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

/**
 * Class Attributes
 * Responsible to load the attributes.
 */
class Attributes implements OptionSourceInterface
{
    /**
     * @var array List of attributes that shouldn't appear on the list.
     */
    const BLACK_LIST = [
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'page_layout',
        'gallery',
        'image',
        'image_label',
        'small_image',
        'small_image_label',
        'thumbnail',
        'thumbnail_label',
        'swatch_image',
        'links_exist',
        'media_gallery',
        'old_id',
        'required_options',
    ];

    /**
     * @var CollectionFactory
     */
    private $collection;

    /**
     * @var array Options list
     */
    private $options;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collection = $collectionFactory;
    }

    /**
     * Get options as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options) {
            return $this->options;
        }
        $collection = $this->collection->create();

        $attributes = [];
        foreach ($collection as $item) {
            if (empty($item->getFrontendLabel()) || in_array($item->getAttributeCode(), self::BLACK_LIST)) {
                continue;
            }
            $attributes[] = [
                'value' => $item->getAttributeCode(),
                'label' => $item->getFrontendLabel()
            ];
        }
        $this->options = $attributes;

        $options = $this->options;
        array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);

        return $options;
    }
}
