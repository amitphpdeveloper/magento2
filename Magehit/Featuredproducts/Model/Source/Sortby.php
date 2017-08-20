<?php
namespace Magehit\Featuredproducts\Model\Source;

class Sortby implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'random',
                'label' => __('Random')
            ),
            array(
                'value' => 'product_name',
                'label' => __('Product Name')
            ),
            array(
                'value' => 'product_price',
                'label' => __('Product Price')
            )
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
			'random' => __('Random'),
			'product_name' => __('Product Name'),
			'product_price' => __('Product Price')
        ];
    }
}