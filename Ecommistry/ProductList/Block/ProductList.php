<?php

namespace Ecommistry\ProductList\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class ProductList extends \Magento\Catalog\Block\Product\ListProduct {

    /**
     * Product collection model
     *
     * @var Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_collection;

    /**
     * Product collection model
     *
     * @var Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_productCollection;

    /**
     * Image helper
     *
     * @var Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * Catalog Layer
     *
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;
	/**
     * @var CategoryCollection
     */
    //protected $_categoryCollection;

    /**
     * Initialize
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
    \Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, \Magento\Catalog\Model\Layer\Resolver $layerResolver, CategoryRepositoryInterface $categoryRepository,\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Catalog\Model\ResourceModel\Product\Collection $collection, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Helper\Image $imageHelper, array $data = []
    ) {
        $this->imageBuilder = $context->getImageBuilder();
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->_collection = $collection;
        $this->_imageHelper = $imageHelper;
        $this->_scopeConfig = $scopeConfig;
        //$this->_categoryCollection = $categoryCollection;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);

        $this->pageConfig->getTitle()->set(__($this->getPageTitle()));
    }
	
	/**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->_getProductCollection()]
        );

        $this->_getProductCollection()->load();

        return parent::_beforeToHtml();
    }

    /**
     * Get product collection
     */
    protected function getProducts() {
        $collection = $this->_collection
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('news_from_date')
                ->addAttributeToSelect('news_to_date')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('special_from_date')
                ->addAttributeToSelect('special_to_date')
                ->addAttributeToFilter('is_saleable', 1, 'left')
				->addAttributeToFilter('status', 1)
				->addAttributeToFilter('visibility', 4)
                ->addAttributeToFilter('handle_display', 1, 'left');
		if($this->getSortbyCollection() == "product_name"){
			$collection->addAttributeToSort("name","ASC");
		}else if($this->getSortbyCollection() == "product_price"){
			$collection->addAttributeToSort("price","ASC");
		}
        $collection->setPageSize(10)->getSelect();
		
        // Set Pagination Toolbar for list page
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'featuredproducts.grid.record.pager')->setLimit(8)->setCollection($collection);
        $this->setChild('pager', $pager); // set pager block in layout

        $this->_productCollection = $collection;
        return $this->_productCollection;
    }
	
	 
	

    /*
     * Load and return product collection 
     */
    public function getLoadedProductCollection() {
		
			return $this->getProducts();
		
    }

    /*
     * Get product toolbar
     */

    public function getToolbarHtml() {
        return $this->getChildHtml('toolbar');
    }

    /*
     * Get grid mode
     */

    public function getMode() {
        return $this->getChildBlock('toolbar')->getCurrentMode();
    }

    /**
     * Get image helper
     */
    public function getImageHelper() {
        return $this->_imageHelper;
    }

    /* Check module is enabled or not */

    public function getSectionStatus() {
        return $this->_scopeConfig->getValue('featuredproducts_settings/featured_products/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    

    /* Get the configured limit of products */

    public function getProductLimit() {
        return $this->_scopeConfig->getValue('featuredproducts_settings/vertical_setting/limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /* Get the configured title of section */

    public function getPageTitle() {
        return $this->_scopeConfig->getValue('featuredproducts_settings/vertical_setting/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	/**
     * Get the configured slide auto of section
     */
	public function getSlideAuto(){
		return $this->_scopeConfig->getValue('featuredproducts_settings/vertical_setting/slide_auto', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	/**
     * Get the configured show pagination of section
     */
	public function getPagination(){
		return $this->_scopeConfig->getValue('featuredproducts_settings/vertical_setting/slide_pagination', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	/**
     * Get the configured show navigation of section
     */
	public function getNavigation(){
		return $this->_scopeConfig->getValue('featuredproducts_settings/vertical_setting/slide_navigation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	/**
     * Get the configured sortby of section
     */
	public function getSortbyCollection(){
		return $this->_scopeConfig->getValue('featuredproducts_settings/featured_products/sortby', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

}
