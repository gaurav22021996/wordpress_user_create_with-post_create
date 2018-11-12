// custom action to save data of the form

add_role('appointment_user', 'Appointment User', array(
    'read' => true, // True allows that capability
    'edit_posts' => true,
    'delete_posts' => false, // Use false to explicitly deny
));

add_action('init', 'register_form_post_type'); 

function register_form_post_type() {
    $args = array(
        'public'    => true,
        'label'     => __( 'User Appointments', 'textdomain' ),
        'menu_icon' => 'dashicons-book',
    );
    register_post_type( 'user_appointments', $args );
}

/////////////////////////

public function msfp_save_data_to_db() {
		// echo $id;
		// var_dump($data);die;
		// var_dump($_POST);die;

		$responseArray = array();

		// echo json_encode(array("status"=>"0", "message"=>"User already present"));exit();

		$post_id = wp_insert_post(array (
		   'post_type' => 'user_appointments',
		   'post_title' => "User Appointmetn Detail",
		   'post_content' => "",
		   'post_status' => 'publish',
		   'comment_status' => 'closed',   // if you prefer
		   'ping_status' => 'closed',      // if you prefer
		));
		if(isset($_POST['fw_data'], $post_id))
		{
			foreach($_POST['fw_data'] as $sections)
			{
				foreach($sections as $sect)
				{
					foreach($sect as $key=>$val)
					{
						add_post_meta($post_id, $key, $val);
					}
				}
			}
			$meta = get_post_meta($post_id);
			// var_dump($meta);die;
			if(!empty($meta))
			{
				$user_name = str_replace(" ", "", $meta["Full Name"][0]).rand(1000, 9999);

				$user_id = username_exists( $user_name );
				if ( !$user_id and email_exists($meta["Email"][0]) == false ) {
					$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );

					$userdata = array(
					    'user_login'  =>  $user_name,
					    'user_pass'   =>  $random_password,
					    'user_email'  =>  $meta["Email"][0],
					    'display_name' => $meta["Full Name"][0],
					    'role' => 'appointment_user'
					);
					$user_id = wp_insert_user( $userdata ) ;
					add_post_meta($post_id, "appointment_user_id", $user_id);

					$responseArray =array("status"=>"1", "message"=>"appointment success");
				} else {

					foreach($meta as $metaKey=>$metaVal)
					{
						delete_post_meta($post_id, $metaKey, $metaVal);
					}
					wp_delete_post($post_id);
					$responseArray =array("status"=>"0", "message"=>"User already exists with same email.");
					// $random_password = __('User email already exists.  Password inherited.');
				}
			}
			else
			{
				wp_delete_post($post_id);
				$responseArray =array("status"=>"0", "message"=>"Error saving details");
			}
		}
		else
		{
			$responseArray = array("status"=>"0", "message"=>"Please provide details.");
		}

		return $responseArray;
	}
