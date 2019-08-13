<?php
class Iksula_Orderemail_IndexController extends Mage_Core_Controller_Front_Action{


public function indexAction(){

		$orderId 	= $this->getRequest()->getParam('orderid');
		$key = $this->getRequest()->getParam('key');
		try{ 
			if($orderId){ 
		$current = date('Y-m-d H:i:s');	
			//load order id from cod_orders table to validate 
		$cod_order = Mage::getModel('orderemail/codorders')->getCollection()
					->addFieldToFilter('increment_id',array('eq'=> $orderId))
					->addFieldToFilter('expire_date',array('gteq'=>$current))
					->getFirstItem(); 
		$expirekey = $cod_order->getExpirekey();			
			if($cod_order && $expirekey == $key){  

				$order = Mage::getModel('sales/order')->loadByIncrementId($cod_order->getIncrementId());
				$status = $order->getStatus();
				$payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
				if($payment_method_code == 'cashondelivery'){
					if($status == 'cod'){
			        	$state = $order->getState();
			        	$status = 'pending';	
			        	$comment = 'Customer has verified cod payment';
			        	$isCustomerNotified = false;
			        	$return = $order->_setState($state, $status, $comment, $isCustomerNotified, true);
			        	$order->save();
			        	$this->layoutChange('onepagecheckout/checkoutpage/verify_success.phtml');
			        	// code to send new order email
			        	$store = Mage::app()->getStore();
			        	$paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
						                ->setIsSecureMode(true);
						            $paymentBlock->getMethod()->setStore($store->getStoreId());
						            $paymentBlockHtml = $paymentBlock->toHtml();
			        	$vars = array('order' => $order,'store'=> $store,"billing"=>$order->getBillingAddress(),"payment_html"=>$paymentBlockHtml);
                        $customer  = Mage::getModel('customer/customer')->load($order->getCustomerId());                         
//	                    $translate  = Mage::getSingleton('core/translate');
//	                    $templateId = 3;
	                    $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
	                    $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');    
	                   	$sender = array('name' => $senderName,'email' => $senderEmail);
	                   	$recepientEmail = $customer->getEmail();
	                   	$recepientName = $customer->getName();
	                   
                            
                             /* Code to send order email */
                                $emailTemplate = Mage::getModel('core/email_template')->loadDefault('order_custom');  
                               
                                $emailTemplate->setSenderName($senderName);
                                $emailTemplate->setSenderEmail($senderEmail);
                                $emailTemplate->setType('html');
                                $emailTemplate->setTemplateSubject('COD Order Notification');
                                $emailTemplate->send($recepientEmail,$recepientName,$vars);
                                
                              /* End code to send order email */  
//	                    Mage::getModel('core/email_template')
//	                        ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $store);
//	                             
//	                    $translate->setTranslateInline(true); 
	                     /* End code to send email */
			        	Mage::getSingleton('core/session')->addSuccess('Congrats, Your order has been confirmed. New order email has been sent to your email.');
			        	$this->layoutChange('onepagecheckout/checkoutpage/verify_success.phtml');
					}
					else{
							Mage::getSingleton('core/session')->addNotice('Your order is already confirmed');
							$this->layoutChange('onepagecheckout/checkoutpage/verify.phtml');
						}
				}
				else{
					$this->layoutChange('onepagecheckout/checkoutpage/verify_fail.phtml');
					Mage::getSingleton('core/session')->addError('Sorry,Order can not be confirmed');
				}	

			}  // end of if condition
			else{
				Mage::getSingleton('core/session')->addError('Order confirmation link has been expired');
			}
		}
		else{
			$this->_redirect('cms');
		} 
		}catch(Exception $ex){
			echo 'error'.$ex->getMessage();
			Mage::getSingleton('core/session')->addError('Something goes wrong'.$ex->getMessage());
		}	
	}

	public function layoutChange($phtml){
		$this->loadLayout();
		$verifyBlock = $this->getLayout()->createBlock('core/template')->setTemplate($phtml);
		$this->getLayout()->getBlock('content')->append($verifyBlock);
		$this->renderLayout();
	}

}