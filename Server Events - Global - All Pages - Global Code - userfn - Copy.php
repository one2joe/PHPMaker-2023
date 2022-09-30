if ("Global Code") {
	
	function startPage () {
		global $Security;
		if (!$Security->isLoggedIn()) {
			return 'login';
		} else {
			return 'MonthlyMemberTrafficLightList';
		}
	}

	function line_notify ($token, $message) {
		// test and take from Postman
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://notify-api.line.me/api/notify",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query(array('message' => $message)),
			CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer {$token}",
				"Content-Type: application/x-www-form-urlencoded"
			),
            CURLOPT_SSL_VERIFYPEER => false,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		if ($response === FALSE) {
			return curl_error($curl);
		} else {
			return json_decode($response);
		}
	}

	function notify ($token, $message) {
		// test and take from Postman
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://notify-api.line.me/api/notify",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query(array('message' => $message)),
			CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer {$token}",
				"Content-Type: application/x-www-form-urlencoded"
			),
            CURLOPT_SSL_VERIFYPEER => false,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		if ($response === FALSE)
			die(curl_error($curl));
		else if ($response = json_decode($response, true) AND $response['status'] != 200)
			die($response['message']);
	}

	function notify_split ($token, $message) {
		$max_length = 980;
		$len = mb_strlen($message);
		if ($len > $max_length) {
			$loop = ceil($len / 960);
			$each_length = floor($len / $loop);
			$message_array = explode("\n", $message);
			$result = [];
			$current_len = 0;
			foreach ($message_array as $key => $value) {
				$current_len += mb_strlen($value);
				$result[floor($current_len / $max_length)][] = $value;
			}
			foreach ($result as $key => $value) {
				$result[$key] = notify($token, trim(implode("\n", $value), "\n"));
			}
			return $result;
		}
		return notify($token, $message);
	}
	
	function notify_with_image ($token, $message, $imageUrl, $imageFile) {
		// test and take from Postman
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://notify-api.line.me/api/notify",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query(array(
				'imageThumbnail' => $imageUrl,
				'imageFullsize' => $imageUrl,
				'message' => $message,
				'imageFile' => $imageFile,
			)),
			CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer {$token}",
				"Content-Type: application/x-www-form-urlencoded"
			),
			CURLOPT_SSL_VERIFYPEER => false,
		));
		$response = curl_exec($curl);
		if ($response === FALSE)
			$return = array('status' => '000', 'message' => curl_error($curl));
		else if ($response = json_decode($response, true) AND $response['status'] != 200)
			$return = array('status' => $response['status'], 'message' => $response['message']);
		else
			$return = true;
		curl_close($curl);
		return $return;
	}

    function replace_row ($class, $rsnew, $where = 'FALSE', $force_update = FALSE) {
		// if (file_exists("../models/{$class}.php")) {
			// include_once "../models/{$class}.php";
			$tbl = Container($class);
			/* ถ้าอยาก trigger rowInserting และ rowInserted
			if ($tbl->rowInserting(null, $rsnew)) {
				if ($tbl->insert($rsnew)) {
					$tbl->rowInserted(null, $rsnew);
				}
			} */
			if (empty($where))
				$where = 'FALSE';
			$rsold = ExecuteRow("SELECT * FROM {$tbl->TableName} WHERE {$where}", 2);
			// $rsnew['Updated'] = date("Y-m-d H:i:s");
			ExecuteQuery('SET FOREIGN_KEY_CHECKS=0;');
			if (empty($rsold) AND $force_update != TRUE) {
				$result['Affected_Rows'] = $tbl->Insert($rsnew);
				$result['action'] = 'insert';
			} else {
				$result['Affected_Rows'] = $tbl->Update($rsnew, $where);
				$result['action'] = 'update';
			}
		/* } else {
			$result['action'] = 'error';
			$result['Affected_Rows'] = 0;
			$result['ErrorMsg'] = "{$tbl->TableName} class file ({$tbl}.php) is not exist";
		} */
		return $result;
	}
}