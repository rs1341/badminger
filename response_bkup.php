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
			$_SESSION['club_id'] = $batchArr[0]['club_id'];
			$_SESSION['club_name'] = $batchArr[0]['club_name'];
			$_SESSION['is_auto_pilot'] = $batchArr[0]['is_auto_pilot'];
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

                        if($memberArr[11] != ""){
                            $row = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and (member_id_12='".$memberArr[11]."' or member_id_21='".$memberArr[11]."' or  member_id_22='".$memberArr[11]."')", true);
                            if(count($row)>0){
                                print "duplicate member";
                                exit();
                            }
                        }

                        if($memberArr[12] != ""){
                            $row = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and (member_id_11='".$memberArr[12]."' or member_id_21='".$memberArr[12]."' or  member_id_22='".$memberArr[12]."')", true);
                            //print $mysql->last_query();
                            if(count($row)>0){
                                print "duplicate member";
                                exit();
                            }
                        }

                        if($memberArr[21] != ""){
                            $row = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and (member_id_11='".$memberArr[21]."' or member_id_12='".$memberArr[21]."' or  member_id_22='".$memberArr[21]."')", true);
                            if(count($row)>0){
                                print "duplicate member";
                                exit();
                            }
                        }

                        if($memberArr[22] != ""){
                            $row = $mysql->query("select * from matchq where match_no='".$matchNo."' and batch_id='".$_SESSION['batch_id']."' and (member_id_11='".$memberArr[22]."' or member_id_12='".$memberArr[22]."' or  member_id_21='".$memberArr[22]."')", true);
                            if(count($row)>0){
                                print "duplicate member";
                                exit();
                            }
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

                        if($matchNo != ""){
                            $mysql->where(array('match_no'=>$matchNo, 'batch_id' => $_SESSION['batch_id']))->delete('matchq');
                        }

                        $matchID = uniqid();

                        $mysql->insert('matchq', array('match_id' => $matchID, 'match_no' => $matchNo, 'member_id_11' => $memberArr[11], 'member_id_12' => $memberArr[12], 'member_id_21' => $memberArr[21], 'member_id_22' => $memberArr[22],'match_status' => 'in queue', 'batch_id' => $_SESSION['batch_id'], 'created_by' => $_SESSION['user_name']));

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
        case "get_history":
        	if($_SESSION['user_name'] != ""){
                  $memberID = $_GET['member_id'];
                  $memberName = $_GET['member_name'];
                  $row = $mysql->query("select a.match_no, b.member_name as m_11,  c.member_name as m_12,  d.member_name as m_21,  e.member_name as m_22, b.member_id as id_11,  
                  c.member_id as id_12,  d.member_id as id_21,  e.member_id as id_22, b2.class_color as color_11, c2.class_color as color_12, d2.class_color as color_21, e2.class_color as color_22, 
                  match_status, date_format(case when a.match_status='completed' then a.completed_date when a.match_status='playing' then a.start_playing_date when a.match_status='in queue' then a.created_date end,'%H:%i') as completed_time from matchq a left join member b on a.member_id_11=b.member_id left join member c on a.member_id_12=c.member_id left join member d on a.member_id_21=d.member_id 
                  left join member e on a.member_id_22=e.member_id left join class b2 on b.class_id=b2.class_id left join class c2 on c.class_id=c2.class_id left join class d2 on d.class_id=d2.class_id 
                  left join class e2 on e.class_id=e2.class_id where a.match_status in('in queue','completed','playing')  and a.batch_id='".$_SESSION['batch_id']."' and (a.member_id_11='".$memberID."' or a.member_id_12='".$memberID."' or a.member_id_21='".$memberID."' or a.member_id_22='".$memberID."') order by a.match_no", true);

                  print "<div class='header_h1'>ประวัติการเล่นของ: ".$memberName."</div>";
                  print "<table class='tblmatch' width='100%'>";
                  print "<thead><tr>";
                  print "<th width='10%'>ลำดับที่</th>";
                  print "<th width='25%'>สถานะ</th>";
                  print "<th width='30%'>คู่ที่ 1</th>";
                  print "<th width='5%'></th>";
                  print "<th width='30%'>คู่ที่ 2</th>";
                  print "</tr></thead>";
                  for($i=0; $i<count($row); $i++){
                      print "<tr>";
                      print "<td  align='center'>";
                      print ($i+1).". ";
                      print "</td>";
                      print "<td>";
                      print $row[$i]['match_status']." [".$row[$i]['completed_time']."]";
                      print "</td>";
                      print "<td align='center'>";
                      if($row[$i]['id_11']==$memberID){print "<font color='red'>";}else{print "<font color='gray'>";} print $row[$i]['m_11']."</font>";
                      print " + ";
                      if($row[$i]['id_12']==$memberID){print "<font color='red'>";}else{print "<font color='gray'>";} print $row[$i]['m_12']."</font>";
                      print "</td>";
                      print "<td align='center'>";
                      print " vs ";
                      print "</td>";
                      print "<td align='center'>";
                      if($row[$i]['id_21']==$memberID){print "<font color='red'>";}else{print "<font color='gray'>";} print $row[$i]['m_21']."</font>";
                      print " + ";
                      if($row[$i]['id_22']==$memberID){print "<font color='red'>";}else{print "<font color='gray'>";} print $row[$i]['m_22']."</font>";
                      print "</td>";
                      print "</tr>";
                  }
                  print "</table>";

                  if(count($row)==0){
                      print "---- No Record!----";
                  }
            }
        	break;
		case "get_waiting":
        	if($_SESSION['batch_status']!="Completed"){
				getWaiting($mysql, $_SESSION['batch_id']);
            }
			break;
		case "get_home_back":
        	if($_SESSION['user_name'] != ""){
                $row = $mysql->query("select distinct a.member_id, b.member_name, (select count(z.match_id) from matchq z where z.batch_id='".$_SESSION['batch_id']."' and (a.member_id=z.member_id_11 or a.member_id=z.member_id_12 or a.member_id=z.member_id_21 or a.member_id=z.member_id_22) and z.match_status in('in queue','completed','playing')) as match_played from (
                    select member_id_11 as member_id from matchq where batch_id='".$_SESSION['batch_id']."' and match_status != 'cancelled' union 
                    select member_id_12 as member_id from matchq where batch_id='".$_SESSION['batch_id']."' and match_status != 'cancelled' union
                    select member_id_21 as member_id from matchq where batch_id='".$_SESSION['batch_id']."' and match_status != 'cancelled' union
                    select member_id_22 as member_id from matchq where batch_id='".$_SESSION['batch_id']."' and match_status != 'cancelled' 
                    ) a inner join member b on a.member_id=b.member_id where a.member_id not in(select member_id from queue where batch_id='".$_SESSION['batch_id']."') order by CONVERT(b.member_name USING tis620) ASC", true);
                //print $mysql->last_query();
                print "<div class='header_h1'>รายชื่อสมาชิกที่กลับบ้านแล้ว</div>";
                if(count($row)>0){
                    print "<font color='blue'>";
                    $kk=0;
                    for($i=0; $i<count($row); $i++){
                      $kk++;
                      print ($i+1).". ";
                      print $row[$i]['member_name']."&nbsp;<font color=gray>[".$row[$i]['match_played']."]</font>&nbsp;&nbsp;&nbsp;";
                      if($kk==5){
                          print "<br/>";
                          $kk=0;
                      }

                    }
                }else{
                    print "<font color='gray'>";
                    print "---- No Record!----";
                }
                print "</font>";
            }
			break;
		case "get_queue":
        	if($_SESSION['user_name'] != ""){
                $jj=0;
                  if(isset($_GET['keyword']) && $_GET['keyword']!=null){
                    $keyword = $_GET['keyword'];
                  }else{
                    $keyword = "";
                  }
                $row = $mysql->query("select distinct c.class_name, b.member_id, b.member_name, (select count(z.match_id) from matchq z where z.batch_id=a.batch_id and (a.member_id=z.member_id_11 or a.member_id=z.member_id_12 or a.member_id=z.member_id_21 or a.member_id=z.member_id_22) and z.match_status in('in queue','completed','playing')) as match_played from queue a inner join member b on a.member_id=b.member_id inner join class c on b.class_id=c.class_id  left join matchq mq on a.match_id=mq.match_id and mq.match_status in('in queue','completed','playing')
                where a.queue_status='in queue' and b.member_status='Active' and a.batch_id='".$_SESSION['batch_id']."' order by ifnull(mq.created_date, a.created_date)", true);
                //print $mysql->last_query();

                print "<div class='header_h1'>สมาชิกที่มาแล้ว <font color='red'>".count($row)."</font> Members : ".$_SESSION['club_name']."</div>";
                print "<div style='clear:both'></div>";


                for($i=0; $i<count($row); $i++){
                    $jj++;
                    print "<div class='square".$row[$i]['class_name']."' id='member_".$row[$i]['member_id']."' title='See History' ondblclick='removeMember(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\");' onClick='showHist(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\",\"".$row[$i]['match_played']."\");'>".$row[$i]['member_name']."&nbsp;[".$row[$i]['match_played']."]</div>";
                    if($jj==5){
                        print "<div style='clear:both'></div>";
                        $jj=0;
                    }
                }

                //print "<div style='clear:both'></div>";
                //print "<br/>";
                //if($jj==5){
                    print "<div style='clear:both'></div>";
                //}
                print "<div class='squaretrash' onclick='highlightObj(this.id);showHomeBack()' id='divBackHome'><img src='images/icon_home.png' width='25' height='25'><br/>กลับบ้าน</div>";
                print "<div class='squaredelete'><img src='images/trash.png' width='25' height='25'><br/>ลบถาวร</div>";
				print "<div class='squaretrash' onclick='setAutoMatch(\"".$_SESSION['is_auto_pilot']."\")'>";
				if($_SESSION['is_auto_pilot']=="Y"){
					print "<img src='images/icon_on.png' width='60' height='24'>\n";
				}else{
					print "<img src='images/icon_off.png' width='60' height='24'>\n";
				}
				print "Auto Pilot";
				print "</div>";
              	print "<div class='squaretrash' valign='bottom'><select id='club_level_auto' onchange='setAutoMatchLevel(\"".$_SESSION['is_auto_pilot']."\")'style='font-size:18px'>";
				
				for($mm=0; $mm<5; $mm++){
					if($mm==$_SESSION['club_auto_level']){
						print "<option value='".$mm."' selected>".$mm."</option>";
					}else{
						print "<option value='".$mm."'>".$mm."</option>";
					}
				}
				print "</select><br/>Auto Scale</div>";		
              	print "<div class='squaredelete'  onclick='highlightObj(this.id);getCondition()' id='divMemberCond'><img src='images/icon_member_setup.png' width='25' height='25'><br/>ตั้งค่า</div>";
                print "<div style='clear:both'></div>";


                //print "<div style='clear:both'></div>";
                //print "<br/>";
                print "<br/>";
                print "<div id='divHistory' style='font-size:12px; color:gray'></div>";


                //print $mysql->last_query();
                print "<div style='clear:both'></div>";
                print "<br/>";
                print "<div id='divWaiting' style='font-size:12px; color:gray'>\n";
                getWaiting($mysql, $_SESSION['batch_id']);
                print "</div>";



                print "<br/>";
                print "<div class='header_h1'>เพิ่มสมาชิกใหม่</div>";
                print "<div style='clear:both'></div>";
                print "ชื่อ: <input type='text' id='vNewMemberName' size='5'>\n";
                print "<select id='vNewMemberClass'>\n";
                $strQuery = "select * from class order by class_id";
                $classArr = $mysql->query($strQuery,true);
                for ($i=0; $i<count($classArr); $i++){
                    print "<option value='".$classArr[$i]['class_id']."'>".$classArr[$i]['class_title']."</option>";
                }
                print "</select>";
                print "&nbsp;<select id='vNewMemberType'><option value='Adult'>ผู้ใหญ่</option><option value='Student'>นักเรียน</option></select>";
                print "&nbsp;<input type='button' value='Add' onclick='javascript:addNewMember()'>";
                print "<div style='clear:both'></div>";
                print "<div style='clear:both'></div>";
                print "<br/>";
                print "<div class='header_h1'>สมาชิกทั้งหมด</div>";
                //print "ชื่อ: <input type='text' id='txtMemberName' size='10' value='".$keyword."'>&nbsp;<input type='button' id='btnSearch' value='Search' onClick='getQueue()'>&nbsp;<input type='button' value='Clear' onClick='clearForm();'>";
                //print "<div style='clear:both'></div>";
                //print "<br/>";

                $jj=0;

                $strQuery = "select a.*, b.class_name from member a inner join class b on a.class_id=b.class_id where a.member_status='Active' and a.club_id='".$_SESSION['club_id']."' and a.member_id not in(select member_id from queue where queue_status='in queue' and batch_id='".$_SESSION['batch_id']."') ";
                if($keyword!=""){
                  $strQuery = $strQuery." and a.member_name like '%".$keyword."%'";
                }
                $strQuery = $strQuery." order by b.class_id, a.member_name";

                      $memberArr = $mysql->query($strQuery,true);
                      for($i=0; $i<count($memberArr); $i++){
                          $jj++;
                          print "<div class='squareFix".$memberArr[$i]['class_name']."' id='m_".$memberArr[$i]['member_id']."' title='Add to queue' onClick='addMember(this.id,\"".$memberArr[$i]['member_name']."\")'><img src='images/add.png' height='15px' width='15px'>&nbsp;".$memberArr[$i]['member_name']."</div>";
                          if($jj==5){
                              print "<div style='clear:both'></div>";
                              $jj=0;
                          }
                      }
        	

            }
			break;
		case "create_batch":
        	if($_SESSION['user_name'] != ""){
                $batch_id=uniqid();
                $batch_no=date("YmdHis");
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
                $mysql->where(array('match_status' => 'playing', 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_status' => 'completed', 'completed_date' => date("Y-m-d H:i:s")));
                $_SESSION['batch_status'] = "Completed";
            }
			break;
		case "add_member":
        	if($_SESSION['user_name'] != ""){
                if($_SESSION['batch_id'] != ""){
                    $mysql->insert('queue', array('queue_id' => uniqid(), 'member_id' => str_replace("m_","",$_GET['member_id']), 'batch_id' => $_SESSION['batch_id'], 'queue_status' => 'in queue', 'created_by' => $_SESSION['user_name']));
                }else{
                    print "Error";
                }
            }
			break;
        case "add_new_member":
        	if($_SESSION['user_name'] != ""){
                $row = $mysql->query("select * from member where member_name='".trim($_GET['member_name'])."' and member_status='Active' and club_id='".$_SESSION['club_id']."'", true);
                if(count($row)==0){
                    $mysql->insert('member', array('member_id' => uniqid(), 'member_name' => trim($_GET['member_name']), 'class_id' => $_GET['class_id'], 'member_status' => 'Active', 'club_id'=> $_SESSION['club_id'], 'member_type' => $_GET['member_type']));
                    print "Success";
                }else{
                    print "Duplicate";
                }
            }
			break;
		case "delete_member":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('member_id'=>$_GET['member_id']))->update('member', array('member_status'=>'Inactive'));
                $mysql->where(array('member_id'=>$_GET['member_id'], 'batch_id' => $_SESSION['batch_id']))->delete('queue');
            }
			break;
		case "remove_member":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('member_id'=>$_GET['member_id'], 'batch_id' => $_SESSION['batch_id']))->delete('queue');
            }
			break;
		case "delete_match":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('match_id'=>$_GET['match_id'], 'batch_id' => $_SESSION['batch_id']))->delete('matchq');
            }
			break;
    	case "cancel_match":
        	if($_SESSION['user_name'] != ""){
                $mysql->query("delete a from queue a inner join matchq b on a.member_id=b.member_id_11 or a.member_id=b.member_id_12 or a.member_id=b.member_id_21 or a.member_id=b.member_id_22 where b.match_id='".$_GET['match_id']."' and a.batch_id=b.batch_id and a.batch_id='".$_SESSION['batch_id']."' and a.queue_status='in queue'");

                $mysql->query("update queue a inner join matchq b on a.member_id=b.member_id_11 or a.member_id=b.member_id_12 or 
            a.member_id=b.member_id_21 or a.member_id=b.member_id_22 set a.queue_status='in queue', a.match_id='' where b.match_id='".$_GET['match_id']."' and a.batch_id=b.batch_id and a.match_id=b.match_id and a.batch_id='".$_SESSION['batch_id']."' and a.queue_status='in match'");

                $mysql->where(array('match_id'=>$_GET['match_id'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_status' => 'cancelled', 'court_no' => ''));
            }
      		break;
    	case "complete_match":
        	if($_SESSION['user_name'] != ""){
            	$mysql->where(array('match_id'=>$_GET['match_id'], 'batch_id' => $_SESSION['batch_id']))->update('matchq',array('match_status' => 'completed', 'completed_date' => date("Y-m-d H:i:s")));
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
            $mysql->where(array('batch_id' => $_SESSION['batch_id'], 'court_no' => $_GET['court_no'], 'match_status' => 'playing'))->update('matchq',array('match_status' => 'completed', 'completed_date' => date("Y-m-d H:i:s")));
            $mysql->where(array('match_no'=>$_GET['match_no'], 'batch_id' => $_SESSION['batch_id'], 'match_status' => 'in queue'))->update('matchq',array('court_no' => $_GET['court_no'], 'match_status' => 'playing', 'start_playing_date' => date("Y-m-d H:i:s")));
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
		case "get_match":
            $matchID = $_GET['match_id'];
            $isPreview = $_GET['is_preview'];
            $nextMatch = false;
	 
            print "<h2>ตารางประกบคู่&nbsp;&nbsp;: ".$_SESSION['club_name'];
            if ($isPreview != "1"){
                print "&nbsp;&nbsp;[<a href='index.php?club_id=".$_SESSION['club_id']."' target='_blank'>User View</a>]\n";
            }
            print "</h2>"; 
            if ($isPreview != "1"){
                $row = $mysql->query("select batch_id, date_format(batch_no,'%Y-%m-%d %a') as batch_no, batch_status from batch where club_id='".$_SESSION['club_id']."' and batch_status != 'Cancelled' order by batch_no desc", true);
                print "<div>Batch No. <select id='batch_id' onchange='changeBatch(this);'>\n";
                for ($i=0; $i<count($row); $i++){
                    $itemSelected = "";
                    if($_SESSION['batch_id']==$row[$i]['batch_id']){
                        $itemSelected = "selected";
                    }
                    print "<option value='".$row[$i]['batch_id']."' ".$itemSelected.">".$row[$i]['batch_no']." - [".$row[$i]['batch_status']."]</option>\n";
                }
                print "</select>\n";

                print "[<a href='javascript:createBatch();'>New Batch</a>]&nbsp;&nbsp;";
                if($_SESSION['batch_status'] == "Open"){
                    print "[<a href='javascript:completeBatch(\"".$_SESSION['batch_id']."\");'>Complete Batch</a>]&nbsp;&nbsp;";
                    print "[<a href='javascript:cancelBatch(\"".$_SESSION['batch_id']."\");'>Cancel Batch</a>]&nbsp;&nbsp;";
                }
                print "</div><br/>";
            }

			if($_SESSION['batch_id'] != ""){
				$row = $mysql->query("select a.match_id, x.match_no, a.member_id_11, a.member_id_12, a.member_id_21, a.member_id_22, b.member_name as m_11,  
				c.member_name as m_12,  d.member_name as m_21,  e.member_name as m_22, b2.class_color as color_11, c2.class_color as color_12, d2.class_color as color_21, 
				e2.class_color as color_22, a.match_status, a.court_no, a.is_request, case when a.match_status='completed' then TIMESTAMPDIFF(MINUTE,ifnull(a.start_playing_date,a.created_date),a.completed_date) when a.match_status='playing' then TIMESTAMPDIFF(MINUTE,ifnull(a.start_playing_date,a.created_date), now()) end as match_time, a.no_of_shuttle 
				from matchseq x 
				left join matchq a on x.match_no=a.match_no and a.match_status in('in queue','completed','playing', 'cancelled','auto') and a.batch_id='".$_SESSION['batch_id']."'
				left join member b on a.member_id_11=b.member_id left join member c on a.member_id_12=c.member_id and b.club_id='".$_SESSION['club_id']."'
				left join member d on a.member_id_21=d.member_id left join member e on a.member_id_22=e.member_id and d.club_id='".$_SESSION['club_id']."'
				left join class b2 on b.class_id=b2.class_id 
				left join class c2 on c.class_id=c2.class_id left join class d2 on d.class_id=d2.class_id left join class e2 on e.class_id=e2.class_id 
				order by x.match_no", true);

				for($i=0; $i<50; $i++){ 
					  $suffix = "";
					  $suffix_button = "";
					  $text_color_11 = "";
					  $text_color_12 = "";
					  $text_color_21 = "";
					  $text_color_22 = "";

					  if($row[$i]['match_id']!="" && $row[$i]['match_status']!="cancelled"){
						$suffix="_disabled";
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

					print "<div class='iconAll'>".($i+1).".</div>\n";
						  
					if ($isPreview == "1" && $row[$i]['match_status'] =="cancelled"){
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
							for($jjj=1; $jjj<9; $jjj++){
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
				  
					if ($isPreview == "1" && $row[$i]['match_status'] =="cancelled"){
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_21'><font color='".$text_color_21."'></font></div>\n";
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_22'><font color='".$text_color_22."'></font></div>\n";
					}else{
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_21'><font color='".$text_color_21."'>".$row[$i]['m_21']."</font></div>\n";
						print "<div class='squaredotted".$suffix."' id='match_".($i)."_22'><font color='".$text_color_22."'>".$row[$i]['m_22']."</font></div>\n"; 
					}
					
						if ($isPreview != "1"){
							if($row[$i]['match_status'] == "auto"){
								print "<div class='iconAll' id='saveIcon".$i."' style='display:block'>";
							}else{
								print "<div class='iconAll' id='saveIcon".$i."' style='display:none'>";
							}
						  
						  if($row[$i]['match_status'] != "completed"){
							//print "<img src='images/icon_ok".$suffix_button.".png' height='20px' width='20px' id='imgSave_".($i)."' onclick='saveMatch(this)' title='Save'/>";
							print "<img src='images/icon_save.png' height='20px' width='20px' id='imgSave_".($i)."' onclick='saveMatch(this,\"".$row[$i]['match_status']."\",\"".$_SESSION['is_auto_pilot']."\")' title='Save'/>";
						  }

						  print "</div>\n";
						  if($row[$i]['match_status'] != 'completed' && $row[$i]['match_status'] != 'cancelled' && $row[$i]['match_status'] != 'auto' && $row[$i]['match_id'] != ''){
							  //print "<div class='iconAll'><img src='images/icon_edit".$suffix_button.".png' height='20px' width='20px' title='Edit' onClick='editMatch(\"".$row[$i]['match_id']."\")'/></div>\n";
							  //print "<div class='iconAll'><img src='images/icon_cancel".$suffix_button.".png' height='20px' width='20px' title='Cancel' onClick='cancelMatch(\"".$row[$i]['match_id']."\")'/></div>\n";	 
							 print "<div class='iconAll' onclick='highlightObj(this.id);' id='divCM".$row[$i]['match_no']."'><a href='javascript:completeMatch(\"".$row[$i]['match_no']."\",\"".$row[$i]['match_id']."\",\"".$_SESSION['is_auto_pilot']."\")' title='Complete match'><img src='images/icon_ok.png' height='20px' width='20px' id='icoComplete".$row[$i]['match_no']."'></a></div>\n";
							 print "<div class='iconAll' onclick='highlightObj(this.id);' id='divMup".$row[$i]['match_no']."'><img src='images/icon_up.png'  height='20px' width='20px' title='เลื่อนแมทช์ขึ้น' onclick='moveMatchUp(\"".$row[$i]['match_no']."\")'>\n</div>";
							 print "<div class='iconAll' onclick='highlightObj(this.id);' id='divMdw".$row[$i]['match_no']."'><img src='images/icon_down.png'  height='20px' width='20px' title='เลื่อนแมทช์ลง' onclick='moveMatchDown(\"".$row[$i]['match_no']."\")'>\n</div>";
                          }
                          if(($row[$i]['match_status'] != 'completed' && $row[$i]['match_status'] != 'cancelled' && $row[$i]['match_id'] != '') || ($row[$i]['match_status'] == 'auto')){
                            print "<div class='iconAll' onclick='highlightObj(this.id);' id='divCC".$row[$i]['match_no']."'><img src='images/icon_cancel.png' height='20px' width='20px' title='Cancel' onClick='cancelMatch(\"".$row[$i]['match_id']."\")'/></div>\n";	
                          }
                        if($row[$i]['match_status'] != 'completed' && $row[$i]['match_status'] != 'cancelled' && $row[$i]['match_status'] != 'auto' && $row[$i]['match_id'] != ''){
							  if($row[$i]['is_request']=="Y"){
								print "<div class='iconRequest'><img src='images/icon_request.png' height='10px' width='30px' title='Request' onClick='requestMatch(\"".$row[$i]['match_id']."\",\"N\")'/></div>\n";	
							  }else{
								print "<div class='iconRequest'><img src='images/icon_request.png' height='10px' width='30px' title='Request'  style='opacity: 0.3; filter: grayscale(100%); alpha(opacity=30);' onClick='requestMatch(\"".$row[$i]['match_id']."\",\"Y\")'/></div>\n";	
							  }
							 
						  }

						  if($row[$i]['match_id'] != "" && ($row[$i]['match_status'] == "auto" || $row[$i]['match_status'] == "cancelled")){
								print "<div class='iconAll' onclick='highlightObj(this.id);'  id='divDelM".$row[$i]['match_no']."'><img src='images/trash.png' height='20px' width='20px' title='Delete' onClick='deleteMatch(\"".$row[$i]['match_id']."\")'/></div>\n";	
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
						print "<input type='hidden' id='match_txt_".($i)."' value='".$match_txt."' size='100'/>\n";
					}else{
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
					print "<div style='clear:both'></div>\n";
				}
			}
			break;
	}

	function getWaiting($vMysql, $vBatchID){
		//$row = $vMysql->query("select a.member_name, TIMESTAMPDIFF(MINUTE,max(b.created_date),now()) as waiting_minute from member a left join matchq b on (b.member_id_11 = a.member_id or b.member_id_12 = a.member_id or b.member_id_21 = a.member_id or b.member_id_22 = a.member_id) and b.match_status in('completed','in queue','in match') where b.batch_id='".$vBatchID."' and a.member_id in(select member_id from queue where batch_id='".$vBatchID."') group by a.member_name order by max(b.created_date) limit 0,5", true);
		$row = $vMysql->query("select a.member_name, TIMESTAMPDIFF(MINUTE,max(b.created_date),now()) as waiting_minute from member a inner join queue b on b.member_id = a.member_id and b.queue_status in('in queue') where b.batch_id='".$vBatchID."' group by a.member_name order by max(b.created_date) limit 0,5", true);
			print "<div class='header_h1'>สมาชิก 5 อันดับที่รอนานสุด</div>";
            for($i=0; $i<count($row); $i++){
              print ($i+1).". ";
              print "<font color='blue'>".$row[$i]['member_name']."</font>&nbsp;&nbsp;[".$row[$i]['waiting_minute']." นาที]";
              print "<br/>";
            }
	}

?>
