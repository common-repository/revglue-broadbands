<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function revglue_store_subscription_validate()
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$sanitized_sub_id	= sanitize_text_field( $_POST['sub_id']);
	$sanitized_email	= sanitize_email( $_POST['sub_email']);
	$password			= $_POST['sub_pass'];
	//die(RGSTORE__API_URL . "api/validate_subscription_key/$sanitized_email/$password/$sanitized_sub_id");
	$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGSTORE__API_URL . "api/validate_subscription_key/$sanitized_email/$password/$sanitized_sub_id", array( 'timeout' => 120, 'sslverify'   => false ))), true);
 	$result = $resp_from_server['response']['result'];
 	//pre($result);
 	//die();
 	$project =$result['project'];
 	$iFrameid =$result['iframe_id'];
	$data=array();
	if($iFrameid!=""){
		$data=array(
					'subcription_id' 	=> $sanitized_sub_id,
					'user_name' 		=> $result['user_name'],
					'email' 			=> $result['email'],
					'project' 			=> $result['project'],
					'password'     		=> $password, 
					'expiry_date' 		=> $result['expiry_date'],
					'partner_iframe_id'	=> $result['iframe_id'],  
					'status' 			=> $result['status']
				) ;
	} else{
		$data=array( 
			'subcription_id' 				=> $sanitized_sub_id, 
			'user_name' 					=> $result['user_name'], 
			'email' 						=> $result['email'], 
			'project' 						=> $result['project'],
			'password'     					=> $password, 
			'expiry_date' 					=> $result['expiry_date'],  
			'status' 						=> $result['status']
		) ;
	}
	$string = '';
	if( $resp_from_server['response']['success'] == true )
	{
		$sql = "SELECT * FROM $project_table WHERE project LIKE '".$result['project']."' and status = 'active'";
	    $execute_query = $wpdb->get_results($sql);
	    //pre($execute_query);
	    //die();
		$rows = $wpdb->num_rows;
		if( empty ( $rows ))
		{
			$string .= "<div class='panel-white mgBot'>";
			if($iFrameid!="" )
			{
				$string .= "<p><b>Your RevEmbed Free Broadband data is ".$result['status']." .&nbsp; </b><img  class='tick-icon' src=".RGSTORE__PLUGIN_URL. '/admin/images/tick.png'." >  </p>";
				$string .= "<p><b>Name = </b> RevEmbed Data </p>";
				$string .= "<p><b>Project = </b>".$result['project']." UK </p>";
				$string .= "<p><b>Email = </b>".$result['email']."</p>";
			}
			else{
				if($project == " Banners UK" ){
					$string .= "<p><b>Your Broadband subscription is ".$result['status']." .&nbsp; </b><img  class='tick-icon' src=".RGSTORE__PLUGIN_URL. '/admin/images/tick.png'." >  </p>";
				}
				else{
					$string .= "<p><b>Your Broadband subscription is ".$result['status']." .&nbsp; </b><img  class='tick-icon' src=".RGSTORE__PLUGIN_URL. '/admin/images/tick.png'." >  </p>";
				}
			$string .= "<p><b>Name = </b>".$result['user_name']."</p>";
			$string .= "<p><b>Project = </b>".$result['project']."</p>";
			$string .= "<p><b>Email = </b>".$result['email']."</p>";
			$string .= "<p><b>Expiry Date = </b>".date('d-M-Y', strtotime($result['expiry_date']))."</p>";
			$string .= "</div>";
			}
			$wpdb->insert(
				$project_table,
				$data
			);
		} else
		{
			$string .= "<div style='color: green;'>You already have subscription of this project, thankyou! </div>";
		}
	} else
	{
		$string .= "<p>&raquo; Your subscription unique ID <b class='grmsg'> ". $sanitized_sub_id ." </b> is Invalid.</p>";
	}
	echo $string;
	wp_die();
}
add_action( 'wp_ajax_revglue_store_subscription_validate', 'revglue_store_subscription_validate' );
function revglue_store_data_import()
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$stores_table = $wpdb->prefix.'rg_stores';
	$categories_table = $wpdb->prefix.'rg_categories';
	$broadband_table = $wpdb->prefix.'rg_broadband';
	$broadband_category_table = $wpdb->prefix.'rg_broadband_category';
	$string = '';
	$date = date("Y-m-d H:i:s");
	$import_type = sanitize_text_field( @$_POST['import_type']);
	$sql = "SELECT * FROM $project_table WHERE project NOT LIKE 'banners uk'"  ;
	$project_detail = $wpdb->get_results($sql);
	$rows = $wpdb->num_rows;
	if( !empty ( $rows ))
	{
		$subscriptionid 	= 	$project_detail[0]->subcription_id;
		$useremail 			= 	$project_detail[0]->email;
		$userpassword 		= 	$project_detail[0]->password;
		$projectid 			= 	$project_detail[0]->partner_iframe_id;
		if( $import_type == 'rg_stores_import' || !isset($_POST['import_type']))
		{
			revglue_broad_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);
			if($project_detail[0]->expiry_date=="Free" && $project_detail[0]->partner_iframe_id!="" )
			{
				$partner_broadband_stores_url ="https://www.revglue.com/partner/broadband_stores/".$project_detail[0]->partner_iframe_id."/json/wp";
				//die($partner_broadband_stores_url);
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $partner_broadband_stores_url, array( 'timeout' => 12000, 'sslverify'   => false ) ) ), true);
			} else{
			$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGSTORE__API_URL . "api/broadband_stores/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true);
		}
		//pre($resp_from_server);
		//die;
			$result = $resp_from_server['response']['stores'];
			$success = $resp_from_server['response']['success'] == true;
	  		if( $success = true )
			{
				foreach($result as $row)
				{
					$sqlinstore = "SELECT rg_store_id FROM $stores_table WHERE rg_store_id = '".$row['rg_store_id']."'";
					$rg_store_exists = $wpdb->get_var( $sqlinstore );
					if( empty( $rg_store_exists ))
					{
						$wpdb->insert(
							$stores_table,
							array(
							'rg_store_id' 					=> $row['rg_store_id'],
							'mid' 							=> $row['affiliate_network_mid'],
							'title'							=> $row['store_title'],
							'url_key' 						=> $row['url_key'],
							'description'					=> $row['store_description'],
							'image_url' 					=> $row['image_url'],
							'affiliate_network' 			=> $row['affiliate_network'],
							'affiliate_network_link'		=> $row['affiliate_network_link'],
							'store_base_currency' 			=> $row['store_base_currency'],
							'store_base_country' 			=> $row['store_base_country'],
							'category_ids'					=> $row['store_category_ids'],
							'date'							=> $date
							)
						);
					} else
					{
						$wpdb->update(
							$stores_table,
							array(
							'rg_store_id' 					=> $row['rg_store_id'],
							'mid' 							=> $row['affiliate_network_mid'],
							'title'							=> $row['store_title'],
							'url_key' 						=> $row['url_key'],
							'description'					=> $row['store_description'],
							'image_url' 					=> $row['image_url'],
							'affiliate_network' 			=> $row['affiliate_network'],
							'affiliate_network_link'		=> $row['affiliate_network_link'],
							'store_base_currency' 			=> $row['store_base_currency'],
							'store_base_country' 			=> $row['store_base_country'],
							'category_ids'					=> @$row['store_category_ids'],
							'date'							=> $date
							),
							array( 'rg_store_id' => $rg_store_exists )
						);
					}	
				}
				$wpdb->query( "DELETE FROM $stores_table WHERE `date` != '$date' " );
				$sql = "SELECT rg_store_id FROM $stores_table WHERE  homepage_store_tag ='no' LIMIT 10";
						$storeIDs = $wpdb->get_results( $sql );
							foreach ($storeIDs as $sID) {
								$wpdb->update(
										$stores_table,
										array(
											'homepage_store_tag' 	=> 'yes'
										),
										array( 'rg_store_id' => $sID->rg_store_id )
									);
								}
				 $wpdb->query(
					"
					INSERT INTO
					$broadband_category_table
					(`title`,`status`)
					VALUES
					('Broadband only','Active'),
					('Broadband & Phone','Active'),
					('Broadband, TV & Phone','Active'),
					('On Demand','Active'),
					('Mobile Broadband','Active'),
					('Business Broadband','Active')"
				 );
			} else
			{
				$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
			}
		}
		if( $import_type == 'rg_broadbands_import' || !isset($_POST['import_type'])  )
		{
			revglue_broad_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);
			// echo "Broadband is  working";
			$date = date("Y-m-d H:s:i");
			$sql = "SELECT * FROM $project_table WHERE project NOT LIKE 'banners uk'";
			$project_detail_1 = $wpdb->get_results($sql);
			if($project_detail_1[0]->expiry_date=="Free" && $project_detail_1[0]->partner_iframe_id!="" )
				{
				$partner_broadband_stores_url ="https://www.revglue.com/partner/broadband/".$project_detail_1[0]->partner_iframe_id."/json/wp";
				//die($partner_broadband_stores_url);
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $partner_broadband_stores_url, array( 'timeout' => 12000, 'sslverify'   => false ) ) ), true);
			} else{
			$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGSTORE__API_URL."api/broadbands/json/".$project_detail_1[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true);
		}
			$resultBroadband = $resp_from_server['response']['broadbands'];
			$success =$resp_from_server['response']['success'];
			if($success == true )
			{
				foreach($resultBroadband as $row)
				{	
					// pre($row);
					$sqlinbroadband = "SELECT broadband_id FROM $broadband_table WHERE broadband_id = '".$row['broadband_id']."'";
					$rg_broadband_exists = $wpdb->get_var( $sqlinbroadband );
					$is_store_exist = "SELECT count(rg_store_id) as total FROM $stores_table WHERE rg_store_id = '".$row['rg_store_id']."'";
					$store_exists = $wpdb->get_var( $is_store_exist );
					$rg_broadband_exists = $wpdb->get_var( $sqlinbroadband );
					if( empty( $rg_broadband_exists ) && $store_exists > 0 )
					{					
						$wpdb->insert(
							$broadband_table,
							array( 
						'broadband_id' 					=> $row['broadband_id'],
						'broadband_title' 				=> $row['broadband_title'],
						'rg_store_id' 					=> $row['rg_store_id'],
						'broadband_category_id'			=> $row['broadband_category_id'],
						'services' 			   			=> $row['services'],
						'deal_type' 					=> $row['deal_type'],
						'speed' 						=> $row['speed'],
						'speed_for_filters' 			=> $row['speed'],
						'download_limit' 				=> $row['download_limit'],
						'setup_cost' 					=> $row['setup_cost'],
						'cost_per_month' 				=> $row['cost_per_month'],
						'cost_per_month_for_filter' 	=> $row['cost_per_month'],
						'no_of_contract_month' 			=> $row['no_of_contract_month'],
						'total_contract_cost' 			=> $row['total_contract_cost'],
						'first_month_cost' 				=> $row['first_month_cost'],
						'cost_after_first_month' 		=> $row['cost_after_first_month'],
						'cost_after_x_month' 			=> $row['cost_after_x_month'],
						'standard_cost' 				=> $row['standard_cost'],
						'upfront_cost' 					=> $row['upfront_cost'],
						'router' 						=> $row['router'],
						'router_detail' 				=> $row['router_detail'],
						'router_price' 					=> $row['router_price'],
						'online_discount' 				=> $row['online_discount'],
						'phone_line' 					=> $row['phone_line'],
						'line_rental' 					=> $row['line_rental'],
						'promotion' 					=> $row['promotion'],
						'promotion_detail' 				=> $row['promotion_detail'],
						'promotion_issue_date' 			=> $row['promotion_issue_date'],
						'promotion_expiry_date' 		=> $row['promotion_expiry_date'],
						'cashback' 						=> $row['cashback'],
						'delivery_charges' 				=> $row['delivery_charges'],
						'tech_support' 					=> $row['tech_support'],
						'tech_telephone' 				=> $row['tech_telephone'],
						'broadband_type' 				=> $row['broadband_type'],
						'tv' 							=> $row['tv'],
						'movies' 						=> $row['movies'],
						'sports_channel' 				=> $row['sports_channel'],
						'tv_quality' 					=> $row['tv_quality'],
						'deeplink' 						=> $row['deeplink'],
						'issue_date' 					=> $row['issue_date'],
						'expiry_date' 					=> $row['expiry_date'],
						'date' 							=> $date
							)
						);
					} else
					{
						$wpdb->update(
							$broadband_table,
							array(
						'broadband_title' 				=> $row['broadband_title'],
						'rg_store_id' 					=> $row['rg_store_id'],
						'broadband_category_id'			=> $row['broadband_category_id'],
						'services' 			   			=> $row['services'],
						'deal_type' 					=> $row['deal_type'],
						'speed_for_filters' 			=> $row['speed'],
						'speed' 						=> $row['speed'],
						'download_limit' 				=> $row['download_limit'],
						'setup_cost' 					=> $row['setup_cost'],
						'cost_per_month_for_filter' 	=> $row['cost_per_month'],
						'cost_per_month' 				=> $row['cost_per_month'],
						'no_of_contract_month' 			=> $row['no_of_contract_month'],
						'total_contract_cost' 			=> $row['total_contract_cost'],
						'first_month_cost' 				=> $row['first_month_cost'],
						'cost_after_first_month' 		=> $row['cost_after_first_month'],
						'cost_after_x_month' 			=> $row['cost_after_x_month'],
						'standard_cost' 				=> $row['standard_cost'],
						'upfront_cost' 					=> $row['upfront_cost'],
						'router' 						=> $row['router'],
						'router_detail' 				=> $row['router_detail'],
						'router_price' 					=> $row['router_price'],
						'online_discount' 				=> $row['online_discount'],
						'phone_line' 					=> $row['phone_line'],
						'line_rental' 					=> $row['line_rental'],
						'promotion' 					=> $row['promotion'],
						'promotion_detail' 				=> $row['promotion_detail'],
						'promotion_issue_date' 			=> $row['promotion_issue_date'],
						'promotion_expiry_date' 		=> $row['promotion_expiry_date'],
						'cashback' 						=> $row['cashback'],
						'delivery_charges' 				=> $row['delivery_charges'],
						'tech_support' 					=> $row['tech_support'],
						'tech_telephone' 				=> $row['tech_telephone'],
						'broadband_type' 				=> $row['broadband_type'],
						'tv' 							=> $row['tv'],
						'movies' 						=> $row['movies'],
						'sports_channel' 				=> $row['sports_channel'],
						'tv_quality' 					=> $row['tv_quality'],
						'deeplink' 						=> $row['deeplink'],
						'issue_date' 					=> $row['issue_date'],
						'expiry_date' 					=> $row['expiry_date'],
						'date' 							=> $date
							),
							array( 'broadband_id' => $rg_broadband_exists )
						);
					}
				}
				 $wpdb->query( " DELETE FROM $broadband_table WHERE `date` != '$date' " );
			} else
			{
				$string .= '<p style="color:red">Broadband API is not SET.</p>';
			}
	} /*else
	{
		$string .= "<p style='color:red'>Please subscribe for your RevGlue project first, then you have the facility to import the data";
	}*/
	$response_array = array();
	$response_array['error_msgs'] = $string;
	$response_array["Current Date"] = $date;
	$sql = "SELECT MAX(date) FROM $stores_table";
	$last_updated_store = $wpdb->get_var($sql);
	$response_array['last_updated_store'] = ( $last_updated_store ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_store ) ) : '-' );
	$sql_3 = "SELECT count(*) as Broadbands FROM $stores_table";
	$count_store = $wpdb->get_results($sql_3);
	$response_array['count_store'] = $count_store[0]->Broadbands;
	$sql_4 = "SELECT MAX(date) FROM $broadband_table";
	$last_updated_broadband = $wpdb->get_var($sql_4);
	$response_array['last_updated_broadband'] = ( $last_updated_broadband ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_broadband ) ) : '-' );
	$sql_5 = "SELECT count(*) as broadbandcount FROM $broadband_table";
	$broadbandcount = $wpdb->get_results($sql_5);
	$response_array['broadbandcount'] = $broadbandcount[0]->broadbandcount;
	if(!isset($_POST['import_type']) ){
		return json_encode($response_array);
	}else{
		echo json_encode($response_array);
	}
	wp_die();
}
}
add_action( 'wp_ajax_revglue_store_data_import', 'revglue_store_data_import' );
function revglue_banner_data_import()
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$banner_table = $wpdb->prefix.'rg_banner';
	$date = date("Y-m-d H:i:s");
	$string = '';
	$import_type = sanitize_text_field( $_POST['import_type'] );
	$sql = "SELECT *FROM $project_table WHERE project LIKE 'Banners UK'";
	$project_detail = $wpdb->get_results($sql);
	$rows = $wpdb->num_rows;
	if( !empty ( $rows ) )
	{
		if( $import_type == 'rg_banners_import' )
		{
			$i = 0;
			$paging = 1;
			do {
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGSTORE__API_URL . "api/banners/json/".$project_detail[0]->subcription_id."/".$paging, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
				$total = ceil( $resp_from_server['response']['banners_total'] / 1000 ) ;
				$result = $resp_from_server['response']['banners'];
				if($resp_from_server['response']['success'] == 'true' )
				{
					foreach($result as $row) {
						$sqlinstore = "Select rg_store_banner_id FROM $banner_table Where rg_store_banner_id = '".$row['rg_banner_id']."' AND `banner_type` = 'imported'";
						$rg_banner_exists = $wpdb->get_var( $sqlinstore );
						if( empty( $rg_banner_exists ) )
						{
							$wpdb->insert( 
								$banner_table, 
								array( 
									'rg_store_banner_id' 	=> $row['rg_banner_id'], 
									'rg_store_id' 			=> $row['rg_store_id'], 
									'rg_store_name' 		=> $row['banner_alt_text'], 
									'title' 				=> $row['banner_alt_text'], 
									'image_url' 			=> $row['banner_image_url'], 
									'url' 			        => $row['deep_link'], 
									'placement' 			=> 'unassigned', 
									'rg_size' 			    => $row['width_pixels'].'x'.$row['height_pixels'], 
									'banner_type' 			=> 'imported',
									'date' 					=> $date
								) 
							);
						} else {
							$wpdb->update( 
								$banner_table, 
								array( 
									'rg_store_id' 			=> $row['rg_store_id'], 
									'date' 			        => $date,
									'rg_store_name' 		=> $row['banner_alt_text'], 
									'title' 				=> $row['banner_alt_text'], 
									'date' 					=> $date,
									'image_url' 			=> $row['banner_image_url'], 
									'url' 			        => $row['deep_link'], 
									'placement' 			=> 'unassigned', 
									'rg_size' 			    => $row['width_pixels'].'x'.$row['height_pixels'], 	
								),
								array( 'rg_store_banner_id' => $rg_banner_exists )
							);
						}													
					}
				} else 
				{
					$string .= '<div class="alert alert-danger" role="alert">Records were not added.</div>';
				}
				$i++;
				$paging++;
			} while ( $i < $total ); 
			$wpdb->query( "DELETE FROM $banner_table WHERE `date` != '$date' " );
		}
	} else 
	{
		$string .= "<div class='alert alert-danger' role='alert'>Please subscribe for your RevGlue project first, then you have the facility to import the data</div>";
	}
	$response_array = array();
	$response_array['error_msgs'] = $string;
	$sql1 = "SELECT count(*) as banner FROM $banner_table WHERE DATE(date) = CURDATE()";
	$count_banner = $wpdb->get_results($sql1);
	$response_array['count_banner'] = $count_banner[0]->banner;
	$sql_1 = "SELECT MAX(date) FROM $banner_table";
	$last_updated_banners = $wpdb->get_var($sql_1);
	$response_array['last_updated_banner'] = ( $last_updated_banners ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_banners ) ) : '-' );
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_banner_data_import', 'revglue_banner_data_import' );
function revglue_store_data_delete()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$categories_table = $wpdb->prefix.'rg_categories';
	$broadband_table = $wpdb->prefix.'rg_broadband';
	$banner_table = $wpdb->prefix.'rg_banner';
	$data_type = sanitize_text_field( $_POST['data_type'] );
	$response_array = array();
	if( $data_type == 'rg_stores_delete' )
	{
		$response_array['data_type'] = 'rg_stores';
		$wpdb->query( "DELETE FROM $stores_table" );	
		$sql = "SELECT MAX(date) FROM $stores_table";
		$last_updated_store = $wpdb->get_var($sql);
		$response_array['last_updated_store'] = ( $last_updated_store ? date( 'l jS \of F Y h:i:s A', strtotime( $last_updated_store ) ) : '-' );
		$sql2 = "SELECT count(*) as stores FROM $stores_table";
		$count_store = $wpdb->get_results($sql2);
		$response_array['count_store'] = $count_store[0]->stores;
	} else if( $data_type == 'rg_categories_delete' )
	{
		$response_array['data_type'] = 'rg_categories';
		$wpdb->query( "DELETE FROM $categories_table" );	
		$sql = "SELECT MAX(date) FROM $categories_table";
		$last_updated_category = $wpdb->get_var($sql);
		$response_array['last_updated_category'] = ( $last_updated_category ? date( 'l jS \of F Y h:i:s A', strtotime( $last_updated_category ) ) : '-' );
		$sql2 = "SELECT count(*) as categories FROM $categories_table";
		$count_category = $wpdb->get_results($sql2);
		$response_array['count_category'] = $count_category[0]->categories;
	} else if( $data_type == 'rg_banners_delete' )
	{
		$response_array['data_type'] = 'rg_banners';
		$wpdb->query( "DELETE FROM $banner_table where banner_type='imported'" );	
		$sql1 = "SELECT count(*) as banner FROM $banner_table where banner_type= 'imported'";
		$count_banner = $wpdb->get_results($sql1);
		$response_array['count_banner'] = $count_banner[0]->banner;
	}else if( $data_type == 'rg_broadbands_delete' ) 
	{
		  $response_array['data_type'] = 'rg_broadbands';
		$wpdb->query( "DELETE FROM $broadband_table" );	
		$sql3 = "SELECT count(*) as broadband FROM $broadband_table";
		$count_broadband = $wpdb->get_results($sql3);
		$response_array['count_broadband'] = $count_broadband[0]->broadband ; 
	}
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_store_data_delete', 'revglue_store_data_delete' );
function revglue_store_update_home_store()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_stores';
	$store_id		= absint( $_POST['store_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'homepage_store_tag' => $cat_state ), 
		array( 'rg_store_id' => $store_id )
	);
	wp_die();
}
add_action( 'wp_ajax_revglue_store_update_home_store', 'revglue_store_update_home_store' );
function revglue_store_update_popular_store()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_stores';
	$store_id		= absint( $_POST['store_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'popular_store_tag' => $cat_state ), 
		array( 'rg_store_id' => $store_id )
	);
	wp_die();
}
add_action( 'wp_ajax_revglue_store_update_popular_store', 'revglue_store_update_popular_store' );
function revglue_broadband_update_popular_tag()
{
	global $wpdb; 
	$broadband_table = $wpdb->prefix.'rg_broadband'; 
	$bb_state 	    = sanitize_text_field( $_POST['state'] );
	$broadband_id 	= absint( $_POST['broadband_id'] );
	$wpdb->update( 
		$broadband_table, 
		array( 'homepage_broadband_tag' => $bb_state ), 
		array( 'broadband_id' => $broadband_id )
	);
	wp_die();
}
add_action( 'wp_ajax_revglue_broadband_update_popular_tag', 'revglue_broadband_update_popular_tag' );
function revglue_store_update_header_category()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'header_category_tag' => $cat_state ), 
		array( 'rg_category_id' => $cat_id )
	);
	wp_die();
}
add_action( 'wp_ajax_revglue_store_update_header_category', 'revglue_store_update_header_category' );
function revglue_store_update_popular_category()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'popular_category_tag' => $cat_state ), 
		array( 'rg_category_id' => $cat_id )
	);
	wp_die();
}
add_action( 'wp_ajax_revglue_store_update_popular_category', 'revglue_store_update_popular_category' );
function revglue_store_update_category_icon()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$icon_url 	= esc_url_raw( $_POST['icon_url'] );
	$wpdb->update( 
		$categories_table, 
		array( 'icon_url' => $icon_url ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_store_update_category_icon', 'revglue_store_update_category_icon' );
function revglue_store_delete_category_icon()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$wpdb->update( 
		$categories_table, 
		array( 'icon_url' => '' ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_store_delete_category_icon', 'revglue_store_delete_category_icon' );
function revglue_store_update_category_image()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$image_url 	= esc_url_raw( $_POST['image_url'] );
	$wpdb->update( 
		$categories_table, 
		array( 'image_url' => $image_url ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_store_update_category_image', 'revglue_store_update_category_image' );
function revglue_store_delete_category_image()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$wpdb->update( 
		$categories_table, 
		array( 'image_url' => '' ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_store_delete_category_image', 'revglue_store_delete_category_image' );
function revglue_bb_query_string_bb_popup()
{
	global $wpdb;
	$broadband_table = $wpdb->prefix.'rg_broadband';
	$stores_table = $wpdb->prefix.'rg_stores';
	$storeid		= absint( $_POST['storeid'] );
	$broadbandid		= absint( $_POST['broadbandid'] );
	$bb_data_array = array();
	$sql = "SELECT * FROM $broadband_table where rg_store_id = $storeid AND broadband_id = $broadbandid";
	$broadbands = $wpdb->get_results($sql); 
		$sqls = "SELECT rg_store_id, image_url, title FROM $stores_table group by rg_store_id ";
		$storeData = $wpdb->get_results($sqls);
		$storearray = array();
		foreach($storeData as $row){
				$storearray[$row->rg_store_id]['image_url'] = $row->image_url;
				$storearray[$row->rg_store_id]['title'] = $row->title;
		} 
		foreach ($broadbands as $broadband) {
			$broadband_id 				=	$broadband->broadband_id;  
			$broadband_category_id 	 	=	$broadband->broadband_category_id 	; 
			$broadband_title 			=	$broadband->broadband_title; 
			$services 					=	$broadband->services; 
			$deal_type 					=	$broadband->deal_type; 
			$download_limit 			=	$broadband->download_limit; 
			$speed 						=	$broadband->speed_for_filters; 
			$setup_cost 				=	$broadband->setup_cost; 
			$cost_per_month 			=	$broadband->cost_per_month_for_filter; 
			$no_of_contract_month 		=	$broadband->no_of_contract_month; 
			$total_contract_cost 		=	$broadband->total_contract_cost; 
			$first_month_cost 			=	$broadband->first_month_cost; 
			$cost_after_first_month 	=	$broadband->cost_after_first_month; 
			$cost_after_x_month 		=	$broadband->cost_after_x_month; 
			$standard_cost 				=	$broadband->standard_cost; 
			$upfront_cost 				=	$broadband->upfront_cost; 
			$router 					=	$broadband->router; 
			$router_detail 				=	$broadband->router_detail; 
			$router_price 				=	$broadband->router_price; 
			$online_discount 			=	$broadband->online_discount; 
			$phone_line 				=	$broadband->phone_line; 
			$line_rental 				=	$broadband->line_rental; 
			$promotion 					=	$broadband->promotion; 
			$promotion_detail 			=	$broadband->promotion_detail; 
			$promotion_issue_date 		=	$broadband->promotion_issue_date; 
			$promotion_expiry_date 		=	$broadband->promotion_expiry_date; 
			$cashback 					=	$broadband->cashback; 
			$delivery_charges 			=	$broadband->delivery_charges; 
			$tech_support 				=	$broadband->tech_support; 
			$tech_telephone 			=	$broadband->tech_telephone; 
			$broadband_type 			=	$broadband->broadband_type; 
			$tv 						=	$broadband->tv; 
			$movies 					=	$broadband->movies; 
			$sports_channel 			=	$broadband->sports_channel; 
			$tv_quality 				=	$broadband->tv_quality; 
			$deeplink 					=	$broadband->deeplink; 
			$issue_date 				=	$broadband->issue_date; 
			$expiry_date 				=	$broadband->expiry_date; 
		}
		$bb_data_array['store_title'] 				= $storearray[$storeid]['title'] ; 
		$bb_data_array['storeimg'] 					=  $storearray[$storeid]['image_url'] ;
		$bb_data_array['storeid'] 					=  $storeid;
		$bb_data_array['broadband_id'] 				= $broadband_id;
		$bb_data_array['broadband_category_id'] 	= $broadband_category_id;
		$bb_data_array['broadband_title'] 			= $broadband_title;
		$bb_data_array['services'] 					= $services;
		$bb_data_array['deal_type'] 				= $deal_type; 
		$bb_data_array['download_limit'] 			= $download_limit;
		$bb_data_array['speed'] 					=	$speed." MB" ; 
		$bb_data_array['setup_cost'] 				=	"£".$setup_cost ;  
		$bb_data_array['cost_per_month'] 			=	"£".$cost_per_month ;  
		$bb_data_array['no_of_contract_month'] 		=	$no_of_contract_month;  
		$bb_data_array['total_contract_cost'] 		=	"£".$total_contract_cost;  
		$bb_data_array['first_month_cost'] 			=	"£".$first_month_cost;  
		$bb_data_array['cost_after_first_month'] 	=	"£".$cost_after_first_month;  
		$bb_data_array['cost_after_x_month'] 		=	"£".$cost_after_x_month;  
		$bb_data_array['standard_cost'] 			=	"£".$standard_cost;  
		$bb_data_array['upfront_cost'] 				=	"£".$upfront_cost;  
		$bb_data_array['router'] 					=	$router;  
		$bb_data_array['router_detail'] 			=	$router_detail;  
		$bb_data_array['router_price'] 				=	"£".$router_price;  
		$bb_data_array['online_discount'] 			=	"£".$online_discount;  
		$bb_data_array['phone_line'] 				=	$phone_line;  
		$bb_data_array['line_rental'] 				=	$line_rental;  
		$bb_data_array['promotion'] 				=	$promotion;  
		$bb_data_array['promotion_detail'] 			=	$promotion_detail;  
		$bb_data_array['promotion_issue_date'] 		=	$promotion_issue_date;  
		$bb_data_array['promotion_expiry_date'] 	=	$promotion_expiry_date;  
		$bb_data_array['cashback'] 					=	$cashback;  
		$bb_data_array['delivery_charges'] 			=   $delivery_charges!="Free" ? "£".$delivery_charges :	$delivery_charges;  
		$bb_data_array['tech_support'] 				=	$tech_support;  
		$bb_data_array['tech_telephone'] 			=	$tech_telephone;  
		$bb_data_array['broadband_type'] 			=	$broadband_type;  
		$bb_data_array['tv'] 						=	$tv;  
		$bb_data_array['movies'] 					=	$movies;  
		$bb_data_array['sports_channel'] 			=	$sports_channel;  
		$bb_data_array['tv_quality'] 				=	$tv_quality;  
		$bb_data_array['deeplink'] 					=	$deeplink;  
		$bb_data_array['issue_date'] 				=	$issue_date;  
		$bb_data_array['expiry_date'] 				=	$expiry_date;  
		echo json_encode( $bb_data_array );
		wp_die();
}
add_action( 'wp_ajax_revglue_bb_query_string_bb_popup', 'revglue_bb_query_string_bb_popup' );
function revglue_store_load_stores()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$sTable = $wpdb->prefix.'rg_stores';
	$aColumns = array( 'rg_store_id', 'affiliate_network', 'mid', 'image_url', 'title', 'store_base_country', 'affiliate_network_link', 'homepage_store_tag', 'popular_store_tag' ); 
	$sIndexColumn = "rg_store_id"; 
	$sLimit = "LIMIT 1, 50"; 
	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length']) != '-1' )
	
	{
		$sLimit = "LIMIT ".intval(sanitize_text_field($_REQUEST['start'])).", ".intval(sanitize_text_field($_REQUEST['length']));
	}
	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']);
			$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {

			$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}
		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	}
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."` FROM   $sTable $where $sOrder $sLimit";
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	$sQuery = "SELECT FOUND_ROWS()";
	$rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( sanitize_text_field($_REQUEST['draw']) ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	foreach($rResult as $aRow)
	{
		//pre($rResult);
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if( $i == 3 )
			{
				$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="' . RGSTORE__PLUGIN_URL . '/admin/images/loading.gif" data-src="' . esc_url( $aRow[ $aColumns[$i] ] ) . '" /></div>';
			} else if( $i == 6 )
			{
				$row[] = '<a class="" title="'.esc_url( $aRow[ $aColumns[$i] ] ).'" id="'. esc_html( $aRow[ $aColumns[0] ] )  .'"  href="'. esc_url( $aRow[ $aColumns[$i] ] ).'" target="_blank"><img src="'. RGSTORE__PLUGIN_URL .'/admin/images/linkicon.png" style="width:50px;"/><div id="imp_popup'. esc_html( $aRow[ $aColumns[0] ] ).'" style="background: #ececec; left: 60px; margin: 5px 0; padding: 10px; position: absolute; top: 10px; display:none; border-radius: 8px; border: 1px solid #ccc">'. ( $aRow[ $aColumns[$i] ] ? esc_url( $aRow[ $aColumns[$i] ] ) : 'No Link' ) .'</div></a>';
			} else if( $i == 7 )
			{
				if( $aRow[ $aColumns[$i] ] == 'yes' )
				{
					$checked = 'checked="checked"';
				} else
				{
					$checked = '';
				}
				$row[] = '<input '.$checked.' type="checkbox" id="'.$aRow[ $aColumns[0] ].'" class="rg_store_homepage_tag" />';
			} else if( $i == 8 )
			{
				if( $aRow[ $aColumns[$i] ] == 'yes' )
				{
					$checked = 'checked="checked"';
				} else
				{
					$checked = '';
				}
				$row[] = '<input '.$checked.' type="checkbox" id="'.$aRow[ $aColumns[0] ].'" class="rg_store_popular_tag" />';
			}
			else if ( $aColumns[$i] != ' ' )
			{    
				$row[] = $aRow[ $aColumns[$i] ];
			}
		}
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die(); 
}
add_action( 'wp_ajax_revglue_store_load_stores', 'revglue_store_load_stores' );
function revglue_store_load_banners()
{
	global $wpdb; 
	$stores_table = $wpdb->prefix.'rg_stores';
	$sTable = $wpdb->prefix.'rg_banner';
	$upload = wp_upload_dir();
	$base_url = $upload['baseurl'];
	$uploadurl = $base_url.'/revglue/broadband/banners/';
	$placements = array(
		'home-top'				=> 'Home:: Top Header',
		'home-slider'			=> 'Home:: Main Banners',
		'home-mid'				=> 'Home:: After Categories',
		'home-after-slider'		=> 'Home:: After Home Page Slider',
		'home-bottom'			=> 'Home:: Before Footer',  
		'detail-page-banner'	=> 'Store Detail:: After Menu Banner',
		'category-after-menu'	=> 'Category Page :: After Menu Banner',
		'unassigned' 			=> 'Unassigned Banners'
	);
	$aColumns = array( 'banner_type', 'placement', 'status', 'title', 'url', 'image_url', 'rg_store_id', 'rg_id', 'rg_store_banner_id', 'rg_store_name', 'rg_size'  ); 
	$sIndexColumn = "rg_store_id"; 
	$sLimit = "LIMIT 1, 50";

	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length']) != '-1' )
	
	{
		$sLimit = "LIMIT ".intval(sanitize_text_field($_REQUEST['start'])).", ".intval(sanitize_text_field($_REQUEST['length']));
	}

	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']);

		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}

		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {

			$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}
		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			//$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	}
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."` FROM   $sTable $where $sOrder $sLimit";
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	$sQuery = "SELECT FOUND_ROWS()";
	$rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( sanitize_text_field($_REQUEST['draw']) ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	foreach($rResult as $aRow)
	{
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if( $i == 0 )
			{
				if( $aRow[ $aColumns[5] ] == '' )
				{
					$uploadedbanner = $uploadurl . $aRow[ $aColumns[3] ];
					$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGSTORE__PLUGIN_URL .'/admin/images/loading.gif" data-src="'. esc_url( $uploadedbanner ) .'"/></div>';
				} else
				{
					$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGSTORE__PLUGIN_URL .'/admin/images/loading.gif" data-src="'. esc_url( $aRow[ $aColumns[5] ] ) .'" /></div>';
				}
			}else if( $i == 1 )
			{
				$row[] = $aRow[ $aColumns[8] ];
			} else if( $i == 2 )
			{
				$row[] = $aRow[ $aColumns[9] ];
			} else if( $i == 3 )
			{
				$row[] = ( $aRow[ $aColumns[0] ] == 'local' ? 'Local' : 'RevGlue Banner' );
			} else if( $i == 4 )
			{
				$row[] = $placements[$aRow[ $aColumns[1]]];
			} else if( $i == 5 )
			{
				$row[] = $aRow[ $aColumns[10]];
			} else if( $i == 6 )
			{
				if( ! empty( $aRow[ $aColumns[4]] ) )
				{
					$url_to_show = esc_url( $aRow[ $aColumns[4]] ); 
				} else if( ! empty( $aRow[ $aColumns[6]] ) )
				{
					$sql_1 = "SELECT affiliate_network_link FROM $stores_table where rg_store_id = ".$aRow[ $aColumns[6]];
					$deep_link = $wpdb->get_results($sql_1);
					$url_to_show = ( !empty( $deep_link[0]->affiliate_network_link ) ? esc_url( $deep_link[0]->affiliate_network_link ) : 'No Link'  );
				} else
				{
					$url_to_show = 'No Link';
				}
				$row[] = '<a class="" id="'. $aRow[ $aColumns[7]] .'" title="'. $url_to_show .'" href="'. $url_to_show .'" target="_blank"><img src="'. RGSTORE__PLUGIN_URL .'/admin/images/linkicon.png" style="width:50px;"/><div id="imp_popup'. $aRow[ $aColumns[7]] .'" style="background: #ececec; left: 60px; margin: 5px 0; padding: 10px; position: absolute; top: 10px; display:none; border-radius: 8px; border: 1px solid #ccc">'.$url_to_show.'</div></a>';
			} else if( $i == 7 )
			{
				$row[] = $aRow[ $aColumns[2]];
			} else if( $i == 8 )
			{
				$row[] = '<a href="'. admin_url( 'admin.php?page=revglue-banners&action=edit&banner_id='.$aRow[ $aColumns[7]] ) .'">Edit</a>';
			} else if ( $aColumns[$i] != ' ' )
			{    
				$row[] = $aRow[ $aColumns[$i] ];
			}
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die(); 
}
add_action( 'wp_ajax_revglue_store_load_banners', 'revglue_store_load_banners' );
function revglue_bb_query_storeimg()
{
	global $wpdb; 
	$stores_table 		= $wpdb->prefix.'rg_stores';
	$storeid			= absint( $_POST['streid'] );  
	$sqls = "SELECT * FROM $stores_table WHERE rg_store_id = $storeid ";
	//echo $sqls;
	$storeData = $wpdb->get_results($sqls);
		$strArray = array();
		foreach ($storeData  as $row ) {
			$strArray['storeimg'] = $row->image_url;
			$strArray['storeid'] = $row->rg_store_id;
			$strArray['store_title'] = $row->title;
		} 
	echo json_encode($strArray); 
	wp_die();
}
add_action( 'wp_ajax_revglue_bb_query_storeimg', 'revglue_bb_query_storeimg' );
//add_action( 'wp_ajax_nopriv_revglue_bb_query_storeimg', 'revglue_bb_query_storeimg' );
function revglue_broad_update_subscription_expiry_date($purchasekey, $userpassword, $useremail, $projectid){
 global $wpdb;
 $projects_table = $wpdb->prefix.'rg_projects';
 $apiurl = RGSTORE__API_URL."api/validate_subscription_key/$useremail/$userpassword/$purchasekey";
 // die($apiurl);
 $resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $apiurl , array( 'timeout' => 120, 'sslverify' => false ))), true);
 // pre($resp_from_server);
 // die;
 $expiry_date = $resp_from_server['response']['result']['expiry_date'];
 if ( empty($projectid)){
  $sql ="UPDATE $projects_table SET `expiry_date` = '$expiry_date' WHERE `subcription_id` ='$purchasekey'";
  // echo $sql;
  // die;
  $wpdb->query($sql);
 } 
}