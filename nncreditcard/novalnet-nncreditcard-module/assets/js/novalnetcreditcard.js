window.onload = function() {
	// Set your Client key
	NovalnetUtility.setClientKey('88fcbbceb1948c8ae106c3fe2ccffc12');
	
	var userdata = document.getElementById('nncustomerdata').value;
	var customerdata = JSON.parse(userdata);  
	console.log(customerdata);
	console.log(customerdata.userlastname);

	var configurationObject = {
	
		// You can handle the process here, when specific events occur.
		callback: {
		
			// Called once the pan_hash (temp. token) created successfully.
			on_success: function (data) {
				document.getElementById('novalnet_pan_hash').value = data ['hash'];
				document.getElementById('novalnet_unique_id').value = data ['unique_id'];
				document.getElementById('novalnet_do_redirect').value = data ['do_redirect'];
				document.querySelector('form[id="payment"]').submit();
		    
				return true;
			},
			
			// Called in case of an invalid payment data or incomplete input. 
			on_error:  function (data) {
				if ( undefined !== data['error_message'] ) {
					alert(data['error_message']);
					return false;
				}
			},
			
			// Called in case the challenge window Overlay (for 3ds2.0) displays 
			on_show_overlay:  function (data) {
				document.getElementById('novalnet_iframe').classList.add(".overlay");
			},
			
			// Called in case the Challenge window Overlay (for 3ds2.0) hided
			on_hide_overlay:  function (data) {
				document.getElementById('novalnet_iframe').classList.remove(".overlay");
			}
		},
		
		// You can customize your Iframe container styel, text etc. 
		iframe: {
		
			// It is mandatory to pass the Iframe ID here.  Based on which the entire process will took place.
			id: customerdata.creditcard_iframe,
			
			// Set to 1 to make you Iframe input container more compact (default - 0)
			inline: 0,
			
			// Add the style (css) here for either the whole Iframe contanier or for particular label/input field
			style: {
				// The css for the Iframe container
				container: "",
			
				// The css for the input field of the Iframe container
				input: "width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; border:2px solid LightGray;border-radius: 10px",
			
				// The css for the label of the Iframe container
				label: "font-weight: bold; color:#091f36;font-family: 'Courier New', monospace;"
			},
			
			
			// You can customize the text of the Iframe container here
			text: {
				
				// The End-customers selected language. The Iframe container will be rendered in this Language.
				lang : customerdata.language_code,
				
				// Basic Error Message
				error: "Your credit card details are invalid",
				
				// You can customize the text for the Card Holder here
				card_holder : {
				
					// You have to give the Customized label text for the Card Holder Container here
					label: "Card holder name",
					
					// You have to give the Customized placeholder text for the Card Holder Container here
					place_holder: "Name on card",
					
					// You have to give the Customized error text for the Card Holder Container here
					error: "Please enter the valid card holder name"
				},
				card_number : {
					
					// You have to give the Customized label text for the Card Number Container here
					label: "Card number",
					
					// You have to give the Customized placeholder text for the Card Number Container here
					place_holder: "XXXX XXXX XXXX XXXX",
					
					// You have to give the Customized error text for the Card Number Container here
					error: "Please enter the valid card number"
				},
				expiry_date : {
				
					// You have to give the Customized label text for the Expiry Date Container here
					label: "Expiry date",
					
					// You have to give the Customized error text for the Expiry Date Container here
					error: "Please enter the valid expiry month / year in the given format"
				},
				cvc : {
				
					// You have to give the Customized label text for the CVC/CVV/CID Container here
					label: "CVC/CVV/CID",
					
					// You have to give the Customized placeholder text for the CVC/CVV/CID Container here
					place_holder: "XXX",
					
					// You have to give the Customized error text for the CVC/CVV/CID Container here
					error: "Please enter the valid CVC/CVV/CID"
				}
			}
		},
		
		// Add Customer data
		customer: {
			
			// Your End-customer's First name which will be prefilled in the Card Holder field
			first_name:customerdata.userfistname,
			
			// Your End-customer's Last name which will be prefilled in the Card Holder field
			last_name: customerdata.userlastname,
			
			// Your End-customer's Email ID. 
			email: customerdata.useremail,
			
			// Your End-customer's billing address.
			billing: {
			
				// Your End-customer's billing street (incl. House no).
				street: customerdata.street_address,
				
				// Your End-customer's billing city.
				city:customerdata.city,
				
				// Your End-customer's billing zip.
				zip:customerdata.postcode,
				
				// Your End-customer's billing country ISO code.
				country_code:customerdata.country_code
			},
			shipping: {
			
				// Set to 1 if the billing and shipping address are same and no need to specify shipping details again here.
				"same_as_billing": 1,
			},
		},
		
		// Add transaction data
		transaction: {
			
			// The payable amount that can be charged for the transaction (in minor units), for eg:- Euro in Eurocents (5,22 EUR = 522).
			amount:customerdata.total,
			
			// The three-character currency code as defined in ISO-4217.
			currency:customerdata.currency,
			
			// Set to 1 for the TEST transaction (default - 0).
			test_mode:customerdata.testmode
		},
		custom: {
			
			// Shopper's selected language in shop
			lang:customerdata.language_code
		}
	}
	
	//if(values.fields.same_as_billing == 1){ configurationObject.customer.shipping = { "same_as_billing" : 1 } } 
	//console.log(configurationObject.customer);
	// Create the Credit Card form
	NovalnetUtility.createCreditCardForm(configurationObject);
};


document.getElementById('payment').addEventListener('submit', function(event) {
   // console.log("onclick works");

    if (
        document.getElementById('payment_nncreditcard').checked &&
        document.getElementById('payment_nncreditcard').value === "nncreditcard"
    ){
       // console.log("inside the block success");
        if (document.getElementById('novalnet_pan_hash') !== undefined && document.getElementById('novalnet_pan_hash').value === '') {
            // console.log("inside the panhash");
            event.preventDefault();
            event.stopImmediatePropagation();
            NovalnetUtility.getPanHash();
        }
    }
});

