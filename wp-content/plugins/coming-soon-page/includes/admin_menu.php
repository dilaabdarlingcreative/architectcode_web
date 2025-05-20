<?php

/*############  Admin Menu Class  ################*/

class coming_soon_admin_menu {

	private $menu_name;
	private $databese_parametrs;
	private $plugin_url;
	private $text_parametrs;

	/*############  The construct function  ################*/

	function __construct($param) {

		$this->text_parametrs = array(
			'parametrs_sucsses_saved' => 'Successfully saved.',
			'error_in_saving' => 'can\'t save "%s" plugin parameter<br>',
			'missing_title' => 'Type Message Title',
			'missing_fromname' => 'Type From Name',
			'missing_frommail' => 'Type From mail',
			'mising_massage' => 'Type Message',
			'sucsses_mailed' => 'Your message was sent successfully.',
			'error_maied' => 'error sending email',
			'authorize_problem' => 'Authorization Problem'
		);

		$this->menu_name = $param['menu_name'];
		$this->databese_parametrs = $param['databese_parametrs'];
		if (isset($params['plugin_url']))
			$this->plugin_url = $params['plugin_url'];
		else
			$this->plugin_url = trailingslashit(dirname(plugins_url('', __FILE__)));

		add_action('wp_ajax_coming_soon_page_save', array($this, 'save_in_databese'));
		add_action('wp_ajax_coming_soon_send_mail', array($this, 'sending_mail'));
	}

	/*############  Function for creating the menu  ################*/

	public function create_menu() {
		global $submenu;
		$sub_men_cap = str_replace(' ', '-', $this->menu_name);
		$main_page 	 		 = add_menu_page('Coming Soon', 'Coming Soon', 'manage_options', 'coming-soon', array($this, 'main_menu_function'), esc_url($this->plugin_url) . 'images/menu_icon.png');
		$page_coming_soon	  =	add_submenu_page('coming-soon',  'Coming Soon',  'Coming Soon', 'manage_options','coming-soon', array($this, 'main_menu_function'));
		$page_coming_soon_subscribers	  = add_submenu_page('coming-soon', 'Subscribers', 'Subscribers', 'manage_options', 'mailing-list-subscribers', array($this, 'mailing_list'));
		$featured_page	 	  = add_submenu_page('coming-soon', 'Featured Plugins', 'Featured Plugins', 'manage_options', 'coming-soon-featured-plugins', array($this, 'featured_plugins'));
		$featured_theme_page = add_submenu_page("coming-soon", "Featured Themes", "Featured Themes", 'read', "coming-soon_featured_themes", array($this, 'featured_themes'));
        $hire_expert = add_submenu_page("coming-soon", 'Hire an Expert', '<span style="color:#00ff66" >Hire an Expert</span>', 'read', "coming-soon_hire_expert", array($this, 'hire_expert'));
		add_action('admin_print_styles-' . $main_page, array($this, 'menu_requeried_scripts'));
		add_action('admin_print_styles-' . $page_coming_soon, array($this, 'menu_requeried_scripts'));
		add_action('admin_print_styles-' . $page_coming_soon_subscribers, array($this, 'menu_requeried_scripts'));
		add_action('admin_print_styles-' . $featured_page, array($this, 'featured_plugins_js_css'));
        add_action('admin_print_styles-' . $featured_theme_page, array($this, 'featured_themes_js_css'));
        add_action('admin_print_styles-' . $hire_expert, array($this, 'hire_expert_js_css'));

		if (isset($submenu['coming-soon'])){
			add_submenu_page('coming-soon', "Support or Any Ideas?", "<span style='color:#00ff66' >Support or Any Ideas?</span>", 'manage_options', "wpdevart_comingsoon_any_ideas", array($this, 'any_ideas'), 155);
			$count_pages = count($submenu['coming-soon'])-1;
			$submenu['coming-soon'][$count_pages][2] = esc_url(wpdevart_comingsoon_support_url);
		}
	}

	/*############  Function for the Any Ideas section  ################*/	
	
	public function any_ideas() {
	}

	/*############  The required scripts function  ################*/

	public function menu_requeried_scripts() {
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('angularejs');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('coming-soon-script-admin');
		wp_enqueue_style('jquery-ui-style');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_style('coming-soon-admin-style');
		wp_enqueue_media();
	}

	public function featured_plugins_js_css() {
        wp_enqueue_style('wpda_coming_soon_featured_page_css', esc_url($this->plugin_url) . 'includes/style/featured_plugins_css.css');
    }

    public function featured_themes_js_css() {
        wp_enqueue_style('wpda_coming_soon_featured_themes_page_css', esc_url($this->plugin_url) . 'includes/style/featured_themes_css.css');
    }

    public function hire_expert_js_css() {
        wp_enqueue_style('wpda_coming_soon_hire_expert_css', esc_url($this->plugin_url) . 'includes/style/hire_expert.css');
    }

	/*############  Function for generating parameters ################*/

	private function generete_parametrs($page_name) {
		$page_parametrs = array();
		if (isset($this->databese_parametrs[$page_name])) {
			foreach ($this->databese_parametrs[$page_name] as $key => $value) {
				$page_parametrs[$key] = get_option($key, $value);
			}
			return $page_parametrs;
		}
		return NULL;
	}

	/*############  The database function  ################*/

	public function save_in_databese() {
		if(!current_user_can( 'manage_options' )){
			echo esc_html($this->text_parametrs['authorize_problem']);
			die();
		}
		$updated = 1;
		if (isset($_POST['coming_soon_options_nonce']) && wp_verify_nonce($_POST['coming_soon_options_nonce'], 'coming_soon_options_nonce')) {
			$curent_page = sanitize_text_field($_POST['curent_page']);
			foreach ($this->databese_parametrs[$_POST['curent_page']] as $key => $value) {
				if (isset($_POST[$key])) {
					if (strpos($key, 'message') !== false) {
						$sanitize_post = wp_kses_post($_POST[$key]);
					} else {
						$sanitize_post = sanitize_text_field($_POST[$key]);
					}
					update_option($key, stripslashes_deep($sanitize_post));
				} else {
					$updated = 0;
					printf($this->text_parametrs['error_in_saving'], $key);
				}
			}
		} else {
			die(esc_html($this->text_parametrs['authorize_problem']));
		}
		if ($updated == 0) {
			exit;
		}
		die(esc_html($this->text_parametrs['parametrs_sucsses_saved']));
	}

	/*############  Main menu function  ################*/

	public function main_menu_function() {
		$enable_disable = $this->generete_parametrs('general_save_parametr');
		$enable_disable = $enable_disable['coming_soon_page_mode'];
?>
		<script>
			var coming_soon_ajaxurl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
			var comig_soon_plugin_url = "<?php echo esc_url($this->plugin_url); ?>";
			var comin_soon_parametrs_sucsses_saved = "<?php echo esc_html($this->text_parametrs['parametrs_sucsses_saved']); ?>";
			var comin_soon_all_parametrs = <?php echo wp_json_encode($this->databese_parametrs); ?>;
		</script>
		<div class="wpdevart_plugins_header div-for-clear">
			<div class="wpdevart_plugins_get_pro div-for-clear">
				<div class="wpdevart_plugins_get_pro_info">
					<h3>WpDevArt Coming Soon Premium</h3>
					<p>Powerful and Customizable Coming Soon</p>
				</div>
				<a target="blank" href="https://wpdevart.com/wordpress-coming-soon-plugin/" class="wpdevart_upgrade">Upgrade</a>
			</div>
			<a target="blank" href="<?php echo esc_url(wpdevart_comingsoon_support_url); ?>" class="wpdevart_support">Have any Questions? Get a quick support!</a>
		</div>
		<div id="coming_soon_enable" class="field switch">
			<label for="radio1" class="cb-enable <?php if ($enable_disable == 'on') echo 'selected'; ?>"><span>Enable</span></label>
			<label for="radio2" class="cb-disable <?php if ($enable_disable == 'off') echo 'selected'; ?>"><span>Disable</span></label>
			<span class="progress_enable_disable_buttons"><span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span><span class="error_massage"></span></span>
			<div style="clear:both"> </div>
		</div>
		<br>
		<div class="wp-table right_margin">
			<table class="wp-list-table widefat fixed posts">
				<thead>
					<tr>
						<th>
							<h4 class="live_previev">Parameters <a target="_blank" href="<?php echo esc_url(site_url()); ?>/?special_variable_for_live_previev=sdfg564sfdh645fds4ghs515vsr5g48strh846sd6g41513btsd" style="color:#7052fb;">(Live Preview)</a></h4>
							<span class="save_all_paramss"> <button type="button" id="save_all_parametrs" class="save_all_section_parametrs button button-primary"><span class="save_button_span">Save All Sections</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button></span>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div id="coming_soon_page">
								<div class="left_sections">
									<?php
									$this->generete_logo_section($this->generete_parametrs('coming_logo'));
									$this->generete_title_section($this->generete_parametrs('coming_title'));
									$this->generete_message_section($this->generete_parametrs('coming_message'));
									$this->generete_countdown_section($this->generete_parametrs('coming_countdown'));
									$this->generete_progressbar_section($this->generete_parametrs('coming_progressbar'));
									$this->generete_subscribe_section($this->generete_parametrs('coming_subscribe'));
									$this->generete_social_network_section($this->generete_parametrs('coming_social_networks'));
									$this->generete_link_to_tashboard_section($this->generete_parametrs('coming_link_to_dashboard'));
									$this->generete_message_footer_section($this->generete_parametrs('coming_message_footer'));
									?>
								</div>
								<div class="right_sections">
									<?php
									$this->generete_content_section($this->generete_parametrs('coming_content'));
									$this->generete_background_section($this->generete_parametrs('coming_background'));
									$this->generete_except_section($this->generete_parametrs('except_page'));
									$this->generete_search_engine_section($this->generete_parametrs('search_engine_and_favicon'));
									?>
								</div>
								<div style="clear:both"></div>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th>
							<span class="save_all_paramss"><button type="button" id="save_all_parametrs" class="save_all_section_parametrs button button-primary"><span class="save_button_span">Save All Sections</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button></span>
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
	<?php
		wp_nonce_field('coming_soon_options_nonce', 'coming_soon_options_nonce');
	}

	/*#########################  Logo Function  #################################*/

