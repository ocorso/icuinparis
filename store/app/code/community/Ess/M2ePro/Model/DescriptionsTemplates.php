<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_DescriptionsTemplates extends Mage_Core_Model_Abstract
{
    const TITLE_MODE_PRODUCT = 0;
    const TITLE_MODE_CUSTOM  = 1;

    const SUBTITLE_MODE_NONE     = 0;
    const SUBTITLE_MODE_CUSTOM   = 1;

    const DESCRIPTION_MODE_PRODUCT = 0;
    const DESCRIPTION_MODE_SHORT   = 1;
    const DESCRIPTION_MODE_CUSTOM  = 2;

    const CUT_LONG_TITLE_DISABLED = 0;
    const CUT_LONG_TITLE_ENABLED  = 1;

    const EDITOR_TYPE_SIMPLE    = 0;
    const EDITOR_TYPE_TINYMCE   = 1;

    const IMAGE_MAIN_MODE_NONE       = 0;
    const IMAGE_MAIN_MODE_PRODUCT    = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE  = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/DescriptionsTemplates');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_DescriptionsTemplates
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Description template does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingId
     * @return Ess_M2ePro_Model_DescriptionsTemplates
     */
    public function loadByListing($listingId)
    {
         $tempModel = Mage::getModel('M2ePro/Listings')->load($listingId);

         if (is_null($tempModel->getId())) {
             throw new Exception('Listing does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('description_template_id'));
    }

    // ########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
        if (!$this->getId()) {
            return false;
        }

        return (bool)Mage::getModel('M2ePro/Listings')
                            ->getCollection()
                            ->addFieldToFilter('description_template_id', $this->getId())
                            ->getSize();
    }
    
    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getListings($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/Listings')->getCollection();
        $tempCollection->addFieldToFilter('description_template_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $tempInstance = Mage::getModel('M2ePro/Listings')
                                        ->loadInstance($item['id']);
                $tempInstance->setDescriptionTemplate($this);
                $resultArray[] = $tempInstance;
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    // ########################################

    public function cutLongTitles($str, $length = 80)
    {
        $str = trim($str);

        if ($str === '' || strlen($str) <= $length) {
            return $str;
        }

        if (!preg_match('/^.{0,'.$length.'}\s/us', $str, $matches)) {
            return '';
        }

        if (!isset($matches[0])) {
            return '';
        }

        $str = trim($matches[0]);

        return $str;
    }

    // ########################################

    public function getTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('title');
    }

    public function isCutLongTitles()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (bool)$this->getData('cut_long_titles');
    }

    public function getHitCounterType()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('hit_counter');
    }

    public function getGalleryImagesMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('gallery_images_mode');
    }

    //-------------------------

    public function getTitleMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('title_mode');
    }

    public function isTitleModeProduct()
    {
        return $this->getTitleMode() == self::TITLE_MODE_PRODUCT;
    }

    public function isTitleModeCustom()
    {
        return $this->getTitleMode() == self::TITLE_MODE_CUSTOM;
    }

    public function getTitleSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return array(
            'mode'     => $this->getTitleMode(),
            'template' => $this->getData('title_template')
        );
    }

    public function getTitleAttributes()
    {
        $attributes = array();
        $src = $this->getTitleSource();

        if ($src['mode'] == self::TITLE_MODE_PRODUCT) {
            $attributes[] = 'name';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getSubTitleMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('subtitle_mode');
    }

    public function isSubTitleModeProduct()
    {
        return $this->getSubTitleMode() == self::SUBTITLE_MODE_NONE;
    }

    public function isSubTitleModeCustom()
    {
        return $this->getSubTitleMode() == self::SUBTITLE_MODE_CUSTOM;
    }

    public function getSubTitleSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return array(
            'mode'     => $this->getSubTitleMode(),
            'template' => $this->getData('subtitle_template')
        );
    }

    public function getSubTitleAttributes()
    {
        $attributes = array();
        $src = $this->getSubTitleSource();

        if ($src['mode'] == self::SUBTITLE_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getDescriptionMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('description_mode');
    }

    public function isDescriptionModeProduct()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_PRODUCT;
    }

    public function isDescriptionModeShort()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_SHORT;
    }

    public function isDescriptionModeCustom()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_CUSTOM;
    }

    public function getDescriptionSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return array(
            'mode'     => $this->getDescriptionMode(),
            'template' => $this->getData('description_template')
        );
    }

    public function getDescriptionAttributes()
    {
        $attributes = array();
        $src = $this->getDescriptionSource();

        if ($src['mode'] == self::DESCRIPTION_MODE_PRODUCT) {
            $attributes[] = 'description';
        } elseif ($src['mode'] == self::DESCRIPTION_MODE_SHORT) {
            $attributes[] = 'short_description';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------
    
    public function getEditorType()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('editor_type');
    }

    public function isEditorTypeSimple()
    {
        return $this->getEditorType() == self::EDITOR_TYPE_SIMPLE;
    }

    public function isEditorTypeTinyMce()
    {
        return $this->getEditorType() == self::EDITOR_TYPE_TINYMCE;
    }

    //-------------------------

    public function getImageMainMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('image_main_mode');
    }

    public function isImageMainModeNone()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_NONE;
    }

    public function isImageMainModeProduct()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_PRODUCT;
    }

    public function isImageMainModeAttribute()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_ATTRIBUTE;
    }

    public function getImageMainSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return array(
            'mode'     => $this->getImageMainMode(),
            'attribute' => $this->getData('image_main_attribute')
        );
    }

    public function getImageMainAttributes()
    {
        $attributes = array();
        $src = $this->getImageMainSource();
        
        if ($src['mode'] == self::IMAGE_MAIN_MODE_PRODUCT) {
            $attributes[] = 'image';
        } else if ($src['mode'] == self::IMAGE_MAIN_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getVariationConfigurableImages()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('variation_configurable_images');
    }

    public function isVariationConfigurableImages()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getVariationConfigurableImages() != '';
    }

    //-------------------------

    public function getUsedAttributes()
    {
        $tempArray = array_unique(array_merge(
            $this->getTitleAttributes(),
            $this->getSubTitleAttributes(),
            $this->getDescriptionAttributes()
            //$this->getImageMainAttributes()
        ));
        
        $resultArray = array();
        foreach ($tempArray as $attribute) {
            if (strpos($attribute,'media_gallery') !== false) {
                continue;
            }
            $resultArray[] = $attribute;
        }

        return $resultArray;
    }

    // ########################################
}