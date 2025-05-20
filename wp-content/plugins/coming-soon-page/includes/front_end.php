<?php

/*############  Coming soon Front-end Code  ################*/

class coming_soon_front_end {
	private $menu_name;
	private $plugin_url;
	private $databese_parametrs;
	private $params;
	private $list_of_animations;

	/*############  The construct function ################*/

	function __construct($params) {

		$this->menu_name = $params['menu_name'];
		$this->databese_parametrs = $params['databese_parametrs'];
		if (isset($params['plugin_url']))
			$this->plugin_url = $params['plugin_url'];
		else
			$this->plugin_url = trailingslashit(dirname(plugins_url('', __FILE__)));
		$this->params = $this->generete_params();
	}

	/*############  Function for generating parameters  ################*/

	private function generete_params() {

		foreach ($this->databese_parametrs as $param_array_key => $param_value) {
			foreach ($this->databese_parametrs[$param_array_key] as $key => $value) {
				if ($key == 'coming_soon_page_showed_ips') {
					$ips = json_decode(stripslashes(get_option($key, $value)), true);
					if (is_array($ips)) {
						foreach ($ips as $key1 => $value1) {
							$ips[$key1] = esc_html($value1);
						}
					} else {
						$ips = array();
					}
					$front_end_parametrs[$key] = $ips;
				} elseif (strpos($key, 'message') !== false) {
					$front_end_parametrs[$key] = wp_filter_post_kses(get_option($key, $value));
				} else {
					$front_end_parametrs[$key] = esc_html(get_option($key, $value));
				}
			}
		}

		return $front_end_parametrs;
	}

	/*############  Function for creating the Front-end  ################*/

	public function create_fornt_end() {
		if ($this->params['coming_soon_page_mode'] == 'on') {
			//If user doesn't logged in, then the page will automatically redirect him to the Coming soon page
			if ((!is_user_logged_in() && !$this->is_in_except()) || (isset($_GET['special_variable_for_live_previev']) && $_GET['special_variable_for_live_previev'] == 'sdfg564sfdh645fds4ghs515vsr5g48strh846sd6g41513btsd')) {
				//Get path of the Coming Soon display page and then do a redirect
				$this->generete_front_end_html();
				exit();
			}
		} else
		if ((isset($_GET['special_variable_for_live_previev']) && $_GET['special_variable_for_live_previev'] == 'sdfg564sfdh645fds4ghs515vsr5g48strh846sd6g41513btsd')) {
			$this->generete_front_end_html();
			exit();
		}
	}

	/*############################### Function for generating the Front-end HTML ################################*/

	public function generete_front_end_html() {
?>
		<!DOCTYPE html>
		<html>

		<head>
			<meta charset="utf-8">
			<title><?php echo  esc_html($this->params['coming_soon_page_page_seo_title']);  ?></title>
			<meta name="viewport" content="width=device-width" />
			<meta name="viewport" content="initial-scale=1.0" />
			<meta name="robots" content="<?php if ((int)$this->params['coming_soon_page_enable_search_robots'])  echo "index, follow";	else echo "noindex, nofollow"; ?>" />
			<meta name="description" content="<?php echo esc_html($this->params['coming_soon_page_meta_description']); ?>">
			<meta name="keywords" content="<?php echo esc_html($this->params['coming_soon_page_meta_keywords']); ?>">
			<?php
			wp_print_scripts('jquery');
			wp_print_scripts('coming-soon-script');
			wp_print_styles('coming-soon-style');
			$this->generete_front_styles();
			$this->generete_front_javascript();
			?>
		</head>
		<body>
			<?php $this->content_html(); ?>
		</body>
		</html><?php
	}

			/*############  Function for generating the Front-end JavaScript ################*/

			private function generete_front_javascript() {
				?><script type="text/javascript">
					var loading_gif_url = "<?php echo esc_url($this->plugin_url) . 'images/loading.gif' ?>";
					var coming_soon_ajax_mail_url = "<?php echo esc_url(admin_url('admin-ajax.php?action=coming_soon_page_save_user_mail')); ?>";
					var curen_site_home_page = "<?php echo esc_url(site_url()); ?>";
					var animation_parametrs = []
				</script><?php
			}

			/*############  Function for generating the Front-end styles ################*/

			private function generete_front_styles() {
				?><style>
				<?php
					$this->background_css();
					$this->content_css();
					$this->generete_logo_css();
					$this->generete_title_css();
					$this->generete_message_css();
					$this->generete_socialis_css();
					$this->generete_message_footer_css();

				?></style><?php
			}


