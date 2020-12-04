<p><?php 
_e( 'Thank you for installing our plugin.', VG_Required_Taxonomies::$textname );
?></p>

<?php 
$steps = array();
$post_types = get_post_types( array(
    'public' => true,
), 'objects', 'OR' );
$allowed_post_types = VG_Required_Taxonomies_Obj()->get_allowed_post_types();
$enabled_post_types = get_option( 'vg_required_taxonomies_post_types', array() );
$enabled_taxonomies = get_option( 'vg_required_taxonomies_taxonomies', array() );
$error_message = get_option( 'vg_required_taxonomies_error_message' );
if ( empty($enabled_post_types) ) {
    $enabled_post_types = array();
}
if ( empty($enabled_taxonomies) ) {
    $enabled_taxonomies = array();
}
if ( empty($error_message) ) {
    $error_message = __( '{taxonomy_name} is required', VG_Required_Taxonomies::$textname );
}
ob_start();
?>
<p><?php 
_e( 'Available post types', VG_Required_Taxonomies::$textname );
?></p>

<?php 
foreach ( $post_types as $post_type ) {
    $key = $post_type->name;
    $post_type_name = $post_type->label;
    ?>
	<div class="post-type-field post-type-<?php 
    echo  $key ;
    ?>"><input type="checkbox" name="post_types[]" value="<?php 
    echo  $key ;
    ?>" id="<?php 
    echo  $key ;
    ?>" <?php 
    checked( in_array( $key, $enabled_post_types ) );
    ?>> <label for="<?php 
    echo  $key ;
    ?>"><?php 
    echo  $post_type_name ;
    ?> </label></div>
<?php 
}
$post_type_select = ob_get_clean();
$steps['select_post_type'] = $post_type_select;
ob_start();
?>
<p><?php 
_e( 'Force users to select at least one term of these taxonomies when creating posts:', VG_Required_Taxonomies::$textname );
?></p>

<?php 
foreach ( $post_types as $post_type ) {
    $key = $post_type->name;
    $post_type_name = $post_type->label;
    $disabled = '';
    $maybe_go_premium = '';
    $taxonomies = get_object_taxonomies( $key, 'objects' );
    ?>
	<div class="post-type-taxonomies post-type-taxonomies-<?php 
    echo  $key ;
    ?>">
		<b><?php 
    echo  $post_type_name ;
    ?></b>

		<?php 
    
    if ( empty($taxonomies) ) {
        ?>
			<p><?php 
        printf( __( '%s don´t have taxonomies.', VG_Required_Taxonomies::$textname ), $post_type_name );
        ?></p>
		<?php 
    }
    
    ?>
		<?php 
    foreach ( $taxonomies as $taxonomy_key => $taxonomy ) {
        if ( !$taxonomy->show_ui ) {
            continue;
        }
        
        if ( strpos( $taxonomy_key, 'pa_' ) !== false ) {
            $disabled = ' disabled ';
            $maybe_go_premium = '<small><a href="' . $upgrade_url . '">' . __( '(Premium)', VG_Required_Taxonomies::$textname ) . '</a></small>';
        }
        
        ?>
			<div class="taxonomy-field taxonomy-<?php 
        echo  $key ;
        ?> taxonomy-<?php 
        echo  $taxonomy_key ;
        ?>"><input type="checkbox" name="taxonomies[<?php 
        echo  $key ;
        ?>][]" value="<?php 
        echo  $taxonomy_key ;
        ?>" id="<?php 
        echo  $taxonomy_key ;
        ?>" <?php 
        echo  $disabled ;
        ?> <?php 
        checked( isset( $enabled_taxonomies[$key] ) && in_array( $taxonomy_key, $enabled_taxonomies[$key] ) );
        ?>> <label for="<?php 
        echo  $taxonomy_key ;
        ?>"><?php 
        echo  $taxonomy->label ;
        ?> <?php 
        echo  $maybe_go_premium ;
        ?></label></div>
		<?php 
    }
    ?>

	</div>
	<?php 
}
$taxonomy_select = ob_get_clean();
$steps['select_taxonomies'] = $taxonomy_select;
ob_start();
?>
<label><?php 
_e( 'Error message displayed when a required taxonomy is not selected. <br/><small>Use {taxonomy_name} for the taxonomy name.</small>', VG_Required_Taxonomies::$textname );
?> <br/>
	<input type="text" name="error_message" value="<?php 
echo  $error_message ;
?>" style="width: 100%; display: block;"/></label>


<?php 
wp_nonce_field( VG_Required_Taxonomies::$textname );
?>
<br/>
<button class="save-settings button button-primary" data-loading-text="<?php 
_e( 'Loading...', VG_Required_Taxonomies::$textname );
?>"  data-success-text="<?php 
_e( 'Settings saved', VG_Required_Taxonomies::$textname );
?>"><?php 
_e( 'Save settings', VG_Required_Taxonomies::$textname );
?></button>

<?php 
$steps['error_message'] = ob_get_clean();
$steps['get_started'] = '<p>' . sprintf( __( 'Done. Now when users create a new post, they will be forced to select a taxonomy according to the settings', VG_Required_Taxonomies::$textname ) ) . '</p>';
$allowed_message = '<p>' . __( 'You can make any taxonomy required when creating a new post. This works with all post types, including WooCommerce Products, Events, Projects, Menus, etc. And it works with WooCommerce Global Attributes too.', VG_Required_Taxonomies::$textname ) . '</p>';
$steps['allowed'] = $allowed_message;
$steps = apply_filters( 'vg_required_taxonomies/welcome_steps', $steps );

if ( !empty($steps) ) {
    echo  '<ol class="steps">' ;
    foreach ( $steps as $key => $step_content ) {
        ?>
		<li><?php 
        echo  $step_content ;
        ?></li>		
		<?php 
    }
    echo  '</ol>' ;
}

?>
<hr>
<p>
	<?php 
printf( __( '<a href="%s" class="button"><span class="dashicons dashicons-email"></span> Contact Support</a>.', VG_Required_Taxonomies::$textname ), vgrtt_fs()->contact_url() );
?>
</p>
<style>
	.post-type-taxonomies {
		display: none;		
		margin-bottom: 30px;
	}
</style>
<script>
	jQuery(document).ready(function () {
		function showTaxonomiesForPostType() {
			var $postTypesSelected = jQuery('.post-type-field input:checked');
			jQuery('.post-type-taxonomies').hide();
			$postTypesSelected.each(function () {
				var postType = jQuery(this).val();
				jQuery('.post-type-taxonomies-' + postType).show();
			});
		}

		showTaxonomiesForPostType();
		jQuery('.post-type-field input').change(function () {
			showTaxonomiesForPostType();
		});

		var $saveButton = jQuery('.save-settings');
		$saveButton.click(function (e) {
			e.preventDefault();

			$saveButton.data('original-text', $saveButton.text());
			$saveButton.text($saveButton.data('loading-text'));

			var data = jQuery('.vg-plugin-sdk-page .steps input').serializeArray();
			data.push({
				name: 'action',
				value: 'vg_required_taxonomies_save_settings'
			});

			jQuery.post(ajaxurl, data, function (response) {
				if (response.success) {
					alert($saveButton.data('success-text'));
					$saveButton.text($saveButton.data('original-text'));
				}
			});
		});
	});
</script>