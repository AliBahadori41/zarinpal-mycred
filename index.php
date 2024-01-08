<?php
/**
* Plugin Name: درگاه زرین پال myCRED
* Description: درگاه پرداخت زرین پال برای myCRED
* Version: 2.0.0
* Author: Ali Bahadori
* Author URI: https://bahadori.dev
*/

add_action('plugins_loaded','mycred_zarinpal_plugins_loaded');
function mycred_zarinpal_plugins_loaded(){
	
    add_filter('mycred_setup_gateways', 'Add_Zarinpal_to_Gateways_By_BAHADORI');
	function Add_Zarinpal_to_Gateways_By_BAHADORI($installed) {    
        $installed['zarinpal'] = array(
            'title' => get_option('zarinpal_name') ? get_option('zarinpal_name') : 'زرین پال',
            'callback' => array('myCred_Zarinpal')
        );
        return $installed;
    }

	add_filter('mycred_buycred_refs', 'Add_Zarinpal_to_Buycred_Refs_By_BAHADORI');
	function Add_Zarinpal_to_Buycred_Refs_By_BAHADORI($addons ) {    
		$addons['buy_creds_with_zarinpal']          = __( 'buyCRED Purchase (ZarinPal)', 'mycred' );
		return $addons;
	}
	
	add_filter('mycred_buycred_log_refs', 'Add_Zarinpal_to_Buycred_Log_Refs_By_BAHADORI');
	function Add_Zarinpal_to_Buycred_Log_Refs_By_BAHADORI( $refs ) {
		$zarinpal = array('buy_creds_with_zarinpal');
		return $refs = array_merge($refs, $zarinpal);
	}
}
	
