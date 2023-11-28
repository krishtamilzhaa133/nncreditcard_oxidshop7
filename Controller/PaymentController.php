<?php

namespace Nncreditcard\Controller;

use Nncreditcard\Core\Novalnetutil;

use OxidEsales\Eshop\Core\Registry;

class PaymentController extends PaymentController_parent
{
   
    public function render()
    {
        return parent::render();
    }

    
    public function getPaymentList()
    {
        parent::getPaymentList();
        foreach ($this->_oPaymentList as $oPayment) {
            $sCurrentPayment = $oPayment->oxpayments__oxid->value;
          
    }
    $novalnetUtil = new Novalnetutil();
    $session = \OxidEsales\Eshop\Core\Registry::getSession();
    // echo "<pre>";
    // print_r($session);
    // echo "</pre>";
    // die();
    $currentUser =$session->getUser();
    $getcurrency=$session->getBasket()->getBasketCurrency();
    $getordertotal=$session->getBasket()->getPrice();
 
    $userinformation=[
        'useremail'    =>$currentUser->oxuser__oxusername->value,
        'userfistname' => $currentUser->oxuser__oxfname->value,
        'userlastname' => $currentUser->oxuser__oxlname->value,
        'street_address' => $currentUser->oxuser__oxstreet->value,
        'city' => $currentUser->oxuser__oxlname->value,
        'postcode' => $currentUser->oxuser__oxzip->value,
        'country_code' =>$novalnetUtil->getcountrycode($currentUser->oxuser__oxcountryid->value),
        'currency' => $getcurrency->name,
        'total' =>  $getordertotal->getBruttoPrice(),
        'language_code' => strtoupper(Registry::getLang()->getLanguageAbbr()),
        'creditcard_iframe'=>"novalnet_iframe",
        'testmode'=>$novalnetUtil->backendsettingaccessnncreditcard('nncreditcardtestmode'),
    ];
    $jsonuserinfomation = json_encode($userinformation);
    $this->_aViewData["customerdata"]=$jsonuserinfomation;  

        return $this->_oPaymentList;
    }
   


}
