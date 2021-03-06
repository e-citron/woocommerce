<?php
/**
 * My Account Shortcodes
 * 
 * Shows the 'my account' section where the customer can view past orders and update their information.
 *
 * @package		WooCommerce
 * @category	Shortcode
 * @author		WooThemes
 */
 
/**
 * Shortcode wrappers
 */
function get_woocommerce_my_account( $atts ) {
	global $woocommerce;
	return $woocommerce->shortcode_wrapper('woocommerce_my_account', $atts); 
}
function get_woocommerce_edit_address() {
	global $woocommerce;
	return $woocommerce->shortcode_wrapper('woocommerce_edit_address'); 
}
function get_woocommerce_change_password() {
	global $woocommerce;
	return $woocommerce->shortcode_wrapper('woocommerce_change_password'); 
}
function get_woocommerce_view_order() {
	global $woocommerce;
	return $woocommerce->shortcode_wrapper('woocommerce_view_order'); 
}

/**
 * My Account Shortcode.
 *
 * @package WooCommerce
 * @since 1.4
 */
function woocommerce_my_account( $atts ) {
	global $woocommerce, $current_user, $recent_orders;
	
	if ( ! is_user_logged_in() ) :
		
		woocommerce_get_template( 'myaccount/login.php' );
	
	else :

		extract(shortcode_atts(array(
	    	'recent_orders' => 5
		), $atts));
		
	  	$recent_orders = ('all' == $recent_orders) ? -1 : $recent_orders;
		
		get_currentuserinfo();
		
		woocommerce_get_template( 'myaccount/my-account.php' );

	endif;
		
}

/**
 * Edit Address Shortcode.
 *
 * @todo Address fields should be loaded using the array defined in 
 * the checkout class, and the fields should be built off of that.
 *
 * Adapted from spencerfinnell's pull request
 *
 * @package WooCommerce
 * @since 1.4
 */
function woocommerce_edit_address() {
	global $woocommerce, $load_address, $address;
	
	if ( ! is_user_logged_in() ) :
		wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
		exit;
	endif;

	$user_id      = get_current_user_id();
	$load_address = woocommerce_get_address_to_edit();

	$address = array(
		'first_name' => get_user_meta( $user_id, $load_address . '_first_name', true ),
		'last_name'  => get_user_meta( $user_id, $load_address . '_last_name', true ),
		'company'    => get_user_meta( $user_id, $load_address . '_company', true ),
		'email'      => get_user_meta( $user_id, $load_address . '_email', true ),
		'phone'      => get_user_meta( $user_id, $load_address . '_phone', true ),
		'address'    => get_user_meta( $user_id, $load_address . '_address_1', true ),
		'address2'   => get_user_meta( $user_id, $load_address . '_address_2', true ),
		'city'       => get_user_meta( $user_id, $load_address . '_city', true ),		
		'state'      => get_user_meta( $user_id, $load_address . '_state', true ),
		'postcode'   => get_user_meta( $user_id, $load_address . '_postcode', true ),
		'country'    => get_user_meta( $user_id, $load_address . '_country', true )
	);
	
	woocommerce_get_template( 'myaccount/edit-address.php' );
}

/**
 * Save and and update a billing or shipping address if the
 * form was submitted through the user account page.
 *
 * @todo Address fields should be loaded using the array defined in 
 * the checkout class.
 *
 * @package WooCommerce
 * @since 1.4
 */
function woocommerce_save_address() {
	global $woocommerce;

	if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
		return;

	if ( empty( $_POST[ 'action' ] ) || ( 'edit_address' !== $_POST[ 'action' ] ) )
		return;

	$woocommerce->verify_nonce( 'edit_address' );

	$user_id = get_current_user_id();

	if ( $user_id <= 0 )
		return;

	$load_address = woocommerce_get_address_to_edit();

	$_POST = array_map( 'woocommerce_clean', $_POST );

	if ( empty( $_POST[ 'address_first_name' ] ) )
		$woocommerce->add_error( __( 'First name is a required field.', 'woothemes' ) );

	if ( empty( $_POST[ 'address_last_name' ] ) )
		$woocommerce->add_error( __( 'Last name is a required field.', 'woothemes') );

	if ( empty( $_POST[ 'address_address_1' ] ) )
		$woocommerce->add_error( __( 'Address is a required field.', 'woothemes') );

	if ( empty( $_POST[ 'address_city' ] ) )
		$woocommerce->add_error( __( 'City is a required field.', 'woothemes') );

	if ( empty( $_POST[ 'address_postcode' ] ) )
		$woocommerce->add_error( __( 'Postcode is a required field.', 'woothemes') );

	if ( empty( $_POST[ 'address_country' ] ) )
		$woocommerce->add_error( __( 'Country is a required field.', 'woothemes' ) );

	if ( empty( $_POST[ 'address_state' ] ) )
		$woocommerce->add_error( __( 'State is a required field.', 'woothemes' ) );

	// Billing only
	if ( $load_address == 'billing' ) {
		if ( empty( $_POST[ 'address_email' ] ) )
			$woocommerce->add_error( __( 'Email is a required field.', 'woothemes' ) ); 

		if ( empty( $_POST[ 'address_phone' ] ) )
			$woocommerce->add_error( __( 'Phone number is a required field.', 'woothemes' ) );

		if ( ! $woocommerce->validation->is_email( $_POST[ 'address_email' ] ) )
			$woocommerce->add_error( __( 'Please enter a valid email address.', 'woothemes' ) );

		if ( ! $woocommerce->validation->is_phone( $_POST[ 'address_phone' ] ) )
			$woocommerce->add_error( __( 'Please enter a valid phone number.', 'woothemes' ) );
	}

	if ( ! $woocommerce->validation->is_postcode( $_POST[ 'address_postcode' ], $_POST[ 'address_country' ] ) )
		$woocommerce->add_error( __( 'Please enter a valid postcode/ZIP.', 'woothemes' ) ); 
	else
		$_POST[ 'address_postcode' ] = $woocommerce->validation->format_postcode( $_POST[ 'address_postcode' ], $_POST[ 'address_country' ] );

	if ( $woocommerce->error_count() == 0 ) {
		update_user_meta( $user_id, $load_address . '_first_name', $_POST[ 'address_first_name' ] );
		update_user_meta( $user_id, $load_address . '_last_name', $_POST[ 'address_last_name' ] );
		update_user_meta( $user_id, $load_address . '_company', $_POST[ 'address_company' ] );
		update_user_meta( $user_id, $load_address . '_address_1', $_POST[ 'address_address_1' ] );
		update_user_meta( $user_id, $load_address . '_address_2', $_POST[ 'address_address_2' ] );
		update_user_meta( $user_id, $load_address . '_city', $_POST[ 'address_city' ] );
		update_user_meta( $user_id, $load_address . '_postcode', $_POST[ 'address_postcode' ] );
		update_user_meta( $user_id, $load_address . '_country', $_POST[ 'address_country' ] );
		update_user_meta( $user_id, $load_address . '_state', $_POST[ 'address_state' ] );

		if ( $load_address == 'billing' ) {
			update_user_meta( $user_id, $load_address . '_email', $_POST['address_email'] );
			update_user_meta( $user_id, $load_address . '_phone', $_POST['address_phone'] );
		}

		do_action( 'woocommerce_customer_save_address', $user_id );

		wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
		exit;
	}
}
add_action( 'template_redirect', 'woocommerce_save_address' );

