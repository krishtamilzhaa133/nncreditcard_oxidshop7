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
       
        $aDynValue = Registry::getSession()->getVariable('dynvalue'); //get credit card details like panhash and unique id.
        $novalnet_do_redirect=$aDynValue['novalnet_do_redirect'];
        $novalnetUtil = new Novalnetutil();
         
        $getpaymentid=$oBasket->getPaymentId(); 
        
        $url = $novalnetUtil->endpoint('Payment');
        $data= $novalnetUtil->build_payment_params($oUser,$oBasket,$getpaymentid,$aDynValue);
        $response=$novalnetUtil->send_payment_request($data,$url);
        echo "<pre>";
        print_r($response);
        echo "</pre>";
        die();
        $novalnet_status = $response->result->status;
        $novalnet_status_text = $response->result->status_text;

        if($novalnet_status=='SUCCESS'){ 
        //      $novalnet_invoice_details = $novalnetUtil->getInvoiceComments($response);
        // //      print_r($novalnet_invoice_details);
           
        // //    die()
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

