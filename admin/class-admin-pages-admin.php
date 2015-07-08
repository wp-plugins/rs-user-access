<?php
/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 */
class Admin_Pages_Admin {

	protected static $instance = null;
	protected $plugin_screen_hook_suffix = null;
	private function __construct() {
		global $wpdb;


		$this->wpdb = $wpdb;
		$this->table_name = $this->wpdb->prefix . "rs_admin_pages";	
		$plugin = Admin_Pages::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		$this->message 		= "";
		$this->userMessage 	= "";
		$this->myOops 		= "";

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Lets remove menu items for the logged in user
		add_action( 'admin_menu', array( $this, 'remove_menu_items') );

	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Admin_Pages::VERSION );
			wp_enqueue_style( $this->plugin_slug .'-font-awesome', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ), array(), Admin_Pages::VERSION );
		}

	}

	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Admin_Pages::VERSION );
		}

	}

	public function add_plugin_admin_menu() {
		$result = $this->plugin_screen_hook_suffix = add_menu_page( 'Admin Pages', 'Admin Pages', 'manage_options', $this->plugin_slug, array( $this, 'display_plugin_admin_page' ), '
dashicons-admin-generic');
	}

	private function getMySelect() {
		global $current_user;
		// Let's populate a select for the user to select user role for
		$mySelect = '<select name="myUsers" id="myUsers">';
		$mySelect .= '<option value="-1">-- Please Select User --</option>';
		foreach (get_users() as $user) {
			if ( $user->data->ID !== $current_user->data->ID) {
				$userSel = '';
				if ( isset($_GET['id']) && $_GET['id'] == $user->data->ID ) {
					$userSel = 'selected="selected"';
				}
				$mySelect .= '<option '.$userSel.' value="'.$user->data->ID.'">'.$user->data->user_login.' - '.$user->data->user_nicename.'</option>';
			}
		}
		$mySelect .= '</select>';
		return $mySelect;
	}

	private function getMyMenuCheck() {
		global $menu, $submenu;

		$userOptions 	= array(); 
		$myDataArray 	= array(); 
		$myDataArraySub = array();

		if ( isset($_GET['id']) ) {
			// We need to grab details for user
			$userOptions = $this->wpdb->get_results("SELECT * FROM {$this->table_name} WHERE admin_page_user_id = {$_GET['id']}");

			if ( count($userOptions) ) {
				$myDataArray 	= json_decode($userOptions[0]->admin_page_menu);
				$myDataArraySub = json_decode($userOptions[0]->admin_page_sub_menu);
				$this->message 	= $userOptions[0]->admin_page_exit_message;
			}
		} 

		// Let's populate some checkboxes for the menu items
		$myCheck 	= '<ul class="parent-menu">';
		$mySubCount = 1;
		$count 		= 0;
		foreach ( $menu as $item ) {
			// It's a little weird, but we need to check to see if the first key in the array has a value, as it apears to have menu items without names
			// We only care about parent menu items as if this is gone then child will be gone
			// Future versions may have the ability to hide child rather than parent... maybe...
			if ( $item[0] != '' ) {
				$checkMe = '';
				// $item[0] for some menu items has tags and a 0 in it, we are removing these for visual benefit
				if ( count($myDataArray) > 0 && in_array(trim( strip_tags( str_replace('0', '',$item[0]) ) ), $myDataArray ) ) {
					$checkMe = 'checked="checked"';
				}

				// If a user has not been saved yet, the assumption is they can see everything for their role until specified
				if ( count($myDataArray) <= 0 ) {
					$checkMe = 'checked="checked"';
				}

				$check = (++$count % 2) ? "odd" : "even";

				$myFamily = '';
				if ( isset($submenu[$item[2]]) && count($submenu[$item[2]]) > 0 ) {
					$myFamily = '<i class="fa fa-plus-square-o fltRight mgTop5 pointer"></i>';
				}

				$myCheck .= '<li class="'.$check.'">'.$myFamily;
				$myInpVal1 = trim( strip_tags( str_replace('0', '',$item[0]) ) );
				$myInpVal2 = trim( strip_tags( strtr( $item[0], array(' ' => '-') ) ) );
				$myCheck .= '<input '.$checkMe.' class="check-boxes mainMenu" data-menu="'.$myInpVal1.'" type="checkbox" name="menu[]" id="check-'.$myInpVal1.'"  value="'.$myInpVal1.'"><label for="check-'.$myInpVal1.'">'.$myInpVal1. '</label>';
				if ( isset($submenu[$item[2]]) && count($submenu[$item[2]]) > 0 ) {
					// This parent item has children, we need to show these
					$myCheck .= '<ul>';
					
					foreach ($submenu[$item[2]] as $subItem) {

						$checkMeSub = '';
						if ( count($myDataArraySub) > 0 && in_array(trim( strip_tags( str_replace('0', '', $subItem[0]) ) ), $myDataArraySub->$item[2] ) ) {
							$checkMeSub = 'checked="checked"';
						}

						// If a user has not been saved yet, the assumption is they can see everything for their role until specified
						if ( count($myDataArraySub) <= 0 ) {
							$checkMeSub = 'checked="checked"';
						}

						$mySubInpVal1 = trim( strip_tags( str_replace('0', '',$subItem[0]) ) );
						$mySubInpVal2 = trim( strip_tags( strtr( $subItem[0], array(' ' => '-') ) ) );

						$myCheck .= '<li>';
							$myCheck .= '<i class="fa fa-angle-right"></i><input '.$checkMeSub.' type="checkbox" data-sub-menu="'.$myInpVal1.'" class="check-boxes subCheck" name="subMenu['.trim( strip_tags( str_replace('0', '',$item[2]) ) ).'][]" id="check-'.$mySubInpVal2.'-'.$mySubCount.'" value="'.$mySubInpVal1.'"><label for="check-'.$mySubInpVal2.'-'.$mySubCount.'">'.$mySubInpVal1.'</label>';
						$myCheck .= '</li>';
						$mySubCount++;
					}
					$myCheck .= '</ul>';
				}
				$myCheck .= '</li>';
			}
			
		}
		$myCheck .= '</ul>';
		return $myCheck;
	}

	private function getMessage() {
		return $this->message;
	}

	private function getUserMessage() {
		return $this->userMessage;
	}

	public function display_plugin_admin_page() {
		global $menu, $current_user, $submenu;

		if ( isset($_POST['go']) ) {
			// User has saved a users information
			$this->saveUser(json_encode($_POST['menu']), json_encode($_POST['subMenu']), $_POST['userId'], $_POST['user_message']);
			$this->userMessage = "Details have been saved";
		}

		$userId = ( isset($_GET['id']) ? $_GET['id'] : 0);

		include_once( 'views/admin.php' );
	}

	public function remove_menu_items() {
		global $current_user, $menu, $submenu;

		$myUser = get_users();

		// Let's grab anything from our table for the logged in user
		$results = $this->wpdb->get_results("SELECT * FROM {$this->table_name} WHERE admin_page_user_id = {$current_user->ID} LIMIT 1");

		// We now need to remove the menu items
		if ( count($results) > 0 ) {
			// Get the specific message for this user
			$this->myOops = $results[0]->admin_page_exit_message;
			
			foreach ($menu as $item) {
				// See if the menu item is in the user array, if it is show the menu item
				if ( count( json_decode($results[0]->admin_page_menu) ) > 0 && !in_array(trim( strip_tags( str_replace('0', '', $item[0]) ) ), json_decode($results[0]->admin_page_menu) ) ) {
					remove_menu_page($item[2]);
					add_action( 'load-'.$item[2], array( $this, 'prevent_access') );
					if ( isset($item[5]) ) {
						add_action( 'load-'.$item[5], array( $this, 'prevent_access') );
					}
				}

				// Now let's get to work on our submenus
				if ( $item[0] != '' && count( json_decode($results[0]->admin_page_sub_menu) ) > 0 ) {
					$mySubData = json_decode($results[0]->admin_page_sub_menu);
					
					$myArray = [];
					if ( isset($mySubData->$item[2]) ) {
						foreach ($mySubData->$item[2] as $subItem) {
							$myArray[] = trim( strip_tags( str_replace('0', '', $subItem) ) );
						}
					}

					if ( isset($submenu[$item[2]]) ) {
						// First we need to loop through and pull all relevant info into a 1D array
						$myInfo = json_encode($myArray);
						
						foreach ($submenu[$item[2]] as $subItem) { 

							// See if the sub menu item is in our sub menu saved data, if it is show the menu item
							if ( count($myInfo) > 0 && !in_array(trim( strip_tags( str_replace('0', '', $subItem[0]) ) ), json_decode($myInfo) ) ) {
								remove_submenu_page($item[2], $subItem[2]);
								add_action( 'load-'.$subItem[2], array( $this, 'prevent_access') );
							}
						}
					}
				}
			}
		}
	}

	public function prevent_access() {
		wp_die($this->myOops);
		exit;
	}

	private function saveUser($data = array(), $subMenu = array(), $id = 0, $message = '') {

		if ( $message != '' ) {
			$myColumns 	= 'admin_page_menu, admin_page_sub_menu, admin_page_user_id, admin_page_exit_message';
			$myData 	= "'".$data."',"."'".$subMenu."',"."'".$id."',"."'".$message."'";
			$myUpdate 	= ', admin_page_exit_message = "'.$message.'"';
		} else {
			$myColumns 	= 'admin_page_menu, admin_page_sub_menu, admin_page_user_id';
			$myData 	= "'".$data."',"."'".$subMenu."',"."'".$id."'";
			$myUpdate 	= '';
		}

		if ( $id > 0 ) {
			// Let's go ahead and save the user
			$myUser = $this->wpdb->get_results("INSERT INTO {$this->table_name} ({$myColumns}) VALUES ({$myData}) ON DUPLICATE KEY UPDATE admin_page_menu = '{$data}', admin_page_sub_menu = '{$subMenu}'".$myUpdate);
		}
	}

	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	public function action_method_name() {
		
	}

	public function filter_method_name() {
		
	}

}