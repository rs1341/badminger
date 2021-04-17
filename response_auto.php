<?php
	session_start();
	ini_set('error_reporting', E_ALL & ~NOTICE);
	ini_set('log_errors', 1);
	ini_set('display_errors', 1);

	include_once('db_connect.php');
	include_once('batch_session.php');
	
	switch($_GET['task']){
		case "create_auto_match_OLD":
			$mysql->where(array('batch_id' => $_SESSION['batch_id'], 'match_status' => 'auto'))->delete('matchq');
			$member1 = $mysql->query("select b.member_id, c.class_ratio
										from queue a 
												inner join member b on a.member_id=b.member_id 
												inner join class c on b.class_id=c.class_id 
										where a.queue_status='in queue' and a.batch_id='".$_SESSION['batch_id']."'
										order by a.created_date 
										limit 0,1",true);
			//print $mysql->last_query();

			if(count($member1)>=1){
				/*  -- Exclude Member is in match  */
				/*$member24 = $mysql->query("select member_id, class_ratio from (select b.member_id, c.class_ratio
													from queue a 
															inner join member b on a.member_id=b.member_id 
															inner join class c on b.class_id=c.class_id 
													where a.queue_status='in queue' and a.member_id not in(select member_id from queue where batch_id='".$_SESSION['batch_id']."' and queue_status='in match') and a.batch_id='".$_SESSION['batch_id']."' and c.class_ratio between ".($member1[0]['class_ratio']-($_SESSION['club_auto_level']*10))." and ".($member1[0]['class_ratio']+($_SESSION['club_auto_level']*10))." and a.member_id != '".$member1[0]['member_id']."'
													order by a.created_date limit 0,10) as a order by rand()",true);
													//print $mysql->last_query();
				
				*/
				/*  -- Exclude Member is in match  */
              	
				$member24 = $mysql->query("select member_id, class_ratio from (select b.member_id, c.class_ratio
													from queue a 
															inner join member b on a.member_id=b.member_id 
															inner join class c on b.class_id=c.class_id 
													where a.queue_status='in queue' and a.batch_id='".$_SESSION['batch_id']."' and c.class_ratio between ".($member1[0]['class_ratio']-($_SESSION['club_auto_level']*10))." and ".($member1[0]['class_ratio']+($_SESSION['club_auto_level']*10))." and a.member_id != '".$member1[0]['member_id']."'
													order by a.created_date) as a order by rand()",true);
													//print $mysql->last_query();
				
				$memberAll = array();
				$memberNew = array();

				$memberAll[$member1[0]['member_id']] = $member1[0]['class_ratio'];
				$memberAll[$member24[0]['member_id']] = $member24[0]['class_ratio'];
				$memberAll[$member24[1]['member_id']] = $member24[1]['class_ratio'];
				$memberAll[$member24[2]['member_id']] = $member24[2]['class_ratio'];

				asort($memberAll);

				foreach($memberAll as $key=>$value){
					$memberNew[] = $key;
				}

				//1st pair
				$member11 = $memberNew[0];
				$member12 = $memberNew[2];

				//2nd pair
				$member21 = $memberNew[1];
				$member22 = $memberNew[3];

				if(count($member24)>=3){
					$matchRow = $mysql->query("select max(cast(match_no as int)) as last_match_no from matchq where batch_id='".$_SESSION['batch_id']."' and match_status not in('auto')", true);

					//print $member1[0]['member_name']."---".$member24[0]['member_name']."---".$member24[1]['member_name']."---".$member24[2]['member_name'];

					$matchID = uniqid();
					if($matchRow[0]['last_match_no'] == null){
						$matchNo = 0;
					}else{
						$matchNo = $matchRow[0]['last_match_no']+1;
					}

					$mysql->insert('matchq', array('match_id' => $matchID, 'match_no' => $matchNo, 'member_id_11' => $member11, 'member_id_12' => $member12, 'member_id_21' => $member21, 'member_id_22' => $member22,'match_status' => 'auto', 'batch_id' => $_SESSION['batch_id'], 'created_by' => $_SESSION['user_name']));
				}
			}

			break;
		case "create_auto_match":
			$mysql->where(array('batch_id' => $_SESSION['batch_id'], 'match_status' => 'auto'))->delete('matchq');
			
			//Member #11
			$member11 = $mysql->query("select b.member_id, c.class_ratio
										from queue a 
												inner join member b on a.member_id=b.member_id 
												inner join class c on b.class_id=c.class_id 
                                                left join matchq mq on a.match_id=mq.match_id and mq.match_status in('in queue','completed','playing')
										where a.queue_status='in queue' and a.batch_id='".$_SESSION['batch_id']."'
										order by ifnull(mq.created_date, a.created_date) 
										limit 0,1",true);
			//print $mysql->last_query();
			print "<br/>";

			if(count($member11)>=1){
				$member11con = getConditionArr($member11[0]['member_id']);
				//print $member11[0]['member_id'];
				//Member #12
				$sql = "";
				$sql .= "select member_id, class_ratio from (select b.member_id, c.class_ratio from queue a inner join member b on a.member_id=b.member_id inner join class c on b.class_id=c.class_id where a.queue_status='in queue' and a.batch_id='".$_SESSION['batch_id']."' and c.class_ratio between ".($member11[0]['class_ratio']-($_SESSION['club_auto_level']*10))." and ".($member11[0]['class_ratio']+($_SESSION['club_auto_level']*10))." and a.member_id != '".$member11[0]['member_id']."' ";

                //ยกเว้นเคยคู่กันแล้ว
                if($_SESSION['allow_repeat_buddy']=="1"){
                    $sql .= " and a.member_id not in(select member_id_11 from matchq where batch_id=a.batch_id and match_status in('completed','in queue','playing') and member_id_12='".$member11[0]['member_id']."' union select member_id_12 from matchq where batch_id=a.batch_id and match_status in('completed','in queue','playing') and member_id_11='".$member11[0]['member_id']."' union select member_id_22 from matchq where batch_id=a.batch_id and match_status in('completed','in queue','playing') and member_id_21='".$member11[0]['member_id']."' union select member_id_21 from matchq where batch_id=a.batch_id and match_status in('completed','in queue','playing') and member_id_22='".$member11[0]['member_id']."') ";
                }

				if($member11con['MustPairWith'] != ""){
					$sql .= " and a.member_id in(".$member11con['MustPairWith'].") ";
				}

				if($member11con['NoPairWith'] != ""){
					$sql .= " and a.member_id not in(".$member11con['NoPairWith'].") ";
				}

				$sql .= " order by a.created_date) as a order by rand() limit 0,1";

				$member12 = $mysql->query($sql,true);
                //print "member12:<br/>";
                //print $sql;
                //print_r($member12);

				if(count($member12)>=1){
					//Member #21  (Same class as 11)
					$sql = "";
					$sql .= "select member_id, class_ratio from (select b.member_id, c.class_ratio from queue a inner join member b on a.member_id=b.member_id inner join class c on b.class_id=c.class_id where a.queue_status='in queue' and a.batch_id='".$_SESSION['batch_id']."' and c.class_ratio = ".$member11[0]['class_ratio']."  and a.member_id not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1 in('".$member11[0]['member_id']."','".$member12[0]['member_id']."')) and '".$member11[0]['member_id']."' not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1=a.member_id) and '".$member12[0]['member_id']."' not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1=a.member_id) and a.member_id not in('".$member11[0]['member_id']."','".$member12[0]['member_id']."') ";
					$sql .= " order by a.created_date) as a order by rand() limit 0,1";
					$member21 = $mysql->query($sql,true);
                    if(count($member21)==0){
                        $sql = "";
                        $sql .= "select member_id, class_ratio from (select b.member_id, c.class_ratio from queue a inner join member b on a.member_id=b.member_id inner join class c on b.class_id=c.class_id where a.queue_status='in queue' and a.batch_id='".$_SESSION['batch_id']."' and c.class_ratio  between ".($member11[0]['class_ratio']-(1*10))." and ".($member11[0]['class_ratio']+(1*10))."  and a.member_id not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1 in('".$member11[0]['member_id']."','".$member12[0]['member_id']."')) and '".$member11[0]['member_id']."' not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1=a.member_id) and '".$member12[0]['member_id']."' not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1=a.member_id) and a.member_id not in('".$member11[0]['member_id']."','".$member12[0]['member_id']."') ";
                        $sql .= " order by a.created_date) as a order by rand() limit 0,1";
                        $member21 = $mysql->query($sql,true);
                    }
                    //print "member21:<br/>";
                    //print_r($member21);

					if(count($member21)>=1){
						//Member #22 (Same class as 12)
						$member21con = getConditionArr($member21[0]['member_id']);
                        $diffRatio = (($member11[0]['class_ratio']+$member12[0]['class_ratio'])-$member21[0]['class_ratio']);

						$sql = "";
						$sql .= "select member_id, class_ratio from (select b.member_id, c.class_ratio from queue a inner join member b on a.member_id=b.member_id inner join class c on b.class_id=c.class_id where a.queue_status='in queue' and a.batch_id='".$_SESSION['batch_id']."' and c.class_ratio ='".$diffRatio."' and a.member_id not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1 in('".$member11[0]['member_id']."','".$member12[0]['member_id']."')) and '".$member11[0]['member_id']."' not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1=a.member_id) and '".$member12[0]['member_id']."' not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1=a.member_id) and a.member_id not in('".$member11[0]['member_id']."','".$member12[0]['member_id']."','".$member21[0]['member_id']."') ";

                        //ยกเว้นเคยคู่กันแล้ว
                        if($_SESSION['allow_repeat_buddy']=="1"){
                            $sql .= " and a.member_id not in(select member_id_11 from matchq where batch_id=a.batch_id and match_status in('completed','in queue','playing') and member_id_12='".$member11[0]['member_id']."' union select member_id_12 from matchq where batch_id=a.batch_id and match_status in('completed','in queue','playing') and member_id_11='".$member11[0]['member_id']."' union select member_id_22 from matchq where batch_id=a.batch_id and match_status in('completed','in queue','playing') and member_id_21='".$member11[0]['member_id']."' union select member_id_21 from matchq where batch_id=a.batch_id and match_status in('completed','in queue','playing') and member_id_22='".$member11[0]['member_id']."') ";
                        }

						if($member21con['MustPairWith'] != ""){
							$sql .= " and a.member_id in(".$member21con['MustPairWith'].") ";
						}

						if($member21con['NoPairWith'] != ""){
							$sql .= " and a.member_id not in(".$member21con['NoPairWith'].") ";
						}

						$sql .= " order by a.created_date) as a order by rand() limit 0,1";
						//print $sql;
						$member22 = $mysql->query($sql,true);
                        if(count($member22)==0){
                            $sql = "";
                            $sql .= "select member_id, class_ratio from (select b.member_id, c.class_ratio from queue a inner join member b on a.member_id=b.member_id inner join class c on b.class_id=c.class_id where a.queue_status='in queue' and a.batch_id='".$_SESSION['batch_id']."' and c.class_ratio between '".($diffRatio-10)."' and '".($diffRatio+10)."' and a.member_id not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1 in('".$member11[0]['member_id']."','".$member12[0]['member_id']."')) and '".$member11[0]['member_id']."' not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1=a.member_id) and '".$member12[0]['member_id']."' not in(select member_id_2 from member_condition where condition_type='NoOpponentWith' and club_id='".$_SESSION['club_id']."' and member_id_1=a.member_id) and a.member_id not in('".$member11[0]['member_id']."','".$member12[0]['member_id']."','".$member21[0]['member_id']."') ";

                            if($member21con['MustPairWith'] != ""){
                                $sql .= " and a.member_id in(".$member21con['MustPairWith'].") ";
                            }

                            if($member21con['NoPairWith'] != ""){
                                $sql .= " and a.member_id not in(".$member21con['NoPairWith'].") ";
                            }

                            $sql .= " order by a.created_date) as a order by rand() limit 0,1";                
                            $member22 = $mysql->query($sql,true);
                        }



                        //print "member22:<br/>";
                        //print_r($member22);
						if(count($member22)>=1){
							$matchRow = $mysql->query("select max(convert(match_no,decimal)) as last_match_no from matchq where batch_id='".$_SESSION['batch_id']."' and match_status not in('auto')", true);
//print $mysql->last_query();
							//print $member1[0]['member_name']."---".$member24[0]['member_name']."---".$member24[1]['member_name']."---".$member24[2]['member_name'];

							$matchID = uniqid();
							if($matchRow[0]['last_match_no'] == null){
								$matchNo = 0;
							}else{
								$matchNo = $matchRow[0]['last_match_no']+1;
							}

							$mysql->insert('matchq', array('match_id' => $matchID, 'match_no' => $matchNo, 'member_id_11' => $member11[0]['member_id'], 'member_id_12' => $member12[0]['member_id'], 'member_id_21' => $member21[0]['member_id'], 'member_id_22' => $member22[0]['member_id'],'match_status' => 'auto', 'batch_id' => $_SESSION['batch_id'], 'created_by' => $_SESSION['user_name']));
						}
					}
				}
			}

			break;
		case "set_auto_match_level":
			$mysql->where(array('batch_id' => $_SESSION['batch_id']))->update('batch', array('club_auto_level'=>$_GET['club_auto_level']));
			$_SESSION['club_auto_level'] = $_GET['club_auto_level'];
			break;
		case "set_auto_pilot":
			$mysql->where(array('batch_id' => $_SESSION['batch_id']))->update('batch', array('is_auto_pilot'=>$_GET['is_auto_pilot']));
			$_SESSION['is_auto_pilot'] = $_GET['is_auto_pilot'];
			break;
	}

	function getConditionArr($member_id){
		global $mysql;

		$returnArr = array();
		$conMPWarray = $mysql->query("select member_id_2 from member_condition where member_id_1='".$member_id."' and club_id='".$_SESSION['club_id']."' and condition_type='MustPairWith'",true);
		$conMPWtext = "";
		for($i=0;$i<count($conMPWarray);$i++){
			$conMPWtext .= "'".$conMPWarray[$i]['member_id_2']."'";
			if($i<count($conMPWarray)-1){
				$conMPWtext .= ",";
			}
		}

		$conNPWarray = $mysql->query("select member_id_2 from member_condition where member_id_1='".$member_id."' and club_id='".$_SESSION['club_id']."' and condition_type='NoPairWith'",true);
		$conNPWtext = "";
		for($i=0;$i<count($conNPWarray);$i++){
			$conNPWtext .= "'".$conNPWarray[$i]['member_id_2']."'";
			if($i<count($conNPWarray)-1){
				$conNPWtext .= ",";
			}
		}

		$conNOWarray = $mysql->query("select member_id_2 from member_condition where member_id_1='".$member_id."' and club_id='".$_SESSION['club_id']."' and condition_type='NoOpponentWith'",true);
		$conNOWtext = "";
		for($i=0;$i<count($conNOWarray);$i++){
			$conNOWtext .= "'".$conNOWarray[$i]['member_id_2']."'";
			if($i<count($conNOWarray)-1){
				$conNOWtext .= ",";
			}
		}

		$returnArr['MustPairWith'] = $conMPWtext;
		$returnArr['NoPairWith'] = $conNPWtext;
		$returnArr['NoOpponentWith'] = $conNOWtext;

		return $returnArr;
	}

?>
