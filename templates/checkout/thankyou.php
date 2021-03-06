<?php
/**
 * Cart Page
 */
 
global $woocommerce, $order;
?>

<?php if ($order) : ?>

	<?php if (in_array($order->status, array('failed'))) : ?>
				
		<p><?php _e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'woothemes'); ?></p>

		<p><?php
			if (is_user_logged_in()) :
				_e('Please attempt your purchase again or go to your account page.', 'woothemes');
			else :
				_e('Please attempt your purchase again.', 'woothemes');
			endif;
		?></p>
				
		<p>
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e('Pay', 'woothemes') ?></a>
			<?php if (is_user_logged_in()) : ?>
			<a href="<?php echo esc_url( get_permalink(get_option('woocommerce_myaccount_page_id')) ); ?>" class="button pay"><?php _e('My Account', 'woothemes'); ?></a>
			<?php endif; ?>
		</p>

	<?php else : ?>
				
		<p><?php _e('Thank you. Your order has been received.', 'woothemes'); ?></p>
				
		<ul class="order_details">
			<li class="order">
				<?php _e('Order:', 'woothemes'); ?>
				<strong># <?php echo $order->id; ?></strong>
			</li>
			<li class="date">
				<?php _e('Date:', 'woothemes'); ?>
				<strong><?php echo date(get_option('date_format'), strtotime($order->order_date)); ?></strong>
			</li>
			<li class="total">
				<?php _e('Total:', 'woothemes'); ?>
				<strong><?php echo woocommerce_price($order->order_total); ?></strong>
			</li>
			<li class="method">
				<?php _e('Payment method:', 'woothemes'); ?>
				<strong><?php 
					echo $order->payment_method_title;
				?></strong>
			</li>
		</ul>
		<div class="clear"></div>
				
	<?php endif; ?>
		
	<?php do_action( 'woocommerce_thankyou_' . $order->payment_method, $order->id ); ?>
	<?php do_action( 'woocommerce_thankyou', $order->id ); ?>

<?php else : ?>
	
	<p><?php _e('Thank you. Your order has been received.', 'woothemes'); ?></p>
	
<?php endif; ?>