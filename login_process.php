<?php
  	session_start();
  	
 	include_once('db_connect.php');
	

	$userArr = $mysql->query("select a.*, b.club_name, b.allow_repeat_buddy, b.allow_repeat_opponent, b.allow_pre_queue_when_playing, sorting_queue_by, 
	b.shuttle_cost, b.bet_cost, b.bet_rate, b.line_token, b.show_member_photo, datediff(a.user_expiry_date,now()) as days_expiry from user a inner join club b on a.club_id=b.club_id where a.user_name='".$_REQUEST['txtUsername']."' and a.user_password=md5('".$_REQUEST['txtPassword']."') and a.user_status='Active' and now()<=a.user_expiry_date and b.club_status='Active'", true);
	//print $mysql->last_query();exit;
	if(count($userArr) > 0){
      		$_SESSION['user_name'] = $userArr[0]['user_name'];
			$_SESSION['club_id'] = $userArr[0]['club_id'];
			$_SESSION['club_name'] = $userArr[0]['club_name'];
			$_SESSION['shuttle_cost'] = $userArr[0]['shuttle_cost'];
			$_SESSION['bet_cost'] = $userArr[0]['bet_cost'];
			$_SESSION['bet_rate'] = $userArr[0]['bet_rate'];
			$_SESSION['show_member_photo'] = $userArr[0]['show_member_photo'];
            $_SESSION['allow_repeat_buddy'] = $userArr[0]['allow_repeat_buddy'];
            $_SESSION['allow_repeat_opponent'] = $userArr[0]['allow_repeat_opponent'];
            $_SESSION['allow_pre_queue_when_playing'] = $userArr[0]['allow_pre_queue_when_playing'];
			$_SESSION['sorting_queue_by'] = $userArr[0]['sorting_queue_by'];
			$_SESSION['days_expiry'] = $userArr[0]['days_expiry'];
			$_SESSION['line_token'] = $userArr[0]['line_token'];
            $_SESSION['batch_id'] = "";
            $_SESSION['batch_no'] = "";

            $mysql->insert('user_log', array('log_id'=>uniqid(), 'user_name'=>$userArr[0]['user_name'], 'ip_address'=>$_SERVER['REMOTE_ADDR']));

			//UPDATE ขาประจำ/ขาจร  หรือจะเรียก store ก็ได้   แต่ตอนนี้ยังมีปัญหาการเรียก store ด้วย PHP   ให้ไปเรียกผ่าน phpmyadmin โดยตรงก็ได้
			$mysql->query("UPDATE member SET member_category=2 WHERE CLUB_ID='".$userArr[0]['club_id']."';", false);
			$mysql->query("	UPDATE member SET member_category=1 
							WHERE CLUB_ID='".$userArr[0]['club_id']."' AND MEMBER_ID IN( 
								SELECT * FROM (
								SELECT A.MEMBER_ID FROM member A 
								INNER JOIN payment B ON A.MEMBER_ID=B.MEMBER_ID 
								INNER JOIN batch C ON B.BATCH_ID=C.BATCH_ID 
								WHERE C.CLUB_ID='".$userArr[0]['club_id']."' AND B.PAYMENT_STATUS='Paid' 
								AND B.CREATED_DATE >= DATE_ADD(NOW(), INTERVAL -3 MONTH) GROUP BY A.MEMBER_ID HAVING COUNT(A.MEMBER_ID)>=3
							) AS TEMP);", false);
							//print $mysql->last_query();exit;

      		header("Location: admin.php");
    }else{
     	 	$_SESSION['user_name'] = "";
      		header("Location: login.php?error=failed");
    }
  
?>