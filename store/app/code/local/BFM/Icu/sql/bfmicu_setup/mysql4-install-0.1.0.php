<?php
$setup = $this;
$setup->startSetup();



$setup->addAttribute('catalog_product', 'bfm_shipping_information', array(
                'type'              => 'text',
                'backend'           => '',
                'frontend'          => '',
                'label'             => 'Shipping Information',
                'input'             => 'textarea',
                'class'             => '',
                'source'            => '',
                'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                'visible'           => true,
                'required'          => false,
                'user_defined'      => true,
                'default'           => '',
                'searchable'        => true,
                'filterable'        => false,
                'comparable'        => true,
                'wysiwyg_enabled'   => true,
                'is_html_allowed_on_front' => true,
                'visible_on_front'  => false,
                'visible_in_advanced_search' => true,
                'unique'            => false,
                'group'             => 'General',
                'used_in_product_listing' => false,
                'used_for_sort_by' => true
));

$setup->endSetup();
