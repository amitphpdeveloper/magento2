<?php

namespace Magehit\Featuredproducts\Block\Home;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class FeaturedList extends \Magento\Catalog\Block\Product\ListProduct {

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
     * @var Magento\Catalog\Model\Layer\Resolver
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
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Initialize
     *
     * @param Magento\Catalog\Block\Product\Context $context
     * @param Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository,
     * @param Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param Magento\Framework\Url\Helper\Data $urlHelper
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
    \Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, \Magento\Catalog\Model\Layer\Resolver $layerResolver, CategoryRepositoryInterface $categoryRepository, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Catalog\Model\ResourceModel\Product\Collection $collection, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Helper\Image $imageHelper, array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->_collection = $collection;
        $this->_scopeConfig = $scopeConfig;
        $this->_imageHelper = $imageHelper;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    /**
     * Get product collection
     */
    public function getProducts() {
        $limit = $this->getProductLimit();
        $this->_collection->clear()->getSelect()->reset('where');
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
                ->addAttributeToFilter('is_featuredproducts', 1, 'left');
		if($this->getSortbyCollection() == "product_name"){
			$collection->addAttributeToSort("name","ASC");
		}else if($this->getSortbyCollection() == "product_price"){
			$collection->addAttributeToSort("price","ASC");
		}
        $collection->getSelect()->limit($limit);
        $this->_productCollection = $collection;
        return $this->_productCollection;
    }
	
	/**
     * Get featured category.
     */
	 protected function getCategory() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
		$collection = $categoryFactory->create()
					->addAttributeToSelect('*')
					->addAttributeToFilter('is_active', '1')
					->addAttributeToFilter('cat_featured', '1');
		return $collection;
	 }
	 
	/**
     * Get featured category collection.
     */
	 protected function getCategoryCollection(){
		$limit = $this->getProductLimit();
		$catIds = $this->getCategory()->getAllIds();
		$categoriesCollection = null;
		if(count($catIds) > 0){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$categoriesCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
			$categoriesCollection->addAttributeToSelect('*');
			$categoriesCollection->addAttributeToFilter('status', 1);
			$categoriesCollection->addAttributeToFilter('visibility', 4);
			$categoriesCollection->addCategoriesFilter(['in' => $catIds]);
			if($this->getSortbyCollection() == "product_name"){
				$categoriesCollection->addAttributeToSort("name","ASC");
			}else if($this->getSortbyCollection() == "product_price"){
				$categoriesCollection->addAttributeToSort("price","ASC");
			}
			$categoriesCollection->getSelect()->order('rand()')->limit($limit);
		}
		$this->_productCollection = $categoriesCollection;
		return array(
			'collection' => $this->_productCollection,
			'items'		 => count($this->_productCollection)
		);
	 }

    /**
     * load and return product collection
     */
     public function getLoadedProductCollection() {
		$categories = $this->getCategoryCollection();
		if($categories["items"] > 0){
			return $categories["collection"];
		}else{
			return $this->getProducts();
		}
    }

    /**
     * Get grid mode
     */
    public function getMode() {
        return 'grid';
    }

    /**
     * Get image helper
     */
    public function getImageHelper() {
        return $this->_imageHelper;
    }

    /**
     * Check that module is enabled or not
     */
    public function getSectionStatus() {
        return $this->_scopeConfig->getValue('featuredproducts_settings/featured_products/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the configured limit of products
     */
    public function getProductLimit() {
        return $this->_scopeConfig->getValue('featuredproducts_settings/horizontal_setting/limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the configured title of section
     */
    public function getPageTitle() {
        return $this->_scopeConfig->getValue('featuredproducts_settings/horizontal_setting/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	
	/**
     * Get the configured slide auto of section
     */
	public function getSlideAuto(){
		return $this->_scopeConfig->getValue('featuredproducts_settings/horizontal_setting/slide_auto', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
     * Get the configured show pagination of section
     */
	public function getPagination(){
		return $this->_scopeConfig->getValue('featuredproducts_settings/horizontal_setting/slide_pagination', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
     * Get the configured show navigation of section
     */
	public function getNavigation(){
		return $this->_scopeConfig->getValue('featuredproducts_settings/horizontal_setting/slide_navigation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getSortbyCollection(){
		return $this->_scopeConfig->getValue('featuredproducts_settings/featured_products/sortby', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	/**
     * Get the configured sortby of section
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product) {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

}