			/*############################################# Function excepting Pages/Posts, IPs #################################################*/

			private function is_in_except() {
				$only_for_home = false;
				$ips = $this->params['coming_soon_page_showed_ips'];
				if (!$ips)
					$ips = array();

				if ($this->params['coming_soon_enable_only_for_home'] == '1') {
					if (is_front_page() || is_home()) {
						$only_for_home = true;
					}
				}
				$in_list = in_array($this->get_real_ip(), $ips) || $only_for_home;
				if ($in_list)
					return true;
				return false;
			}

			/*############  Function for getting the IP ################*/




			private function get_real_ip() {
				$keys = array('REMOTE_ADDR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED');
				foreach ($keys as $k) {
					if (!empty($_SERVER[$k]) && filter_var($_SERVER[$k], FILTER_VALIDATE_IP)) {
						return $_SERVER[$k];
					}
				}
				return "UNKNOWN";
			}

			/*############################################################ Background Function ######################################################*/

			private function background_css() {
				switch ($this->params['coming_soon_page_radio_backroun']) {
					case 'back_color':
						echo 'body{ background-color:' . esc_html($this->params['coming_soon_page_background_color']) . '; }';
						break;
					case 'back_imge':
						echo 'body{ background:url("' . esc_html($this->params['coming_soon_page_background_img']) . '") no-repeat center center fixed; }';
						break;
				}
			}


			/*#################################################################### Content Function ##########################################################*/

			private function content_html() {
				// content conteiner
				echo '<div id="main_inform_div"><span class="aligment"><div class="information"><center>';
				$this->create_logo_html();
				$this->create_title_html();
				$this->create_message_html();
				$this->create_socialis_html();
				$this->create_message_footer_html();
				// Close the content container
				echo '</center></div></span></div> ';
			}

			/*############   Content CSS function  ################*/

			private function content_css() {
				$aligment_position = 'text-align:center; vertical-align:middle;';
				echo ".information{ background: rgba(255,255,255,0.55); border-radius:8px; max-width:740px; padding-right:10px;padding-left:10px;padding-bottom:10px; }\r\n";
				echo ".aligment{" . esc_html($aligment_position) . ";}\r\n";
				echo "#main_inform_div{padding:15px;}\r\n";
			}

			/*############   Content Java-script function  ################*/

			private function content_javascript() {
			}
			/*######################################################################## Logo Function ##############################################################*/
			private function create_logo_html() {
				if ($this->params['coming_soon_page_logo_enable']) {
					?><div id="logo">
							<img id="logo_img" src="<?php echo stripslashes(esc_url($this->params['coming_soon_page_page_logo'])); ?>" />
						</div>
					<?php }
			}

			/*############  Generate Logo function  ################*/

			private function generete_logo_css() {
				echo '#logo{margin-top:10px;text-align:center;}';
				echo '#logo img{max-height:210px;max-width:100%;}';
			}
			private function generete_logo_javascript() {
			}
			/*################################################################################### Title function ###########################################################################*/
			private function create_title_html() {
				if ($this->params['coming_soon_page_title_enable']) {
					?>
						<div id="title_style">
							<h1 id="title_h1"><?php echo esc_html(stripslashes($this->params['coming_soon_page_page_title'])); ?></h1>
						</div>
					<?php
				}
			}

			/*################################################################################### Generate Title CSS Function ###########################################################################*/

			private function generete_title_css() {
				echo '#title_style{margin-top:10px;text-align:center;}';
				echo '#title_h1{font-family:Times New Roman,Times,Georgia,serif;font-size:55px;color:#000000;}';
			}

			/*################################################################################### Generate title JS function ###########################################################################*/

			private function generete_title_javascript() {
			}
			/*################################################################################### Message Box function ###########################################################################*/
			private function create_message_html() {
				if ($this->params['coming_soon_page_message_enable']) {
					?><div id="descrip">
							<?php echo stripslashes(wp_filter_post_kses($this->params['coming_soon_page_page_message'])); ?>
					</div><?php
				}
			}

			/*############ Functions for generating the message CSS  ################*/

			private function generete_message_css() {
				echo '#descrip{margin-top:10px;text-align:center;}';
			}

			/*############ Functions for generating the message Java script  ################*/

			private function generete_message_javascript() {
			}
			/*################################################################################### Message Footer ###########################################################################*/
			private function create_message_footer_html() {
				if ($this->params['coming_soon_page_message_footer_enable'] == '1') {
					?><div id="descrip_footer">
							<?php echo stripslashes(wp_filter_post_kses($this->params['coming_soon_page_page_message_footer'])); ?>
					</div><?php
				}
			}

