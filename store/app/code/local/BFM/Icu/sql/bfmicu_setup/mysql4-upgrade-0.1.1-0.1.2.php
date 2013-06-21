<?php
$setup = $this;
$setup->startSetup();

$setup->addAttribute('catalog_product', 'backorder', array(
                'type'              => 'int',
				'backend'           => '',
				'frontend'          => '',
				'label'             => 'Backorder',
				'input'             => 'select',
				'class'             => '',
				'source'            => 'eav/entity_attribute_source_boolean',
				'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
				'visible'           => false,
				'required'          => false,
				'user_defined'      => false,
				'default'           => '',
				'searchable'        => false,
				'filterable'        => false,
				'comparable'        => false,
				'visible_on_front'  => false,
				'unique'            => false,
				'group'             => 'Inventory'
));

$setup->endSetup();
