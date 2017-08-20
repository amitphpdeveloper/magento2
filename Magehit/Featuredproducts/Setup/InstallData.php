<?php

namespace Magehit\Featuredproducts\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface {

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
	/**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
	private $categorySetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory,CategorySetupFactory $categorySetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
		$this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'is_featuredproducts', [
            'group' => 'Product Details',
            'type' => 'int',
            'sort_order' => 100,
            'backend' => '',
            'frontend' => '',
            'label' => 'Is Featured Product ?',
            'input' => 'boolean',
            'class' => '',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => 'simple,configurable,virtual,bundle,downloadable'
        ]);
		
		 /** @var categorySetupFactory $categorySetup */
		$installer = $setup;
        $installer->startSetup();
		
		$categoryCode = "cat_featured";
		// create attribute of category
		$categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $categorySetup->removeAttribute(
			\Magento\Catalog\Model\Category::ENTITY, $categoryCode
		);
        $categorySetup->addAttribute(
			\Magento\Catalog\Model\Category::ENTITY, $categoryCode, [
				 'type' => 'int',
				 'label' => 'Set category is Featured ?',
				 'input' => 'select',
				 'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
				 'required' => false,
				 'sort_order' => 100,
				 'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				 'group' => 'Display Settings',
			]
		);
		$idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Display Settings');
		$categorySetup->addAttributeToGroup(
			$entityTypeId,
			$attributeSetId,
			$idg,
			$categoryCode,
			46
		);
		$installer->endSetup();
    }

}
