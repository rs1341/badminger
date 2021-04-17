<?php

   include_once('db_connect.php');

   $rowClub = $mysql->query("select * from club where club_id='".$_GET['club_id']."'", true);

   if(count($rowClub)>0 && $rowClub[0]['line_token'] != ""){
	   $accessToken = $rowClub[0]['line_token'];//copy ข้อความ Channel access token ตอนที่ตั้งค่า
	   $content = file_get_contents('php://input');
	   $arrayJson = json_decode($content, true);
	   $arrayHeader = array();
	   $arrayHeader[] = "Content-Type: application/json";
	   $arrayHeader[] = "Authorization: Bearer {$accessToken}";
	   
	   $LINEDatas = array();
	   $LINEDatas['token'] = $accessToken;
	   //event type
	   $event_type = $arrayJson['events'][0]['type'];

	   //รับ id ของผู้ใช้
	   $id = $arrayJson['events'][0]['source']['userId'];
	   #ตัวอย่าง Message Type "Text + Sticker"

	   switch($event_type){
			case "follow":
				$returnArr = getLINEProfile($LINEDatas,$id);
				//$row = $mysql->query("select * from line_user where user_id='".$id."'", true);
				$mysql->where(array('user_id'=>$id, 'club_id'=>$_GET['club_id']))->delete('line_user');
				$obj = json_decode($returnArr['message']);
				$mysql->insert('line_user', array('id' => uniqid(), 'user_id' => $id , 'display_name' => $obj->displayName, 'picture_url' => $obj->pictureUrl, 'club_id' => $_GET['club_id']));

				if($_GET['club_id']=='1' || $_GET['club_id']=='3' ){
					$mysql->where(array('user_id'=>$id, 'club_id'=>'3'))->delete('line_user');
					
					$mysql->insert('line_user', array('id' => uniqid(), 'user_id' => $id , 'display_name' => $obj->displayName, 'picture_url' => $obj->pictureUrl, 'club_id' => '3'));
				}
				//file_put_contents('log-profile.txt', date('Y-m-d H:i:s').' : '.$returnArr['message'].PHP_EOL, FILE_APPEND);
				//file_put_contents('log-profile.txt', $sql.PHP_EOL, FILE_APPEND);

				break;
			case "unfollow":
				$mysql->where(array('user_id'=>$id, 'club_id'=>$_GET['club_id']))->delete('line_user');
				$mysql->where(array('line_id'=>$id, 'club_id'=>$_GET['club_id']))->update('member', array('line_id'=>''));
				if($_GET['club_id']=='1'){
					$mysql->where(array('user_id'=>$id, 'club_id'=>'3'))->delete('line_user');
					$mysql->where(array('line_id'=>$id, 'club_id'=>'3'))->update('member', array('line_id'=>''));
				}

				//file_put_contents('log-profile.txt', date('Y-m-d H:i:s').' : '.$returnArr['message'].PHP_EOL, FILE_APPEND);
				//file_put_contents('log-profile.txt', $sql.PHP_EOL, FILE_APPEND);

				break;
			case "message":	
				/*รับข้อความจากผู้ใช้
				$message = $arrayJson['events'][0]['message']['text'];
				if($message == "สวัสดี"){
					$arrayPostData['to'] = $id;
					$arrayPostData['messages'][0]['type'] = "text";
					$arrayPostData['messages'][0]['text'] = "สวัสดีจ้าาา-".implode(",",$arrayJson);
					$arrayPostData['messages'][1]['type'] = "sticker";
					$arrayPostData['messages'][1]['packageId'] = "2";
					$arrayPostData['messages'][1]['stickerId'] = "34";
					pushMsg($arrayHeader,$arrayPostData);
				}
				
				if((strpos($message,"ค่าคอร์ท") !== false ||  strpos($message,"ค่าใช้จ่าย") !== false)&& $_GET['club_id']=="1"){
					$arrayPostData['to'] = $id;
					$arrayPostData['messages'][0]['type'] = "text";
					$arrayPostData['messages'][0]['text'] = "ค่าคอร์ท 100 บาท ค่าลูกขีดละ 18 บาท ค่ะ";
					pushMsg($arrayHeader,$arrayPostData);
				
				}*/

				$adminLineID = $rowClub[0]['admin_line_id'];
				if($adminLineDI ==""){
					$adminLineID = "ttt1341";
				}

				$arrayPostData['to'] = $id;
				$arrayPostData['messages'][0]['type'] = "text";
				$arrayPostData['messages'][0]['text'] = "ขออภัยค่ะ เนื่องจากช่องทางนี้เป็นระบบตอบรับอัตโนมัติ ทางผู้จัดจะไม่สามารถตอบคำถามผ่านช่องทางนี้ได้ รบกวนแจ้งทางไลน์ผู้จัดโดยตรงนะค่ะ คลิ้กลิ้งค์ด้านล่างนี้เลยค่ะ ขอบคุณค่าาาา\nhttp://line.me/ti/p/~".$adminLineID;
				pushMsg($arrayHeader,$arrayPostData);

				if($_GET['club_id'] == "1" || $_GET['club_id'] == "3"){
					$returnArr = getLINEProfile($LINEDatas,$id);
					$obj = json_decode($returnArr['message']);
					$arrayPostData['to'] = "U405e5d4478d9a2ae8054fdd3e71a20ef";
					$arrayPostData['messages'][0]['type'] = "text";
					$arrayPostData['messages'][0]['text'] = "มีสมาชิกชื่อ ".$obj->displayName." ส่งข้อความ '".$arrayJson['events'][0]['message']['text']."' มายัง admin";
					pushMsg($arrayHeader,$arrayPostData);
				}

				break;
	   }
   }

   

    


   function pushMsg($arrayHeader,$arrayPostData){
      $strUrl = "https://api.line.me/v2/bot/message/push";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$strUrl);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $arrayHeader);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayPostData));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $result = curl_exec($ch);
      curl_close ($ch);
   }

	function getLINEProfile($datas,$idd)
	{
	   $datasReturn = [];
	   $curl = curl_init();
	   curl_setopt_array($curl, array(
		 CURLOPT_URL => "https://api.line.me/v2/bot/profile/".$idd,
		 CURLOPT_RETURNTRANSFER => true,
		 CURLOPT_ENCODING => "",
		 CURLOPT_MAXREDIRS => 10,
		 CURLOPT_TIMEOUT => 30,
		 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		 CURLOPT_CUSTOMREQUEST => "GET",
		 CURLOPT_HTTPHEADER => array(
		   "Authorization: Bearer ".$datas['token'],
		   "cache-control: no-cache"
		 ),
	   ));
	   $response = curl_exec($curl);
	   $err = curl_error($curl);
	   curl_close($curl);
	   if($err){
		  $datasReturn['result'] = 'E';
		  $datasReturn['message'] = $err;
	   }else{
		  if($response == "{}"){
			  $datasReturn['result'] = 'S';
			  $datasReturn['message'] = 'Success';
		  }else{
			  $datasReturn['result'] = 'E';
			  $datasReturn['message'] = $response;
		  }
	   }
	   return $datasReturn;
	}
?>