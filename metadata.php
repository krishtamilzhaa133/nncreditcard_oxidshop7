<?php

$sMetadataVersion = '2.1';


$aModule = [
        'id'          =>'nncreditcard',
        'title'       => [
            'de' => 'Novalnet Credit Card Payment',
            'en' => 'Novalnet Credit Card Payment',
        ],
        'description' => [ 'de' => 'This Extension for Novalnet Card Payment',
                           'en' => 'This Extension for Novalnet Card Payment',
        ],
        'thumbnail'   => 'img/nncard.png',
        'version'     => '2.4.0',
        'author'      => 'Novalnet Developer',
        'url'         => 'https://www.novalnet.com',
        'email'       => 'technical@novalnet.de',
        'extend'      => [
            \OxidEsales\Eshop\Application\Controller\PaymentController::class       => Nncreditcard\Controller\PaymentController::class,
            \OxidEsales\Eshop\Application\Model\Order::class                         => Nncreditcard\Model\Order::class,
      
        ],
        'controllers'  => [  
              
            
        ],


        'settings'      => [
            ['group' => 'nncreditcardpayment', 'name' => 'nncreditcardtestmode',    'type' => 'bool',    'value' => 'true', 'position' => 1 ],
            ['group' => 'nncreditcardpayment', 'name' => 'nncreditcardpaymentaction','type' => 'bool',    'value' => 'false', 'position' => 2 ],
            ['group' => 'nncreditcardpayment', 'name' => 'nncreditcardinline',   'type' => 'bool',    'value' => 'false', 'position' => 3 ],
            ['group' => 'nncreditcardpayment', 'name' => 'nncreditcard3dsecure',   'type' => 'bool',    'value' => 'false', 'position' => 4 ],
            
            
        ],
        'events'    => [
            'onActivate'    => \Nncreditcard\Core\Events::class.'::onActivate',
            'onDeactivate'  => \Nncreditcard\Core\Events::class.'::onDeactivate',
        ],
];
