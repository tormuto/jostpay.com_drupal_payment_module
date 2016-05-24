<?php
function jostpay_help($path, $arg) {
    //$output = '<p>'.  t("jostpay is a module that extends functionality of sms framework.");
    //    The line above outputs in ALL admin/module pages
    switch ($path) {
        case "admin/help/jostpay":
        $output = '<p>'.  t("eCommerce - JostPay - Drupal Plugin.") .'</p>';
            break;
    }
    return $output;
} // function jostpay_help

/**
 * Valid permissions for this module
 * @return array An array of valid permissions for the jostpay module
 */
function jostpay_perm() {
    return array('administer jostpay');
} // function jostpay_perm()

/**
 * Menu for this module
 * @return array An array with this module's settings.
 */
function jostpay_menu() {
    $items = array();


      //Link to the sms_zone admin page:
    $items['jostpay'] = array(
        'title' => 'JostPay (Mastercard, Visacard, Verve, Perfect Money & Bitcoin)',
        'description' => 'JostPay - Drupal Plugin',

		'page callback'    => 'drupal_get_form',
        'page arguments'   => array('jostpay_form'),

        'access arguments' => array('administer nodes'),
        'type' => MENU_NORMAL_ITEM,
    );
	

    return $items;
}




function jostpay_form() {
   $form['merchant'] = array(
      '#type' => 'textfield', 
      '#title' => t('JostPay Merchant ID'), 
      '#default_value' => variable_get('vogue_merchant',''), 
      '#description' => t(''),
      '#required' => TRUE
	  );

    $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Changes'),
  );


	  return $form;
}


function jostpay_form_submit(&$form, $form_state) {

$merchant=$form_state['values']['merchant'];

variable_set('vogue_merchant',$merchant);

drupal_set_message(t("Your changes were saved successfully."));
  
$form_state['redirect'] = 'jostpay';
}

function jostpay_nodeapi(&$node, $op, $a3 = NULL, $a4 = NULL)  {
		
		
		
if($op=="alter")	 {		

 $sitename  = variable_get('site_name', '');
 
         // Get the body
        $body = $node->body;

        // Regular expression to fetch the jostpay tags
		$regex = '/{jostpay\s*.*?}/i';
		preg_match_all( $regex, $body, $matches );

		
        // Fetch the default parameters
        $merchant_id= variable_get('vogue_merchant','');
			
			
		foreach($matches[0] as $key => $match) {

			$pattern = '/item\s*\((?<val>[^\(\)]+)\)/';
			preg_match($pattern, $match, $m);
			$item = $m['val'];
			$pattern = '/price\s*\((?<val>[^\(\)]+)\)/';
			preg_match($pattern, $match, $m);
			$price = $m['val'];
			$pattern = '/description\s*\((?<val>[^\(\)]+)\)/';
			preg_match($pattern, $match, $m);
			$description = empty($m['val']) ? $item.' at '.number_format($price,2) : $m['val'];
			
			$f = '<form method="POST" action="https://jostpay.com/sci/">
			<input type="hidden" name="merchant" value="'.$merchant_id.'" />
			<input type="hidden" name="memo" value="'.$item.' ('.number_format($price,2).') order from '.$sitename.' ('.$description.')" />
			<input type="hidden" name="description_1" value="'.$description.'" />
			<input type="hidden" name="amount" value="'.$price.'" />
			<input type="submit" value="Pay with Mastercard, Visacard, Verve, Perfect Money or Bitcoin (via JostPay)" />
			</form>';
			
	/*		
	<input type='hidden' name='notification_url' value='$notify_url' />
	<input type='hidden' name='success_url' value='$notify_url' />
	<input type='hidden' name='cancel_url' value='$notify_url' />
		*/	
            $body = str_replace($match,$f,$body);
		}
		
		$node->body=$body;
}
		
}