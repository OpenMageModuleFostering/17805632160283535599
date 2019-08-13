<?php
class Iksula_Orderemail_Model_Sales_Order extends Mage_Sales_Model_Order
{

 public function setState($state, $status = false, $comment = '', $isCustomerNotified = null)
    {
    	// code to change state of order to COD which are place with cashondelievery
    	try{ 
    	$payment_method = $this->getPayment()->getMethodInstance()->getCode();
	  			if($payment_method == "cashondelivery"){
	  				return $this->_setState($state, "cod", $comment, $isCustomerNotified, true);	
	  			}else{
	  				return $this->_setState($state, $status, $comment, $isCustomerNotified, true);			
	  			}
	  	}catch(Exception $ex){
	  		Mage::throwException(
                    Mage::helper('sales')->__('Order state not getting set '.$ex->getMessage())
                );
	  	}		
        
    }
public function _setState($state, $status = false, $comment = '',$isCustomerNotified = null, $shouldProtectState = false)
    {
        // attempt to set the specified state
        if ($shouldProtectState) {
            if ($this->isStateProtected($state)) {
                Mage::throwException(
                    Mage::helper('sales')->__('The Order State "%s" must not be set manually.', $state)
                );
            }
        }
        $this->setData('state', $state);

        // add status history
        if ($status) {
            if ($status === true) {
                $status = $this->getConfig()->getStateDefaultStatus($state);
            }
            $this->setStatus($status);
            $history = $this->addStatusHistoryComment($comment, false); // no sense to set $status again
            $history->setIsCustomerNotified($isCustomerNotified); // for backwards compatibility
        }
        return $this;
    }
 public function sendNewOrderEmail()
  {
  		 // code to send order confirmation email to customer if an order payment method is COD
  			try{ 
  			$payment_method = $this->getPayment()->getMethodInstance()->getCode();
	  			if($payment_method == "cashondelivery"){
	  				
	  				//code to save cod order detail in model	
	  				$cod_order = Mage::getModel('orderemail/codorders');
	  				$formKey = Mage::getSingleton('core/session')->getFormKey();
	  				$cod_order->setIncrementId($this->getIncrementId());
	  				$cod_order->setCustomerId($this->getCustomerId());
	  				$expiredate = date('Y-m-d h:i:s', strtotime(date("Y-m-d h:i:s").'2 days'));	
	  				$cod_order->setExpireDate($expiredate);
	  				$cod_order->setExpirekey($formKey);
	  				$cod_order->save();
	  				$customer  = Mage::getModel('customer/customer')->load($this->getCustomerId());

	  				$verify_url = Mage::getUrl('orderemail').'?orderid='.$this->getIncrementId().'&key='.$formKey;
	  				// code to send custom email for confirmation
	  				$vars = array('order' => $this,'cod_verify_url'=> $verify_url);
                                                 
//                    $translate  = Mage::getSingleton('core/translate');
//                    $templateId = 11;
                    $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
                    $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');    
                    $sender = array('name' => $senderName,'email' => $senderEmail);
                    $recepientEmail = $customer->getEmail();
                    $recepientName = $customer->getName();
                    $store = Mage::app()->getStore()->getId();
//
//                    // Send Transactional Email
//                    Mage::getModel('core/email_template')
//                        ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $store);
//                             
//                    $translate->setTranslateInline(true); 
                               /* Code to send order email */
                                $emailTemplate = Mage::getModel('core/email_template')->loadDefault('cod_order');  
                                $emailtemplatevarible = array();
                                $emailtemplatevarible["customer"]= $customer;
                                $emailtemplatevarible["store"]= Mage::app()->getStore()->getId();
                                $emailtemplatevarible["orderlink"] = $verify_url;
                                $emailtemplatevarible["order"] = $this;
                                $emailTemplate->setSenderName($senderName);
                                $emailTemplate->setSenderEmail($senderEmail);
                                $emailTemplate->setType('html');
                                $emailTemplate->setTemplateSubject('COD confirmation');
                                $emailTemplate->send($recepientEmail,$recepientName,$emailtemplatevarible);
                                return true;
                              /* End code to send order email */          
                                        

	  			}else{
	  				parent::sendNewOrderEmail();
	  			}
  			}catch(Exception $ex){
  				Mage::throwException(
                    Mage::helper('sales')->__('Order Email Not Send '.$ex->getMessage())
                );
  				
  			}

	}  

  public function formatPrice($price, $addBrackets = false){
        //return $this->formatPricePrecision($price, 2, $addBrackets);
        /*shaily written for removing decimals from emailer and site*/
        return $this->formatPricePrecision($price, 0, $addBrackets);
    }		

}
		