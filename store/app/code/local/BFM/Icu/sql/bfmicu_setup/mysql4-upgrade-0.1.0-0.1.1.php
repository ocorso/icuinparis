<?php
$setup = $this;
$setup->startSetup();

$setup->addAttribute('catalog_product', 'color', array(
                'type'              => 'int',
				'backend'           => '',
				'frontend'          => '',
				'label'             => 'Color',
				'input'             => 'select',
				'class'             => '',
				'source'            => '',
				'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
				'visible'           => true,
				'required'          => false,
				'user_defined'      => true,
				'default'           => '',
				'searchable'        => true,
				'filterable'        => true,
				'comparable'        => true,
				'visible_on_front'  => false,
				'visible_in_advanced_search' => true,
				'unique'            => false,
				'group'             => 'General'
));

$setup->addAttribute('catalog_product', 'size', array(
                'type'              => 'text',
                'backend'           => '',
                'frontend'          => '',
                'label'             => 'Size',
                'input'             => 'text',
                'class'             => '',
                'source'            => '',
                'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'visible'           => true,
                'required'          => false,
                'user_defined'      => true,
                'default'           => '',
                'searchable'        => true,
                'filterable'        => true,
                'comparable'        => true,
                'visible_on_front'  => false,
                'visible_in_advanced_search' => true,
                'unique'            => false,
                'group'             => 'General'
));

$setup->endSetup();