spl_autoload_register('mycred_zarinpal_plugin');
function mycred_zarinpal_plugin(){	
	
	if ( ! class_exists( 'myCRED_Payment_Gateway' ) ) 
		return;
	
	if ( !class_exists( 'myCred_Zarinpal' ) ) {
		class myCred_Zarinpal extends myCRED_Payment_Gateway {
	
			function __construct($gateway_prefs) {        
				$types = mycred_get_types();
				$default_exchange = array();
				foreach ($types as $type => $label)
					$default_exchange[$type] = 1000;

				parent::__construct(array(
					'id' => 'zarinpal',
					'label' => get_option('zarinpal_name') ? get_option('zarinpal_name') : 'زرین پال',
						'defaults'         => array(
							'zarinpal_merchant'          => '',
							'zarinpal_name'          => 'زرین پال',
							'currency'         => 'ریال',
							'exchange'         => $default_exchange,
							'item_name'        => __( 'Purchase of myCRED %plural%', 'mycred' ),
							'server'            => 'German',
						)
				), $gateway_prefs );
			}
		
			public function Zarinpal_Iranian_currencies_By_BAHADORI( $currencies ) {
				unset( $currencies );
				$currencies['ریال'] = 'ریال';
				$currencies['تومان'] = 'تومان';
				return $currencies;
			}
			
			/**
			* Gateway Prefs
			* @since 1.4
			* @version 1.0
			*/
			function preferences() {
				add_filter( 'mycred_dropdown_currencies', array( $this, 'Zarinpal_Iranian_currencies_By_BAHADORI' ) );
				$prefs = $this->prefs; ?>

				<label class="subheader" for="<?php echo $this->field_id( 'zarinpal_merchant' ); ?>"><?php _e( 'مرچنت', 'mycred' ); ?></label>
				<ol>
					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( 'zarinpal_merchant' ); ?>" id="<?php echo $this->field_id( 'zarinpal_merchant' ); ?>" value="<?php echo $prefs['zarinpal_merchant']; ?>" class="long" /></div>
					</li>
				</ol>
				<label class="subheader" for="<?php echo $this->field_id( 'zarinpal_name' ); ?>"><?php _e( 'نام نمایشی درگاه', 'mycred' ); ?></label>
				<ol>
					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( 'zarinpal_name' ); ?>" id="<?php echo $this->field_id( 'zarinpal_name' ); ?>" value="<?php echo $prefs['zarinpal_name'] ? $prefs['zarinpal_name'] : 'زرین پال'; ?>"  /></div>
					</li>
				</ol>
				<label class="subheader" for="<?php echo $this->field_id( 'currency' ); ?>"><?php _e( 'Currency', 'mycred' ); ?></label>
				<ol>
					<li>
						<?php $this->currencies_dropdown( 'currency', 'mycred-gateway-zarinpal-currency' ); ?>
					</li>
				</ol>
				<label class="subheader" for="<?php echo $this->field_id( 'server' ); ?>"><?php _e( 'سرور زرین پال', 'mycred' ); ?></label>
				<ol>
					<li>
						<select name="<?php echo $this->field_name( 'server' ); ?>" id="<?php echo $this->field_id( 'server' ); ?>">
						<?php
						$options = array(
							'German'   => __( 'آلمان', 'mycred' ),
							'Iran'    => __( 'ایران', 'mycred' )
						);
						foreach ( $options as $value => $label ) {
							echo '<option value="' . $value . '"';
							if ( $prefs['server'] == $value ) 
								echo ' selected="selected"';
							echo '>' . $label . '</option>';
						}
						?>
						</select>
					</li>
				</ol>
				<label class="subheader" for="<?php echo $this->field_id( 'item_name' ); ?>"><?php _e( 'Item Name', 'mycred' ); ?></label>
				<ol>
					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( 'item_name' ); ?>" id="<?php echo $this->field_id( 'item_name' ); ?>" value="<?php echo $prefs['item_name']; ?>" class="long" /></div>
						<span class="description"><?php _e( 'Description of the item being purchased by the user.', 'mycred' ); ?></span>
					</li>
				</ol>
				<label class="subheader"><?php _e( 'Exchange Rates', 'mycred' ); ?></label>
				<ol>
					<?php $this->exchange_rate_setup(); ?>
				</ol>
			<?php
			}
		
			/**
			* Sanatize Prefs
			* @since 1.4
			* @version 1.1
			*/
			public function sanitise_preferences( $data ) {

				$new_data['zarinpal_merchant'] = sanitize_text_field( $data['zarinpal_merchant'] );
				$new_data['zarinpal_name'] = sanitize_text_field( $data['zarinpal_name'] );
				$new_data['currency'] = sanitize_text_field( $data['currency'] );
				$new_data['item_name'] = sanitize_text_field( $data['item_name'] );
				$new_data['server'] = sanitize_text_field( $data['server'] );

				// If exchange is less then 1 we must start with a zero
				if ( isset( $data['exchange'] ) ) {
					foreach ( (array) $data['exchange'] as $type => $rate ) {
						if ( $rate != 1 && in_array( substr( $rate, 0, 1 ), array( '.', ',' ) ) )
							$data['exchange'][ $type ] = (float) '0' . $rate;
					}
				}
				$new_data['exchange'] = $data['exchange'];
			
				update_option('zarinpal_name', $new_data['zarinpal_name']);
			
				return $data;
			}

			/**
			* Buy Creds
			* @since 1.4
			* @version 1.1
			*/
			public function buy() {
				if ( ! isset( $this->prefs['zarinpal_merchant'] ) || empty( $this->prefs['zarinpal_merchant'] ) )
					wp_die( __( 'Please setup this gateway before attempting to make a purchase!', 'mycred' ) );

				// Type
				$type = $this->get_point_type();
				$mycred = mycred( $type );

				// Amount
				$amount = $mycred->number( $_REQUEST['amount'] );
				$amount = abs( $amount );

				// Get Cost
				$cost = $this->get_cost( $amount, $type );

				$to = $this->get_to();
				$from = $this->current_user_id;

				// Revisiting pending payment
				if ( isset( $_REQUEST['revisit'] ) ) {
					$this->transaction_id = strtoupper( $_REQUEST['revisit'] );
				}
				else {
					$post_id = $this->add_pending_payment( array( $to, $from, $amount, $cost, $this->prefs['currency'], $type ) );
					$this->transaction_id = get_the_title( $post_id );
				}

				// Thank you page
				//$thankyou_url = $this->get_thankyou();

				// Cancel page
				//$cancel_url = $this->get_cancelled( $this->transaction_id );

				// Item Name
				$item_name = str_replace( '%number%', $amount, $this->prefs['item_name'] );
				$item_name = $mycred->template_tags_general( $item_name );
	
				$from_user = get_userdata( $from );
			
				$return_url =  add_query_arg('payment_id', $this->transaction_id, $this->callback_url());
			
				$buyername = $from_user->first_name . " " . $from_user->last_name;
				$buyername = strlen($buyername) > 2 ? "|".$buyername : "";
			
			
				$MerchantID = $this->prefs['zarinpal_merchant'];  
				$Amount = ($this->prefs['currency'] == 'تومان') ? $cost : ($cost/10);
				$Amount = intval( str_replace( ',' , '', $Amount) );
				$Description = $item_name.$buyername;
				$Description = $Description ? $Description : "خرید اعتبار";
				$CallbackURL = $return_url;
				$Email = $from_user->user_email; 
				$Mobile ='-'; 
				$Server = ($this->prefs['server'] == 'Iran' ) ? 'https://ir.zarinpal.com/pg/services/WebGate/wsdl' : 'https://de.zarinpal.com/pg/services/WebGate/wsdl';
			/*	$client = new SoapClient( $Server, array('encoding' => 'UTF-8'));
				$result = $client->PaymentRequest(
						array(
								'MerchantID' 	=> $MerchantID,
								'Amount' 	=> $Amount,
								'Description' 	=> $Description,
								'Email' 	=> $Email,
								'Mobile' 	=> $Mobile,
								'CallbackURL' 	=> $CallbackURL
							)
				);*/

                $data = array("merchant_id" => $MerchantID,
                    "amount" => $Amount,
                    "callback_url" => $return_url,
                    "description" => $item_name.$buyername
                );
                $jsonData = json_encode($data);
                $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
                curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData)
                ));

                $result = curl_exec($ch);
                $err = curl_error($ch);
                $result = json_decode($result, true, JSON_PRETTY_PRINT);
                curl_close($ch);

                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    if (empty($result['errors'])) {
                        if ($result['data']['code'] == 100) {
                            header('Location: https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);
                        }
                    } else {
                       // echo'Error Code: ' . $result['errors']['code'];
                       // echo'message: ' .  $result['errors']['message'];

                        $this->get_page_header( __( 'Processing payment &hellip;', 'mycred' ) );
                        echo $this->Fault($result['errors']['code']);
                        $this->get_page_footer();

                    }
                }
                $message = 'خطا رخ داده است د';
                $message = isset($result->errorMessage) ? $result->errorMessage : $message;

                $this->log_call($payment, [__($message, 'mycred')]);

                wp_die($message);
                exit;


                //Redirect to Zarinpal
		/*		if($result->Status == 100)
				{
					Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result->Authority);
				} 
				else 
				{	
					$this->get_page_header( __( 'Processing payment &hellip;', 'mycred' ) ); 
					echo $this->Fault($result->Status);
					$this->get_page_footer();
				}
				// Exit
		        $message = 'خطا رخ داده است د';
                        $message = isset($result->errorMessage) ? $result->errorMessage : $message;

                        $this->log_call($payment, [__($message, 'mycred')]);

                        wp_die($message);
                        exit;
			}*/

			/**
			* Process
			* @since 1.4
			* @version 1.1
			*/
			public function process() {
				// Required fields
				if (  isset($_REQUEST['payment_id']) && isset($_REQUEST['mycred_call']) && $_REQUEST['mycred_call'] == 'zarinpal') 
				{	
					$new_call = array();
					$redirect = $this->get_cancelled("");
					// Get Pending Payment
					$pending_post_id = sanitize_key( $_REQUEST['payment_id'] );
					$org_pending_payment = $pending_payment = $this->get_pending_payment( $pending_post_id );
					
					if (is_object($pending_payment))
						$pending_payment = (array) $pending_payment;
					
					if ( $pending_payment !== false ) {
					
						$cost = ( str_replace( ',' , '', $pending_payment['cost']) );
						$cost = (int) $cost;
						$Amount = ($this->prefs['currency'] == 'تومان') ? $cost : ($cost/10);
						
						$MerchantID = $this->prefs['zarinpal_merchant'];  
						$Authority = $_GET['Authority'];
						if($_GET['Status'] == 'OK'){
						/*	$Server = ($this->prefs['server'] == 'Iran' ) ? 'https://ir.zarinpal.com/pg/services/WebGate/wsdl' : 'https://de.zarinpal.com/pg/services/WebGate/wsdl';
							$client = new SoapClient( $Server, array('encoding' => 'UTF-8')); 
							$result = $client->PaymentVerification(
								array(
										'MerchantID'	 => $MerchantID,
										'Authority' 	 => $Authority,
										'Amount'	 => $Amount
									)
							);*/

                            $data = array("merchant_id" => $MerchantID, "authority" => $Authority, "amount" => $Amount);
                            $jsonData = json_encode($data);
                            $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
                            curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/json',
                                'Content-Length: ' . strlen($jsonData)
                            ));

                            $result = curl_exec($ch);
                            curl_close($ch);
                            $result = json_decode($result, true);
                            $err = curl_error($ch);
                            if ($err) {
                                echo "cURL Error #:" . $err;
                            } else {
                                if ($result['data']['code'] == 100) {
                                   // echo 'Transation success. RefID:' . $result['data']['ref_id'];
                                    if ( $this->complete_payment( $org_pending_payment, $result['data']['ref_id'] ) ) {
                                        $new_call[] = sprintf( __( 'تراکنش با موفقیت به پایان رسید . کد رهگیری : %s', 'mycred' ), $result['data']['ref_id'] );
                                        $this->trash_pending_payment( $pending_post_id );
                                        $redirect = $this->get_thankyou();
                                    }
                                } else {
                                    echo'code: ' . $result['errors']['code'];
                                    echo'message: ' .  $result['errors']['message'];
                                    $new_call[] = sprintf( __( 'در حین تراکنش خطای رو به رو رخ داده است : %s', 'mycred' ),$result['errors']['code'] );
                                }
                            }

                            if ( !empty( $new_call ) )
                                $this->log_call( $pending_post_id, $new_call );

                            wp_redirect($redirect);
                            die();



					/*		if($result->Status == 100){
								if ( $this->complete_payment( $org_pending_payment, $result->RefID ) ) {
									$new_call[] = sprintf( __( 'تراکنش با موفقیت به پایان رسید . کد رهگیری : %s', 'mycred' ), $result->RefID );
									$this->trash_pending_payment( $pending_post_id );
									$redirect = $this->get_thankyou();
								}
								else
									$new_call[] = __( 'در حین تراکنش خطای نامشخصی رخ داده است .', 'mycred' );
							}
							else
								$new_call[] = sprintf( __( 'در حین تراکنش خطای رو به رو رخ داده است : %s', 'mycred' ), $this->Fault($result->Status) );
						}
						else
							$new_call[] = __( 'تراکنش به دلیل انصراف کاربر از ادامه پرداخت نا تمام باقی ماند .', 'mycred' );
				
					}
					else
						$new_call[] = __( 'در حین تراکنش خطای نامشخصی رخ داده است .', 'mycred' );
			
			
					if ( !empty( $new_call ) )
						$this->log_call( $pending_post_id, $new_call );
				
					wp_redirect($redirect);
					die();*/
				
				}

					}
                    }
			}
			
			
			/**
			* Returning
			* @since 1.4
			* @version 1.0
			*/
			public function returning() { 
				if (  isset($_REQUEST['payment_id']) && isset($_REQUEST['mycred_call']) && $_REQUEST['mycred_call'] == 'zarinpal') 
				{
					// DO Some Actions
				}
			}


			private static function Fault($err_code){
				$message = " ";
				switch($err_code)
				{
					case "-1" :
						$message = "اطلاعات ارسال شده ناقص است .";
					break;

					case "-2" :
						$message = "آی پی یا مرچنت زرین پال اشتباه است .";
					break;

					case "-3" :
						$message = "با توجه به محدودیت های شاپرک امکان پرداخت با رقم درخواست شده میسر نمیباشد .";
					break;
                                                
					case "-4" :
						$message = "سطح تایید پذیرنده پایین تر از سطح نقره ای میباشد .";
					break;
												
					case "-11" :
						$message = "درخواست مورد نظر یافت نشد .";
					break;
												
					case "-21" :
						$message = "هیچ نوع عملیات مالی برای این تراکنش یافت نشد .";
					break;
												
					case "-22" :
						$message = "تراکنش نا موفق میباشد .";
					break;
												
					case "-33" :
						$message = "رقم تراکنش با رقم وارد شده مطابقت ندارد .";
					break;
												
					case "-40" :
						$message = "اجازه دسترسی به متد مورد نظر وجود ندارد .";
					break;
												
					case "-54" :
						$message = "درخواست مورد نظر آرشیو شده است .";
					break;
												
					case "100" :
						$message = "تراکنش با موفقیت به پایان رسید .";
					break;
				
					case "101" :
						$message = "تراکنش با موفقیت به پایان رسیده بود و تاییدیه آن نیز انجام شده بود .";
					break;			
				}
				return $message;
			}

			
		}

	}
}
?>