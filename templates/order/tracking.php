<?php
/**
 * Order Tracking
 */
 
global $woocommerce, $order;
?>

<?php
	$status = get_term_by('slug', $order->status, 'shop_order_status');

	$order_status_text = sprintf( __('Order #%s which was made %s has the status &ldquo;%s&rdquo;', 'woothemes'), $order->id, human_time_diff(strtotime($order->order_date), current_time('timestamp')).__(' ago', 'woothemes'), __($status->name, 'woothemes') );
	
	if ($order->status == 'completed') $order_status_text .= ' ' . __('and was completed', 'woothemes') . ' ' . human_time_diff(strtotime($order->completed_date), current_time('timestamp')).__(' ago', 'woothemes');
	
	$order_status_text .= '.';
	
	echo wpautop(apply_filters('woocommerce_order_tracking_status', $order_status_text, $order));
?>

<?php
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
?>

<?php do_action( 'woocommerce_view_order', $order->id ); ?>

<div style="width: 49%; float:left;">
	<h2><?php _e('Billing Address', 'woothemes'); ?></h2>
	<p><?php
	$address = $order->billing_first_name.' '.$order->billing_last_name.'<br/>';
	if ($order->billing_company) $address .= $order->billing_company.'<br/>';
	$address .= $order->formatted_billing_address;
	echo $address;
	?></p>
</div>
<div style="width: 49%; float:right;">
	<h2><?php _e('Shipping Address', 'woothemes'); ?></h2>
	<p><?php
	$address = $order->shipping_first_name.' '.$order->shipping_last_name.'<br/>';
	if ($order->shipping_company) $address .= $order->shipping_company.'<br/>';
	$address .= $order->formatted_shipping_address;
	echo $address;
	?></p>
</div>
<div class="clear"></div>