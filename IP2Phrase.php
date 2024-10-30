<?php
/*
Plugin Name: IP2Phrase
Plugin URI: http://www.ip2phrase.com
Description:
Version: 1.0.16
Author: IP2Location
Author URI: http://www.ip2phrase.com
*/

$ip2phrase = new IP2Phrase();

add_action('widgets_init', [$ip2phrase, 'register']);
add_action('admin_menu', [$ip2phrase, 'menu']);
add_action('admin_head', [$ip2phrase, 'farbtastic']);
add_action('admin_enqueue_scripts', [$ip2phrase, 'plugin_enqueues']);
add_action('wp_ajax_ip2phrase_widget_submit_feedback', [$ip2phrase, 'submit_feedback']);
add_action('admin_footer_text', [$ip2phrase, 'admin_footer_text']);

class IP2Phrase
{
	public function activate()
	{
		if (!function_exists('wp_register_sidebar_widget')) {
			return;
		}

		$options = ['title' => 'IP2Phrase'];

		if (!get_option('IP2Phrase')) {
			add_option('IP2Phrase', $options);
		} else {
			update_option('IP2Phrase', $options);
		}
	}

	public function deactivate()
	{
		delete_option('IP2Phrase');
	}

	public function control()
	{
		echo '<a href="options-general.php?page=' . basename(__FILE__) . '">Go to Settings</a>';
	}

	public function widget($args)
	{
		$options = get_option('IP2Phrase');
		$text = str_replace(['%lt%', '%gt%'], ['<', '>'], $options['text']);

		echo $args['before_widget'] . $args['before_title'] . $options['title'] . $args['after_title'];
		echo '<style>p#ip2phrase a{font-size:' . $options['fontSize'] . 'px;color:' . $options['fontColor'] . ';}</style>
		<p id="ip2phrase"><script language="javascript" src="https://www.ip2phrase.com/ip2phrase.asp?template=' . str_replace('"', '&quot;', nl2br($text)) . '"></script></p><hr style="margin-bottom:0;" /><p style="font-size:10px;">Powered by <a href="http://www.ip2phrase.com" target="_blank">IP2Phrase.com</a></p>';

		echo $args['after_widget'];
	}

	public function menu()
	{
		add_submenu_page('options-general.php', 'IP2Phrase', 'IP2Phrase', 'administrator', basename(__FILE__), ['IP2Phrase', 'setting']);
	}

	public function setting()
	{
		$options = get_option('IP2Phrase');

		$fontSize = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18];