	public function generete_logo_section($page_parametrs) {
	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/logo.png' ?>"></span>
				<span class="title_parametrs_group">Logo</span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Show the Logo<span title="Use this option to show/hide the Logo from Coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_logo_enable">
									<option <?php selected($page_parametrs['coming_soon_page_logo_enable'], '1') ?> value="1">Show</option>
									<option <?php selected($page_parametrs['coming_soon_page_logo_enable'], '0') ?> value="0">Hide</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Logo<span title="Click on the 'Upload' button for uploading the coming soon page logo." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" class="upload" id="coming_soon_page_page_logo" name="coming_soon_page_page_logo" value="<?php echo esc_html($page_parametrs['coming_soon_page_page_logo']); ?>" />
								<input class="upload-button button" type="button" value="Upload" />
							</td>
						</tr>
						<tr>
							<td>
								Logo position<span class="pro_feature"> (pro)</span> <span title="Here you can choose the Coming soon page logo position(Left, Center, Right)." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_logo_in_content_position">
									<option value="0">Left</option>
									<option selected="selected" value="1">Center</option>
									<option value="2">Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Distance from the top<span class="pro_feature"> (pro)</span> <span title="Type here the logo distance from the top." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_logo_top_distance" id="coming_soon_page_logo_top_distance" value="10">(Px)
							</td>
						</tr>

						<tr>
							<td>
								Logo maximum width<span class="pro_feature"> (pro)</span> <span title="Type here the coming soon page logo maximum width(px)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_logo_max_width" id="coming_soon_page_logo_max_width" value="">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Logo maximum height<span class="pro_feature"> (pro)</span> <span title="Type here the coming soon page logo maximum height(px)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_logo_max_height" id="coming_soon_page_logo_max_height" value="210">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Logo Animation type<span class="pro_feature"> (pro)</span> <span title="Select the animation type for the coming soon page logo." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_showing_effect('coming_soon_page_logo_animation_type', 'none'); ?>
							</td>
						</tr>
						<tr>
							<td>
								Animation waiting time<span class="pro_feature"> (pro)</span> <span title="Type here the Logo animation(in milliseconds) waiting time." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_logo_animation_after_time" id="coming_soon_page_logo_animation_after_time" value="0">(milliseconds)
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_logo" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}

	/*#########################  Maintenance page Title Function   #################################*/

	public function generete_title_section($page_parametrs) {

	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/title.png' ?>"></span>
				<span class="title_parametrs_group">Title</span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Show the Title<span title="Use this option to show/hide the title from the Coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_title_enable">
									<option <?php selected($page_parametrs['coming_soon_page_title_enable'], '1') ?> value="1">Show</option>
									<option <?php selected($page_parametrs['coming_soon_page_title_enable'], '0') ?> value="0">Hide</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Title<span title="Type here the coming soon page title." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_page_title" id="coming_soon_page_page_title" value="<?php echo esc_html($page_parametrs['coming_soon_page_page_title']); ?>">
							</td>
						</tr>
						<tr>
							<td>
								Title color<span class="pro_feature"> (pro)</span> <span title="Set the title color." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(0, 0, 0);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Title Font size<span class="pro_feature"> (pro)</span> <span title="Type here the coming soon page title font size(px)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" class="pro_input" name="coming_soon_page_page_title_font_size" id="coming_soon_page_page_title_font_size" value="55">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Title Font-family<span class="pro_feature"> (pro)</span> <span title="Select the title font family." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_font('coming_soon_page_page_title_font', 'Times New Roman,Times,Georgia,serif') ?>
							</td>
						</tr>
						<tr>
							<td>
								Title position<span class="pro_feature"> (pro)</span> <span title="Select the title position(Left, Center, Right)." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_title_in_content_position">
									<option value="0">Left</option>
									<option selected="selected" value="1">Center</option>
									<option value="2">Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Distance from the top<span class="pro_feature"> (pro)</span> <span title="Type here the title field distance from the top." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_title_top_distance" id="coming_soon_page_title_top_distance" value="10">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Title Animation type<span class="pro_feature"> (pro)</span> <span title="Select the title animation type." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_showing_effect('coming_soon_page_title_animation_type', 'none'); ?>
							</td>
						</tr>
						<tr>
							<td>
								Animation waiting time<span class="pro_feature"> (pro)</span> <span title="Type here the title animation(in milliseconds) waiting time." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_title_animation_after_time" id="coming_soon_page_title_animation_after_time" value="0">(milliseconds)
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_title" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}

	/*#########################  Maintenance page Content function  #################################*/

	public function generete_message_section($page_parametrs) {

	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/message.png' ?>"></span>
				<span class="title_parametrs_group">Message</span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Show the Message <span title="Use this option to show/hide the Message box from the Coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_message_enable">
									<option <?php selected($page_parametrs['coming_soon_page_message_enable'], '1') ?> value="1">Show</option>
									<option <?php selected($page_parametrs['coming_soon_page_message_enable'], '0') ?> value="0">Hide</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<b>Message</b>
								<div style="width:100%"> <?php wp_editor(wp_filter_post_kses(stripslashes($page_parametrs['coming_soon_page_page_message'])), 'coming_soon_page_page_message', $settings = array('media_buttons' => false, 'textarea_rows' => 5)); ?></div>
							</td>

						</tr>
						<tr>
							<td>
								Message position<span class="pro_feature"> (pro)</span> <span title="Select the Message box position(Left, Center, Right)." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_message_in_content_position">
									<option value="0">Left</option>
									<option selected="selected" value="1">Center</option>
									<option value="2">Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Distance from the top<span class="pro_feature"> (pro)</span> <span title="Type here the Message box distance from the top." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_message_top_distance" id="coming_soon_page_message_top_distance" value="10">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Message Animation type<span class="pro_feature"> (pro)</span> <span title="Select the Message box animation type." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_showing_effect('coming_soon_page_message_animation_type', 'none'); ?>
							</td>
						</tr>
						<tr>
							<td>
								Animation waiting time<span class="pro_feature"> (pro)</span> <span title="Type here the Message box animation waiting time(in milliseconds)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_message_animation_after_time" id="coming_soon_page_message_animation_after_time" value="0">(milliseconds)
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_message" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}

	/*#########################  Countdown Timer Function  #################################*/

	public function generete_countdown_section($page_parametrs) {

	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/timer.png' ?>"></span>
				<span class="title_parametrs_group">Countdown Timer <span class="pro_feature_label"> (Pro feature!)</span></span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Display the Countdown Timer<span class="pro_feature"> (pro)</span> <span title="Show/hide Countdown from the Coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_countdown_enable">
									<option value="1">Show</option>
									<option selected="selected" value="0">Hide</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Text for the day field<span class="pro_feature"> (pro)</span> <span title="Type here the Day field text." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_countdown_days_text" id="coming_soon_page_countdown_days_text" value="day">
							</td>
						</tr>
						<tr>
							<td>
								Text for the hour field<span class="pro_feature"> (pro)</span> <span title="Type here the Hour field text." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_countdown_hourse_text" id="coming_soon_page_countdown_hourse_text" value="hour">
							</td>
						</tr>
						<tr>
							<td>
								Text for the minute field<span class="pro_feature"> (pro)</span> <span title="Type here the Minute field text." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_countdown_minuts_text" id="coming_soon_page_countdown_minuts_text" value="minute">
							</td>
						</tr>
						<tr>
							<td>
								Text for the second field<span class="pro_feature"> (pro)</span> <span title="Type here the Second field text." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_countdown_seconds_text" id="coming_soon_page_countdown_seconds_text" value="second">
							</td>
						</tr>
						<tr>
							<td>
								Countdown Timer date<span class="pro_feature"> (pro)</span> <span title="Type here the Countdown time(days, hours), then select the Countdown start date. Days and hours will be counted from start date." class="desription_class">?</span>
							</td>
							<td style="vertical-align: top !important;">

								<span style="display:inline-block; width:65px;">
									<input class="pro_input" type="text" onchange="refresh_countdown()" placeholder="Day" id="coming_soon_page_countdownday" size="2" value="" />
									<small style="display:block">Day</small>
								</span>
								<span style="display:inline-block; width:85px;">
									<input class="pro_input" type="text" onchange="refresh_countdown()" placeholder="Hour" id="coming_soon_page_countdownhour" size="5" value="" />
									<small>Hour</small>
								</span>
								<span style="display:inline-block; width:100px;">
									<input class="pro_input" type="text" onchange="refresh_countdown()" placeholder="Start date" id="coming_soon_page_countdownstart_day" size="9" value="" />
									<small style="font-weight:bold;">Start date</small>
								</span>
							</td>
						</tr>
						<tr>
							<td>
								<span style="font-weight:bold;">After Countdown Timer expired</span><span class="pro_feature"> (pro)</span> <span title="Select the action you need after the Countdown timer expires(Disable coming soon or only hide Countdown)." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_countdownstart_on">
									<option value="on">Disable coming soon</option>
									<option selected="selected" value="off">Hide Countdown</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								The Countdown Timer position<span class="pro_feature"> (pro)</span> <span title="Select the position for the countdown(Left, Center, Right)." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_countdown_in_content_position">
									<option value="0">Left</option>
									<option selected="selected" value="1">Center</option>
									<option value="2">Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Distance from the top<span class="pro_feature"> (pro)</span> <span title="Type here the countdown distance from the top." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_countdown_top_distance" id="coming_soon_page_countdown_top_distance" value="10">(Px)
							</td>
						</tr>

						<tr>
							<td>
								The Countdown Timer Buttons type<span class="pro_feature"> (pro)</span> <span title="Select the countdown buttons type(button, circle, vertical slider)" class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_countdown_type" class="coming_set_hiddens">
									<option selected="selected" value="button">Button</option>
									<option value="circle">Circle</option>
									<option value="vertical_slide">Vertical Slider</option>
								</select>
							</td>
						</tr>

						<tr class="tr_button tr_circle tr_vertical_slide">
							<td>
								Countdown Timer text color<span class="pro_feature"> (pro)</span> <span title="Set the countdown text color." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(0, 0, 0);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr class="tr_button tr_circle tr_vertical_slide">
							<td>
								Countdown Timer background color<span class="pro_feature"> (pro)</span> <span title="Set the countdown background color." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(255, 255, 255);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr class="tr_circle">
							<td>
								Countdown Timer size<span class="pro_feature"> (pro)</span> <span title="Type the countdown size." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_countdown_circle_size" id="coming_soon_page_countdown_circle_size" value="120">(Px)
							</td>
						</tr>

						<tr class="tr_circle">
							<td>
								Countdown Timer border width<span class="pro_feature"> (pro)</span> <span title="Set the countdown border width for circle buttons(only apears when you choose Countedown circle buttons)(px)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" size="3" class="coming_number_slider pro_input" data-max-val="100" data-min-val="0" name="coming_soon_page_countdown_circle_border" value="3" id="coming_soon_page_countdown_circle_border" style="border:0; color:#f6931f; font-weight:bold; width:35px">%
								<div class="slider_div"></div>
							</td>
						</tr>
						<tr class="tr_button">
							<td>
								Countdown Timer border radius<span class="pro_feature"> (pro)</span> <span title="Type here the countdown buttons border radius(px)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="countdown_border_radius" id="countdown_border_radius" value="15">(Px)
							</td>
						</tr>
						<tr class="tr_button tr_vertical_slide">
							<td>
								Countdown Timer font-size<span class="pro_feature"> (pro)</span> <span title="Type here the countdown text font-size(px)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="countdown_font_size" id="countdown_font_size" value="35">(Px)
							</td>
						</tr>

						<tr class="tr_button tr_circle tr_vertical_slide">
							<td>
								Countdown Timer Font-family<span class="pro_feature"> (pro)</span> <span title="Select the countdown text Font family." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_font('coming_soon_page_countdown_font_famaly', 'monospace') ?>
							</td>
						</tr>
						<tr>
							<td>
								Countdown Timer animation type<span class="pro_feature"> (pro)</span> <span title="Select the animation type for countdown." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_showing_effect('coming_soon_page_countdown_animation_type', 'none'); ?>
							</td>
						</tr>
						<tr>
							<td>
								Animation waiting time<span class="pro_feature"> (pro)</span> <span title="Type here the waiting time for the countdown animation(in milliseconds)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" class="pro_input" name="coming_soon_page_countdown_animation_after_time" id="coming_soon_page_countdown_animation_after_time" value="0">(milliseconds)
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_countdown" class="pro_input button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}

	/*#########################  Maintenance page Progress bar function #################################*/

	public function generete_progressbar_section($page_parametrs) {
	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/progressbar.png' ?>"></span>
				<span class="title_parametrs_group">Progress bar<span class="pro_feature_label"> (Pro feature!)</span></span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Show the Progress bar<span class="pro_feature"> (pro)</span> <span title="Use this option to show/hide the Progress bar from the Coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_progressbar_enable">
									<option value="1">Show</option>
									<option selected="selected" value="0">Hide</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Progress bar processing percentage<span class="pro_feature"> (pro)</span> <span title="Use this option to set the processing percentage of the Progress bar." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" size="3" class="coming_number_slider pro_input" data-max-val="100" data-min-val="0" name="coming_soon_page_progressbar_initial_pracent" value="25" id="coming_soon_page_progressbar_initial_pracent" style="border:0; color:#f6931f; font-weight:bold; width:35px">%
								<div class="slider_div"></div>
							</td>
						</tr>
						<tr>
							<td>
								Progress bar Width<span class="pro_feature"> (pro)</span> <span title="Set here the Progress bar width(px)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" size="3" class="coming_number_slider pro_input" data-max-val="100" data-min-val="0" name="coming_soon_page_progressbar_width" value="100" id="coming_soon_page_progressbar_width" style="border:0; color:#f6931f; font-weight:bold; width:35px">%
								<div class="slider_div"></div>
							</td>
						</tr>
						<tr>
							<td>
								Progress bar position<span class="pro_feature"> (pro)</span> <span title="Select the position of the Progress bar(Left, Center, Right)." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_progressbar_in_content_position">
									<option value="0">Left</option>
									<option selected="selected" value="1">Center</option>
									<option value="2">Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Distance from the top<span class="pro_feature"> (pro)</span> <span title="Type here the distance of the Progress bar from the top." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_progressbar_top_distance" id="coming_soon_page_progressbar_top_distance" value="10">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Progress bar loading color<span class="pro_feature"> (pro)</span> <span title="Set the loading color of the progress bar." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(255, 255, 255);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Border color<span class="pro_feature"> (pro)</span> <span title="Set the border color of the Progress bar." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(0, 0, 0);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Border width<span class="pro_feature"> (pro)</span> <span title="Type the border width(px) of the progress bar." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_progressbar_border_width" id="coming_soon_page_progressbar_border_width" value="3">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Border radius<span class="pro_feature"> (pro)</span> <span title="Type the border radius(px) of the progress bar." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_progressbar_border_radius" id="coming_soon_page_progressbar_border_radius" value="15">(Px)
							</td>
						</tr>

						<tr>
							<td>
								Animation type<span class="pro_feature"> (pro)</span> <span title="Select the animation type of the Progress bar." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_showing_effect('coming_soon_page_progressbar_animation_type', 'none'); ?>
							</td>
						</tr>
						<tr>
							<td>
								Animation waiting time<span class="pro_feature"> (pro)</span> <span title="Type here the waiting time for Progress bar animation(in milliseconds)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_progressbar_animation_after_time" id="coming_soon_page_progressbar_animation_after_time" value="0">(milliseconds)
							</td>
						</tr>

					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_progressbar" class="pro_input button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}

	/*#########################  Maintenance page Subscribe function #################################*/

	public function generete_subscribe_section($page_parametrs) {
	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/subscribe.png' ?>"></span>
				<span class="title_parametrs_group">Subscribe Form (Mailing list)<span class="pro_feature_label"> (Pro feature!)</span></span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Show the Subscribe Form<span class="pro_feature"> (pro)</span> <span title="Use this option to show or hide the Subscribe Form from the Coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="enable_mailing_list">
									<option value="on">Show</option>
									<option selected="selected" value="off">Hide</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								First name<span class="pro_feature"> (pro)</span> <span title="Type here the text for the first name field." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_firstname" id="coming_soon_page_subscribe_firstname" value="First name">
							</td>
						</tr>
						<tr>
							<td>
								Last name<span class="pro_feature"> (pro)</span> <span title="Type here the text for the last name field." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_lastname" id="coming_soon_page_subscribe_lastname" value="Last name">
							</td>
						</tr>
						<tr>
							<td>
								Text for the email field <span class="pro_feature"> (pro)</span> <span title="Type here the text for the email field." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="mailing_list_value_of_emptyt" id="mailing_list_value_of_emptyt" value="Email">
							</td>
						</tr>
						<tr>
							<td>
								Text for the Send button<span class="pro_feature"> (pro)</span> <span title="Type here the Send button text." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="mailing_list_button_value" id="mailing_list_button_value" value="Subscribe">
							</td>
						</tr>
						<tr>
							<td>
								Success email text<span class="pro_feature"> (pro)</span> <span title="Type here the message that will appear if users submit the correct email." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_after_text_sucsess" id="coming_soon_page_subscribe_after_text_sucsess" value="You Have Been Successfully Subscribed!">
							</td>
						</tr>
						<tr>
							<td>
								Existing email text<span class="pro_feature"> (pro)</span> <span title="Type here the message that will appear if users type already submitted the email." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_after_text_alredy_exsist" id="coming_soon_page_subscribe_after_text_alredy_exsist" value="You're Already Subscribed!">
							</td>
						</tr>
						<tr>
							<td>
								Blank email text<span class="pro_feature"> (pro)</span> <span title="Type here the message that will appear if users submit a blank email field. " class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_after_text_none" id="coming_soon_page_subscribe_after_text_none" value="Please Type Your Email">
							</td>
						</tr>
						<tr>
							<td>
								Invalid email text<span class="pro_feature"> (pro)</span> <span title="Type here the message that will appear if users submit a wrong email address." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_after_text_invalid" id="coming_soon_page_subscribe_after_text_invalid" value="Email Doesn't Exist">
							</td>
						</tr>

						<tr>
							<td>
								Subscribe Form position<span class="pro_feature"> (pro)</span> <span title="Select the position for the Subscribe Form(Left, Center, Right)." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_subscribe_in_content_position">
									<option value="0">Left</option>
									<option selected="selected" value="1">Center</option>
									<option value="2">Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Distance from the top<span class="pro_feature"> (pro)</span> <span title="Type here the Subscribe Form distance from the top(px). " class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_top_distance" id="coming_soon_page_subscribe_top_distance" value="10">(Px)
							</td>
						</tr>
						</tr>
						<tr>
							<td>
								Font Size<span class="pro_feature"> (pro)</span> <span title="Type here the font size for all texts of the Subscribe Form(px)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="sendmail_input_font_size" id="sendmail_input_font_size" value="14">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Email field border radius<span class="pro_feature"> (pro)</span> <span title="Type here the border radius of the email field." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_button_radius" id="coming_soon_page_subscribe_button_radius" value="0">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Input maximum width<span class="pro_feature"> (pro)</span> <span title="Type here the maximum width of the input field(px)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_input_max_width" id="coming_soon_page_subscribe_input_max_width" value="350">(Px)
							</td>
						</tr>

						<tr>
							<td>
								Font family<span class="pro_feature"> (pro)</span> <span title="Select the font family of all texts of the Subscribe Form." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_font('coming_soon_page_subscribe_font_famely', 'monospace') ?>
							</td>
						</tr>
						<tr>
							<td>
								Input field border color<span class="pro_feature"> (pro)</span> <span title="Set the input field border color." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(255, 255, 255);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Placeholder text color<span class="pro_feature"> (pro)</span> <span title="Set the default text color of the input fields. " class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(0, 0, 0);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Send button bg color<span class="pro_feature"> (pro)</span> <span title="Set the send button background color." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(0, 0, 0);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Send button text color<span class="pro_feature"> (pro)</span> <span title="Set the send button text color." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(255, 255, 255);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>

						<tr>
							<td>
								Input field text color<span class="pro_feature"> (pro)</span> <span title="Set the input field text color." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(255, 255, 255);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								After submit text color<span class="pro_feature"> (pro)</span> <span title="Set the color of the text, that will appear after submitting the form." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(0, 0, 0);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Animation type<span class="pro_feature"> (pro)</span> <span title="Select the animation type for the Subscribe Form." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_showing_effect('coming_soon_page_subscribe_animation_type', 'none'); ?>
							</td>
						</tr>
						<tr>
							<td>
								Animation waiting time<span class="pro_feature"> (pro)</span> <span title="Type the animation waiting time(in milliseconds)of the Subscribe Form." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_subscribe_animation_after_time" id="coming_soon_page_subscribe_animation_after_time" value="0">(milliseconds)
							</td>
						</tr>

					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_subscribe" class="pro_input button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}
	/*#########################  Socials Buttons Function #################################*/
	public function generete_social_network_section($page_parametrs) {

	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/social_network.png' ?>"></span>
				<span class="title_parametrs_group">Socials buttons</span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Show social buttons <span title="Show or hide social buttons on coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_socialis_enable">
									<option <?php selected($page_parametrs['coming_soon_page_socialis_enable'], '1') ?> value="1">Show</option>
									<option <?php selected($page_parametrs['coming_soon_page_socialis_enable'], '0') ?> value="0">Hide</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Open in new tab <span title="If you want to open social page in a new tab then enable this option" class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_open_new_tabe">
									<option <?php selected($page_parametrs['coming_soon_page_open_new_tabe'], '1') ?> value="1">Enable</option>
									<option <?php selected($page_parametrs['coming_soon_page_open_new_tabe'], '0') ?> value="0">Disable</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Facebook URL <span title="Type here Facebook page url." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_facebook" id="coming_soon_page_facebook" value="<?php echo esc_html($page_parametrs['coming_soon_page_facebook']); ?>">
							</td>
						</tr>
						<tr>
							<td>
								Facebook button icon URL<span class="pro_feature"> (pro)</span> <span title="Insert here Facebook icon url or upload it." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" class="upload" id="social_facbook_bacground_image" name="social_facbook_bacground_image" value="" />
								<input class="button pro_input" type="button" value="Upload" />
							</td>
						</tr>
						<tr>
							<td>
								Twitter URL <span title="Type here Twitter page url." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_twitter" id="coming_soon_page_twitter" value="<?php echo esc_html($page_parametrs['coming_soon_page_twitter']); ?>">
							</td>
						</tr>
						<tr>
							<td>
								Twitter button icon URL<span class="pro_feature"> (pro)</span> <span title="Insert here Twitter icon url or upload it." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" class="pro_input" class="upload" id="social_twiter_bacground_image" name="social_twiter_bacground_image" value="" />
								<input class="pro_input button" type="button" value="Upload" />
							</td>
						</tr>
						<tr>
							<td>
								YouTube URL <span title="Type here YouTube page url." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_youtube" id="coming_soon_page_youtube" value="<?php echo esc_html($page_parametrs['coming_soon_page_youtube']) ?>">
							</td>
						</tr>
						<tr>
							<td>
								YouTube button icon URL<span class="pro_feature"> (pro)</span> <span title="Insert here YouTube icon url or upload it." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" class="pro_input" class="upload" id="social_youtobe_bacground_image" name="social_youtobe_bacground_image" value="" />
								<input class="pro_input button" type="button" value="Upload" />
							</td>
						</tr>
						<tr>
							<td>
								Instagram URL <span title="Type here Instagram page url." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_instagram" id="coming_soon_page_instagram" value="<?php echo esc_html($page_parametrs['coming_soon_page_instagram']); ?>">
							</td>
						</tr>
						<tr>
							<td>
								Instagram button icon URL<span class="pro_feature"> (pro)</span> <span title="Insert here Instagram icon url or upload it." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" class="pro_input" class="upload" id="social_instagram_bacground_image" name="social_instagram_bacground_image" value="" />
								<input class="pro_input button" type="button" value="Upload" />
							</td>
						</tr>
						<tr>
							<td>
								Social buttons position<span class="pro_feature"> (pro)</span> <span title="Choose position for Social buttons(Left, Center, Right)." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_socialis_in_content_position">
									<option value="0">Left</option>
									<option selected="selected" value="1">Center</option>
									<option value="2">Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Distance from the top<span class="pro_feature"> (pro)</span> <span title="Type here Social buttons distance from the top." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_socialis_top_distance" placeholder="Enter Distance" id="coming_soon_page_socialis_top_distance" value="10">(Px)
							</td>
						</tr>

						<tr>
							<td>
								Social buttons maximum width<span class="pro_feature"> (pro)</span> <span title="Type here maximum width for Social buttons." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_socialis_max_width" id="coming_soon_page_socialis_max_width" value="">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Social buttons maximum height<span class="pro_feature"> (pro)</span> <span title="Type here maximum height for Social buttons." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_socialis_max_height" id="coming_soon_page_socialis_max_height" value="">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Animation type<span class="pro_feature"> (pro)</span> <span title="Choose animation type for Social buttons." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_showing_effect('coming_soon_page_socialis_animation_type', 'none'); ?>
							</td>
						</tr>
						<tr>
							<td>
								Animation waiting time<span class="pro_feature"> (pro)</span> <span title="Type here Social buttons animation waiting time(in milliseconds)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_socialis_animation_after_time" id="coming_soon_page_socialis_animation_after_time" value="0">(milliseconds)
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_social_networks" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}
	/*#########################  Link To Admin Function #################################*/
	public function generete_link_to_tashboard_section($page_parametrs) {

	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/link_dashboard.png' ?>"></span>
				<span class="title_parametrs_group">Link to Admin<span class="pro_feature_label"> (Pro feature!)</span></span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Show Link to Admin<span class="pro_feature"> (pro)</span> <span title="Choose to show or hide Link To Admin." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="coming_soon_page_link_to_dashboard_enable">
									<option value="1">Show</option>
									<option selected="selected" value="0">Hide</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Link To Admin text<span class="pro_feature"> (pro)</span> <span title="Type here the text for the Link To Admin." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_page_link_to_dashboard" placeholder="Enter Link Name" id="coming_soon_page_page_link_to_dashboard" value="Link To Admin">
							</td>
						</tr>
						<tr>
							<td>
								Text color<span class="pro_feature"> (pro)</span> <span title="Select the text color." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(0, 0, 0);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Font Size<span class="pro_feature"> (pro)</span> <span title="Type here the font-size of the text." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_page_link_to_dashboard_font_size" id="coming_soon_page_page_link_to_dashboard_font_size" value="55">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Font family<span class="pro_feature"> (pro)</span> <span title="Select Font family for Link To Admin." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_font('coming_soon_page_page_link_to_dashboard_font', 'monospace') ?>
							</td>
						</tr>
						<tr>
							<td>
								Position<span class="pro_feature"> (pro)</span> <span title="Choose position for Link To Admin section(Left, Center, Right)." class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_link_to_dashboard_in_content_position">
									<option value="0">Left</option>
									<option selected="selected" value="1">Center</option>
									<option value="2">Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Distance from the top<span class="pro_feature"> (pro)</span> <span title="Type here Link To Admin distance from the top." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_link_to_dashboard_top_distance" id="coming_soon_page_link_to_dashboard_top_distance" value="10">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Animation type<span class="pro_feature"> (pro)</span> <span title="Choose animation type for Link To Admin." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_showing_effect('coming_soon_page_link_to_dashboard_animation_type', 'none'); ?>
							</td>
						</tr>
						<tr>
							<td>
								Animation waiting time<span class="pro_feature"> (pro)</span> <span title="Type here waiting time for Link To Admin animation(in milliseconds)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_link_to_dashboard_animation_after_time" id="coming_soon_page_link_to_dashboard_animation_after_time" value="0">(milliseconds)
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_link_to_dashboard" class="pro_input button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}
	/*#########################  Footer Message Part  #################################*/
	public function generete_message_footer_section($page_parametrs) {

	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/message.png' ?>"></span>
				<span class="title_parametrs_group">Footer Message</span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Footer Message Section <span title="Choose to show or hide Footer Message box from Coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_message_footer_enable">
									<option <?php selected($page_parametrs['coming_soon_page_message_footer_enable'], '1') ?> value="1">Show</option>
									<option <?php selected($page_parametrs['coming_soon_page_message_footer_enable'], '0') ?> value="0">Hide</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<b>Footer Message Content</b>
								<div style="width:100%"> <?php wp_editor(wp_filter_post_kses(stripslashes($page_parametrs['coming_soon_page_page_message_footer'])), 'coming_soon_page_page_message_footer', $settings = array('media_buttons' => false, 'textarea_rows' => 5)); ?></div>
							</td>

						</tr>
						<tr>
							<td>
								Footer Message position <span title="Choose position for Footer Message box(Left, Center, Right)." class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_message_footer_in_content_position">
									<option <?php selected($page_parametrs['coming_soon_page_message_footer_in_content_position'], '0') ?> value="0">Left</option>
									<option <?php selected($page_parametrs['coming_soon_page_message_footer_in_content_position'], '1') ?> value="1">Center</option>
									<option <?php selected($page_parametrs['coming_soon_page_message_footer_in_content_position'], '2') ?> value="2">Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Distance from the top <span title="Type here Footer Message box distance from the top." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_message_footer_top_distance" id="coming_soon_page_message_footer_top_distance" value="<?php echo esc_html($page_parametrs['coming_soon_page_message_footer_top_distance']); ?>">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Animation type of the Footer Message <span class="pro_feature"> (pro)</span> <span title="Choose animation type for Footer Message box." class="desription_class">?</span>
							</td>
							<td>
								<?php $this->create_select_element_for_showing_effect('coming_soon_page_message_footer_animation_type', 'none'); ?>
							</td>
						</tr>
						<tr>
							<td>
								Waiting time of Animation <span class="pro_feature"> (pro)</span> <span title="Type here waiting time for Footer Message box animation(in milliseconds)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_message_footer_animation_after_time" id="coming_soon_page_message_footer_animation_after_time" value="0">(milliseconds)
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_message_footer" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}
	/*#########################  Search Engine Optimization Part  #################################*/
	public function generete_search_engine_section($page_parametrs) {

	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/seo.png' ?>"></span>
				<span class="title_parametrs_group">Search engines and Favicon</span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Title(SEO) <span title="Type here the Title for Search engines. 60 max recommended characters(It will be visible for search engines only)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_page_seo_title" id="coming_soon_page_page_seo_title" value="<?php echo esc_html($page_parametrs['coming_soon_page_page_seo_title']); ?>">
							</td>
						</tr>
						<tr>
							<td>
								Favicon <span class="pro_feature"> (pro)</span> <span title="Here you can upload favicon for coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" class="upload pro_input" id="coming_soon_page_page_favicon" name="coming_soon_page_page_favicon" value="" />
								<input class="pro_input button" type="button" value="Upload" />
							</td>
						</tr>
						<tr>
							<td>
								Search Robots <span title="Here you can enable or disable coming soon page for search robots. " class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_enable_search_robots">
									<option <?php selected($page_parametrs['coming_soon_page_enable_search_robots'], '1') ?> value="1">Enable</option>
									<option <?php selected($page_parametrs['coming_soon_page_enable_search_robots'], '0') ?> value="0">Disable</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Meta Keywords <span title="Type here meta keywords for coming soon page(It will be visible for search engines only)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_meta_keywords" placeholder="Enter Meta Keywords" id="coming_soon_page_meta_keywords" value="<?php echo esc_html($page_parametrs['coming_soon_page_meta_keywords']); ?>">
							</td>
						</tr>
						<tr>
							<td>
								Meta Description <span title="Type here meta description for coming soon page. 160 max recommended characters(It will be visible for search engines only)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" name="coming_soon_page_meta_description" placeholder="Enter Meta Description" id="coming_soon_page_meta_description" value="<?php echo esc_html($page_parametrs['coming_soon_page_meta_description']) ?>">
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="search_engine_and_favicon" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}
	/*#########################  Except Page or IP Part  #################################*/
	public function generete_except_section($page_parametrs) {
	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/except.png' ?>"></span>
				<span class="title_parametrs_group">Except pages and IPs</span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Disable coming soon for these IPs <span title="You can disable coming soon for these IPs, just type the IP and click anywhere, then type the next IP in next field that will appear and so." class="desription_class">?</span>
							</td>
							<td>
								<div id="no_blocked_ips"></div>
							</td>
						</tr>
						<tr>
							<td>
								Disable coming soon for these URLs<span class="pro_feature"> (pro)</span> <span title="You can disable coming soon page for these URLs, just type the URL and click anywhere, then type the next URL in next field that will appear." class="desription_class">?</span>
							</td>
							<td>
								<input type="hidden" value="" id="coming_soon_page_showed_urls" name="coming_soon_page_showed_urls">
								<div class="emelent_coming_soon_page_showed_urls"> <input class="pro_input" type="text" placeholder="Type The URL Here" value=""><span class="remove_element remove_element_coming_soon_page_showed_urls"></span> </div>
							</td>
						</tr>
						<tr>
							<td>
								Enable only for Homepage <span title="Disable coming soon for all pages except Homepage" class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_enable_only_for_home">
									<option <?php selected($page_parametrs['coming_soon_enable_only_for_home'], '1') ?> value="1">Enable</option>
									<option <?php selected($page_parametrs['coming_soon_enable_only_for_home'], '0') ?> value="0">Disable</option>
								</select>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="except_page" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
				<script>
					jQuery(document).ready(function(e) {
						many_inputs.main_element_for_inserting_element = 'no_blocked_ips';
						many_inputs.element_name_and_id = 'coming_soon_page_showed_ips';
						many_inputs.placeholder = 'Type Ip Here';
						many_inputs.value_jsone_encoded = '<?php echo htmlspecialchars_decode(esc_js(stripslashes($page_parametrs['coming_soon_page_showed_ips']))); ?>';
						many_inputs.creates_elements();
					});
				</script>
			</div>
		</div>
	<?php
	}
	/*#########################  Background options Part  #################################*/
	public function generete_background_section($page_parametrs) {
	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/background.png' ?>"></span>
				<span class="title_parametrs_group">Background</span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Background type <span title="Select the background type you want to use for your coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select id="coming_soon_page_radio_backroun" class="coming_set_hiddens">
									<option <?php selected($page_parametrs['coming_soon_page_radio_backroun'], 'back_color') ?> value="back_color">Background Color</option>
									<option <?php selected($page_parametrs['coming_soon_page_radio_backroun'], 'back_imge') ?> value="back_imge">Background Image</option>
									<option disabled value="back_imge">Background Slider<span class="pro_feature"> (pro)</span></option>
									<option disabled value="back_imge">Video background(not for mobile)<span class="pro_feature"> (pro)</span></option>
								</select>
							</td>
						</tr>
						<tr class="tr_back_color white">
							<td>
								Set the color <span title="Select the background color for coming soon page(option will apear if you choose 'Background color' type)." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" class="color_option" id="coming_soon_page_background_color" name="coming_soon_page_background_color" value="<?php echo esc_html($page_parametrs['coming_soon_page_background_color']); ?>" />
							</td>
						</tr>
						<tr class="tr_back_imge white">
							<td>
								Img url <span title="ype the image url or just upload image for coming soon page background(option will apear if you choose " Background image" type). " class=" desription_class">?</span>
							</td>
							<td>
								<input type="text" class="upload" id="coming_soon_page_background_img" name="coming_soon_page_background_img" value="<?php echo esc_html($page_parametrs['coming_soon_page_background_img']); ?>" />
								<input class="upload-button button" type="button" value="Upload" />
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_background" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}
	/*#########################  Content Part  #################################*/
	public function generete_content_section($page_parametrs) {

	?>
		<div class="main_parametrs_group_div closed_params ">
			<div class="head_panel_div" title="Click to toggle">
				<span class="title_parametrs_image"><img src="<?php echo esc_url($this->plugin_url) . 'images/content.png' ?>"></span>
				<span class="title_parametrs_group">Content <span class="pro_feature_label"> (Pro feature!)</span></span>
				<span class="enabled_or_disabled_parametr"></span>
				<span class="open_or_closed"></span>
			</div>
			<div class="inside_information_div">
				<table class="wp-list-table widefat fixed posts section_parametrs_table">
					<tbody>
						<tr>
							<td>
								Content position<span class="pro_feature"> (pro)</span> <span title="Choose content position on coming soon page." class="desription_class">?</span>
							</td>
							<td>
								<select class="pro_select" id="page_content_position">
									<option value="left-top">Top Left</option>
									<option value="left-middle">Middle Left</option>
									<option value="left-bottom">Bottom Left</option>
									<option value="center-top">Top center</option>
									<option selected="selected" value="center-middle">Middle center</option>
									<option value="center-bottom">Bottom center</option>
									<option value="right-top">Top right</option>
									<option value="right-middle">Middle right</option>
									<option value="right-bottom">Bottom right</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								Content background color<span class="pro_feature"> (pro)</span> <span title="Select content background color." class="desription_class">?</span>
							</td>
							<td>
								<div class="wp-picker-container disabled_picker">
									<button type="button" class="button wp-color-result" aria-expanded="false" style="background-color: rgb(0, 0, 0);"><span class="wp-color-result-text">Select Color</span></button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Content transparency<span class="pro_feature"> (pro)</span> <span title="Select transparency for content." class="desription_class">?</span>
							</td>
							<td>
								<input type="text" size="3" class="coming_number_slider pro_input" data-max-val="100" data-min-val="0" name="coming_soon_page_content_trasparensy" value="55" id="coming_soon_page_content_trasparensy" style="border:0; color:#f6931f; font-weight:bold; width:35px">%
								<div class="slider_div"></div>
							</td>
						</tr>
						<tr>
							<td>
								Border radius<span class="pro_feature"> (pro)</span> <span title="Type here border radius for content." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="page_content_boreder_radius" id="page_content_boreder_radius" value="8">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Content maximum width<span class="pro_feature"> (pro)</span> <span title="Type here content maximum width." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_content_max_width" id="coming_soon_page_content_max_width" value="740">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Padding<span class="pro_feature"> (pro)</span> <span title="Type here content padding value(padding properties define the space between the element border and the element content)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_content_padding" id="coming_soon_page_content_padding" value="10">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Margin<span class="pro_feature"> (pro)</span> <span title="Type here content margin value(margin properties define the space around elements)." class="desription_class">?</span>
							</td>
							<td>
								<input class="pro_input" type="text" name="coming_soon_page_content_margin" id="coming_soon_page_content_margin" value="15">(Px)
							</td>
						</tr>
						<tr>
							<td>
								Elements ordering<span class="pro_feature"> (pro)</span> <span title="Choose the order of showing elements on coming soon page(you can move all elements using drop down functionality)." class="desription_class">?</span>
							</td>
							<td>
								<ul id="coming_soon_sortable">
									<li date-value="logo" class="ui-state-default">Logo<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
									<li date-value="title" class="ui-state-default">Title<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
									<li date-value="message" class="ui-state-default">Message<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
									<li date-value="countdown" class="ui-state-default">Countdown<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
									<li date-value="subscribe" class="ui-state-default">Subscribe Form<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
									<li date-value="loading_animation" class="ui-state-default">Progress bar<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
									<li date-value="link_to_dashboard" class="ui-state-default">Link to Admin<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
									<li date-value="share_buttons" class="ui-state-default">Social buttons<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
									<li date-value="message_footer" class="ui-state-default">Message footer<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
								</ul>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button type="button" id="coming_content" class="pro_input button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	<?php
	}

	/*############  Subscribers table list function  ################*/

	private function generete_subscriber_table_lists($mailing_lsit_array) {
		$generete = '';
		if ($mailing_lsit_array) {
			foreach ($mailing_lsit_array as $key => $value) {
				$generete .= "{'email':'" . $key . "','firstname':'" . $value['firstname'] . "','lastname':'" . $value['lastname'] . "'},";
			}
			$generete = rtrim($generete, ",");
		}
	?>
		<style>
			.description_row:nth-child(odd) {
				background-color: #f9f9f9;
			}
		</style>
		<script>
			// jsone date for angiaulare js
			var my_table_list = <?php echo esc_js("[" . $generete . "]"); ?>
		</script>
		<div>
			<form method="post" action="" id="admin_form" name="admin_form" ng-app="" ng-controller="customersController">
				<div class="tablenav top" style="width:95%">
					<input type="text" placeholder="Search" ng-change="filtering_table();" ng-model="searchText">
					<div class="tablenav-pages"><span class="displaying-num">{{filtering_table().length}} items</span>
						<span ng-show="(numberOfPages()-1)>=1">
							<span class="pagination-links"><a class="first-page" ng-class="{disabled:(curPage < 1 )}" title="Go to the first page" ng-click="curPage=0">«</a>
								<a class="prev-page" title="Go to the previous page" ng-class="{disabled:(curPage < 1 )}" ng-click="curPage=curPage-1; curect()">‹</a>
								<span class="paging-input"><span class="total-pages">{{curPage + 1}}</span> of <span class="total-pages">{{ numberOfPages() }}</span></span>
								<a class="next-page" title="Go to the next page" ng-class="{disabled:(curPage >= (numberOfPages() - 1))}" ng-click=" curPage=curPage+1; curect()">›</a>
								<a class="last-page" title="Go to the last page" ng-class="{disabled:(curPage >= (numberOfPages() - 1))}" ng-click="curPage=numberOfPages()-1">»</a></span>
					</div>
					</span>
				</div>
				<table class="wp-list-table widefat fixed pages" style="width:95%">
					<thead>
						<tr>
							<th data-ng-click="order_by='email'; reverse=!reverse; ordering($event,order_by,reverse)" class="manage-column sortable desc"><a><span>Email</span><span class="sorting-indicator"></span></a></th>
							<th data-ng-click="order_by='firstname'; reverse=!reverse; ordering($event,order_by,reverse)" class="manage-column sortable desc"><a><span>First name</span><span class="sorting-indicator"></span></a></th>
							<th data-ng-click="order_by='lastname'; reverse=!reverse; ordering($event,order_by,reverse)" class="manage-column sortable desc"><a><span>Last name</span><span class="sorting-indicator"></span></a></th>
							<th style="width:80px">Delete</th>
						</tr>
					</thead>
					<tbody>
						<tr ng-repeat="rows in names | filter:filtering_table" class="description_row">
							<td><a href="#">{{rows.email}}</a></td>
							<td><a href="#">{{rows.firstname}}</a></td>
							<td><a href="#">{{rows.lastname}}</a></td>
							<td><a href="admin.php?page=mailing-list-subscribers&task=remove_user&id={{rows.email}}">Delete</a></td>

						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<script>
			jQuery(document).ready(function(e) {
				jQuery('a.disabled').click(function() {
					return false
				});
				jQuery('form').on("keyup keypress", function(e) {
					var code = e.keyCode || e.which;
					if (code == 13) {
						e.preventDefault();
						return false;
					}
				});
			});

			function customersController($scope, $filter) {
				var orderBy = $filter('orderBy');
				$scope.previsu_search_result = '';
				$scope.oredering = new Array();
				$scope.baza = my_table_list;
				$scope.curPage = 0;
				$scope.pageSize = 10;
				$scope.names = $scope.baza.slice($scope.curPage * $scope.pageSize, ($scope.curPage + 1) * $scope.pageSize)
				$scope.numberOfPages = function() {
					return Math.ceil($scope.filtering_table().length / $scope.pageSize);
				};
				$scope.filtering_table = function() {
					var new_searched_date_array = new Array;
					new_searched_date_array = [];
					angular.forEach($scope.baza, function(value, key) {
						var catched = 0;
						angular.forEach(value, function(value_loc, key_loc) {
							if (('' + value_loc).indexOf($scope.searchText) != -1 || $scope.searchText == '' || typeof($scope.searchText) == 'undefined')
								catched = 1;
						})
						if (catched)
							new_searched_date_array.push(value);
					})
					if ($scope.previsu_search_result != $scope.searchText) {

						$scope.previsu_search_result = $scope.searchText;
						$scope.ordering($scope.oredering[0], $scope.oredering[1], $scope.oredering[2]);

					}
					if (new_searched_date_array.length <= $scope.pageSize)
						$scope.curPage = 0;
					return new_searched_date_array;
				}
				$scope.curect = function() {
					if ($scope.curPage < 0) {
						$scope.curPage = 0;
					}
					if ($scope.curPage > $scope.numberOfPages() - 1)
						$scope.curPage = $scope.numberOfPages() - 1;
					$scope.names = $scope.filtering_table().slice($scope.curPage * $scope.pageSize, ($scope.curPage + 1) * $scope.pageSize)
				}

				$scope.ordering = function($event, order_by, revers) {
					if (typeof($event) != 'undefined' && typeof($event.currentTarget) != 'undefined')
						element = $event.currentTarget;
					else
						element = jQuery();

					if (revers)
						indicator = 'asc'
					else
						indicator = 'desc'
					$scope.oredering[0] = $event;
					$scope.oredering[1] = order_by;
					$scope.oredering[2] = revers;
					jQuery(element).parent().find('.manage-column').removeClass('sortable desc asc sorted');
					jQuery(element).parent().find('.manage-column').not(element).addClass('sortable desc');
					jQuery(element).addClass('sorted ' + indicator);
					$scope.names = orderBy($scope.filtering_table(), order_by, revers).slice($scope.curPage * $scope.pageSize, ($scope.curPage + 1) * $scope.pageSize)
				}
			}
		</script>
	<?php

	}
	public function mailing_list() {
		$page_parametrs = $this->generete_parametrs('mailing_list');
		$mailing_lists = NULL;
		if ($mailing_lists == NULL)
			$mailing_lists = array();
		if (isset($_GET['id']) && isset($_GET['task']) && $_GET['task'] == 'remove_user' && wp_verify_nonce($_POST['wpda_coming_soon_mail_nonce'], 'wpda_coming_soon_mail_nonce')) {
			$get_id = intval($_GET['id']);
			unset($mailing_lists[$get_id]);
			update_option('users_mailer', json_encode($mailing_lists));
		}
	?>
		<div class="wpdevart_plugins_header div-for-clear">
			<div class="wpdevart_plugins_get_pro div-for-clear">
				<div class="wpdevart_plugins_get_pro_info">
					<h3>WpDevArt Coming Soon Premium</h3>
					<p>Powerful and Customizable Coming Soon</p>
				</div>
				<a target="blank" href="https://wpdevart.com/wordpress-coming-soon-plugin/" class="wpdevart_upgrade">Upgrade</a>
			</div>
			<a target="blank" href="<?php echo esc_url(wpdevart_comingsoon_support_url); ?>" class="wpdevart_support">Have any Questions? Get quick support!</a>
		</div>
		<h2>Send Mail to all subscribed Users</h2>
		<p><span style="color:#7052fb;font-weight:bold;">All fields are required</span></p>

		<form method="post" id="coming_soon_options_form_send_mail" action="admin.php?page='<?php echo  'coming-soon' ?>'">
			<span class="user_information_inputs">
				<input class="req_fields" type="text" value="" placeholder="Display Email" id="massage_from_mail" /><br />
				<input class="req_fields" type="text" value="" placeholder="Display Name " id="massage_from_name" /><br />
				<input class="req_fields" type="text" value="" placeholder="Message Title" id="massage_title" />
			</span>
			<textarea id="massage_description" placeholder="Message" style="width:400px; height:300px"></textarea><br /><br />
			<button type="button" id="send_mailing" class="save_button button button-primary"><span>Send Mail</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button>
			<br /><br />
			<span class="error_massage mailing_list"></span>
			<?php wp_nonce_field('wpda_coming_soon_mail_nonce', 'wpda_coming_soon_mail_nonce'); ?>
		</form>
		<h2>The list of the subscribed users</h2> <?php	$this->generete_subscriber_table_lists($mailing_lists);	?><h2>The list of the Subscribed users emails</h2><p><span style="color:#7052fb;font-weight:bold;">You can copy the emails list from the below and send emails using Gmail or other email services.</span></p><textarea readonly style="min-height:200px;width:95%">
		<?php foreach ($mailing_lists as $key => $value) {
			echo esc_html($key) . ',';
		} ?></textarea>
		<script>
			jQuery(document).ready(function(e) {
				jQuery('#send_mailing').click(function() {
					jQuery('#send_mailing').addClass('padding_loading');
					jQuery("#send_mailing").prop('disabled', true);
					jQuery('#coming_soon_options_form_send_mail .saving_in_progress').css('display', 'inline-block');

					jQuery.ajax({
						type: 'POST',
						url: "<?php echo esc_url(admin_url('admin-ajax.php?action=coming_soon_send_mail')); ?>",
						data: {
							massage_from_mail: jQuery('#massage_from_mail').val(),
							massage_from_name: jQuery('#massage_from_name').val(),
							massage_description: jQuery('#massage_description').val(),
							massage_title: jQuery('#massage_title').val(),
							wpda_coming_soon_mail_nonce:jQuery('#wpda_coming_soon_mail_nonce').val()
						},
					}).done(function(date) {
						switch (date) {
							case "<?php echo esc_html($this->text_parametrs['sucsses_mailed']); ?>":
								jQuery('#coming_soon_options_form_send_mail .saving_in_progress').css('display', 'none');
								jQuery('#coming_soon_options_form_send_mail .sucsses_save').css('display', 'inline-block');
								setTimeout(function() {
									jQuery('.sucsses_save').css('display', 'none');
									jQuery('#send_mailing').removeClass('padding_loading');
									jQuery("#send_mailing").prop('disabled', false);
								}, 2500);
								break;
							case "<?php echo esc_html($this->text_parametrs['mising_massage']); ?>":
							case "<?php echo esc_html($this->text_parametrs['missing_fromname']); ?>":
							case "<?php echo esc_html($this->text_parametrs['missing_frommail']); ?>":
								jQuery('#coming_soon_options_form_send_mail .saving_in_progress').css('display', 'none');
								jQuery('#coming_soon_options_form_send_mail .error_in_saving').css('display', 'inline-block');
								jQuery('#coming_soon_options_form_send_mail .error_massage').css('display', 'inline-block');
								jQuery('#coming_soon_options_form_send_mail .error_massage').html(date);
								setTimeout(function() {
									jQuery('#coming_soon_options_form_send_mail .error_massage').css('display', 'none');
									jQuery('#coming_soon_options_form_send_mail .error_in_saving').css('display', 'none');
									jQuery('#send_mailing').removeClass('padding_loading');
									jQuery("#send_mailing").prop('disabled', false);
								}, 3000);
								break;
							case "<?php echo esc_html($this->text_parametrs['missing_title']); ?>":
								jQuery('#coming_soon_options_form_send_mail .saving_in_progress').css('display', 'none');
								jQuery('#coming_soon_options_form_send_mail .error_in_saving').css('display', 'inline-block');
								jQuery('#coming_soon_options_form_send_mail .error_massage').css('display', 'inline-block');
								jQuery('#coming_soon_options_form_send_mail .error_massage').html(date);
								setTimeout(function() {
									jQuery('#coming_soon_options_form_send_mail .error_massage').css('display', 'none');
									jQuery('#coming_soon_options_form_send_mail .error_in_saving').css('display', 'none');
									jQuery('#send_mailing').removeClass('padding_loading');
									jQuery("#send_mailing").prop('disabled', false);
								}, 3000);
								break;
							default:
								jQuery('#coming_soon_options_form_send_mail .saving_in_progress').css('display', 'none');
								jQuery('#coming_soon_options_form_send_mail .error_in_saving').css('display', 'inline-block');
								jQuery('#coming_soon_options_form_send_mail .error_massage').css('display', 'inline-block');
								jQuery('#coming_soon_options_form_send_mail .error_massage').html(date);
						}
					});
				});
			});
		</script>

	<?php
	}
	/*######################################### SUBSCRIBE #######################################*/
	public function sending_mail() {
		if(!current_user_can( 'manage_options' )){
			echo esc_html($this->text_parametrs['authorize_problem']);
			die();
		}
		if(wp_verify_nonce($_POST['wpda_coming_soon_mail_nonce'], 'wpda_coming_soon_mail_nonce') === false){
			echo esc_html($this->text_parametrs['authorize_problem']);
			die();
		}

		$mailing_lists = json_decode(stripslashes(get_option('users_mailer', '')), true);
		if ($mailing_lists == NULL)
			$mailing_lists = array();
		$not_sending_mails = array();
		$sending_mails = array();
		if (!(isset($_POST['massage_title']) && $_POST['massage_title'] != '')) {
			echo esc_html($this->text_parametrs['missing_title']);
			die();
		}
		if (!(isset($_POST['massage_description']) && $_POST['massage_description'] != '')) {
			echo esc_html($this->text_parametrs['mising_massage']);
			die();
		}
		if (!(isset($_POST['massage_from_name']) && $_POST['massage_from_name'] != '')) {
			echo esc_html($this->text_parametrs['missing_fromname']);
			die();
		}
		if (!(isset($_POST['massage_from_mail']) && $_POST['massage_from_mail'] != '')) {
			echo esc_js($this->text_parametrs['missing_frommail']);
			die();
		}
		$mails_array = array();
		foreach ($mailing_lists as $key => $mail) {
			array_push($mails_array, $key);
		}
		$headers_from = sanitize_text_field($_POST['massage_from_mail']);
		$message_description = sanitize_text_field($_POST['massage_description']);
		$message_from_name = sanitize_text_field($_POST['massage_from_name']);
		$message_title = sanitize_text_field($_POST['massage_title']);
		$headers = 'From: ' . $message_from_name . ' <' . $headers_from . '>' . "\r\n";
		$send = wp_mail($mails_array, $message_title, $message_description, $headers);
		if (!$send) {
			die(esc_html($this->text_parametrs['error_maied']));
		}
		die(esc_html($this->text_parametrs['sucsses_mailed']));
	}

	/*######################### Library functions  #############################*/
	private function create_select_element_for_showing_effect($select_id = '', $curent_effect = 'none') {
		?>
		<select class="pro_select" id="<?php echo esc_html($select_id); ?>" name="<?php echo esc_html($select_id); ?>">
			<option <?php selected('none', $curent_effect); ?> value="none">none</option>
			<option <?php selected('random', $curent_effect); ?> value="random">random</option>
			<optgroup label="Attention Seekers">
				<option <?php selected('bounce', $curent_effect); ?> value="bounce">bounce</option>
				<option <?php selected('flash', $curent_effect); ?> value="flash">flash</option>
				<option <?php selected('pulse', $curent_effect); ?> value="pulse">pulse</option>
				<option <?php selected('rubberBand', $curent_effect); ?> value="rubberBand">rubberBand</option>
				<option <?php selected('shake', $curent_effect); ?> value="shake">shake</option>
				<option <?php selected('swing', $curent_effect); ?> value="swing">swing</option>
				<option <?php selected('tada', $curent_effect); ?> value="tada">tada</option>
				<option <?php selected('wobble', $curent_effect); ?> value="wobble">wobble</option>
			</optgroup>

			<optgroup label="Bouncing Entrances">
				<option <?php selected('bounceIn', $curent_effect); ?> value="bounceIn">bounceIn</option>
				<option <?php selected('bounceInDown', $curent_effect); ?> value="bounceInDown">bounceInDown</option>
				<option <?php selected('bounceInLeft', $curent_effect); ?> value="bounceInLeft">bounceInLeft</option>
				<option <?php selected('bounceInRight', $curent_effect); ?> value="bounceInRight">bounceInRight</option>
				<option <?php selected('bounceInUp', $curent_effect); ?> value="bounceInUp">bounceInUp</option>
			</optgroup>

			<optgroup label="Fading Entrances">
				<option <?php selected('fadeIn', $curent_effect); ?> value="fadeIn">fadeIn</option>
				<option <?php selected('fadeInDown', $curent_effect); ?> value="fadeInDown">fadeInDown</option>
				<option <?php selected('fadeInDownBig', $curent_effect); ?> value="fadeInDownBig">fadeInDownBig</option>
				<option <?php selected('fadeInLeft', $curent_effect); ?> value="fadeInLeft">fadeInLeft</option>
				<option <?php selected('fadeInLeftBig', $curent_effect); ?> value="fadeInLeftBig">fadeInLeftBig</option>
				<option <?php selected('fadeInRight', $curent_effect); ?> value="fadeInRight">fadeInRight</option>
				<option <?php selected('fadeInRightBig', $curent_effect); ?> value="fadeInRightBig">fadeInRightBig</option>
				<option <?php selected('fadeInUp', $curent_effect); ?> value="fadeInUp">fadeInUp</option>
				<option <?php selected('fadeInUpBig', $curent_effect); ?> value="fadeInUpBig">fadeInUpBig</option>
			</optgroup>

			<optgroup label="Flippers">
				<option <?php selected('flip', $curent_effect); ?> value="flip">flip</option>
				<option <?php selected('flipInX', $curent_effect); ?> value="flipInX">flipInX</option>
				<option <?php selected('flipInY', $curent_effect); ?> value="flipInY">flipInY</option>
			</optgroup>

			<optgroup label="Lightspeed">
				<option <?php selected('lightSpeedIn', $curent_effect); ?> value="lightSpeedIn">lightSpeedIn</option>
			</optgroup>

			<optgroup label="Rotating Entrances">
				<option <?php selected('rotateIn', $curent_effect); ?> value="rotateIn">rotateIn</option>
				<option <?php selected('rotateInDownLeft', $curent_effect); ?> value="rotateInDownLeft">rotateInDownLeft</option>
				<option <?php selected('rotateInDownRight', $curent_effect); ?> value="rotateInDownRight">rotateInDownRight</option>
				<option <?php selected('rotateInUpLeft', $curent_effect); ?> value="rotateInUpLeft">rotateInUpLeft</option>
				<option <?php selected('rotateInUpRight', $curent_effect); ?> value="rotateInUpRight">rotateInUpRight</option>
			</optgroup>

			<optgroup label="Specials">

				<option <?php selected('rollIn', $curent_effect); ?> value="rollIn">rollIn</option>
			</optgroup>

			<optgroup label="Zoom Entrances">
				<option <?php selected('zoomIn', $curent_effect); ?> value="zoomIn">zoomIn</option>
				<option <?php selected('zoomInDown', $curent_effect); ?> value="zoomInDown">zoomInDown</option>
				<option <?php selected('zoomInLeft', $curent_effect); ?> value="zoomInLeft">zoomInLeft</option>
				<option <?php selected('zoomInRight', $curent_effect); ?> value="zoomInRight">zoomInRight</option>
				<option <?php selected('zoomInUp', $curent_effect); ?> value="zoomInUp">zoomInUp</option>
			</optgroup>
		</select>
	<?php
	}

	/*############  Fonts function  ################*/

	private function create_select_element_for_font($select_id = '', $curent_font = 'none') {
	?>
		<select class="pro_select" id="<?php echo esc_html($select_id); ?>" name="<?php echo esc_html($select_id); ?>">

			<option <?php selected('Arial,Helvetica Neue,Helvetica,sans-serif', $curent_font); ?> value="Arial,Helvetica Neue,Helvetica,sans-serif">Arial *</option>
			<option <?php selected('Arial Black,Arial Bold,Arial,sans-serif', $curent_font); ?> value="Arial Black,Arial Bold,Arial,sans-serif">Arial Black *</option>
			<option <?php selected('Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif', $curent_font); ?> value="Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif">Arial Narrow *</option>
			<option <?php selected('Courier,Verdana,sans-serif', $curent_font); ?> value="Courier,Verdana,sans-serif">Courier *</option>
			<option <?php selected('Georgia,Times New Roman,Times,serif', $curent_font); ?> value="Georgia,Times New Roman,Times,serif">Georgia *</option>
			<option <?php selected('Times New Roman,Times,Georgia,serif', $curent_font); ?> value="Times New Roman,Times,Georgia,serif">Times New Roman *</option>
			<option <?php selected('Trebuchet MS,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Arial,sans-serif', $curent_font); ?> value="Trebuchet MS,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Arial,sans-serif">Trebuchet MS *</option>
			<option <?php selected('Verdana,sans-serif', $curent_font); ?> value="Verdana,sans-serif">Verdana *</option>
			<option <?php selected('American Typewriter,Georgia,serif', $curent_font); ?> value="American Typewriter,Georgia,serif">American Typewriter</option>
			<option <?php selected('Andale Mono,Consolas,Monaco,Courier,Courier New,Verdana,sans-serif', $curent_font); ?> value="Andale Mono,Consolas,Monaco,Courier,Courier New,Verdana,sans-serif">Andale Mono</option>
			<option <?php selected('Baskerville,Times New Roman,Times,serif', $curent_font); ?> value="Baskerville,Times New Roman,Times,serif">Baskerville</option>
			<option <?php selected('Bookman Old Style,Georgia,Times New Roman,Times,serif', $curent_font); ?> value="Bookman Old Style,Georgia,Times New Roman,Times,serif">Bookman Old Style</option>
			<option <?php selected('Calibri,Helvetica Neue,Helvetica,Arial,Verdana,sans-serif', $curent_font); ?> value="Calibri,Helvetica Neue,Helvetica,Arial,Verdana,sans-serif">Calibri</option>
			<option <?php selected('Cambria,Georgia,Times New Roman,Times,serif', $curent_font); ?> value="Cambria,Georgia,Times New Roman,Times,serif">Cambria</option>
			<option <?php selected('Candara,Verdana,sans-serif', $curent_font); ?> value="Candara,Verdana,sans-serif">Candara</option>
			<option <?php selected('Century Gothic,Apple Gothic,Verdana,sans-serif', $curent_font); ?> value="Century Gothic,Apple Gothic,Verdana,sans-serif">Century Gothic</option>
			<option <?php selected('Century Schoolbook,Georgia,Times New Roman,Times,serif', $curent_font); ?> value="Century Schoolbook,Georgia,Times New Roman,Times,serif">Century Schoolbook</option>
			<option <?php selected('Consolas,Andale Mono,Monaco,Courier,Courier New,Verdana,sans-serif', $curent_font); ?> value="Consolas,Andale Mono,Monaco,Courier,Courier New,Verdana,sans-serif">Consolas</option>
			<option <?php selected('Constantia,Georgia,Times New Roman,Times,serif', $curent_font); ?> value="Constantia,Georgia,Times New Roman,Times,serif">Constantia</option>
			<option <?php selected('Corbel,Lucida Grande,Lucida Sans Unicode,Arial,sans-serif', $curent_font); ?> value="Corbel,Lucida Grande,Lucida Sans Unicode,Arial,sans-serif">Corbel</option>
			<option <?php selected('Franklin Gothic Medium,Arial,sans-serif', $curent_font); ?> value="Franklin Gothic Medium,Arial,sans-serif">Franklin Gothic Medium</option>
			<option <?php selected('Garamond,Hoefler Text,Times New Roman,Times,serif', $curent_font); ?> value="Garamond,Hoefler Text,Times New Roman,Times,serif">Garamond</option>
			<option <?php selected('Gill Sans MT,Gill Sans,Calibri,Trebuchet MS,sans-serif', $curent_font); ?> value="Gill Sans MT,Gill Sans,Calibri,Trebuchet MS,sans-serif">Gill Sans MT</option>
			<option <?php selected('Helvetica Neue,Helvetica,Arial,sans-serif', $curent_font); ?> value="Helvetica Neue,Helvetica,Arial,sans-serif">Helvetica Neue</option>
			<option <?php selected('Hoefler Text,Garamond,Times New Roman,Times,sans-serif', $curent_font); ?> value="Hoefler Text,Garamond,Times New Roman,Times,sans-serif">Hoefler Text</option>
			<option <?php selected('Lucida Bright,Cambria,Georgia,Times New Roman,Times,serif', $curent_font); ?> value="Lucida Bright,Cambria,Georgia,Times New Roman,Times,serif">Lucida Bright</option>
			<option <?php selected('Lucida Grande,Lucida Sans,Lucida Sans Unicode,sans-serif', $curent_font); ?> value="Lucida Grande,Lucida Sans,Lucida Sans Unicode,sans-serif">Lucida Grande</option>
			<option <?php selected('monospace', $curent_font); ?> value="monospace">monospace</option>
			<option <?php selected('Palatino Linotype,Palatino,Georgia,Times New Roman,Times,serif', $curent_font); ?> value="Palatino Linotype,Palatino,Georgia,Times New Roman,Times,serif">Palatino Linotype</option>
			<option <?php selected('Tahoma,Geneva,Verdana,sans-serif', $curent_font); ?> value="Tahoma,Geneva,Verdana,sans-serif">Tahoma</option>
			<option <?php selected('Rockwell, Arial Black, Arial Bold, Arial, sans-serif', $curent_font); ?> value="Rockwell, Arial Black, Arial Bold, Arial, sans-serif">Rockwell</option>
		</select>
<?php
	}
	/*################################## FEATURED PLUGINS ADMIN PAGE #########################################*/


	public function hire_expert() {
        $plugins_array = array(
            'custom_site_dev' => array(
                'image_url' => $this->plugin_url . 'images/hire_expert/1.png',
                'title' => 'Custom WordPress Development',
                'description' => 'Hire a WordPress expert and make any custom development for your WordPress website.',
            ),
            'custom_plug_dev' => array(
                'image_url' => $this->plugin_url . 'images/hire_expert/2.png',
                'title' => 'WordPress Plugin Development',
                'description' => 'Our developers can create any WordPress plugin from zero. Also, they can customize any plugin and add any functionality.',
            ),
            'custom_theme_dev' => array(
                'image_url' => $this->plugin_url . 'images/hire_expert/3.png',
                'title' => 'WordPress Theme Development',
                'description' => 'If you need an unique theme or any customizations for a ready theme, then our developers are ready.',
            ),
            'custom_theme_inst' => array(
                'image_url' => $this->plugin_url . 'images/hire_expert/4.png',
                'title' => 'WordPress Theme Installation and Customization',
                'description' => 'If you need a theme installation and configuration, then just let us know, our experts configure it.',
            ),
            'gen_wp_speed' => array(
                'image_url' => $this->plugin_url . 'images/hire_expert/5.png',
                'title' => 'General WordPress Support',
                'description' => 'Our developers can provide general support. If you have any problem with your website, then our experts are ready to help.',
            ),
            'speed_op' => array(
                'image_url' => $this->plugin_url . 'images/hire_expert/6.png',
                'title' => 'WordPress Speed Optimization',
                'description' => 'Hire an expert from WpDevArt and let him take care of your website speed optimization.',
            ),
            'mig_serv' => array(
                'image_url' => $this->plugin_url . 'images/hire_expert/7.png',
                'title' => 'WordPress Migration Services',
                'description' => 'Our developers can migrate websites from any platform to WordPress.',
            ),
            'page_seo' => array(
                'image_url' => $this->plugin_url . 'images/hire_expert/8.png',
                'title' => 'WordPress SEO',
                'description' => 'SEO is an important part of any website. Hire an expert and he will organize the SEO of your website.',
            ),
        );
        $content = '';
        $content .= '<h1 class="wpda_hire_exp_h1"> Hire an Expert from WpDevArt </h1>';
        $content .= '<div class="hire_expert_main">';
        foreach ($plugins_array as $key => $plugin) {
            $content .= '<div class="wpdevart_hire_main"><a target="_blank" class="wpda_hire_buklet" href="https://wpdevart.com/hire-a-wordpress-developer-online-submit-form/">';
            $content .= '<div class="wpdevart_hire_image"><img src="' . esc_url($plugin["image_url"]) . '"></div>';
            $content .= '<div class="wpdevart_hire_information">';
            $content .= '<div class="wpdevart_hire_title">' . esc_html($plugin["title"]) . '</div>';
            $content .= '<p class="wpdevart_hire_description">' . esc_html($plugin["description"]) . '</p>';
            $content .= '</div></a></div>';
        }
        $content .= '<div><a target="_blank" class="wpda_hire_button" href="https://wpdevart.com/hire-a-wordpress-developer-online-submit-form/">Hire an Expert</a></div>';
        $content .= '</div>';
        echo $content;
    }

    public function featured_plugins() {
        $plugins_array = array(
			'gallery_album' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/gallery-album-icon.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-gallery-plugin',
				'title'			=>	'WordPress Gallery plugin',
				'description'	=>	'Gallery plugin is an useful tool that will help you to create Galleries and Albums. Try our nice Gallery views and awesome animations.'
			),
			'Pricing Table' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/Pricing-table.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-pricing-table-plugin/',
				'title'			=>	'WordPress Pricing Table',
				'description'	=>	'WordPress Pricing Table plugin is a nice tool for creating beautiful pricing tables. Use WpDevArt pricing table themes and create tables just in a few minutes.'
			),
			'countdown-extended' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/icon-128x128.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-countdown-extended-version/',
				'title'			=>	'WordPress Countdown Extended',
				'description'	=>	'Countdown extended is an fresh and extended version of countdown timer. You can easily create and add countdown timers to your website.'
			),
			'chart' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/chart-featured.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-organization-chart-plugin/',
				'title'			=>	'WordPress Organization Chart',
				'description'	=>	'WordPress organization chart plugin is a great tool for adding organizational charts to your WordPress websites.'
			),
			'Contact forms' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/contact_forms.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-contact-form-plugin/',
				'title'			=>	'Contact Form Builder',
				'description'	=>	'Contact Form Builder plugin is an handy tool for creating different types of contact forms on your WordPress websites.'
			),
			'Booking Calendar' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/Booking_calendar_featured.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-booking-calendar-plugin/',
				'title'			=>	'WordPress Booking Calendar',
				'description'	=>	'WordPress Booking Calendar plugin is an awesome tool to create a booking system for your website. Create booking calendars in a few minutes.'
			),
			'youtube' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/youtube.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-youtube-embed-plugin',
				'title'			=>	'WordPress YouTube Embed',
				'description'	=>	'YouTube Embed plugin is an convenient tool for adding videos to your website. Use YouTube Embed plugin for adding YouTube videos in posts/pages, widgets.'
			),
			'facebook-comments' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/facebook-comments-icon.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-facebook-comments-plugin/',
				'title'			=>	'Wpdevart Social comments',
				'description'	=>	'WordPress Facebook comments plugin will help you to display Facebook Comments on your website. You can use Facebook Comments on your pages/posts.'
			),
			'countdown' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/countdown.jpg',
				'site_url'		=>	'https://wpdevart.com/wordpress-countdown-plugin/',
				'title'			=>	'WordPress Countdown plugin',
				'description'	=>	'WordPress Countdown plugin is an nice tool for creating countdown timers for your website posts/pages and widgets.'
			),
			'lightbox' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/lightbox.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-lightbox-plugin',
				'title'			=>	'WordPress Lightbox plugin',
				'description'	=>	'WordPress Lightbox Popup is an high customizable and responsive plugin for displaying images and videos in popup.'
			),
			'facebook' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/facebook.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-facebook-like-box-plugin',
				'title'			=>	'Social Like Box',
				'description'	=>	'Facebook like box plugin will help you to display Facebook like box on your wesite, just add Facebook Like box widget to sidebar or insert it into posts/pages and use it.'
			),
			'vertical_menu' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/vertical-menu.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-vertical-menu-plugin/',
				'title'			=>	'WordPress Vertical Menu',
				'description'	=>	'WordPress Vertical Menu is a handy tool for adding nice vertical menus. You can add icons for your website vertical menus using our plugin.'
			),
			'duplicate_page' => array(
				'image_url'		=>	$this->plugin_url . 'images/featured_plugins/featured-duplicate.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-duplicate-page-plugin-easily-clone-posts-and-pages/',
				'title'			=>	'WordPress Duplicate page',
				'description'	=>	'Duplicate Page or Post is a great tool that allows duplicating pages and posts. Now you can do it with one click.'
			)
        );
        $html = '';
        $html .= '<h1 class="wpda_featured_plugins_title">Featured Plugins</h1>';
        foreach ($plugins_array as $plugin) {
            $html .= '<div class="featured_plugin_main">';
            $html .= '<div class="featured_plugin_image"><a target="_blank" href="' . esc_url($plugin['site_url']) . '"><img src="' . esc_url($plugin['image_url']) . '"></a></div>';
            $html .= '<div class="featured_plugin_information">';
            $html .= '<div class="featured_plugin_title">';
            $html .= '<h4><a target="_blank" href="' . esc_url($plugin['site_url']) . '">' . esc_html($plugin['title']) . '</a></h4>';
            $html .= '</div>';
            $html .= '<p class="featured_plugin_description">' . esc_html($plugin['description']) . '</p>';
            $html .= '<a target="_blank" href="' . esc_url($plugin['site_url']) . '" class="blue_button">Check The Plugin</a>';
            $html .= '</div>';
            $html .= '<div style="clear:both"></div>';
            $html .= '</div>';
        }
        echo $html;
    }

    public function featured_themes() {
        $themes_array = array(
            'tistore' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/tistore.jpg',
                'site_url' => 'https://wpdevart.com/tistore-best-ecommerce-theme-for-wordpress/',
                'title' => 'TiStore',
                'description' => 'TiStore is one of the best eCommerce WordPress themes that is fully integrated with WooCommerce.',
            ),
            'megastore' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/megastore.jpg',
                'site_url' => 'https://wpdevart.com/megastore-best-woocommerce-theme-for-wordpress/',
                'title' => 'MegaStore',
                'description' => 'MegaStore is one of the best WooCommerce themes available for WordPress.',
            ),
            'jevstore' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/jevstore.jpg',
                'site_url' => 'https://wpdevart.com/jewstore-best-wordpress-jewelry-store-theme/',
                'title' => 'JewStore',
                'description' => 'JewStore is a WordPress WooCommerce theme designed for jewelry stores and blogs.',
            ),
            'cakeshop' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/cakeshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-cake-shop-theme/',
                'title' => 'Cake Shop',
                'description' => 'WordPress Cake Shop is a multi-purpose WooCommerce-ready theme.',
            ),
            'flowershop' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/flowershop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-flower-shop-theme/',
                'title' => 'Flower Shop',
                'description' => 'WordPress Flower Shop is a responsive and WooCommerce-ready theme developed by our team.',
            ),
            'coffeeshop' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/coffeeshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-coffee-shop-cafe-theme/',
                'title' => 'Coffee Shop',
                'description' => 'It is a responsive and user-friendly theme designed specifically for coffee shop or cafe websites.',
            ),
            'weddingplanner' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/weddingplanner.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-wedding-planner-theme/',
                'title' => 'Wedding Planner',
                'description' => 'Wedding Planner is a responsive WordPress theme that is fully integrated with WooCommerce.',
            ),
            'Amberd' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/Amberd.jpg',
                'site_url' => 'https://wpdevart.com/amberd-wordpress-online-store-theme/',
                'title' => 'AmBerd',
                'description' => 'AmBerd has all the necessary features and functionality to create a beautiful WordPress website.',
            ),
            'bookshop' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/bookshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-book-shop-theme/',
                'title' => 'Book Shop',
                'description' => 'The Book Shop WordPress theme is a fresh and well-designed theme for creating bookstores or book blogs.',
            ),
            'ecommercemodernstore' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/ecommercemodernstore.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-ecommerce-modern-store-theme/',
                'title' => 'Ecommerce Modern Store',
                'description' => 'WordPress Ecommerce Modern Store theme is one of the best solutions if you want to create an online store.',
            ),
            'electrostore' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/electrostore.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-electronics-store-electro-theme/',
                'title' => 'ElectroStore',
                'description' => 'This is a responsive and WooCommerce-ready electronic store theme.',
            ),
            'jewelryshop' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/jewelryshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-jewelry-shop-theme/',
                'title' => 'Jewelry Shop',
                'description' => 'WordPress Jewelry Shop theme is designed specifically for jewelry websites, but of course, you can use this theme for other types of websites as well.',
            ),
            'fashionshop' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/fashionshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-fashion-shop-theme/',
                'title' => 'Fashion Shop',
                'description' => 'The Fashion Shop is one of the best responsive WordPress WooCommerce themes for creating a fashion store website.',
            ),
            'barbershop' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/barbershop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-barbershop-theme/',
                'title' => 'Barbershop',
                'description' => 'WordPress Barbershop is another responsive and functional theme developed by our team.',
            ),
            'furniturestore' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/furniturestore.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-furniture-store-theme/',
                'title' => 'Furniture Store',
                'description' => 'This is a great option to quickly create an online store using our theme and the WooCommerce plugin. Our theme is fully integrated with WooCommerce.',
            ),
            'clothing' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/clothing.jpg',
                'site_url' => 'https://wpdevart.com/tistore-best-ecommerce-theme-for-wordpress/',
                'title' => 'Clothing',
                'description' => 'The Clothing WordPress theme is one of the best responsive eCommerce themes available for WordPress.',
            ),
            'weddingphotography' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/weddingphotography.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-wedding-photography-theme/',
                'title' => 'Wedding Photography',
                'description' => 'WordPress Wedding Photography theme is one of the best themes specially designed for wedding photographers or photography companies.',
            ),
            'petshop' => array(
                'image_url' => $this->plugin_url . 'images/featured_themes/petshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-pet-shop-theme/',
                'title' => 'Pet Shop',
                'description' => 'Pet Shop is a powerful and well-designed WooCommerce WordPress theme.',
            ),

        );
        $html = '';
        $html .= '<div class="wpdevart_main"><h1 class="wpda_featured_themes_title">Featured Themes</h1>';

        $html .= '<div class="div-container">';
        foreach ($themes_array as $theme) {
            $html .= '<div class="theme" data-slug="tistore"><div class="theme-img">';                
            $html .= ' <img src="'.esc_url($theme['image_url']).'" alt="' . esc_attr($theme['title']) . '">';
            $html .= '</div>';
            $html .= '<div class="theme-description">' . esc_html($theme['description']) . '</div>';
            $html .= '<div class="theme-name-container">'; 
            $html .= '<h2 class="theme-name">' . esc_html($theme['title']) . '</h2>';
            $html .= '<div class="theme-actions">';
            $html .= '<a target="_blank" aria-label="Check theme" class="button button-primary load-customize" href="' . esc_url($theme['site_url']) . '">Check Theme</a>';
            $html .= '</div></div></div>';
            
            
        }
        $html .= '</div></div>';
        echo $html;
    }

	
}
