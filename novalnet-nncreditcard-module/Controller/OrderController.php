<?php

namespace Nncreditcard\Controller;
use OxidEsales\Eshop\Core\Registry;
use Nncreditcard\Core\Novalnetutil;


class OrderController extends OrderController_parent
{

    public function render()
    {
        if($_REQUEST['status'] && $_REQUEST['txn_secret'] &&  $_REQUEST['checksum'] &&  $_REQUEST['status_text']) {
            $request = $_REQUEST;
            $novalnetUtil = new Novalnetutil();
            $result=$novalnetUtil->checkSum($_REQUEST);
          
          
        }
        return parent::render();
    }
  
}
