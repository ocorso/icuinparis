<?php
class Icu_Designs_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {		
		$this->loadLayout();     
		$this->renderLayout();
    }
	
	public function sendemailAction()
    {
        //Fetch submited params
        $params = $this->getRequest()->getParams();
 		//$params['comment']
		

		//------------- GET ADMIN EMAIL ADDRESS --------------

$model = Mage::getModel('admin/user');
$admins = $model->getCollection();
foreach($admins as $admin){
$mainAdminUserName = $admin->getUsername();//get username of admin.
//$admin->getWhatYouWant();//or other fields.
}
//Example: get list emails admin accounts
$list_emails = array();//get list emails admin.
foreach($admins as $admin){
$list_emails[] = $admin->getEmail();
}
$adminEmailAddress = "icu@icuinparis.com";//$list_emails[0];

//------------------------------------------------------


		
		$mailBodyHtml = <<<aaa
<table width="100%" border="0" cellspacing="0" cellpadding="0">
						  <tr>
							<td width="34%"><strong>OUTLET:</strong></td>
							<td width="66%">{$params['outlet']}</td>
						  </tr>
						  <tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						  </tr>
						  <tr>
							<td style="color:red;" colspan="2"><strong>TELL US ABOUT YOUR WORK</strong></td>
						  </tr>
						  <tr>
							<td><strong>Year founded:</strong></td>
							<td>{$params['yearfounded']}</td>
						  </tr>
						  <tr>
							<td><strong>Describe your brand:</strong></td>
							<td>{$params['yourbrand']}</td>
						  </tr>
						  <tr>
							<td><strong>What made you want to start?</strong></td>
							<td>{$params['whatmade']}</td>
						  </tr>
						  <tr>
							<td><strong>Who is your target customer?</strong></td>
							<td>{$params['targetcustomer']}</td>
						  </tr>

						  <tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						  </tr>
						  <tr>
							<td style="color:red;" colspan="2"><strong>TELL US A LITTLE ABOUT YOU</strong></td>
						  </tr>
						  <tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						  </tr>
						  <tr>
							<td><strong>Name:</strong></td>
							<td>{$params['name']}</td>
						  </tr>
						  <tr>
							<td><strong>Company Name/Design Alias/Blog:</strong></td>
							<td>{$params['compname']}</td>
						  </tr>
						  <tr>
							<td><strong>Website:</strong></td>
							<td>{$params['website']}</td>
						  </tr>
						  <tr>
							<td><strong>E-mail:</strong></td>
							<td>{$params['email']}</td>
						  </tr>

						  <tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						  </tr>
						  <tr>
							<td><strong>Describe your submission:</strong></td>
							<td>{$params['submission']}</td>
						  </tr>
						  <tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						  </tr>
						  <tr>
							<td colspan="2">Please find my creations attached.</td>
						  </tr>
						  <tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						  </tr>
						  <tr>
							<td colspan="2">Thanks...</td>
						  </tr>
						</table>
aaa;
		
        $mail = new Zend_Mail();
        //$mail->setBodyText($mailBodyHtml);
		$mail->setBodyHtml($mailBodyHtml);
        $mail->setFrom($params['email'], $params['name']);
        //$mail->addTo('ghanashyam@acumensoft.info', 'Site Admin');
		$mail->addTo($adminEmailAddress, $mainAdminUserName);
        $mail->setSubject('New Design Submited (www.icuinparis.com)');
		
		
				//---------------------------------------- Image File Upload -------------------------------------
for($pcounter=1; $pcounter <=5; $pcounter++) {		
$imageFileName = "p".$pcounter;
if (isset($_FILES[$imageFileName]['name']) && $_FILES[$imageFileName]['name'] != '') {
	    try {
	        $uploader = new Varien_File_Uploader($imageFileName);
	        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
	        $uploader->setAllowRenameFiles(false);
	        $uploader->setFilesDispersion(false);
	        $path = Mage::getBaseDir('media') . DS . 'designs' . DS;
	        // $path = Mage::getBaseDir('media') . DS . 'logo' . DS;
	        $logoName = time().$pcounter."-".$_FILES[$imageFileName]['name'];
	        $uploader->save($path, $logoName);
			$finalFileName = $path.$logoName;
			$fileContents = file_get_contents($finalFileName);
			$attachment = $mail->createAttachment($fileContents);
			$attachment->filename = $logoName;  
	 
	    } catch (Exception $e) {
	 
	    }
	}	
}
		//----------------------------------------------------------------------------------------------
		
		
        try {
            $mail->send();
			Mage::getSingleton('core/session')->addSuccess('Your request sent to store owner successfully.');
        }        
        catch(Exception $ex) {


            Mage::getSingleton('core/session')->addError('Unable to send email. Sample of a custom notification error from ActiveCodeline_SimpleContact.');
 
        }
 		$myargs = array("success" => "1");
        //Redirect back to index action of (this) activecodeline-simplecontact controller
        $this->_redirect('designs/success/');
    }
	
	
}