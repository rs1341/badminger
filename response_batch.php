<?php
	session_start();
	ini_set('error_reporting', E_ALL & ~NOTICE);
	ini_set('log_errors', 1);
	ini_set('display_errors', 1);

	if($_SESSION['user_name']=="" && $_GET['club_id']==""){
		header("Location: login.php");
	}

	include_once('db_connect.php');
	include_once('batch_session.php');

	if($_GET['is_preview'] != "" && $_SESSION['user_name'] == ""){
		if($_GET['club_id'] != ""){//user view
			$batchArr = $mysql->query("select a.*, b.club_name from batch a inner join club b on a.club_id=b.club_id where a.club_id='".$_GET['club_id']."' and a.batch_status != 'Cancelled' order by a.created_date desc limit 0,1", true);
			$_SESSION['batch_id'] = $batchArr[0]['batch_id'];
			$_SESSION['batch_no'] = $batchArr[0]['batch_no'];
			$_SESSION['batch_status'] = $batchArr[0]['batch_status'];	
			//$_SESSION['club_id'] = $batchArr[0]['club_id'];
			//$_SESSION['club_name'] = $batchArr[0]['club_name'];
			$_SESSION['is_auto_pilot'] = $batchArr[0]['is_auto_pilot'];
		}
	}
	

	switch($_GET['task']){
		case "create_batch":
        	if($_SESSION['user_name'] != ""){
                $batch_id=uniqid();
                $batch_no=date("YmdHis",strtotime("+7 hours"));
                $mysql->insert('batch', array('batch_id' => $batch_id , 'batch_no' => $batch_no, 'created_by'=>$_SESSION['user_name'], 'club_id'=> $_SESSION['club_id']));
                $mysql->insert('team_spending', array('id' => uniqid(), 'batch_id' => $batch_id));
                $_SESSION['batch_id'] = "";

            }
			break;
        case "change_batch":
        	//nothing to do
			break;
       	 case "cancel_batch":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('batch_id'=>$_SESSION['batch_id']))->update('batch', array('batch_status'=>'Cancelled'));
                $mysql->where(array('batch_id' => $_SESSION['batch_id']))->delete('queue');
                $_SESSION['batch_id'] = "";
            }
			break;
        case "complete_batch":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('batch_id'=>$_SESSION['batch_id']))->update('batch', array('batch_status'=>'Completed'));
                $mysql->where(array('batch_id' => $_SESSION['batch_id']))->delete('queue');
                $mysql->where(array('match_status' => 'playing', 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_status' => 'completed', 'completed_date' => date("Y-m-d H:i:s",strtotime("+7 hours"))));
                $_SESSION['batch_status'] = "Completed";
            }
			break;
	}


?>
