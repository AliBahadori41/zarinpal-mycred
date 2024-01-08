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
							'currency'         => 'IRR',
							'exchange'         => $default_exchange,
							'item_name'        => __( 'Purchase of myCRED %plural%', 'mycred' ),
						)
				), $gateway_prefs );
			}
		
			public function Zarinpal_Iranian_currencies_By_BAHADORI( $currencies ) {
				unset( $currencies );
				$currencies['IRR'] = 'ریال';
				$currencies['IRT'] = 'تومان';
				return $currencies;
			}
			
			/**
			* Gateway Prefs
			* @since 1.4
			* @version 1.0
			*/
			function preferences() {
				add_filter( 'mycred_dropdown_currencies', array( $this, 'Zarinpal_Iranian_currencies_By_BAHADORI' ) );
				$prefs = $this->prefs;
			?>
			
			<div class="row">
				<div class="col-12 col-xl-6">
					<div class="form-group">
						<div class="mb-3">
							<label for="<?php echo $this->field_id( 'zarinpal_merchant' ); ?>" class="form-label">مرچنت کد</label>
							<input
								type="text"
								class="form-control"
								name="<?php echo $this->field_name( 'zarinpal_merchant' ); ?>"
								id="<?php echo $this->field_id( 'zarinpal_merchant' ); ?>"
								value="<?php echo $prefs['zarinpal_merchant']; ?>"
								aria-describedby="helpId"
								placeholder="مرچنت کد"
							/>
							<small id="helpId" class="form-text text-muted">برای دریافت مرچنت کد به حساب کاربری خود در <a target="_blank" href="https://zarinpal.com">زرین پال</a> مرجعه کنید</small>
						</div>
					</div>
				</div>
				<div class="col-12 col-xl-6">
					<div class="form-group">
						<div class="mb-3">
							<label for="<?php echo $this->field_id( 'zarinpal_name' ); ?>" class="form-label">نام نمایشی درگاه</label>
							<input
								type="text"
								class="form-control"
								name="<?php echo $this->field_name( 'zarinpal_name' ); ?>"
								id="<?php echo $this->field_id( 'zarinpal_name' ); ?>"
								value="<?php echo $prefs['zarinpal_name']; ?>"
								aria-describedby="helpId"
								placeholder="نام نمایشی درگاه"
							/>
							<small id="helpId" class="form-text text-muted">نامی دلخواه خود که قصد دارید به کاربر نمایش دهید.</small>
						</div>
					</div>
				</div>
				<div class="col-12 col-xl-6">
					<div class="form-group">
						<div class="mb-3">
							<label for="zarinpal_currency" class="form-label">ارز درگاه</label>
							<?php $this->currencies_dropdown( 'currency', 'mycred-gateway-zarinpal-currency' ); ?>
							<small id="helpId" class="form-text text-muted">انتخاب کردن ارز درگاه</small>
						</div>
					</div>
				</div>
				<div class="col-12 col-xl-6">
					<div class="form-group">
						<div class="mb-3">
							<div class="mb-3">
								<label for="<?php echo $this->field_id( 'item_name' ); ?>" class="form-label">توضیح تراکنش</label>
								<input
									type="text"
									class="form-control"
									aria-describedby="helpId"
									placeholder="توضیحات تراکنش"
									name="<?php echo $this->field_name( 'item_name' ); ?>"
									id="<?php echo $this->field_id( 'item_name' ); ?>"
									value="<?php echo $prefs['item_name']; ?>"
								/>
								<small id="helpId" class="form-text text-muted">توضیحاتی که برای هر تراکنش ایجاد شده ثبت می شود.</small>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12">
				<?php $this->exchange_rate_setup(); ?>
				</div>
			</div>
			
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

				// Item Name
				$item_name = str_replace( '%number%', $amount, $this->prefs['item_name'] );
				$item_name = $mycred->template_tags_general( $item_name );
	
				$from_user = get_userdata( $from );
				$return_url =  add_query_arg('payment_id', $this->transaction_id, $this->callback_url());
				$buyername = $from_user->first_name . " " . $from_user->last_name;
				$buyername = strlen($buyername) > 2 ? "|".$buyername : "";
				$Description = $item_name.$buyername;
				$Description = $Description ? $Description : "خرید اعتبار";

                $data = array(
					"merchant_id" => $this->prefs['zarinpal_merchant'],
                    "amount" => $cost,
					"currency" => $this->prefs['currency'],
                    "callback_url" => $return_url,
                    "description" => $item_name.$buyername
                );

                $jsonData = json_encode($data);
                $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
                curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
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
                    echo " خطا در اتصال به درگاه : " . $err;
                } else {
                    if (empty($result['errors'])) {
                        if ($result['data']['code'] == 100) {
                            header('Location: https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);
                        }
                    } else {

                        $this->get_page_header( __( 'Processing payment &hellip;', 'mycred' ) );
                        echo self::error_message($result['errors']['code']);
                        $this->get_page_footer();

                    }
                }
                $message = 'خطا رخ داده است.';
                $message = isset($result->errorMessage) ? $result->errorMessage : $message;

                $this->log_call($payment, [__($message, 'mycred')]);

                wp_die($message);
                exit;

			}

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
						if($_GET['Status'] == 'OK'){
							$MerchantID = $this->prefs['zarinpal_merchant'];  
							$Authority = $_GET['Authority'];

							$cost = ( str_replace( ',' , '', $pending_payment['cost']) );
							$cost = (int) $cost;

                            $data = array(
								"merchant_id" => $MerchantID,
								"authority" => $Authority,
								"amount" => $cost,
							);

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
                                echo "خطا در اتصال به درگاه برای وریفای تراکنش : " . $err;
                            } else {
								$code = $result['data']['code'];
                                if ($code === 100) {
                                    if ( $this->complete_payment( $org_pending_payment, $result['data']['ref_id'] ) ) {
                                        $new_call[] = sprintf( __( 'تراکنش با موفقیت به پایان رسید . کد رهگیری : %s', 'mycred' ), $result['data']['ref_id'] );
                                        $this->trash_pending_payment( $pending_post_id );
                                        $redirect = $this->get_thankyou();
                                    }

                                } elseif ($code === 101) {
									$new_call[] = sprintf( __( ' تراکنش یکبار قبلا وریفای شده است.  : %s', 'mycred' ), $result['data']['ref_id'] );
									$redirect = $this->get_thankyou();
                                } else {
                                    echo' خطا : </br>' ;
                                    echo self::error_message($result['errors']['code']);
                                    $new_call[] = sprintf( __( 'در حین تراکنش خطای رو به رو رخ داده است : %s', 'mycred' ),$result['errors']['code'] );
                                }
                            }

                            if ( !empty( $new_call ) )
                                $this->log_call( $pending_post_id, $new_call );

                            wp_redirect($redirect);
                            die();
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

			/**
			 * Zarinpal error message.
			 * 
			 * @param int $code
			 * @return string
			 */
			public static function error_message($code)
			{
				$message = null;
				switch ($code) {
					case $code == -9:
						$message = ('اطلاعات ارسال شده نادرست می باشد.');
						$message .= "<br>" . ('1- مرچنت کد داخل تنظیمات وارد نشده باشد');
						$message .= "<br>" . ('2- مبلغ پرداختی کمتر یا بیشتر از حد مجاز می باشد');
					break; 
					case $code == -10:
						$message = ('ای پی یا مرچنت كد پذیرنده صحیح نیست.');
					break; 
					case $code == -11:
						$message = ('مرچنت کد فعال نیست، پذیرنده مشکل خود را به امور مشتریان زرین‌پال ارجاع دهد.');
					break; 
					case $code == -12:
						$message = ('تلاش بیش از دفعات مجاز در یک بازه زمانی کوتاه به امور مشتریان زرین پال اطلاع دهید');
					break; 
					case $code == -15:
						$message = ('درگاه پرداخت به حالت تعلیق در آمده است، پذیرنده مشکل خود را به امور مشتریان زرین‌پال ارجاع دهد.');
					break; 
					case $code == -16:
						$message = ('سطح تایید پذیرنده پایین تر از سطح نقره ای است.');
					break; 
					case $code == -17:
						$message = ('محدودیت پذیرنده در سطح آبی');
					break; 
					case $code == -30:
						$message = ('پذیرنده اجازه دسترسی به سرویس تسویه اشتراکی شناور را ندارد.');
					break; 
					case $code == -31:
						$message = ('حساب بانکی تسویه را به پنل اضافه کنید. مقادیر وارد شده برای تسهیم درست نیست. پذیرنده جهت استفاده از خدمات سرویس تسویه اشتراکی شناور، باید حساب بانکی معتبری به پنل کاربری خود اضافه نماید.');
					break; 
					case $code == -32:
						$message = ('مبلغ وارد شده از مبلغ کل تراکنش بیشتر است.');
					break; 
					case $code == -33:
						$message = ('درصدهای وارد شده صحیح نیست.');
					break; 
					case $code == -34:
						$message = ('مبلغ وارد شده از مبلغ کل تراکنش بیشتر است.');
					break; 
					case $code == -35:
						$message = ('تعداد افراد دریافت کننده تسهیم بیش از حد مجاز است.');
					break; 
					case $code == -36:
						$message = ('حداقل مبلغ جهت تسهیم باید 10000 ریال باشد');
					break; 
					case $code == -37:
						$message = ('یک یا چند شماره شبای وارد شده برای تسهیم از سمت بانک غیر فعال است.');
					break; 
					case $code == -38:
						$message = ('خط،عدم تعریف صحیح شبا،لطفا دقایقی دیگر تلاش کنید.');
					break; 
					case $code == -39:
						$message = ('خطایی رخ داده است به امور مشتریان زرین پال اطلاع دهید');
					break; 
					case $code == -50:
						$message = ('مبلغ پرداخت شده با مقدار مبلغ ارسالی در متد وریفای متفاوت است.');
					break; 
					case $code == -51:
						$message = ('پرداخت ناموفق');
					break; 
					case $code == -52:
						$message = ('خطای غیر منتظره‌ای رخ داده است. پذیرنده مشکل خود را به امور مشتریان زرین‌پال ارجاع دهد.');
					break; 
					case $code == -53:
						$message = ('پرداخت متعلق به این مرچنت کد نیست.');
					break; 
					case $code == -54:
						$message = ('اتوریتی نامعتبر است.');
					break;
				}
				return $message;
			}
		}
	}
}
?>