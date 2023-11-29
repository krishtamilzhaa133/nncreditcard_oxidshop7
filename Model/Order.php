<?php

namespace Nncreditcard\Model;
use Nncreditcard\Core\Novalnetutil;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Registry;



class Order extends Order_parent
{
    public function finalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
      
        if (!preg_match("/nncreditcard/i", $oBasket->getPaymentId())) {
            
            return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        } else {
      
            return $this->handleFinalizeOrderProcesscreditcard($oBasket, $oUser, $blRecalculatingOrder);   
        }   
    }
    public function handleFinalizeOrderProcesscreditcard($oBasket, $oUser, $blRecalculatingOrder){

        $novalnetUtil = new Novalnetutil();

        $aDynValue = Registry::getSession()->getVariable('dynvalue'); //get credit card details like panhash and unique id.
        $novalnet_do_redirect=$aDynValue['novalnet_do_redirect'];
       
     
       
        $getpaymentid=$oBasket->getPaymentId(); 
        
        $url = $novalnetUtil->endpoint('Payment');
        $paymentdata= $novalnetUtil->build_payment_params($oUser,$oBasket,$getpaymentid,$aDynValue);
        if($novalnetUtil->backendsettingaccessnncreditcard('nncreditcard3dsecure')== 1 ||  $novalnet_do_redirect == 1) {
            $paymentdata =$novalnetUtil->redirectParameters($paymentdata,$getpaymentid);

        }
        // print_r($paymentdata);
        // die();
        $response=$novalnetUtil->send_payment_request($paymentdata,$url);

        $redirect_url = $response->result->redirect_url;

        $novalnet_status = $response->result->status;
        $novalnet_status_text = $response->result->status_text;

        if($novalnet_status=='SUCCESS'){ 
            if($redirect_url){
             Registry::getUtils()->redirect($redirect_url, false);
    
            }
          
        return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
    }
    else{
             
        $redirectUrl = sprintf(
            "%sindex.php?type=error&cl=payment&payerror=-50&payerrortext=%s",
            Registry::getConfig()->getShopSecureHomeURL(),$novalnet_status_text);
            Registry::getUtils()->redirect($redirectUrl, true, 302);
    }

    }
}

