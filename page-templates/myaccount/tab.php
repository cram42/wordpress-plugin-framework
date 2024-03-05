<?php
/**
 * My Account Tab Template
 */

defined('ABSPATH') || exit;

?>

<form class="form-<?php echo($tab->getSlug()); ?>" action="" method="post">
	<?php do_action('wpf_myaccounttab_' . $tab->getID(), $user_id); ?>
	<p>
		<?php wp_nonce_field($action_name, $nonce_name); ?>
		<button 
			type="submit" 
			class="woocommerce-Button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" 
			name="<?php echo($action_name); ?>" 
			value="Save changes"
			>Save changes</button>
		<input type="hidden" name="action" value="<?php echo($action_name); ?>" />
	</p>
</form>