<?php
/*
Plugin Name: Kahi's Notes
Plugin URI: http://kahi.cz/wordpress/wp-notes-plugin/
Description: A place for your notes inside WordPress administration.
Author: Peter Kahoun
Version: 0.7
Author URI: http://kahi.cz
*/


class knotes {

	// Descr: full name. used on options-page, ...
	static $full_name = 'Kahi\'s Notes';

	// Descr: short name. used in menu-item name, ...
	static $short_name = 'Notes';

	// Descr: abbreviation. used in textdomain, ...
	// Descr: must be same as the name of the class
	static $abbr = 'knotes';

	// Descr: path to this this file
	// filled automatically
	static $dir_name;

	// Descr: settings: names => default values
	// Descr: in db are these settings prefixed with abbr_
	static $settings = array (
		'content' => 'Your mind comes here...',
	);


	// Descr: initialization. filling main variables, preparing, hooking
	// Descr: constructor replacement (this class is designed to be used as static). calling the initialization: see the end.
	public static function Init () {

		// set self::$dir_name
		// example: my-plugin
		$t = str_replace('\\', '/', dirname(__FILE__));
		self::$dir_name = trim(substr($t, strpos($t, '/plugins/')+9), '/');

		// load translation
		// @todo: generate .pot (very low priority)
		// load_plugin_textdomain(self::$abbr, 'wp-content/plugins/' . self::$dir_name . '/languages/');

		// prepare settings
		self::PrepareSettings();

		// hooking
		add_action('admin_init', array (self::$abbr, 'admin_init'));
		add_action('admin_menu', array (self::$abbr, 'admin_menu'));
		add_action('admin_head', array (self::$abbr, 'admin_head'));
		add_action('wp_dashboard_setup', array (self::$abbr, 'wp_dashboard_setup'));

	}




	// ====================  WP hooked functions  ====================


	// Hook: Action: admin_init
	public static function admin_init ($content) {

		wp_enqueue_script($handle = 'autogrow', $src = '/wp-content/plugins/'. self::$dir_name  .'/jquery.autogrow.js', $deps = 'jquery', $ver = '1.2.2');

	}


	// Hook: Special: admin_menu
	// Descr: adds own item into menu in administration
	public static function admin_menu () {

		add_menu_page(
			$page_title = self::$short_name,
			$menu_title = self::$short_name,
			$access_level = 'level_10',
			$file = __FILE__,
			$function = array (self::$abbr, 'TheSettingsPage'),
			$icon_url = get_bloginfo('wpurl') . '/wp-content/plugins/' . self::$dir_name  . '/icon.png'
			);

	}


	// Hook: Special: wp_add_dashboard_widget
	// Descr: links the function with widget's content
	public static function wp_dashboard_setup() {
		
		if (current_user_can('level_10'))
			wp_add_dashboard_widget('knotes', 'Notes', array (self::$abbr, 'dashboard_widget'));	
		
	}


	// Hook: Action: admin_head
	// Descr: apply my CSS
	public static function admin_head () {

?>

<!-- by plugin: <?php echo self::$full_name; ?> -->
<style type="text/css">
	#<?php echo self::$abbr; ?> textarea {width:60em; min-height:14em;}
	#dashboard-widgets #<?php echo self::$abbr; ?> textarea {width:100%; /* note: max-height:60em not working*/}
</style>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#<?php echo self::$abbr; ?> textarea').autogrow();
	});
</script>

<?php
	}




	// ====================  WP settings pages  ====================


	// Descr: own settings-page
	// Todo: maybe should be included from another file
	public static function TheSettingsPage () {

?>

	<div class="wrap" id="<?php echo self::$abbr; ?>">

		<h2><?php echo self::$short_name; ?></h2>
		
		<form method="post" action="options.php">
		
			<div>
			<label>
				Place for your notes:
				<br /><textarea name="<?php echo self::$abbr . '_content'; ?>"><?php echo htmlSpecialChars(self::$settings['content']) ?></textarea>
			</label>
			</div>


			<p class="submit">
				<?php wp_nonce_field('update-options') ?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="<?php echo self::$abbr . '_content'; ?>" />
				<input type="submit" name="<?php echo self::$abbr ?>_submit_update" value="<?php _e('Save Changes') ?>" class="button" />
			</p>

		</form>

	</div><!-- /wrap -->

<?php
	}
	
	
	// Descr: Dashboard wiget
	// @note: at the time, this is exact copy of options-page (minus label)
	// @todo: fix max-height?
	public static function dashboard_widget() {

?>

		<form method="post" action="options.php">

			<div>
				<textarea name="<?php echo self::$abbr . '_content'; ?>" rows="3"><?php echo htmlSpecialChars(self::$settings['content']) ?></textarea>
			</div>

			<p class="submit">
				<?php wp_nonce_field('update-options') ?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="<?php echo self::$abbr . '_content'; ?>" />
				<input type="submit" name="<?php echo self::$abbr ?>_submit_update" value="<?php _e('Save Changes') ?>" class="button" />
			</p>

		</form>

<?php
	}




	// ====================  WP general code  ====================


	// Descr: loads settings from db (wp_options) and stores them into self::$settings[setting]
	// Descr: items in db are prefixed with $abbr, items in $settings not
	// WP general
	private static function PrepareSettings () {

		// rewrite default value of the setting, if it's already set by user
		foreach (self::$settings as $name => $default_value) {
			if (false !== ($option = get_option(self::$abbr . '_' . $name)))
				self::$settings[$name] = $option;
		}

	}


} // end of class




// ====================  Initialize the plugin  ====================
knotes::Init();