		if ($_POST['ip2phrase-title']) {
			if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $_POST['ip2phrase-font-color'])) {
				$_POST['ip2phrase-font-color'] = $options['borderColor'];
			}
			if (!in_array($_POST['ip2phrase-font-size'], $fontSize)) {
				$_POST['ip2phrase-font-size'] = 10;
			}

			$data['title'] = strip_tags(stripslashes($_POST['ip2phrase-title']));
			$data['text'] = strip_tags(stripslashes(str_replace(['<', '>'], ['%lt%', '%gt%'], $_POST['ip2phrase-text'])));
			$data['fontColor'] = strip_tags(stripslashes($_POST['ip2phrase-font-color']));
			$data['fontSize'] = strip_tags(stripslashes($_POST['ip2phrase-font-size']));

			update_option('IP2Phrase', $data);
			$options = get_option('IP2Phrase');

			echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div> ';
		}

		if (!is_array($options)) {
			$options = [
			'title'     => 'IP2Phrase',
			'text'      => 'Your IP is %lt%IP%gt%, and you are coming from %lt%COUNTRY%gt%!',
			'fontSize'  => '10',
			'fontColor' => '#000000',
		];
		}

		$text = str_replace(['%lt%', '%gt%'], ['<', '>'], $options['text']);

		$fontSizeOptions = '';
		foreach ($fontSize as $size) {
			$fontSizeOptions .= '<option value="' . $size . '"' . (($size == $options['fontSize']) ? ' selected="selected"' : '') . '> ' . $size . 'px</option>';
		}

		echo '
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2>IP2Phrase Settings</h2>
			<p>&nbsp;</p>
			<form id="form-ip2phrase" method="post">
			<table>
			<tr>
				<td>Title</td>
				<td><input style="width:400px;" name="ip2phrase-title" type="text" value="' . htmlspecialchars($options['title'], ENT_QUOTES) . '" /></td>
			</tr>
			<tr>
				<td>Text</td>
				<td><textarea name="ip2phrase-text" style="width:400px;height:100px;">' . htmlspecialchars($text, ENT_QUOTES) . '</textarea></td>
			</tr>
			<tr>
				<td>Font Size</td>
				<td>
				<select name="ip2phrase-font-size" style="width:400px;">
					' . $fontSizeOptions . '
				</select>
				</td>
			</tr>
			<tr>
				<td>Font Color</td>
				<td><input style="width:400px;" name="ip2phrase-font-color" id="ip2phrase-font-color" type="text" value="' . htmlspecialchars($options['fontColor'], ENT_QUOTES) . '" maxlength="7" class="color-picker" /></td>
			</tr>
			<tr>
				<td></td>
				<td><div id="farbtastic-ip2phrase-font-color"></div></td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="submit" name="submit" class="button-primary" value="Save Changes" />
				</td>
			</tr>
			</table>
			</form>

			<h4>Available Tags</h4>
			<table width="500" cellpadding="0" cellspacing="0" style="border:solid 1px #004884;">
			<thead style="font-weight:bold;color:#fff;background:#004884;">
			<tr>
				<td>Tag</td>
				<td>Description</td>
				<td>Output</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>&lt;IP&gt;</td>
				<td>IP address of visitor</td>
				<td><script language="javascript" src="http://www.ip2phrase.com/ip2phrase.asp?template=<IP>"></script></td>
			</tr>
			<tr style="background:#fff;">
				<td>&lt;COUNTRY&gt;</td>
				<td>Full country name of visitor</td>
				<td><script language="javascript" src="http://www.ip2phrase.com/ip2phrase.asp?template=<COUNTRY>"></script></td>
			</tr>
			<tr>
				<td>&lt;COUNTRYSHORT&gt;</td>
				<td>2-digit country name of visitor</td>
				<td><script language="javascript" src="http://www.ip2phrase.com/ip2phrase.asp?template=<COUNTRYSHORT>"></script></td>
			</tr>
			<tr style="background:#fff;">
				<td>&lt;REGION&gt;</td>
				<td>State or region of visitor</td>
				<td><script language="javascript" src="http://www.ip2phrase.com/ip2phrase.asp?template=<REGION>"></script></td>
			</tr>
			<tr>
				<td>&lt;CITY&gt;</td>
				<td>City of visitor</td>
				<td><script language="javascript" src="http://www.ip2phrase.com/ip2phrase.asp?template=<CITY>"></script></td>
			</tr>
			<tr style="background:#fff;">
				<td>&lt;ISP&gt;</td>
				<td>Internet service provider (ISP) of visitor</td>
				<td><script language="javascript" src="http://www.ip2phrase.com/ip2phrase.asp?template=<ISP>"></script></td>
			</tr>
			<tr>
				<td>&lt;FLAG&gt;</td>
				<td>Flag of country</td>
				<td><script language="javascript" src="http://www.ip2phrase.com/ip2phrase.asp?template=<FLAG>"></script></td>
			</tr>
			</tbody>
			</table>

			<p>&nbsp;</p>

			<p>If you like this plugin, please leave us a <a href="https://wordpress.org/support/view/plugin-reviews/ip2phrase-widget">5 stars rating</a>. Thank You!</p>
		</div>

		<script type="text/javascript">
			jQuery(function(){
				jQuery(document).ready(function() {
				    jQuery(\'.color-picker\').each(function() {
				    	jQuery(\'#farbtastic-\'+this.id).hide();
				    	jQuery(\'#farbtastic-\'+this.id).farbtastic(this);
				    	jQuery(this).click(function(){jQuery(\'#farbtastic-\'+this.id).fadeIn()});
						jQuery(this).blur(function(){jQuery(\'#farbtastic-\'+this.id).hide()});
					});
				});
			});

			jQuery(document).mousedown(function() {
				jQuery(\'.color-picker\').each(function() {
					var display = jQuery(\'#\'+this.id).css(\'display\');
					if(display == \'block\') jQuery(\'#\'+this.id).fadeOut();
				});
			});
		</script>
		';
	}

	public function farbtastic()
	{
		global $current_screen;

		if ($current_screen->id == 'IP2Phrase.php') {
			wp_enqueue_style('farbtastic');
			wp_enqueue_script('farbtastic');
		}
	}

	public function register()
	{
		wp_register_sidebar_widget('IP2Phrase_Widget', 'IP2Phrase', ['IP2Phrase', 'widget']);
		wp_register_widget_control('IP2Phrase_Control', 'IP2Phrase', ['IP2Phrase', 'control']);
	}

	public function plugin_enqueues($hook)
	{
		if ($hook == 'plugins.php') {
			// Add in required libraries for feedback modal
			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_style('wp-jquery-ui-dialog');

			wp_enqueue_script('ip2phrase_widget_admin_script', plugins_url('/assets/js/feedback.js', __FILE__), ['jquery'], null, true);
		}
	}

	public function admin_footer_text($footer_text)
	{
		$plugin_name = substr(basename(__FILE__), 0, strpos(basename(__FILE__), '.'));
		$current_screen = get_current_screen();

		if (($current_screen && strpos($current_screen->id, $plugin_name) !== false)) {
			$footer_text .= sprintf(
				__('Enjoyed %1$s? Please leave us a %2$s rating. A huge thanks in advance!', $plugin_name),
				'<strong>' . __('IP2Phrase Widget', $plugin_name) . '</strong>',
				'<a href="https://wordpress.org/support/plugin/' . $plugin_name . '/reviews/?filter=5/#new-post" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		if ($current_screen->id == 'plugins') {
			return $footer_text . '
			<div id="ip2phrase-widget-feedback-modal" class="hidden" style="max-width:800px">
				<span id="ip2phrase-widget-feedback-response"></span>
				<p>
					<strong>Would you mind sharing with us the reason to deactivate the plugin?</strong>
				</p>
				<p>
					<label>
						<input type="radio" name="ip2phrase-widget-feedback" value="1"> I no longer need the plugin
					</label>
				</p>
				<p>
					<label>
						<input type="radio" name="ip2phrase-widget-feedback" value="2"> I couldn\'t get the plugin to work
					</label>
				</p>
				<p>
					<label>
						<input type="radio" name="ip2phrase-widget-feedback" value="3"> The plugin doesn\'t meet my requirements
					</label>
				</p>
				<p>
					<label>
						<input type="radio" name="ip2phrase-widget-feedback" value="4"> Other concerns
						<br><br>
						<textarea id="ip2phrase-widget-feedback-other" style="display:none;width:100%"></textarea>
					</label>
				</p>
				<p>
					<div style="float:left">
						<input type="button" id="ip2phrase-widget-submit-feedback-button" class="button button-danger" value="Submit & Deactivate" />
					</div>
					<div style="float:right">
						<a href="#">Skip & Deactivate</a>
					</div>
				</p>
			</div>';
		}

		return $footer_text;
	}

	public function submit_feedback()
	{
		$feedback = (isset($_POST['feedback'])) ? $_POST['feedback'] : '';
		$others = (isset($_POST['others'])) ? $_POST['others'] : '';

		$options = [
			1 => 'I no longer need the plugin',
			2 => 'I couldn\'t get the plugin to work',
			3 => 'The plugin doesn\'t meet my requirements',
			4 => 'Other concerns' . (($others) ? (' - ' . $others) : ''),
		];

		if (isset($options[$feedback])) {
			if (!class_exists('WP_Http')) {
				include_once ABSPATH . WPINC . '/class-http.php';
			}

			$request = new WP_Http();
			$response = $request->request('https://www.ip2location.com/wp-plugin-feedback?' . http_build_query([
				'name'    => 'ip2phrase-widget',
				'message' => $options[$feedback],
			]), ['timeout' => 5]);
		}
	}
}
