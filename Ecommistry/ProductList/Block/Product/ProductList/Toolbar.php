<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecommistry\ProductList\Block\Product\ProductList;


/**
 * Product list toolbar
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
	
	 /**
     * Retrieve available view modes
     *
     * @return array
     */
    public function getModes()
    {
        if ($this->_availableMode === []) {
            $this->_availableMode = ['grid' => __('Grid'), 'Slider' =>  __('Slider')];
        };
        return $this->_availableMode;
    }
	
	/**
     * Retrieve current View mode
     *
     * @return string
     */
    public function getCurrentMode()
    {
        $mode = $this->_getData('_current_grid_mode');
		if ($mode) {
            return $mode;
        }
        $defaultMode = $this->_productListHelper->getDefaultViewMode($this->getModes());
		
        $mode = $this->_toolbarModel->getMode();
	    
		if($mode == 'slider')
		{
			return $mode;
		}
		
		if (!$mode || !isset($this->_availableMode[$mode])) {
            $mode = $defaultMode;
        }

		
        $this->setData('_current_grid_mode', $mode);
        return $mode;
    }
	
	/**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
		$this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
        return $this;
    }
}
