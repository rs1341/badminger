<?php
	if($_GET['batch_id'] != ""){
		$batchArr = $mysql->query("select * from batch where batch_id='".$_GET['batch_id']."' and club_id='".$_SESSION['club_id']."' limit 0,1", true);
		$_SESSION['batch_id'] = $batchArr[0]['batch_id'];
		$_SESSION['batch_no'] = $batchArr[0]['batch_no'];
		$_SESSION['batch_status'] = $batchArr[0]['batch_status'];
		//$_SESSION['club_id'] = $batchArr[0]['club_id'];
      	//$_SESSION['club_auto_level'] = $batchArr[0]['club_auto_level'];
		$_SESSION['is_auto_pilot'] = $batchArr[0]['is_auto_pilot'];
	}else{
		if($_SESSION['batch_id'] == ""){
			$batchArr = $mysql->query("select * from batch where batch_status != 'Cancelled' and club_id='".$_SESSION['club_id']."' order by created_date desc limit 0,1", true);
			$_SESSION['batch_id'] = $batchArr[0]['batch_id'];
			$_SESSION['batch_no'] = $batchArr[0]['batch_no'];
			$_SESSION['batch_status'] = $batchArr[0]['batch_status'];
			//$_SESSION['club_id'] = $batchArr[0]['club_id'];
          	//$_SESSION['club_auto_level'] = $batchArr[0]['club_auto_level'];
			$_SESSION['is_auto_pilot'] = $batchArr[0]['is_auto_pilot'];
		}
	}
?>