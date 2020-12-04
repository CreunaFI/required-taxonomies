<?php

/*
  Plugin Name: Required taxonomies
  Description: Force users to select a taxonomy term, for example, category or tag, when publishing posts.
  Version: 1.1.7
  Author: VegaCorp
  Author URI: http://vegacorp.me
  Plugin URI: http://wpsheeteditor.com
  License: GPLv2
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!is_admin()) {
	return;
}
require 'inc/freemius-init.php';
require 'vendor/vg-plugin-sdk/index.php';
if (!class_exists('VG_Required_Taxonomies')) {

	class VG_Required_Taxonomies {

		static private $instance = false;
		static $textname = 'vg_admin_to_frontend';
		static $dir = __DIR__;
		static $version = '1.1.7';
		static $name = 'Required Taxonomies';

		private function __construct() {
			
		}

		function get_allowed_post_types() {

			$out = array(
				'post' => __('Post', VG_Required_Taxonomies::$textname)
			);
			$all_post_types = get_post_types(array(), 'objects', 'OR');
			if (!empty($all_post_types)) {
				foreach ($all_post_types as $post_type) {
					$out[$post_type->name] = $post_type->labels->name;
				}
			}
			return $out;
		}

		function init() {
			$this->args = array(
				'main_plugin_file' => __FILE__,
				'show_welcome_page' => true,
				'welcome_page_file' => VG_Required_Taxonomies::$dir . '/views/welcome-page-content.php',
				'plugin_name' => VG_Required_Taxonomies::$name,
				'plugin_prefix' => 'wprtt_',
				'plugin_version' => VG_Required_Taxonomies::$version,
				'plugin_options' => get_option(VG_Required_Taxonomies::$textname, false),
			);

			$this->vg_plugin_sdk = new VG_Freemium_Plugin_SDK($this->args);
			add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
			add_action('wp_ajax_vg_required_taxonomies_save_settings', array($this, 'save_settings'));
			add_action('admin_menu', array($this, 'register_menu_page'));
		}

		function register_menu_page() {
			add_submenu_page(
					'options-general.php', VG_Required_Taxonomies::$name, VG_Required_Taxonomies::$name, 'manage_options', $this->args['plugin_prefix'] . 'welcome_page', array($this->vg_plugin_sdk, 'render_welcome_page')
			);
		}

		function save_settings() {
			if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], VG_Required_Taxonomies::$textname) || empty($_REQUEST['post_types']) || empty($_REQUEST['taxonomies']) || empty($_REQUEST['error_message'])) {
				wp_send_json_error();
			}

			$post_types = array_map('sanitize_text_field', $_REQUEST['post_types']);
			$error_message = sanitize_text_field($_REQUEST['error_message']);

			$taxonomies = array();
			foreach ($_REQUEST['taxonomies'] as $post_type => $post_type_taxonomies) {
				$taxonomies[sanitize_text_field($post_type)] = array_map('sanitize_text_field', $post_type_taxonomies);
			}

			update_option('vg_required_taxonomies_post_types', $post_types);
			update_option('vg_required_taxonomies_taxonomies', $taxonomies);
			update_option('vg_required_taxonomies_error_message', $error_message);

			wp_send_json_success();
		}

		function get_option($option_name, $default_value = null) {
			$option = get_option('vg_required_taxonomies_' . $option_name, array());

			return (!empty($option)) ? $option : $default_value;
		}

		function enqueue_assets($hook) {
			global $post;

			if ($hook == 'post-new.php' || $hook == 'post.php') {
				$enabled_post_types = $this->get_option('post_types');
				if (in_array($post->post_type, $enabled_post_types)) {
					wp_enqueue_script('wp-required-taxonomies-script', plugins_url('/assets/js/admin.js', __FILE__), array('jquery'), VG_Required_Taxonomies::$version, true);

					$taxonomies = get_object_taxonomies($post->post_type, 'objects');
					$all_required_taxonomies = $this->get_option('taxonomies');

					if (empty($all_required_taxonomies) || empty($all_required_taxonomies[$post->post_type])) {
						return;
					}

					$required_taxonomies = $all_required_taxonomies[$post->post_type];

					$required_taxonomies_data = array();

					foreach ($required_taxonomies as $required_taxonomy_key) {
						$required_taxonomies_data[$required_taxonomy_key] = array(
							'label' => $taxonomies[$required_taxonomy_key]->label,
							'rest_base' => $taxonomies[$required_taxonomy_key]->rest_base);
					}

					wp_localize_script('wp-required-taxonomies-script', 'vgrt_data', array(
						'error_message' => $this->get_option('error_message'),
						'required_taxonomies' => $required_taxonomies_data
					));
				}
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  Foo A single instance of this class.
		 */
		static function get_instance() {
			if (null == VG_Required_Taxonomies::$instance) {
				VG_Required_Taxonomies::$instance = new VG_Required_Taxonomies();
				VG_Required_Taxonomies::$instance->init();
			}
			return VG_Required_Taxonomies::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('VG_Required_Taxonomies_Obj')) {

	function VG_Required_Taxonomies_Obj() {
		return VG_Required_Taxonomies::get_instance();
	}

}

VG_Required_Taxonomies_Obj();
