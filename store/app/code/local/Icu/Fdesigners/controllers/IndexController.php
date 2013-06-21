<?php
class Icu_Fdesigners_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/fdesigners?id=15 
    	 *  or
    	 * http://site.com/fdesigners/id/15 	
    	 */
    	/* 
		$fdesigners_id = $this->getRequest()->getParam('id');

  		if($fdesigners_id != null && $fdesigners_id != '')	{
			$fdesigners = Mage::getModel('fdesigners/fdesigners')->load($fdesigners_id)->getData();
		} else {
			$fdesigners = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($fdesigners == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$fdesignersTable = $resource->getTableName('fdesigners');
			
			$select = $read->select()
			   ->from($fdesignersTable,array('fdesigners_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$fdesigners = $read->fetchRow($select);
		}
		Mage::register('fdesigners', $fdesigners);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}