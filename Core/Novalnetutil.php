<?php
namespace Nncreditcard\Core;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Application\Model\Country;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\EshopCommunity\Core\Module\Module;
use Psr\Container\ContainerInterface;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;

class Novalnetutil
{
    public function endpoint($action) {
        $url =[
              'Payment' => "https://payport.novalnet.de/v2/payment",
              'Merchant_Details'=>"https://payport.novalnet.de/v2/merchant/details",
        ];
        if (isset($url[$action])) {
         return $url[$action];
        }
    }
    public function backendsettingaccess($settingvar){
        $moduleConfiguration = ContainerFactory::getInstance()
        ->getContainer()
        ->get(ModuleConfigurationDaoBridgeInterface::class)
        ->get("nnapiconfigure");
        return $moduleConfiguration->getModuleSetting($settingvar)->getValue();
    }
    public function backendsettingaccessnncreditcard($settingvar){
        $moduleConfiguration = ContainerFactory::getInstance()
        ->getContainer()
        ->get(ModuleConfigurationDaoBridgeInterface::class)
        ->get("nncreditcard");
        return $moduleConfiguration->getModuleSetting($settingvar)->getValue();
    }
    public function getcountrycode($sCountryId)
    {
        $oCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $oCountry->load($sCountryId);
        return $oCountry->oxcountry__oxisoalpha2->value;
    }
    public function priceformate($oBasket)
    {
        return str_replace(',', '', number_format($oBasket->getPrice()->getBruttoPrice(), 2)) * 100;
    }

    public function setRedirectURL()
    {
        return Registry::getConfig()->getSslShopUrl() . 'index.php?cl=payment&payerror=-1&payerrortext=' . urlencode(self::setUTFEncode(''));
    }


    public function get_headers() {
      $encoded_data = base64_encode($this->backendsettingaccess('paymentaccesskey'));

       $headers=[
         'Content-Type:application/json',
         'Charset:utf-8', 
         'Accept:application/json', 
         'X-NN-Access-Key:' . $encoded_data
       ];
       return $headers;
    }
     

    public function getPaymentType($code) {
        $paymentcode = [
            'nncreditcard'   =>"CREDITCARD",
          ];
            if (isset($paymentcode[$code])) {
             return $paymentcode[$code];
            }
    }

    public function redirectParameters($data,$getpaymentid) {
        $payment_type =$this->getPaymentType($getpaymentid);
        if($payment_type =='CREDITCARD'){ 
          if($this->backendsettingaccessnncreditcard('nncreditcard3dsecure') == 1){  
          $data['transaction']['enforce_3d ']=$this->backendsettingaccessnncreditcard('nncreditcard3dsecure');
          }
        
        }
      $sReturnURL = htmlspecialchars_decode(Registry::getConfig()->getShopCurrentURL()) . 'cl=order&stoken='.$this->getRequestParameter('stoken');
      $data['transaction']['return_url'] = $data['transaction']['error_return_url'] = $sReturnURL;
      return $data;      
    }

    public function getRequestParameter($data = '')
    {
        return Registry::getRequest()->getRequestParameter($data);
    }
   
    public function build_payment_params($oUser,$oBasket,$getpaymentid,$aDynValue) { 

        $data = [];
    
        $data['merchant'] = [
            'signature' => $this->backendsettingaccess('productactivationkey'), 
            'tariff'    => $this->backendsettingaccess('traiffid'),
        ];
    
        $data['customer'] = [
            'first_name' =>$oUser->oxuser__oxfname->value,
            'last_name'  =>$oUser->oxuser__oxlname->value, 
            'email'      =>$oUser->oxuser__oxusername->value,
            'tel'        =>$oUser->oxuser__oxfon->value,
            'mobile'     =>$oUser->oxuser__oxprivfon->value,
            'billing' => [
                'street'       => $oUser->oxuser__oxstreet->value,
                'city'         => $oUser->oxuser__oxcity->value,
                'zip'          => $oUser->oxuser__oxzip->value,
                'country_code' => $this->getcountrycode($oUser->oxuser__oxcountryid->value),
            ],
            'shipping' => [
                'street'       => $oUser->oxuser__oxstreet->value,
                'city'         => $oUser->oxuser__oxcity->value,
                'zip'          => $oUser->oxuser__oxzip->value,
                'country_code' => $this->getcountrycode($oUser->oxuser__oxcountryid->value),
            ]
        ];
    
        if ($data['customer']['billing'] == $data['customer']['shipping']) {
            $data['customer']['shipping'] = [
                'same_as_billing' => '1',
            ];
        }
    
        $data['transaction'] = [
            'payment_type'     => $this->getPaymentType($getpaymentid), 
            'amount'           => $this->priceformate($oBasket),
            'currency'         => $oBasket->getBasketCurrency()->name,
            'test_mode'        =>  $this->backendsettingaccessnncreditcard('nncreditcardtestmode'),   
            'payment_data'     => [
                'pan_hash'  => $aDynValue['novalnet_pan_hash'],
                'unique_id' => $aDynValue['novalnet_unique_id'],
            ],
        ];
    
        $data['custom'] = [
            'lang' => strtoupper(Registry::getLang()->getLanguageAbbr()),
        ];
    
        return $data;
    }
    
    public function send_payment_request($data,$url) {
	    $json_data = json_encode($data);
        file_put_contents('request.txt', print_r($json_data, true), FILE_APPEND);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$this->get_headers()); 

        $result = curl_exec($curl);
        file_put_contents('result.txt', print_r($result, true), FILE_APPEND);   
        if (curl_errno($curl)) {
          echo 'Request Error:' . curl_error($curl);
          return $result;
        }
        curl_close($curl);
        $result = json_decode($result);
        return $result;
    }

    public  function checkSum($request) {
   
        $tnx_secret = $request['txn_secret'];
        $checksum = $request['checksum'];
        $tid = $request['tid'];
        $status = $request['status'];
        $error = $request['status_text'];
        $txn_secret = !empty($_SESSION['novalnet_txn_secret']) ? $_SESSION['novalnet_txn_secret'] : $tnx_secret;
        if (!empty($checksum) && !empty($tid) && $txn_secret && !empty($status)) {
            $token_string = $tid . $txn_secret . $status . strrev(strtolower($this->backendsettingaccess('paymentaccesskey')));
            $generated_checksum = hash('sha256', $token_string);
            if ($generated_checksum !== $checksum) {
                $redirectUrl = sprintf(
                    "%sindex.php?type=error&cl=payment&payerror=-51&payerrortext=%s",
                    Registry::getConfig()->getShopSecureHomeURL(),"checksum is wrong!");
                    Registry::getUtils()->redirect($redirectUrl, true, 302);      
            } 
            elseif( $status !=='SUCCESS'){
                $redirectUrl = sprintf(
                    "%sindex.php?type=error&cl=payment&payerror=-51&payerrortext=%s",
                    Registry::getConfig()->getShopSecureHomeURL(),"payment status is failure");
                    Registry::getUtils()->redirect($redirectUrl, true, 302);  
               
            }
            else{
               $sReturnURL = htmlspecialchars_decode(Registry::getConfig()->getShopCurrentURL()) . 'cl=thankyou&stoken='.$this->getRequestParameter('stoken');
                    Registry::getUtils()->redirect($sReturnURL, true, 302); 
             
             
            }
        }
    }


}