			/*############# Footer message function ################*/

			private function generete_message_footer_css() {
				echo '#descrip_footer{margin-top:' . esc_html($this->params['coming_soon_page_message_footer_top_distance']) . 'px;text-align:' . esc_html($this->text_align($this->params['coming_soon_page_message_footer_in_content_position'])) . ';}';
			}

			/*################################################################################### Social Buttons function ###########################################################################*/
			private function create_socialis_html() {
				if ($this->params['coming_soon_page_socialis_enable']) {
					?><div id="soc_icons" class="soc_icon_coneiner">
							<?php if ($this->params['coming_soon_page_facebook']) { ?>
								<span class="soc_icon">
									<a href="<?php echo esc_html($this->params['coming_soon_page_facebook']); ?>" <?php echo $this->params['coming_soon_page_open_new_tabe'] ? ' target="_blank" ' : ''; ?>><img src="<?php echo esc_url($this->plugin_url) . 'images/template1/facebook.png'; ?>" /></a>
								</span>
							<?php } ?>
							<?php if ($this->params['coming_soon_page_twitter']) { ?>
								<span class="soc_icon">
									<a href="<?php echo esc_html($this->params['coming_soon_page_twitter']); ?>" <?php echo $this->params['coming_soon_page_open_new_tabe'] ? ' target="_blank" ' : ''; ?>><img src="<?php echo esc_url($this->plugin_url) . 'images/template1/twiter.png'; ?>" /></a>
								</span>
							<?php } ?>
							<?php if ($this->params['coming_soon_page_youtube']) { ?>
								<span class="soc_icon">
									<a href="<?php echo esc_html($this->params['coming_soon_page_youtube']); ?>" <?php echo $this->params['coming_soon_page_open_new_tabe'] ? ' target="_blank" ' : ''; ?>><img src="<?php echo esc_url($this->plugin_url) . 'images/template1/youtobe.png'; ?>" /></a>
								</span>
							<?php } ?>
							<?php if ($this->params['coming_soon_page_instagram']) { ?>
								<span class="soc_icon">
									<a href="<?php echo esc_html($this->params['coming_soon_page_instagram']); ?>" <?php echo $this->params['coming_soon_page_open_new_tabe'] ? ' target="_blank" ' : ''; ?>><img src="<?php echo esc_url($this->plugin_url) . 'images/template1/instagram.png'; ?>" /></a>
								</span>
							<?php } ?>
						</div><?php
				}
			}
			private function generete_socialis_css() {
				echo '#soc_icons{text-align:center;}';
				echo '#soc_icons img{margin-top:10px; }';
			}
			private function generete_socialis_javascript() {
			}

			private function darkest_brigths($color, $pracent) {
				$new_color = $color;
				if (!(strlen($new_color == 6) || strlen($new_color) == 7)) {
					return $color;
				}
				$color_vandakanishov = strpos($new_color, '#');
				if ($color_vandakanishov == false) {
					$new_color = str_replace('#', '', $new_color);
				}
				$color_part_1 = substr($new_color, 0, 2);
				$color_part_2 = substr($new_color, 2, 2);
				$color_part_3 = substr($new_color, 4, 2);
				$color_part_1 = dechex((int) (hexdec($color_part_1) - (hexdec($color_part_1) * $pracent / 100)));
				$color_part_2 = dechex((int) (hexdec($color_part_2)  - (((hexdec($color_part_2))) * $pracent / 100)));
				$color_part_3 = dechex((int) (hexdec($color_part_3) - (((hexdec($color_part_3))) * $pracent / 100)));
				if (strlen($color_part_1) < 2) $color_part_1 = "0" . $color_part_1;
				if (strlen($color_part_2) < 2) $color_part_2 = "0" . $color_part_2;
				if (strlen($color_part_3) < 2) $color_part_3 = "0" . $color_part_3;

				$new_color = $color_part_1 . $color_part_2 . $color_part_3;
				if ($color_vandakanishov == false) {
					return $new_color;
				} else {
					return '#' . $new_color;
				}
			}
			/*###################################### ANOTHER IMPORTANT FUNCTIONS OF COMING SOON PLUGIN ####################################*/
			private function text_align($value) {
				switch ($value) {
					case '0':
					case 0:
						return 'left';
						break;
					case '1':
					case 1:
						return 'center';
						break;
					case '2':
					case 2:
						return 'right';
						break;
				}
				return $value;
			}
		}
?>