/**
 * Figure out which address is being viewed/edited.
 *
 * @package WooCommerce
 * @since 1.4
 */
function woocommerce_get_address_to_edit() {
	$load_address = 'billing';

	if ( isset( $_GET[ 'address' ] ) )
		$load_address = esc_attr( $_GET[ 'address' ] );

	if ( $load_address == 'billing' )
		$load_address = 'billing'; 
	else 
		$load_address = 'shipping';

	return $load_address;
}

/**
 * Change Password Shortcode
 *
 * @package WooCommerce
 * @since 1.4
 */
function woocommerce_change_password() {
	global $woocommerce;
	
	if ( ! is_user_logged_in() ) :
		wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
		exit;
	endif;

	woocommerce_get_template( 'myaccount/change-password.php' );
}

/**
 * Save the password and redirect back to the my account page.
 *
 * @package WooCommerce
 * @since 1.4
 */
function woocommerce_save_password() {
	global $woocommerce;

	if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
		return;

	if ( empty( $_POST[ 'action' ] ) || ( 'change_password' !== $_POST[ 'action' ] ) )
		return;

	$woocommerce->verify_nonce( 'change_password' );

	$user_id = get_current_user_id();

	if ( $user_id <= 0 )
		return;
		
	$_POST = array_map( 'woocommerce_clean', $_POST );

	if ( empty( $_POST[ 'password_1' ] ) || empty( $_POST[ 'password_2' ] ) )
		$woocommerce->add_error( __( 'Please enter your password.', 'woothemes' ) );
	
	if ( $_POST[ 'password_1' ] !== $_POST[ 'password_2' ] )
		$woocommerce->add_error( __('Passwords do not match.', 'woothemes') );
		
	if ( $woocommerce->error_count() == 0 ) {
		
		wp_update_user( array ('ID' => $user_id, 'user_pass' => esc_attr( $_POST['password_1'] ) ) ) ;

		do_action( 'woocommerce_customer_change_password', $user_id );

		wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
		exit;
	}
}
add_action( 'template_redirect', 'woocommerce_save_password' );

/**
 * View Order Shortcode
 *
 * @package WooCommerce
 * @since 1.4
 */
function woocommerce_view_order() {
	global $woocommerce;
	
	if ( ! is_user_logged_in() ) :
		wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
		exit;
	endif;
	
	$user_id      	= get_current_user_id();
	$order_id		= (isset($_GET['order'])) ? $_GET['order'] : 0;
	$order 			= &new woocommerce_order( $order_id );
	
	if ( $order_id==0 || $order->user_id != $user_id ) :
		wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
		exit;
	endif;
	
	$status = get_term_by('slug', $order->status, 'shop_order_status');
	
	echo '<p>'
	. sprintf( __('Order <mark>#%s</mark> made on <mark>%s</mark>', 'woothemes'), $order->id, date(get_option('date_format'), strtotime($order->order_date)) )
	. sprintf( __('. Order status: <mark>%s</mark>', 'woothemes'), __($status->name, 'woothemes') )
	. '.</p>';

	$notes = $order->get_customer_order_notes();
	if ($notes) :
		?>
		<h2><?php _e('Order Updates', 'woothemes'); ?></h2>
		<ol class="commentlist notes">	
			<?php foreach ($notes as $note) : ?>
			<li class="comment note">
				<div class="comment_container">			
					<div class="comment-text">
						<p class="meta"><?php echo date_i18n('l jS \of F Y, h:ia', strtotime($note->comment_date)); ?></p>
						<div class="description">
							<?php echo wpautop(wptexturize($note->comment_content)); ?>
						</div>
		  				<div class="clear"></div>
		  			</div>
					<div class="clear"></div>			
				</div>
			</li>
			<?php endforeach; ?>
		</ol>
		<?php
	endif;
	
	do_action( 'woocommerce_view_order', $order_id );
}
