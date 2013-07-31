<?php
class Icu_Videos_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/videos?id=15 
    	 *  or
    	 * http://site.com/videos/id/15 	
    	 */
    	/* 
		$videos_id = $this->getRequest()->getParam('id');

  		if($videos_id != null && $videos_id != '')	{
			$videos = Mage::getModel('videos/videos')->load($videos_id)->getData();
		} else {
			$videos = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($videos == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$videosTable = $resource->getTableName('videos');
			
			$select = $read->select()
			   ->from($videosTable,array('videos_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$videos = $read->fetchRow($select);
		}
		Mage::register('videos', $videos);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}