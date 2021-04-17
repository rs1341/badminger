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
	include_once('function.php');

	if($_GET['is_preview'] != "" && $_SESSION['user_name'] == ""){
		if($_GET['club_id'] != ""){//user view
			$batchArr = $mysql->query("select a.*, b.club_name from batch a inner join club b on a.club_id=b.club_id where a.club_id='".$_GET['club_id']."' and a.batch_status != 'Cancelled' order by a.created_date desc limit 0,1", true);
			$_SESSION['batch_id'] = $batchArr[0]['batch_id'];
			$_SESSION['batch_no'] = $batchArr[0]['batch_no'];
			$_SESSION['batch_status'] = $batchArr[0]['batch_status'];	
			$_SESSION['club_id'] = $batchArr[0]['club_id'];
			$_SESSION['club_name'] = $batchArr[0]['club_name'];
			$_SESSION['is_auto_pilot'] = $batchArr[0]['is_auto_pilot'];
			$isPreview = $_GET['is_preview'];
		}
	}

	switch($_GET['task']){
		case "save_match":
        	if($_SESSION['user_name'] != ""){
                $varArr = explode("@",$_GET['data']);
                $matchNo = $_GET['match_id'];
                $matchStatus = $_GET['match_status'];

                if($matchStatus == "cancelled" && count($varArr)<5){
                    if(count($varArr)>=2){
                        $teamArr1 = explode("_",$varArr[1]);
                        $teamArr2 = explode("_",$varArr[2]);
                        $teamArr3 = explode("_",$varArr[3]);
                        $teamArr4 = explode("_",$varArr[4]);

                        $memberArr[$teamArr1[2]]=$teamArr1[3];
                        $memberArr[$teamArr2[2]]=$teamArr2[3];
                        $memberArr[$teamArr3[2]]=$teamArr3[3];
                        $memberArr[$teamArr4[2]]=$teamArr4[3];

                        $matchArr = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and match_status='cancelled'", true);
                        $matchID = $matchArr[0]['match_id'];
						$tempMember11 = $matchArr[0]['member_id_11'];
						$tempMember12 = $matchArr[0]['member_id_12'];
						$tempMember21 = $matchArr[0]['member_id_21'];
						$tempMember22 = $matchArr[0]['member_id_22'];

                        if($memberArr[11] != ""){
							$tempMember11 = $memberArr[11];
                            $row = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and (member_id_12='".$memberArr[11]."' or member_id_21='".$memberArr[11]."' or  member_id_22='".$memberArr[11]."')", true);
                            if(count($row)>0){
                                print "Error01:duplicate member";
                                exit();
                            }
                        }

                        if($memberArr[12] != ""){
							$tempMember12 = $memberArr[12];
                            $row = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and (member_id_11='".$memberArr[12]."' or member_id_21='".$memberArr[12]."' or  member_id_22='".$memberArr[12]."')", true);
                            //print $mysql->last_query();
                            if(count($row)>0){
                                print "Error01:duplicate member";
                                exit();
                            }
                        }

                        if($memberArr[21] != ""){
							$tempMember21 = $memberArr[21];
                            $row = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and (member_id_11='".$memberArr[21]."' or member_id_12='".$memberArr[21]."' or  member_id_22='".$memberArr[21]."')", true);
                            if(count($row)>0){
                                print "Error01:duplicate member";
                                exit();
                            }
                        }

                        if($memberArr[22] != ""){
							$tempMember22 = $memberArr[22];
                            $row = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and (member_id_11='".$memberArr[22]."' or member_id_12='".$memberArr[22]."' or  member_id_21='".$memberArr[22]."')", true);
                            if(count($row)>0){
                                print "Error01:duplicate member";
                                exit();
                            }
                        }
						
						
						$tempStr = checkCondition($tempMember11, $tempMember12, $tempMember21, $tempMember22);
						if($tempStr != ""){
							print $tempStr;
							exit();
						}


                        if($memberArr[11] != ""){
                            $mysql->where(array('match_no' => $matchNo, 'batch_id' => $_SESSION['batch_id']))->update('matchq', array('member_id_11' => $memberArr[11],'match_status' => 'in queue'));
                        }

                        if($memberArr[12] != ""){
                            $mysql->where(array('match_no' => $matchNo, 'batch_id' => $_SESSION['batch_id']))->update('matchq', array('member_id_12' => $memberArr[12],'match_status' => 'in queue'));
                        }

                        if($memberArr[21] != ""){
                            $mysql->where(array('match_no' => $matchNo, 'batch_id' => $_SESSION['batch_id']))->update('matchq', array('member_id_21' => $memberArr[21],'match_status' => 'in queue'));
                        }

                        if($memberArr[22] != ""){
                            $mysql->where(array('match_no' => $matchNo, 'batch_id' => $_SESSION['batch_id']))->update('matchq', array('member_id_22' => $memberArr[22],'match_status' => 'in queue'));
                        }

						$row = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and match_status='in queue'", true);
						$tempMember11 = $row[0]['member_id_11'];
						$tempMember12 = $row[0]['member_id_12'];
						$tempMember21 = $row[0]['member_id_21'];
						$tempMember22 = $row[0]['member_id_22'];

						

                        $mysql->where(array('member_id'=>$tempMember11,'queue_status'=>'in queue', 'batch_id' => $_SESSION['batch_id']))->update('queue',array('queue_status' => 'in match', 'match_id' => $matchID));
                        $mysql->insert('queue', array('queue_id' => uniqid() , 'member_id' => $tempMember11, 'queue_status' => 'in queue', 'batch_id' => $_SESSION['batch_id'],'created_by'=>$_SESSION['user_name'], 'match_id' => $matchID));

                        $mysql->where(array('member_id'=>$tempMember12,'queue_status'=>'in queue', 'batch_id' => $_SESSION['batch_id']))->update('queue',array('queue_status' => 'in match', 'match_id' => $matchID));
                        $mysql->insert('queue', array('queue_id' => uniqid() , 'member_id' => $tempMember12, 'queue_status' => 'in queue', 'batch_id' => $_SESSION['batch_id'],'created_by'=>$_SESSION['user_name'], 'match_id' => $matchID));

                        $mysql->where(array('member_id'=>$tempMember21,'queue_status'=>'in queue', 'batch_id' => $_SESSION['batch_id']))->update('queue',array('queue_status' => 'in match', 'match_id' => $matchID));
                        $mysql->insert('queue', array('queue_id' => uniqid() , 'member_id' => $tempMember21, 'queue_status' => 'in queue', 'batch_id' => $_SESSION['batch_id'],'created_by'=>$_SESSION['user_name'], 'match_id' => $matchID));

                        $mysql->where(array('member_id'=>$tempMember22,'queue_status'=>'in queue', 'batch_id' => $_SESSION['batch_id']))->update('queue',array('queue_status' => 'in match', 'match_id' => $matchID));
                        $mysql->insert('queue', array('queue_id' => uniqid() , 'member_id' => $tempMember22, 'queue_status' => 'in queue', 'batch_id' => $_SESSION['batch_id'],'created_by'=>$_SESSION['user_name'], 'match_id' => $matchID));
                    }
                }else{
                    if(count($varArr)==5){
                        $teamArr1 = explode("_",$varArr[1]);
                        $teamArr2 = explode("_",$varArr[2]);
                        $teamArr3 = explode("_",$varArr[3]);
                        $teamArr4 = explode("_",$varArr[4]);

                        $memberArr[$teamArr1[2]]=$teamArr1[3];
                        $memberArr[$teamArr2[2]]=$teamArr2[3];
                        $memberArr[$teamArr3[2]]=$teamArr3[3];
                        $memberArr[$teamArr4[2]]=$teamArr4[3];

						$tempStr = checkCondition($memberArr[11],$memberArr[12],$memberArr[21],$memberArr[22]);
						if($tempStr != ""){
							print $tempStr;
							exit();
						}

                        if($matchNo != ""){
                            $mysql->where(array('match_no'=>$matchNo, 'batch_id' => $_SESSION['batch_id']))->delete('matchq');
                        }

                        $matchID = uniqid();

                        $mysql->insert('matchq', array('match_id' => $matchID, 'match_no' => $matchNo, 'member_id_11' => $memberArr[11], 'member_id_12' => $memberArr[12], 'member_id_21' => $memberArr[21], 'member_id_22' => $memberArr[22],'match_status' => 'in queue', 'no_of_shuttle'=> 1, 'batch_id' => $_SESSION['batch_id'], 'created_by' => $_SESSION['user_name']));

                        $mysql->where(array('member_id'=>$memberArr[11],'queue_status'=>'in queue', 'batch_id' => $_SESSION['batch_id']))->update('queue',array('queue_status' => 'in match', 'match_id' => $matchID));
                        $mysql->insert('queue', array('queue_id' => uniqid() , 'member_id' => $memberArr[11], 'queue_status' => 'in queue', 'batch_id' => $_SESSION['batch_id'],'created_by'=>$_SESSION['user_name'], 'match_id' => $matchID));

                        $mysql->where(array('member_id'=>$memberArr[12],'queue_status'=>'in queue', 'batch_id' => $_SESSION['batch_id']))->update('queue',array('queue_status' => 'in match', 'match_id' => $matchID));
                        $mysql->insert('queue', array('queue_id' => uniqid() , 'member_id' => $memberArr[12], 'queue_status' => 'in queue', 'batch_id' => $_SESSION['batch_id'],'created_by'=>$_SESSION['user_name'], 'match_id' => $matchID));

                        $mysql->where(array('member_id'=>$memberArr[21],'queue_status'=>'in queue', 'batch_id' => $_SESSION['batch_id']))->update('queue',array('queue_status' => 'in match', 'match_id' => $matchID));
                        $mysql->insert('queue', array('queue_id' => uniqid() , 'member_id' => $memberArr[21], 'queue_status' => 'in queue', 'batch_id' => $_SESSION['batch_id'],'created_by'=>$_SESSION['user_name'], 'match_id' => $matchID));

                        $mysql->where(array('member_id'=>$memberArr[22],'queue_status'=>'in queue', 'batch_id' => $_SESSION['batch_id']))->update('queue',array('queue_status' => 'in match', 'match_id' => $matchID));
                        $mysql->insert('queue', array('queue_id' => uniqid() , 'member_id' => $memberArr[22], 'queue_status' => 'in queue', 'batch_id' => $_SESSION['batch_id'],'created_by'=>$_SESSION['user_name'], 'match_id' => $matchID));
                    }
                }
            }
			break;
		case "delete_match":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('match_id'=>$_GET['match_id'], 'batch_id' => $_SESSION['batch_id']))->delete('matchq');
            }
			break;
    	case "cancel_match":
        	if($_SESSION['user_name'] != ""){
				if($_GET['match_status'] != 'auto'){
					if($_GET['match_status'] != 'completed'){
						$mysql->query("delete a from queue a inner join matchq b on a.member_id=b.member_id_11 or a.member_id=b.member_id_12 or a.member_id=b.member_id_21 or a.member_id=b.member_id_22 where b.match_id='".$_GET['match_id']."' and a.batch_id=b.batch_id and a.batch_id='".$_SESSION['batch_id']."' and a.queue_status='in queue'");
					}

					$mysql->query("update queue a inner join matchq b on a.member_id=b.member_id_11 or a.member_id=b.member_id_12 or 
					a.member_id=b.member_id_21 or a.member_id=b.member_id_22 set a.queue_status='in queue', a.match_id='' where b.match_id='".$_GET['match_id']."' and a.batch_id=b.batch_id and a.match_id=b.match_id and a.batch_id='".$_SESSION['batch_id']."' and a.queue_status='in match'");
				}
                $mysql->where(array('match_id'=>$_GET['match_id'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_status' => 'cancelled', 'court_no' => ''));
            }
      		break;
    	case "complete_match":
        	if($_SESSION['user_name'] != ""){
            	$mysql->where(array('match_id'=>$_GET['match_id'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_status' => 'completed', 'completed_date' => date("Y-m-d H:i:s",strtotime("+7 hours"))));
				$mysql->where(array('batch_id' => $_SESSION['batch_id'], 'match_id'=>$_GET['match_id'], 'queue_status'=>'in match'))->delete('queue');
            }
            break;
        case "move_match_up":
        	if($_SESSION['user_name'] != ""){
                if($_GET['match_no']>0){
                    $mysql->where(array('match_no'=>$_GET['match_no'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_no' => '9999'));
                    $mysql->where(array('match_no'=>$_GET['match_no']-1, 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_no' => $_GET['match_no']));
                    $mysql->where(array('match_no'=>'9999', 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_no' => $_GET['match_no']-1));
                }
            }
            //print "done";
          break;
      
   		case "move_match_down":
            if($_SESSION['user_name'] != ""){
                $mysql->where(array('match_no'=>$_GET['match_no'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_no' => '9999'));
                $mysql->where(array('match_no'=>$_GET['match_no']+1, 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_no' => $_GET['match_no']));
                $mysql->where(array('match_no'=>'9999', 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_no' => $_GET['match_no']+1));
                //print "done";
            }
      		break;
        case "set_court":
            $mysql->where(array('batch_id' => $_SESSION['batch_id'], 'court_no' => $_GET['court_no'], 'match_status' => 'playing'))->update('matchq',array('match_status' => 'completed', 'completed_date' => date("Y-m-d H:i:s",strtotime("+7 hours"))));
            $mysql->where(array('match_no'=>$_GET['match_no'], 'batch_id' => $_SESSION['batch_id'], 'match_status' => 'in queue'))->update('matchq',array('court_no' => $_GET['court_no'], 'match_status' => 'playing', 'start_playing_date' => date("Y-m-d H:i:s",strtotime("+7 hours"))));
            $mysql->where(array('match_no'=>$_GET['match_no'], 'batch_id' => $_SESSION['batch_id'], 'match_status' => 'playing'))->update('matchq',array('court_no' => $_GET['court_no']));
            $mysql->where(array('match_no'=>$_GET['match_no'], 'batch_id' => $_SESSION['batch_id'], 'match_status' => 'completed'))->update('matchq',array('court_no' => $_GET['court_no']));
            //print "done";
          	break;
       case "change_court":
          $mysql->where(array('match_no'=>$_GET['match_no'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('court_no' => ''));
          print $mysql->getlastquery();
          break;
  		case "request_match":
        	if($_SESSION['user_name'] != ""){
      			$mysql->where(array('match_id'=>$_GET['match_id'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('is_request' => $_GET['is_request']));
            }
      		break;
		case "set_shuttle":
        	if($_SESSION['user_name'] != ""){
      			$mysql->where(array('match_id'=>$_GET['match_id'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('no_of_shuttle' => $_GET['no_of_shuttle']));
            }
      		break;
		case "save_score":
			if($_SESSION['user_name'] != "" || $isPreview == "1"){
				$tempUser = "player";
				if($_SESSION['user_name'] != ""){
					$tempUser = $_SESSION['user_name'];
				}

      			$mysql->where(array('match_id'=>$_GET['match_id'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('score_set_11' => $_GET['score11'],'score_set_12' => $_GET['score12'],'score_set_21' => $_GET['score21'],'score_set_22' => $_GET['score22'],'score_set_31' => $_GET['score31'],'score_set_32' => $_GET['score32'], 'modified_by' => $_SESSION['user_name']));
            }
			break;
		case "get_score":
        	if($_SESSION['user_name'] != "" || $isPreview == "1"){
      			$row = $mysql->query("select a.match_id, b.member_name as player11, c.member_name as player12, d.member_name as player21, e.member_name as player22, a.score_set_11, a.score_set_12, a.score_set_21, a.score_set_22, a.score_set_31, a.score_set_32 from matchq a left join member b on a.member_id_11=b.member_id left join member c on a.member_id_12=c.member_id left join member d on a.member_id_21=d.member_id left join member e on a.member_id_22=e.member_id where a.match_id='".$_GET['match_id']."' and a.batch_id='".$_SESSION['batch_id']."'", true);

				for ($i=0; $i<count($row); $i++){
					print "<table width='100%' class='blueTable'>\n";
					print "	<thead>\n";
					print "		<th width='42%' align='center'>".$row[$i]['player11']." + ".$row[$i]['player12']."</th>\n";
					print "		<th width='16%' align='center'>set</td>\n";
					print "		<th width='42%' align='center'>".$row[$i]['player21']." + ".$row[$i]['player22']."</th>\n";
					print "	</thead>\n";
					print "	<tr>\n";
					print "		<td align='center'><input type='text' size='5' id='score11_".$_GET['match_id']."' value='".$row[$i]['score_set_11']."' onfocus='this.select()' onkeypress='keyEnter(\"score12_".$_GET['match_id']."\")'></td>\n";
					print "		<td align='center'>1</td>\n";
					print "		<td align='center'><input type='text' size='5' id='score12_".$_GET['match_id']."' value='".$row[$i]['score_set_12']."' onfocus='this.select()' onkeypress='keyEnter(\"score21_".$_GET['match_id']."\")'></td>\n";
					print "	</tr>\n";
					print "	<tr>\n";
					print "		<td align='center'><input type='text' size='5' id='score21_".$_GET['match_id']."' value='".$row[$i]['score_set_21']."' onfocus='this.select()' onkeypress='keyEnter(\"score22_".$_GET['match_id']."\")'></td>\n";
					print "		<td align='center'>2</td>\n";
					print "		<td align='center'><input type='text' size='5' id='score22_".$_GET['match_id']."' value='".$row[$i]['score_set_22']."' onfocus='this.select()' onkeypress='keyEnter(\"score31_".$_GET['match_id']."\")'></td>\n";
					print "	</tr>\n";
					print "	<tr>\n";
					print "		<td align='center'><input type='text' size='5' id='score31_".$_GET['match_id']."' value='".$row[$i]['score_set_31']."' onfocus='this.select()' onkeypress='keyEnter(\"score32_".$_GET['match_id']."\")'></td>\n";
					print "		<td align='center'>3</td>\n";
					print "		<td align='center'><input type='text' size='5' id='score32_".$_GET['match_id']."' value='".$row[$i]['score_set_32']."' onfocus='this.select()' onkeypress='keyEnter(\"submit\")'></td>\n";
					print "	</tr>\n";
					print "	<tr>\n";
					print "		<td align='center' colspan='3'><a href='javascript:saveScore(\"".$_GET['match_id']."\", \"".$_SESSION['club_id']."\", \"".$isPreview."\");' class='myButtonGreen' id='saveScoreButton'>&nbsp;&nbsp;Save&nbsp;&nbsp;</a>&nbsp;<a href='javascript:$(\"#scoreEdit\").dialog(\"close\");' class='myButtonOrange'>Cancel</a>\n";
					print "	</tr>\n";
					print "</table>";
				}
            }
      		break;
		case "get_match":
            $matchID = $_GET['match_id'];
            $isPreview = $_GET['is_preview'];
			$lastMatchNo = "";
            $nextMatch = false;
//print $_SESSION['batch_no'];

            print "<h2>ตารางประกบคู่&nbsp;&nbsp;: ".$_SESSION['club_name']."&nbsp;&nbsp;".substr($_SESSION['batch_no'],6,2)."/".substr($_SESSION['batch_no'],4,2)."/".substr($_SESSION['batch_no'],0,4);
            if ($isPreview != "1"){
                print "&nbsp;&nbsp;[<a href='index.php?club_id=".$_SESSION['club_id']."' target='_blank'>User View-ลิ้งค์สำหรับสมาชิกเปิดดูคิวของตัวเอง</a>]\n";
            }else{
				
				if($_SESSION['batch_status'] == "Completed"){
					print "<br/><span class='txtSmallGreen'>*** Event นี้จบแล้ว!! ระบบจะหยุดการ Refresh อัตโนมัติ คุณสามารถ Reload หน้านี้ด้วยตัวเอง ***</span>\n";
					print "<script>isEventCompleted=true;</script>";
				}else{
					$tempMem = "";
					$tempMax = 0;
					print "<br/><span class='txtSmallRed'>ข้อมูล ณ เวลา ".date('H:i:s',strtotime("+7 hours"))." น.</span>\n";
					//print "<span id='autoSec' class='txtSmallMemLightGray'>(Auto Refresh in 15 seconds)</span>\n";
					print "<br/><span class='txtSmallMemGray'>สมาชิกที่มาแล้ว : </span>";
					$row = $mysql->query("select b.member_name, max(a.created_date) from queue a inner join member b on a.member_id=b.member_id where b.club_id='".$_SESSION['club_id']."' and a.batch_id='".$_SESSION['batch_id']."' group by b.member_name order by b.member_name", true);

					for ($i=0; $i<count($row); $i++){
						$tempMem .= $row[$i]['member_name'].", ";
						$tempMax = $tempMax + 1;
						if($tempMax == 20){
							$tempMem .= "<br/>";
							$tempMax = 0;
						}
					}
					print "<span class='txtSmallMemLightGray'>";
					print rtrim($tempMem,", ");
					print "</span>\n";
					
				}
			}
            print "</h2>"; 
            if ($isPreview != "1"){
                $row = $mysql->query("select batch_id, date_format(batch_no,'%Y%m%d%H%i %a') as batch_no, batch_status from batch where club_id='".$_SESSION['club_id']."' and batch_status != 'Cancelled' and created_date >= date_add(now(), interval -20 month) order by batch_no desc limit 0,20", true);
                print "<div>Event No. <select id='batch_id' onchange='changeBatch(this);'>\n";
                for ($i=0; $i<count($row); $i++){
                    $itemSelected = "";
                    if($_SESSION['batch_id']==$row[$i]['batch_id']){
                        $itemSelected = "selected";
                    }
                    print "<option value='".$row[$i]['batch_id']."' ".$itemSelected.">".$row[$i]['batch_no']." - [".$row[$i]['batch_status']."]</option>\n";
                }
                print "</select> (<== เลือก Event No. อื่น เพื่อดูข้อมูลย้อนหลัง)\n";

                print "<a href='javascript:createBatch();' class='myButtonGreen'>&nbsp;&nbsp;New Event&nbsp;&nbsp;</a>&nbsp;";
                if($_SESSION['batch_status'] == "Open"){
                    print "<a href='javascript:completeBatch(\"".$_SESSION['batch_id']."\");' class='myButtonBlue'>Complete Event</a>&nbsp;";
                    print "<a href='javascript:cancelBatch(\"".$_SESSION['batch_id']."\");' class='myButtonOrange'>Cancel Event</a>&nbsp;";
                }
                print "</div><br/>";
            }
			
			$max_empty_match = 3;
			$empty_match = "";

			if($_SESSION['batch_id'] != ""){
				$row = $mysql->query("select a.match_id, x.match_no, a.match_no as act_match_no, a.member_id_11, a.member_id_12, a.member_id_21, a.member_id_22, b.member_name as m_11,  
				c.member_name as m_12,  d.member_name as m_21,  e.member_name as m_22, b2.class_text_color as color_11, c2.class_text_color as color_12, d2.class_text_color as color_21, 
				e2.class_text_color as color_22, a.match_status, a.court_no, a.is_request, case when a.match_status='completed' then TIMESTAMPDIFF(MINUTE,ifnull(a.start_playing_date,a.created_date),a.completed_date) when a.match_status='playing' then TIMESTAMPDIFF(MINUTE,ifnull(a.start_playing_date,a.created_date), now()) end as match_time, a.no_of_shuttle,
				score_set_11, score_set_12, score_set_21, score_set_22, score_set_31, score_set_32
				from matchseq x 
				left join matchq a on x.match_no=a.match_no and a.match_status in('in queue','completed','playing', 'cancelled','auto') and a.batch_id='".$_SESSION['batch_id']."'
				left join member b on a.member_id_11=b.member_id left join member c on a.member_id_12=c.member_id and b.club_id='".$_SESSION['club_id']."'
				left join member d on a.member_id_21=d.member_id left join member e on a.member_id_22=e.member_id and d.club_id='".$_SESSION['club_id']."'
				left join class b2 on b.class_id=b2.class_id 
				left join class c2 on c.class_id=c2.class_id left join class d2 on d.class_id=d2.class_id left join class e2 on e.class_id=e2.class_id 
				order by x.match_no", true);

				$rowMax = $mysql->query("select max(match_no) as last_match_no from matchq where batch_id='".$_SESSION['batch_id']."' and match_status in('in queue','completed','playing', 'cancelled','auto') ",true);

				for($i=0; $i<100; $i++){ 
					  $suffix = "";
					  $suffix_button = "";
					  $text_color_11 = "";
					  $text_color_12 = "";
					  $text_color_21 = "";
					  $text_color_22 = "";

					  if($row[$i]['match_id']!="" && $row[$i]['match_status']!="cancelled"){
						$suffix="_disabled";
						if($row[$i]['match_status']=="in queue" ){$suffix.="_inqueue";}
						if($row[$i]['match_status']=="auto" ){$suffix.="_pending";}
						$match_txt = "@match_".$row[$i]['match_no']."_11_".$row[$i]['member_id_11']."@match_".$row[$i]['match_no']."_12_".$row[$i]['member_id_12']."@match_".$row[$i]['match_no']."_21_".$row[$i]['member_id_21']."@match_".$row[$i]['match_no']."_22_".$row[$i]['member_id_22'];
					  }else{
						$match_txt = "";
					  }

					  if(($matchID!="" && $matchID==$row[$i]['match_id']) || ($row[$i]['match_status']=="completed")){
						$text_color_11="black";
						$text_color_12="black";
						$text_color_21="black";
						$text_color_22="black";
					  }else{
						 if($row[$i]['match_status']=="cancelled"){
							$text_color_11="#E3E3E3";
							$text_color_12="#E3E3E3";
							$text_color_21="#E3E3E3";
							$text_color_22="#E3E3E3"; 
						}else{
							$text_color_11=($row[$i]['color_11']=='#FFFFFF'?'#000000':$row[$i]['color_11']);
							$text_color_12=($row[$i]['color_12']=='#FFFFFF'?'#000000':$row[$i]['color_12']);
							$text_color_21=($row[$i]['color_21']=='#FFFFFF'?'#000000':$row[$i]['color_21']);
							$text_color_22=($row[$i]['color_22']=='#FFFFFF'?'#000000':$row[$i]['color_22']);
							$suffix_button="_disabled";
						 }
					  }

					print "<div class='matchNo' title='ลากไปวางในแมทช์ที่ต้องการ เพื่อย้ายขึ้นหรือลงได้' id='matchNo_".$i."' onclick='$(this).zIndex(50000)'>".($i+1).".</div>\n";
						  
					if ($isPreview == "1" && ($row[$i]['match_status'] =="cancelled"|| $row[$i]['match_status'] =="auto")){
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_11'><font color='".$text_color_11."'></font></div>\n";
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_12'><font color='".$text_color_12."'></font></div>\n";
					}else{
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_11'><font color='".$text_color_11."'>".$row[$i]['m_11']."</font></div>\n";
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_12'><font color='".$text_color_12."'>".$row[$i]['m_12']."</font></div>\n";
					}
				  
					if($row[$i]['match_status']!="playing"){
					  $blinkTxt="";
					  $grayScale="filter: grayscale(100%);";
					}else{
					  $blinkTxt="blink";
					  $grayScale="";
					}
				  
					/*if ($isPreview == "1"){
						if($row[$i]['court_no'] =="" || $row[$i]['court_no'] =="0"){
							print "<div class='iconCourt'>\n";
							print "<button class='dropbtn'>&nbsp;สนาม&nbsp;</button>\n";
							print "</div>\n";
						}else{
							print "<div class='iconCourt'><span class='".$blinkTxt."'>&nbsp;<img src='images/court_no_".$row[$i]['court_no'].".png' width='33px' height='30px' style='".$grayScale."'>&nbsp;</span></div>\n";
						}
					}else{*/
						if($row[$i]['court_no'] =="" || $row[$i]['court_no'] =="0"){
							print "<div class='iconCourt'>\n";
							if($row[$i]['match_status'] =="cancelled" || $row[$i]['match_status'] =="auto"){
								if($row[$i]['match_status'] =="auto"){
										print "<button onclick='' class='dropbtn'>&nbsp;&nbsp;Auto&nbsp;&nbsp;</button>\n";
									}else{
										print "<button onclick='' class='dropbtn'>&nbsp;ยกเลิก&nbsp;</button>\n";
									}
							}else{
								if($row[$i]['match_id'] != ""){
									print "<button onclick='showCourtNo(".$row[$i]['match_no'].")' class='dropbtn'>&nbsp;สนาม&nbsp;&nbsp;</button>\n";
								}else{						
									print "<button onclick='' class='dropbtn'>&nbsp;สนาม&nbsp;&nbsp;</button>\n";
								}
							}
							print "<div id='courtDropDown".$row[$i]['match_no']."' class='dropdown-content' style='width:42px'>\n";
							for($jjj=1; $jjj<13; $jjj++){
								print "  <a href='javascript:setCourt(\"".$row[$i]['match_no']."\",".$jjj.",\"".$_SESSION['is_auto_pilot']."\");'>".$jjj."</a>\n";
							}
							print "</div>\n";

							print "</div>\n";
						}else{
							if($row[$i]['match_status'] =="playing"){
								print "<div class='iconCourt'><span class='".$blinkTxt."'>&nbsp;&nbsp;<img src='images/court_no_".$row[$i]['court_no'].".png' width='33px' height='30px' style='".$grayScale."' onclick='changeCourt(\"".$row[$i]['match_no']."\")'>&nbsp;</span></div>\n";
							}else{
								print "<div class='iconCourt'><span class='".$blinkTxt."'>&nbsp;&nbsp;<img src='images/court_no_".$row[$i]['court_no'].".png' width='33px' height='30px' style='".$grayScale."' onclick=''>&nbsp;</span></div>\n";
							}
						}
					//}
				  
					if ($isPreview == "1" && ($row[$i]['match_status'] =="cancelled"|| $row[$i]['match_status'] =="auto")){
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_21'><font color='".$text_color_21."'></font></div>\n";
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_22'><font color='".$text_color_22."'></font></div>\n";
					}else{
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_21'><font color='".$text_color_21."'>".$row[$i]['m_21']."</font></div>\n";
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_22'><font color='".$text_color_22."'>".$row[$i]['m_22']."</font></div>\n"; 
					}

					//add or delete row
					if($_SESSION['batch_status'] != 'Completed'){
						if ($isPreview != "1"){
							if($row[$i]['match_id'] != ""){
								print "<div class='iconButton'><a href='javascript: addRow($i);'><img src='images/icon_add.png' width='18px' height='18px' title='เพิ่มแถวด้านบน'></a></div>";
							}else{
								print "<div class='iconButton'><a href='javascript: delRow($i);'><img src='images/icon_del.png' width='12px' height='12px' title='ลบแถวนี้'></a></div>";
							}
						}
					}
					
					if ($isPreview != "1"){
						if($row[$i]['match_status'] == "auto"){
							print "<div class='iconButton' id='saveIcon".$i."' style='display:block'>";
						}else{
							print "<div class='iconButton' id='saveIcon".$i."' style='display:none'>";
						}

						if($row[$i]['match_status'] != "completed"){
							//print "<img src='images/icon_ok".$suffix_button.".png' height='20px' width='20px' id='imgSave_".($i)."' onclick='saveMatch(this)' title='Save'/>";
							print "<img src='images/icon_save_new.png' height='28px' width='20px' id='imgSave_".($i)."' onclick='highlightObj(this.id);saveMatch(this,\"".$row[$i]['match_status']."\",\"".$_SESSION['is_auto_pilot']."\")' title='Save'/>";
						}else{
							print "<img src='images/icon_save_new.png' height='28px' width='20px' style='filter: grayscale(100%);opacity: 0.1;'>\n";
						}
						print "</div>\n";


					  if($row[$i]['match_status'] != 'completed' && $row[$i]['match_status'] != 'cancelled' && $row[$i]['match_status'] != 'auto' && $row[$i]['match_id'] != ''){
						  //print "<div class='iconButton'><img src='images/icon_edit".$suffix_button.".png' height='20px' width='20px' title='Edit' onClick='editMatch(\"".$row[$i]['match_id']."\")'/></div>\n";
						  //print "<div class='iconButton'><img src='images/icon_cancel".$suffix_button.".png' height='20px' width='20px' title='Cancel' onClick='cancelMatch(\"".$row[$i]['match_id']."\")'/></div>\n";	 
					
						 print "<div class='iconButton' onclick='highlightObj(this.id);' id='divCM".$row[$i]['match_no']."'><a href='javascript:completeMatch(\"".$row[$i]['match_no']."\",\"".$row[$i]['match_id']."\",\"".$_SESSION['is_auto_pilot']."\")' title='Complete match'><img src='images/icon_ok_new.png' height='28px' width='20px' id='icoComplete".$row[$i]['match_no']."'></a></div>\n";
					/* ---Move Up/Down
						 print "<div class='iconButton' onclick='highlightObj(this.id);' id='divMup".$row[$i]['match_no']."'><img src='images/icon_up_new.png'  height='28px' width='20px' title='เลื่อนแมทช์ขึ้น' onclick='moveMatchUp(\"".$row[$i]['match_no']."\")'>\n</div>";
						 print "<div class='iconButton' onclick='highlightObj(this.id);' id='divMdw".$row[$i]['match_no']."'><img src='images/icon_down_new.png'  height='28px' width='20px' title='เลื่อนแมทช์ลง' onclick='moveMatchDown(\"".$row[$i]['match_no']."\")'>\n</div>";
					 ---*/
					  }else{
						print "<div class='iconButton'><img src='images/icon_ok_new.png' height='28px' width='20px' style='filter: grayscale(100%);opacity: 0.1;'></div>\n";
					  }

					  if(($row[$i]['match_status'] != 'cancelled' && $row[$i]['match_id'] != '' && $row[$i]['is_request']!="Y" && $_SESSION['batch_status'] != 'Completed') || ($row[$i]['match_status'] == 'auto' && $_SESSION['batch_status'] != 'completed') || ($row[$i]['is_request']=="Y" && $row[$i]['match_status'] == 'completed' && $_SESSION['batch_status'] != 'Completed')){
							print "<div class='iconButton' onclick='highlightObj(this.id);' id='divCC".$row[$i]['match_no']."'><img src='images/icon_cancel_new.png' width='20px' height='28px' title='Cancel' onClick='cancelMatch(\"".$row[$i]['match_id']."\",\"".$row[$i]['match_status']."\")'/></div>\n";	
					  }else{
							print "<div class='iconButton'><img src='images/icon_cancel_new.png' height='28px' width='20px' style='filter: grayscale(100%);opacity: 0.1;'></div>\n";
					  }
					  if($row[$i]['match_status'] != 'cancelled' && $row[$i]['match_status'] != 'auto' && $row[$i]['match_id'] != ''){
						  if($row[$i]['is_request']=="Y"){
								print "<div class='iconRequest'><img src='images/icon_request.png' height='10px' width='30px' title='Request' onClick='requestMatch(\"".$row[$i]['match_id']."\",\"N\")'/></div>\n";
						  }else{
								print "<div class='iconRequest'><img src='images/icon_request.png' height='10px' width='30px' title='Request'  style='opacity: 0.5; filter: grayscale(100%); alpha(opacity=30);' onClick='requestMatch(\"".$row[$i]['match_id']."\",\"Y\")'/></div>\n";	
						  }
					  }else{
							print "<div class='iconRequest'><img src='images/icon_request.png' height='10px' width='30px' style='filter: grayscale(100%);opacity: 0.1;'></div>\n";	
					  }

					  if($row[$i]['match_id'] != "" && $row[$i]['match_status'] == "cancelled"){
							print "<div class='iconButton' onclick='highlightObj(this.id);'  id='divDelM".$row[$i]['match_no']."'><img src='images/trash_new.png' height='28px' width='20px' title='Delete' onClick='deleteMatch(\"".$row[$i]['match_id']."\")'/></div>\n";	
					  }else{
							print "<div class='iconButton'><img src='images/trash_new.png' height='28px' width='20px' style='filter: grayscale(100%);opacity: 0.1;'></div>\n";
					  }

					  if($row[$i]['match_id'] != "" && $row[$i]['match_status'] == "auto"){
							print "<div class='iconButton' onclick='highlightObj(this.id);'  id='divRefreshAuto".$row[$i]['match_no']."'><img src='images/icon_refresh.png' height='28px' width='20px' title='Refresh Auto' onClick='getAutoMatch()'/></div>\n";	
					  }else{
							print "<div class='iconButton'><img src='images/icon_refresh.png' height='28px' width='20px' style='filter: grayscale(100%);opacity: 0.1;'></div>\n";
					  }

					  
						if($row[$i]['match_id'] != "" && $row[$i]['match_status'] != "auto"){
							//print "<img src='images/shuttlecock_icon.png' width='20px' height='20px'>";
							print "<div class='iconShuttle' style='width:35px;'>\n";
							print "<button onclick='showShuttle(\"".$row[$i]['match_id']."\")' class='dropbtn' style='width:35px;background-image:url(\"images/bg_shuttle.png\");background-repeat: no-repeat;background-color:white;font-size:13px;font-weight: bold;'>".$row[$i]['no_of_shuttle']."&nbsp;&nbsp;</button>\n";

							//shuttle cock selection
							print "<div id='shuttleDropDown".$row[$i]['match_id']."' class='dropdown-content' style='font-size:12px'>";

							for($kkk=0; $kkk<9; $kkk++){
								$selectedItem = "";
								if($kkk==$row[$i]['no_of_shuttle']){
									$selectedItem = "style='background-color: lightgray;'";
								}
								print "<a href='javascript:setShuttle(\"".$row[$i]['match_id']."\",\"".$kkk."\")' ".$selectedItem.">".$kkk." ลูก</a>\n";
							}
							print "</div>";
							print "</div>";
						}
						print "<input type='hidden' id='match_txt_".($i)."' value='".$match_txt."' size='250'/>\n";
					}

					//score
					if($row[$i]['match_id'] != "" && $row[$i]['match_status'] == "completed"){
						$winnerA = "";
						$winnerB = "";

						//print "&nbsp;&nbsp;";
						print "<div class='scoreBox' id='scoreView_".$row[$i]['match_id']."' onclick='getScore(\"".$row[$i]['match_id']."\", \"".$_SESSION['club_id']."\", \"".$isPreview."\");'>";
						if($row[$i]['score_set_11']>$row[$i]['score_set_12']){
							print "<strong>".$row[$i]['score_set_11']."</strong>-".$row[$i]['score_set_12'].", ";
						}else{
							print $row[$i]['score_set_11']."-<strong>".$row[$i]['score_set_12']."</strong>, ";
						}

						if($row[$i]['score_set_21']>$row[$i]['score_set_22']){
							print "<strong>".$row[$i]['score_set_21']."</strong>-".$row[$i]['score_set_22'];
						}else{
							print $row[$i]['score_set_21']."-<strong>".$row[$i]['score_set_22']."</strong>";
						}
						
						if($row[$i]['score_set_31']>0 || $row[$i]['score_set_32']>0){
							if($row[$i]['score_set_31']>$row[$i]['score_set_32']){
								print ", <strong>".$row[$i]['score_set_31']."</strong>-".$row[$i]['score_set_32'];
							}else{
								print ", ".$row[$i]['score_set_31']."-<strong>".$row[$i]['score_set_32']."</strong>";
							}
						}
						print "</div>";
					}

					

					if ($isPreview == "1"){
					  switch ($row[$i]['match_status']){
						case "playing":
							print "<div class='statustxtBlue'><span class='".$blinkTxt."'><font color=red><strong>".$row[$i]['no_of_shuttle']."</strong></font><img src='images/icon_shuttle.png' width='10px' height='15px'>&nbsp;&nbsp;กำลังเล่น&nbsp;[&nbsp;".$row[$i]['match_time']."'&nbsp;]</span></div>";
							break;
						case "in queue":
							print "<div class='statustxtGray'><span class='".$blinkTxt."'>รอเล่น</span></div>";
						  break;
						case "completed":
							print "<div class='statustxtGray'><span class='".$blinkTxt."'><font color=red><strong>".$row[$i]['no_of_shuttle']."</strong></font><img src='images/icon_shuttle.png' width='10px' height='15px'>&nbsp;&nbsp;จบแมทช์ &nbsp;[&nbsp;".$row[$i]['match_time']."'&nbsp;]</span></div>";
							break;
					  }
					}
					print "<div style='clear:both' id='divMatch_".$row[$i]['match_no']."'></div>\n";

					if($empty_match=="" && $row[$i]['match_id']==""){
						$empty_match = $i;
					}


					if(($rowMax[0]['last_match_no']+$max_empty_match)==$i){
						break;
					}
				}

				if($rowMax[0]['last_match_no'] == ""){
					print "<script>lastMatchNo = 0;</script>\n";
				}else{
					print "<script>lastMatchNo = ".$empty_match.";</script>\n";
				}
			



				for($jj=0;$jj<(100-$rowMax[0]['last_match_no']);$jj++){//print empty line for scrollable
					print "<br/>";
				}

				print "<br/";
				print "<br/";
				print "<br/";
			}
			break;
		case "add_row":
			$mysql->query("update matchq set match_no=match_no+1 where match_no >='".$_GET['match_no']."' and batch_id='".$_SESSION['batch_id']."'", false);
			break;
		case "del_row":
			$mysql->query("update matchq set match_no=match_no-1 where match_no >='".$_GET['match_no']."' and batch_id='".$_SESSION['batch_id']."'", false);
			break;
		case "swap_match":
			$mysql->query("update matchq set match_no=999 where match_no ='".$_GET['source_match']."' and batch_id='".$_SESSION['batch_id']."'", false);
			$mysql->query("update matchq set match_no='".$_GET['source_match']."' where match_no ='".$_GET['target_match']."' and batch_id='".$_SESSION['batch_id']."'", false);
			$mysql->query("update matchq set match_no='".$_GET['target_match']."'  where match_no =999 and batch_id='".$_SESSION['batch_id']."'", false);
			break;
		case "get_score_match":
			if($_SESSION['user_name'] != "" || $isPreview == "1"){
      			$row = $mysql->query("select a.match_id, b.member_name as player11, c.member_name as player12, d.member_name as player21, e.member_name as player22, a.score_set_11, a.score_set_12, a.score_set_21, a.score_set_22, a.score_set_31, a.score_set_32 from matchq a left join member b on a.member_id_11=b.member_id left join member c on a.member_id_12=c.member_id left join member d on a.member_id_21=d.member_id left join member e on a.member_id_22=e.member_id where a.match_id='".$_GET['match_id']."' and a.batch_id='".$_SESSION['batch_id']."'", true);

				for ($i=0; $i<count($row); $i++){
					if($row[$i]['score_set_11']>$row[$i]['score_set_12']){
						print "<strong>".$row[$i]['score_set_11']."</strong>-".$row[$i]['score_set_12'].", ";
					}else{
						print $row[$i]['score_set_11']."-<strong>".$row[$i]['score_set_12']."</strong>, ";
					}

					if($row[$i]['score_set_21']>$row[$i]['score_set_22']){
						print "<strong>".$row[$i]['score_set_21']."</strong>-".$row[$i]['score_set_22'];
					}else{
						print $row[$i]['score_set_21']."-<strong>".$row[$i]['score_set_22']."</strong>";
					}
					
					if($row[$i]['score_set_31']>0 || $row[$i]['score_set_32']>0){
						if($row[$i]['score_set_31']>$row[$i]['score_set_32']){
							print ", <strong>".$row[$i]['score_set_31']."</strong>-".$row[$i]['score_set_32'];
						}else{
							print ", ".$row[$i]['score_set_31']."-<strong>".$row[$i]['score_set_32']."</strong>";
						}
					}
				}
			}
			break;
	}
?>
