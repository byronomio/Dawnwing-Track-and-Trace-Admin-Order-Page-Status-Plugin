<?php

/**
 * @link			  https://www.semantica.co.za/
 * @since			  1.0.0
 * @package			  Semantica Core
 *
 * @wordpress-plugin
 * Plugin Name:		  Dawnwing Track and Trace API
 * Plugin URI:		  https://www.semantica.co.za/
 * Description:		  Semantica Core Integrations
 * Version:			  1.0.0
 * Author:			  Semantica
 * Author URI:		  https://www.semantica.co.za/
 * License:			  GPL-2.0+
 * License URI:		  http://www.gnu.org/licenses/gpl-2.0.txt
 */


function helper_data_clean($data, $code)
{
	if (is_array($code)) {
		$check = $code;
	} else {
		$check = array($code);
	}
	$newData = array();
	foreach ($data as $key => $val) {
		if (in_array($val->trackEventCode, $check)) {
			$newData[] = $val;
		}
	}
	if (empty($newData)) {
		return;
	}
	return $newData;
}

add_action('woocommerce_view_order', 'my_custom_tracking');
function my_custom_tracking($order_id)
{

	$order = wc_get_order($order_id);
	$order_number  = $order->get_order_number();
	//$order_number2  = "FTGC400073";
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'http://swatws.dawnwing.co.za/dwwebservices/v2/live/api/trackandtrace?WaybillNo=FTGC' . $order_number,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJJZCI6IjExMCIsImV4cCI6MTk1MjYwMjY5NSwiaXNzIjoiaHR0cDovLzQxLjAuNjkuMTk3LyIsImF1ZCI6Imh0dHA6Ly80MS4wLjY5LjE5Ny8ifQ.FtovsNtqjCf_sgPsuFeKcUb6ai6Jt_upw5oyRnaqcglAC4uKDBPIAfsacGTEnnZx_GOWWMHatg8LOn1sNazbkw'
		),
	));

	$response = curl_exec($curl);
	curl_close($curl);
	$arrJson  = json_decode('[' . $response . ']');
	if (isset($arrJson[0]->trackEvents)) {
		$failed = helper_data_clean($arrJson[0]->trackEvents, array(5));
		$cleanData = helper_data_clean($arrJson[0]->trackEvents, array(1, 13));
		$delivery = helper_data_clean($arrJson[0]->trackEvents, array(3, 35, 15));
		$transit = helper_data_clean($arrJson[0]->trackEvents, array(15, 49, 50, 43, 23, 44, 45, 46));
		$collected = helper_data_clean($arrJson[0]->trackEvents, array(1));
		$packed = helper_data_clean($arrJson[0]->trackEvents, array(13, 52));
		$delivered = helper_data_clean($arrJson[0]->trackEvents, array(4, 8));
		

		echo "<b>Delivery Status:</b><br/>";
		if ($failed) {
			echo "Delivery Failure";
		} elseif ($delivered) {
			echo "Delivered";
		} elseif ($delivery) {
			echo "Out For Delivery";
		} elseif ($transit) {
			echo "In Transit To Delivery Hub";
		} elseif ($collected) {
			echo "Collected By Courier";
		} elseif ($packed) {
			echo "Packed";
		}
	} else {
		echo "<b>Delivery Status:</b><br/>";
		echo "Awaiting 1st Status";
	}
}

// Adding Meta container admin shop_order pages
add_action('add_meta_boxes', 'add_meta_boxes');
if (!function_exists('add_meta_boxes')) {
	function add_meta_boxes()
	{
		add_meta_box('other_fields', __('Dawnwing Status', 'woocommerce'), 'dawnwing_track_and_trace', 'shop_order', 'side', 'core');
	}
}

// Adding Meta field in the meta container admin shop_order pages
if (!function_exists('dawnwing_track_and_trace')) {
	function dawnwing_track_and_trace()
	{
		global $woocommerce, $post;
		$order = new WC_Order($post->ID);
		$order_number  = $order->get_order_number();
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'http://swatws.dawnwing.co.za/dwwebservices/v2/live/api/trackandtrace?WaybillNo=FTGC' . $order_number,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJJZCI6IjExMCIsImV4cCI6MTk1MjYwMjY5NSwiaXNzIjoiaHR0cDovLzQxLjAuNjkuMTk3LyIsImF1ZCI6Imh0dHA6Ly80MS4wLjY5LjE5Ny8ifQ.FtovsNtqjCf_sgPsuFeKcUb6ai6Jt_upw5oyRnaqcglAC4uKDBPIAfsacGTEnnZx_GOWWMHatg8LOn1sNazbkw'
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		$arrJson  = json_decode('[' . $response . ']');
		if (isset($arrJson[0]->trackEvents)) {
			$failed = helper_data_clean($arrJson[0]->trackEvents, array(5));
			$delivery = helper_data_clean($arrJson[0]->trackEvents, array(3, 35, 15));
			//$delivery_description = helper_data_clean($arrJson[0]->trackEventDescription, array(3, 35, 15));
			$transit = helper_data_clean($arrJson[0]->trackEvents, array(15, 49, 50, 43, 23, 44, 45, 46));
			$collected = helper_data_clean($arrJson[0]->trackEvents, array(1));
			$packed = helper_data_clean($arrJson[0]->trackEvents, array(13, 52));
			$delivered = helper_data_clean($arrJson[0]->trackEvents, array(4, 8));
			
			//$failed_comments = helper_data_clean($arrJson[0]->trackEventComments, array(5));
			$finalized = helper_data_clean($arrJson[0]->trackEvents, array(56));
			//$finalized_comments = helper_data_clean($arrJson[0]->trackEventComments, array(56));

			if ($packed) {
				echo "01: Packed<br/>";
			}
			if ($collected) {
				echo "02: Collected By Courier<br/>";
			}
			if ($transit) {
				echo "03: In Transit To Delivery Hub<br/>";
			}
			if ($failed) {
				echo "04: Delivery Failure<br/>";
				//		echo $failed_comments;
			}
			if ($delivery) {
				echo "05: Out For Delivery<br/>";
			}
			if ($delivered) {
				echo "06: Delivered<br/>";
			}
			
			if ($finalized) {
				echo "07: Waybill Finalized<br/>";
				//		echo $finalized_comments;
			}
		} else {
			echo "Awaiting 1st Status";
		//	echo $response;
		}
	}
}
	