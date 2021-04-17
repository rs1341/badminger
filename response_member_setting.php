<?php
	session_start();
	ini_set('error_reporting', E_ALL & ~NOTICE);
	ini_set('log_errors', 1);
	ini_set('display_errors', 0);

	include_once('db_connect.php');
	include_once('batch_session.php');

	if($_SESSION['user_name'] != ""){
		switch($_GET['task']){
			case "update_line_info":
				$userRow = $mysql->query("select * from line_user where member_id='".$_GET['member_id']."'", true);
				$id = $userRow[0]['user_id'];

				if($id != ""){
					$rowClub = $mysql->query("select * from club where club_id='".$_SESSION['club_id']."'", true);
					$accessToken = $rowClub[0]['line_token'];

					$LINEDatas = array();
					$LINEDatas['token'] = $accessToken;

					$returnArr = getLINEProfileNew($LINEDatas,$id);
					$obj = json_decode($returnArr['message']);

					$mysql->where(array('user_id'=>$id))->update('line_user', array('picture_url'=>$obj->pictureUrl, 'display_name' => $obj->displayName, 'updated_date' => date("Y-m-d H:i:s",strtotime("+7 hours"))));
				}
				break;
            case "set_option":
                $mysql->where(array('club_id'=>$_SESSION['club_id']))->update('club',array($_GET['option_name']=>$_GET['option_value']));
                $_SESSION[$_GET['option_name']] = $_GET['option_value'];
				print "Success";
                break;
			case "get_condition":
				$UnpaidAmt=0;
				/*
				$clubSetting = array();
				$clubArr = $mysql->query("select * from config where club_id='".$_SESSION['club_id']."'",true);
				for($i=0;$i<count($clubArr);$i++){
					$clubSetting[$clubArr[$i]['config_name']]=$clubArr[$i]['config_value'];
				}

				print "<h2>ตั้งค่าทั่วไปของชมรม</h2>\n";
				print "<table class='tblpayment' width='800px'>\n";
				print "<tr>\n"; 
				print "<td width='20%'>1. ค่าคอร์ทสำหรับผู้ใหญ่</td>\n";
				print "<td width='5%'></td>\n";
				print "<td width='75%'><input id='vCourtFeeAdult' value='".@$clubSetting['court_fee_adult']."'>บาท</td>\n";
				print "</tr>\n";
				print "<tr>\n"; 
				print "<td>2. ค่าคอร์ทสำหรับนักเรียน</td>\n";
				print "<td></td>\n";
				print "<td><input id='vCourtFeeStudent' value='".@$clubSetting['court_fee_student']."'>บาท</td>\n";
				print "</tr>\n";
				print "<tr>\n"; 
				print "<td>3. ค่าลูกแบด/ลูก/คน</td>\n";
				print "<td></td>\n";
				print "<td><input id='vShuttleFee' value='".@$clubSetting['shuttlecock_fee']."'>บาท</td>\n";
				print "</tr>\n";
				print "<tr>\n"; 
				print "<td>4. ต้นทุนค่าลูกแบด/ลูก</td>\n";
				print "<td></td>\n";
				print "<td><input id='vShuttleCost' value='".@$clubSetting['shuttle_cost']."'>บาท</td>\n";
				print "</tr>\n";
				print "<tr>\n"; 
				print "<td></td>\n";
				print "<td></td>\n";
				print "<td><a href='javascript:saveClubSetting()' class='myButtonBlue'>Save</a>&nbsp;<a href='javascript:getMatch()' class='myButtonBlue'>Cancel</a></td>\n";
				print "</tr>\n";
				print "</table>\n";
				*/

				print "<h2>ตั้งค่าโดยทั่วไปของชมรม</h2>\n";
				print "<table class='tblpayment' width='800px'>\n";
                print "<tr>\n"; 
				print "	<td width='20%'>ชื่อชมรม :</td>\n";
				print "	<td width='5%'></td>\n";
                print "	<td><input type='textbox' id='vClubName' value='".$_SESSION['club_name']."' disabled></td>\n";
				print "</tr>\n";
				print "<tr>\n"; 
				print "	<td>ต้นทุนค่าลูกแบด (ต่อลูก) :</td>\n";
				print "	<td></td>\n";
                print "	<td><input type='textbox' id='vShuttleCost' value='".$_SESSION['shuttle_cost']."' > บาท  (ใช้ในการคำนวณกำไรขาดทุน ในสรุปยอดรายวัน)</td>\n";
				print "</tr>\n";
				print "<tr>\n"; 
				print "	<td>ต้นทุน (ค่าน้ำ) :</td>\n";
				print "	<td></td>\n";
                print "	<td><input type='textbox' id='vBetCost' value='".$_SESSION['bet_cost']."' > บาท  (ต้นทุนค่าน้ำ สำหรับการเล่นทายผลการแข่งขัน)</td>\n";
				print "</tr>\n";
				print "<tr>\n"; 
				print "	<td>อัตราการเสี่ยงทาย (ค่าน้ำ) :</td>\n";
				print "	<td></td>\n";
                print "	<td><input type='textbox' id='vBetRate' value='".$_SESSION['bet_rate']."' > บาท  (สำหรับการเล่นทายผลการแข่งขัน หรือเรียกง่ายๆ ว่า ค่าน้ำ)</td>\n";
				print "</tr>\n";
				print "<tr>\n"; 
				print "	<td></td>\n";
				print "	<td></td>\n";
                print "	<td><a href='javascript:saveClubInfo()' class='myButtonGreen'>Save</a></td>\n";
				print "</tr>\n";
				print "</table>\n";

                print "<h2>ตั้งค่าการประกบคู่อัตโนมัติ</h2>\n";
                print "ระบบจะจับคู่โดยยึดสีของนักกีฬาคนแรกที่อยู่ในคิว จากนั้นจะหานักกีฬาคนอื่นๆ โดยยึดเงื่อนไขด้านล่าง";
				print "<table class='tblpayment' width='800px'>\n";
                print "<tr>\n"; 
				print "<td width='30%' valign='top'><br/>เงื่อนไขการจับคู่</td>\n";
				print "<td width='5%'></td>\n";
                print "<td>\n";
                print "<br/><select id='club_level_auto' onchange='setAutoMatchLevel(\"".$_SESSION['is_auto_pilot']."\");'style='font-size:14px' title='0:สีเดียวกันเท่านั้น, 1:ฝีมือต่างกัน +-1 ขั้น, 2:ฝีมือต่างกัน +-2 ขั้น, 3:ฝีมือต่างกัน +-3 ขั้น, 4:เล่นด้วยกันได้ทุกสี'>";
                $tempLevel = array('สีเดียวกันเท่านั้น','ฝีมือต่างกัน +-1 ขั้น','ฝีมือต่างกัน +-2 ขั้น','ฝีมือต่างกัน +-3 ขั้น','เล่นด้วยกันได้ทุกสี');
				
				for($mm=0; $mm<5; $mm++){
					if($mm==$_SESSION['club_auto_level']){
						print "<option value='".$mm."' selected>ระดับ ".$mm." : ".$tempLevel[$mm]."</option>";
					}else{
						print "<option value='".$mm."'>ระดับ ".$mm." : ".$tempLevel[$mm]."</option>";
					}
				}
				print "</select>";
                print "<br/><br/><strong><u>ตัวอย่าง</u></strong>";
                print "<br/><strong>ระดับ 0</strong> หานักกีฬาสีเดียวกันทั้งหมด เพื่อมาประกบคู่ เช่น ขาวทั้ง 4 คน หรือ แดงทั้ง 4 คน";
                print "<br/><br/><strong>ระดับ 1</strong> หานักกีฬาที่อยู่ในระดับเดียวกัน หรือ ต่างระดับ บวกหรือลบ 1 ขั้น เช่น คนแรกสีน้ำเงิน คนที่จะมาคู่ด้วย เป็นได้ทั้งสีน้ำเงิน แดง และเหลือง";
                print "<br/><br/><strong>ระดับ 2</strong> หานักกีฬาที่อยู่ในระดับเดียวกัน หรือ ต่างระดับ บวกหรือลบ 2 ขั้น เช่น คนแรกสีน้ำเงิน คนที่จะมาคู่ด้วย เป็นได้ทั้งสีน้ำเงิน แดง เหลือง หรือ เขียว";
                print "<br/><br/><strong>ระดับ 3</strong> หานักกีฬาที่อยู่ในระดับเดียวกัน หรือ ต่างระดับ บวกหรือลบ 3 ขั้น เช่น คนแรกสีแดง คนที่จะมาคู่ด้วย เป็นได้ทั้งสีน้ำเงิน แดง เหลือง เขียว หรือ ขาว";
                print "<br/><br/><strong>ระดับ 4</strong> หานักกีฬาที่อยู่ในทุกระดับมาประกบคู่<br/><br/>";
                print "</td>\n";
				print "</tr>\n";
				print "<tr>\n"; 
				print "<td>อนุญาตให้จับคู่ซ้ำกันได้</td>\n";
				print "<td></td>\n";
                if($_SESSION['allow_repeat_buddy']=="1"){
				    print "<td><a href='javascript:setOption(\"allow_repeat_buddy\",\"0\");'><img src='images/icon_on.png'></a></td>\n";
                }else{
                    print "<td><a href='javascript:setOption(\"allow_repeat_buddy\",\"1\");'><img src='images/icon_off.png'></a></td>\n";
                }
				print "</tr>\n";
                print "<tr>\n"; 
				print "<td>อนุญาตให้เจอกันซ้ำได้</td>\n";
				print "<td></td>\n";

				//if($_SESSION['allow_repeat_opponent']=="1"){
				//    print "<td width='65%'><a href='javascript:setOption(\"allow_repeat_opponent\",\"0\");'><img src='images/icon_on.png'></a></td>\n";
                //}else{
                    print "<td width='65%'><a href='javascript:setOption(\"allow_repeat_opponent\",\"1\");'><img src='images/icon_off.png'></a>&nbsp;not available</td>\n";
                //}
				print "</tr>\n";
                print "<tr>\n"; 
				print "<td>อนุญาตให้จับคู่ได้หากนักกีฬายังอยู่ในสนาม</td>\n";
				print "<td></td>\n";
				//if($_SESSION['allow_pre_queue_when_playing']=="1"){
				//    print "<td width='65%'><img src='images/icon_on.png' onclick='setOption(\"allow_pre_queue_when_playing\",\"0\");'></td>\n";
                //}else{
                    print "<td width='65%'><a href='javascript:setOption(\"allow_pre_queue_when_playing\",\"1\");'><img src='images/icon_off.png'></a>&nbsp;not available</td>\n";
                //}
				print "</tr>\n";
				print "</table>\n";
				

				print "<h2>ตั้งค่าการประกบคู่รายบุคคล</h2>\n";
				print "<a href='javascript:newCondition()' class='myButtonGreen'>New</a><br/><br/>\n";
				print "<table class='tblpayment' width='800px'>\n";
				print "<thead><tr>\n";
				print "<th width='5%'></th>\n";
				print "<th width='10%'>ลำดับ</th>\n";
				print "<th width='15%' align='left'>สมาชิก</th>\n";
				print "<th width='20%'>ต้องคู่กับ...(เท่านั้น)</th>\n";
                print "<th width='20%'>ไม่เล่นคู่กับ...</th>\n";
                print "<th width='20%'>ไม่เล่นตรงข้ามกับ...</th>\n";
				print "<th width='10%'>วันที่สร้าง</th>\n";
				print "</thead></tr>\n";
				$row = $mysql->query("select distinct a.member_id_1, b.member_name, a.created_date, a.modified_date, condition_id
                											from member_condition a 
                                                            	inner join member b on a.member_id_1=b.member_id
                                                            where a.club_id='".$_SESSION['club_id']."' and b.member_status='Active' order by CONVERT(b.member_name USING tis620) ASC, a.created_date", true);
				//print $mysql->last_query();
            
				for($i=0; $i<count($row); $i++){
					print "<tr>\n"; 
					print "<td><img src='images/icon_edit.png' height='20px' style='cursor: pointer;' width='20px' onclick='editCondition(\"".$row[$i]['condition_id']."\")'>&nbsp;&nbsp;<img src='images/trash.png' style='cursor: pointer;' height='20px' width='20px' onclick='deleteCondition(\"".$row[$i]['condition_id']."\")'></td>\n";
					print "<td align='center'>".($i+1)."</td>\n";
					print "<td align='left'>".$row[$i]['member_name']."</td>\n";
                    print_r($row3);
					print "<td align='center'>".getMemberCondition($row[$i]['member_id_1'],'MustPairWith')."</td>\n";
                    print "<td align='center'>".getMemberCondition($row[$i]['member_id_1'],'NoPairWith')."</td>\n";
                    print "<td align='center'>".getMemberCondition($row[$i]['member_id_1'],'NoOpponentWith')."</td>\n";
					print "<td align='center'>".$row[$i]['created_date']."</td>\n";
					print "<tr>\n";
				}
				print "</table>\n";
				//print $mysql->last_query();
				break;
			case "edit_condition":
            	print "<h2>ตั้งค่าการประกบคู่รายบุคคล</h2>\n";
				
				if($_GET['member_id']!=""){
					 $memberArr = $mysql->query("select member_id, member_name from member where club_id='".$_SESSION['club_id']."' and member_status='Active' order by CONVERT(member_name USING tis620) ASC", true);
					 $cBatchArr = $mysql->query("select a.* from member_condition a where a.member_id_1='".$_GET['member_id']."' and club_id='".$_SESSION['club_id']."'", true);
					 //print_r($cBatchArr);
					 $tempMPWarr = array();
					 $tempNPWarr = array();
					 $tempNOWarr = array();
					 for($i=0;$i<count($cBatchArr);$i++){
						switch($cBatchArr[$i]['condition_type']){
							case 'MustPairWith':
								$tempMPWarr[$cBatchArr[$i]['member_id_2']] = $cBatchArr[$i]['member_id_2'];
								break;
							case 'NoPairWith':
								$tempNPWarr[$cBatchArr[$i]['member_id_2']] = $cBatchArr[$i]['member_id_2'];
								break;
							case 'NoOpponentWith':
								$tempNOWarr[$cBatchArr[$i]['member_id_2']] = $cBatchArr[$i]['member_id_2'];
								break;
						}
					 }
				}else{
					$memberArr = $mysql->query("select member_id, member_name from member where club_id='".$_SESSION['club_id']."' and member_status='Active' and member_id not in(select member_id_1 from member_condition where club_id='".$_SESSION['club_id']."') order by CONVERT(member_name USING tis620) ASC", true);
				}
				//print $mysql->last_query();
				/*
				print "<table class='tblpayment'>\n";
				print "<tr>\n";
				print "	<td width='30%'><strong>Condition ID</strong></td>\n";
				print "	<td width='5%'></td>\n";
				print "	<td width='65%'><strong>".$cBatchArr[0]['condition_id']."</strong></td>\n";
				print "</tr>\n";
				print "<tr>\n";
				print "	<td><strong>สมาชิกคนที่ 1</strong></td>\n";
				print "	<td></td>\n";
				print "	<td>".getMemberCombo($memberArr, 'vMemberID1', $cBatchArr[0]['member_id_1'])."</td>\n";
				print "</tr>\n";
				print "<tr>\n";
				print "	<td><strong>เงื่อนไข</strong></td>\n";
				print "	<td></td>\n";
				print "	<td><select id='vConditionType'>\n";
				$typeArr = $mysql->query("select * from lookup_table where lookup_category='ConditionType' order by lookup_desc", true);
				for($i=0;$i<count($typeArr);$i++){
					if($cBatchArr[0]['condition_type']==$typeArr[$i]['lookup_value']){
						print "<option value='".$typeArr[$i]['lookup_value']."' selected>".$typeArr[$i]['lookup_desc']."</option>\n";
					}else{
						print "<option value='".$typeArr[$i]['lookup_value']."'>".$typeArr[$i]['lookup_desc']."</option>\n";
					}
				}
				print "</select></td>\n";
				print "</tr>\n";
				print "<tr>\n";
				print "	<td><strong>สมาชิกคนที่ 1</strong></td>\n";
				print "	<td></td>\n";
				print "	<td>".getMemberCombo($memberArr, 'vMemberID2', $cBatchArr[0]['member_id_2'])."</td>\n";
				print "</tr>\n";
				print "<tr>\n";
				print "	<td><strong>สถานะ</strong></td>\n";
				print "	<td></td>\n";
				$selectedActive="";
				$selectedInactive="";
				if($cBatchArr[0]['condition_status']=="Active" || $_GET['condition_id']==""){
				  $selectedActive="selected";
				  $selectedInactive="";
				}else{
				  $selectedActive="";
				  $selectedInactive="selected";
				}
				print "	<td><select id='vConditionStatus'><option value='Active' ".$selectedActive.">Active</option><option value='Inactive' ".$selectedInactive.">Inactive</option></td>\n";
				print "</tr>\n";
				print "<tr>\n";
				print "	<td></td>\n";
				print "	<td></td>\n";
				print "	<td><input type='button' value='    Save   ' onclick='saveMassCondition(\"".$_GET['condition_id']."\")'>&nbsp;<input type='button' value='Cancel' onclick='getCondition();'></td>\n";
				print "</tr>\n";
				print "</table>";
				*/

				//Mass Update
				print "<p>";
				print "<br/><a href='javascript:saveMassCondition()' class='myButtonBlue'>Save</a>&nbsp;<a href='javascript:getCondition();' class='myButtonBlue'>Cancel</a><br/><br/>";
				print "<table class='tblpayment' width='800px'>\n";
				print "<tr>\n";
				print "	<td width='15%'><strong>สมาชิกคนที่ 1</strong></td>\n";
				print "	<td width='5%'></td>\n";
				if($_GET['member_id'] != ""){
					$isDisabled = "disabled";
				}else{
					$isDisabled = "";
				}
				print "	<td width='80%'>".getMemberCombo($memberArr, 'vMemberID1', $cBatchArr[0]['member_id_1'], $isDisabled)."</td>\n";
				print "</tr>\n";
				print "<tr>\n";
				print "	<td width='15%'><strong>สมาชิกคนที่ 2</strong></td>\n";
				print "	<td width='5%'></td>\n";
				print "	<td width='80%'></td>\n";
				print "</tr>\n";
				print "<tr>\n";
				print "	<td valign='top'>ต้องคู่กับ...(เท่านั้น)</td>\n";
				print "	<td></td>\n";
				print "	<td>\n";
				print "		<table width='100%'>\n";
				print "			<tr>\n";
				//print_r($tempMPWarr);
				$jj=0;
				$tempMaxPerColumn = ceil(count($memberArr)/5);
				for($i=0;$i<count($memberArr);$i++){
					if($jj==0){
						print "		<td width='20%' valign='top'>\n";
						if($i==0){
							print "		<input type='checkbox' id='vChkMPW_all' onclick='checkAll(this.checked,\"vChkMPW\")'><strong>Check All</strong><br/><br/>\n";
						}else{
							print "<br/><br/><br/>\n";
						}
					}
					
					$jj++;

					if(array_search($memberArr[$i]['member_id'], $tempMPWarr, false) == ""){
						print "<input type='checkbox' id='vChkMPW' value='".$memberArr[$i]['member_id']."' onclick='unCheckPairWith(this.checked, \"vChkMPW\")'>".$memberArr[$i]['member_name']."<br/>";
					}else{
						print "<input type='checkbox' id='vChkMPW' value='".$memberArr[$i]['member_id']."' checked>".$memberArr[$i]['member_name']."<br/>";
					}

					if($jj==$tempMaxPerColumn){
						print "		<br/>\n";
						print "		</td>\n";
						$jj=0;
					}
				}
				print "			</tr>\n";
				print "		</table>\n";
				print "<input type='hidden' id='vMPWText'>\n";
				print " </td>\n";
				print "</tr>\n";
				print "<tr>\n";
				print "	<td valign='top'>ไม่เล่นคู่กับ...</td>\n";
				print "	<td></td>\n";
				print "	<td>\n";
				print "		<table width='100%'>\n";
				print "			<tr>\n";
				
				$jj=0;
				$tempMaxPerColumn = ceil(count($memberArr)/5);
				for($i=0;$i<count($memberArr);$i++){
					if($jj==0){
						print "		<td width='20%' valign='top'>\n";	
						if($i==0){
							print "		<input type='checkbox' id='vChkNPW_all' onclick='checkAll(this.checked,\"vChkNPW\")'><strong>Check All</strong><br/><br/>\n";
						}else{
							print "<br/><br/><br/>\n";
						}
					}
					
					$jj++;
					if(array_search($memberArr[$i]['member_id'], $tempNPWarr, false) == ""){
						print "<input type='checkbox' id='vChkNPW' value='".$memberArr[$i]['member_id']."' onclick='unCheckPairWith(this.checked, \"vChkNPW\")'>".$memberArr[$i]['member_name']."<br/>";
					}else{
						print "<input type='checkbox' id='vChkNPW' value='".$memberArr[$i]['member_id']."' checked>".$memberArr[$i]['member_name']."<br/>";
					}

					if($jj==$tempMaxPerColumn){
						print "		<br/>\n";
						print "		</td>\n";
						$jj=0;
					}
				}
				print "			</tr>\n";
				print "		</table>\n";
				print "<input type='hidden' id='vNPWText'>\n";
				print " </td>\n";
				print "</tr>\n";
				print "<tr>\n";
				print "	<td valign='top'>ไม่เล่นตรงข้ามกับ...</td>\n";
				print "	<td></td>\n";
				print "	<td>\n";
				print "		<table width='100%'>\n";
				print "			<tr>\n";
				
				$jj=0;
				$tempMaxPerColumn = ceil(count($memberArr)/5);
				for($i=0;$i<count($memberArr);$i++){
					if($jj==0){
						print "		<td width='20%' valign='top'>\n";
						if($i==0){
							print "		<input type='checkbox' id='vChkNOW_all' onclick='checkAll(this.checked,\"vChkNOW\")'><strong>Check All</strong><br/><br/>\n";
						}else{
							print "<br/><br/><br/>\n";
						}
					}

					$jj++;
					
					if(array_search($memberArr[$i]['member_id'], $tempNOWarr, false) == ""){
						print "<input type='checkbox' id='vChkNOW' value='".$memberArr[$i]['member_id']."'>".$memberArr[$i]['member_name']."<br/>";
					}else{
						print "<input type='checkbox' id='vChkNOW' value='".$memberArr[$i]['member_id']."' checked>".$memberArr[$i]['member_name']."<br/>";
					}

					if($jj==$tempMaxPerColumn){
						print "		<br/>\n";
						print "		</td>\n";
						$jj=0;
					}
				}
				print "			</tr>\n";
				print "		</table>\n";
				print "<input type='hidden' id='vNOWText'>\n";
				print " </td>\n";
				print "</tr>\n";
				print "</table>";
				print "<br/><a href='javascript:saveMassCondition()' class='myButtonBlue'>Save</a>&nbsp;<a href='javascript:getCondition();' class='myButtonBlue'>Cancel</a>";
				print "</p><br/><br/>";
				break;
			case "save_condition":
                    if($_GET['condition_id']!=""){
						$row = $mysql->query("select * from member_condition where club_id='".$_SESSION['club_id']."' and ((member_id_1='".$_GET['member_id_1']."' and member_id_2='".$_GET['member_id_2']."') or (member_id_2='".$_GET['member_id_1']."' and member_id_1='".$_GET['member_id_2']."'))and condition_type='".$_GET['condition_type']."' and condition_id != '".$_GET['condition_id']."' and condition_status='Active'", true);
						if(count($row)==0){
							if($_GET['condition_type']=="MustPairWith"){
								$mysql->query("delete from member_condition where condition_type='NoPairWith' and condition_status='Active' and ((member_id_1='".$_GET['member_id_1']."' and member_id_2='".$_GET['member_id_2']."') or (member_id_1='".$_GET['member_id_2']."' and member_id_2='".$_GET['member_id_1']."')) and condition_id !='".$_GET['condition_id']."' and club_id='".$_SESSION['club_id']."'", false);
							}

							if($_GET['condition_type']=="NoPairWith"){
								$mysql->query("delete from member_condition where condition_type='MustPairWith' and condition_status='Active' and ((member_id_1='".$_GET['member_id_1']."' and member_id_2='".$_GET['member_id_2']."') or (member_id_1='".$_GET['member_id_2']."' and member_id_2='".$_GET['member_id_1']."')) and condition_id !='".$_GET['condition_id']."' and club_id='".$_SESSION['club_id']."'", false);
							}

							$mysql->where(array('condition_id'=>$_GET['condition_id']))->update('member_condition',array('member_id_1'=>$_GET['member_id_1'], 'condition_type'=>$_GET['condition_type'], 'member_id_2'=>$_GET['member_id_2'], 'condition_status'=>$_GET['condition_status']));
							print "Success";
						}else{
							print "Duplicate";
						}
                    }else{

                        $row = $mysql->query("select * from member_condition where club_id='".$_SESSION['club_id']."' and ((member_id_1='".$_GET['member_id_1']."' and member_id_2='".$_GET['member_id_2']."') or (member_id_2='".$_GET['member_id_1']."' and member_id_1='".$_GET['member_id_2']."'))and condition_type='".$_GET['condition_type']."' and condition_status='Active'", true);
                        if(count($row)==0){
							if($_GET['condition_type']=="MustPairWith"){
								$mysql->query("delete from member_condition where condition_type='NoPairWith' and condition_status='Active' and ((member_id_1='".$_GET['member_id_1']."' and member_id_2='".$_GET['member_id_2']."') or (member_id_1='".$_GET['member_id_2']."' and member_id_2='".$_GET['member_id_1']."')) and club_id='".$_SESSION['club_id']."'", false);
							}

							if($_GET['condition_type']=="NoPairWith"){
								$mysql->query("delete from member_condition where condition_type='MustPairWith' and condition_status='Active' and ((member_id_1='".$_GET['member_id_1']."' and member_id_2='".$_GET['member_id_2']."') or (member_id_1='".$_GET['member_id_2']."' and member_id_2='".$_GET['member_id_1']."')) and club_id='".$_SESSION['club_id']."'", false);
							}
                            $mysql->insert('member_condition',array('condition_id'=>uniqid(), 'club_id'=>$_SESSION['club_id'] ,'member_id_1'=>$_GET['member_id_1'], 'condition_type'=>$_GET['condition_type'], 'member_id_2'=>$_GET['member_id_2'], 'condition_status'=>$_GET['condition_status']));
                            print "Success";
                        }else{
                            print "Duplicate";
                        }   
                    }
					break;
			case "save_mass_condition":
					$mysql->where(array('member_id_1'=>$_GET['member_id_1']))->delete('member_condition');
					if($_GET['mpw_members'] != ""){  //mpw
						$tempMPWMembers = explode(",",$_GET['mpw_members']);
						for($i=0;$i<count($tempMPWMembers);$i++){
							if($_GET['member_id_1'] != $tempMPWMembers[$i]){
								$mysql->insert('member_condition',array('condition_id'=>uniqid(), 'club_id'=>$_SESSION['club_id'] ,'member_id_1'=>$_GET['member_id_1'], 'condition_type'=>'MustPairWith', 'member_id_2'=>$tempMPWMembers[$i], 'condition_status'=>'Active'));
							}
						}
					}

					if($_GET['npw_members'] != ""){  //mpw
						$tempMPWMembers = explode(",",$_GET['npw_members']);
						for($i=0;$i<count($tempMPWMembers);$i++){
							if($_GET['member_id_1'] != $tempMPWMembers[$i]){
								$mysql->insert('member_condition',array('condition_id'=>uniqid(), 'club_id'=>$_SESSION['club_id'] ,'member_id_1'=>$_GET['member_id_1'], 'condition_type'=>'NoPairWith', 'member_id_2'=>$tempMPWMembers[$i], 'condition_status'=>'Active'));
							}
						}
					}

					if($_GET['now_members'] != ""){  //mpw
						$tempMPWMembers = explode(",",$_GET['now_members']);
						for($i=0;$i<count($tempMPWMembers);$i++){
							if($_GET['member_id_1'] != $tempMPWMembers[$i]){
								$mysql->insert('member_condition',array('condition_id'=>uniqid(), 'club_id'=>$_SESSION['club_id'] ,'member_id_1'=>$_GET['member_id_1'], 'condition_type'=>'NoOpponentWith', 'member_id_2'=>$tempMPWMembers[$i], 'condition_status'=>'Active'));
							}
						}
					}
					print "Success";
					break;
			case "delete_condition":
					$mysql->where(array('condition_id'=>$_GET['condition_id']))->delete('member_condition');
					break;

			case "set_condition_status":
					$mysql->where(array('condition_id'=>$_GET['condition_id']))->update('member_condition',array('condition_status'=>$_GET['condition_status']));
					break;
			case "save_club_setting":
					$mysql->where(array('club_id'=>$_SESSION['club_id']))->delete('config');
					$mysql->insert('config',array('config_id'=>uniqid(), 'club_id'=>$_SESSION['club_id'] ,'config_value'=>$_GET['court_fee_adult'], 'config_name'=>'court_fee_adult', 'config_description'=>'ค่าคอร์ทสำหรับผู้ใหญ่'));
					$mysql->insert('config',array('config_id'=>uniqid(), 'club_id'=>$_SESSION['club_id'] ,'config_value'=>$_GET['court_fee_student'], 'config_name'=>'court_fee_student', 'config_description'=>'ค่าคอร์ทสำหรับนักเรียน'));
					$mysql->insert('config',array('config_id'=>uniqid(), 'club_id'=>$_SESSION['club_id'] ,'config_value'=>$_GET['shuttle_fee'], 'config_name'=>'shuttlecock_fee', 'config_description'=>'ค่าลูกแบด/ลูก/คน'));
					$mysql->insert('config',array('config_id'=>uniqid(), 'club_id'=>$_SESSION['club_id'] ,'config_value'=>$_GET['shuttle_cost'], 'config_name'=>'shuttle_cost', 'config_description'=>'ต้นทุนค่าลูก'));
					print "Success";

					break;
			 case "save_club_info":
					$mysql->where(array('club_id'=>$_SESSION['club_id']))->update('club',array('shuttle_cost'=>$_GET['shuttle_cost'], 'bet_cost'=>$_GET['bet_cost'], 'bet_rate'=>$_GET['bet_rate']));
					$_SESSION['shuttle_cost'] = $_GET['shuttle_cost'];
					$_SESSION['bet_cost'] = $_GET['bet_cost'];
					$_SESSION['bet_rate'] = $_GET['bet_rate'];
					print "Success";
					break;

			}
    }

	function getMemberCombo($memberArr, $comboName, $memberSelected, $isDisabled=''){
		$returnStr = "";
		$returnStr .= "<select id='".$comboName."' ".$isDisabled.">\n";
		for($ii=0;$ii<count($memberArr);$ii++){
			if($memberArr[$ii]['member_id']==$memberSelected){
				$returnStr .= "<option value='".$memberArr[$ii]['member_id']."' selected>".$memberArr[$ii]['member_name']."</option>\n";
			}else{
				$returnStr .= "<option value='".$memberArr[$ii]['member_id']."'>".$memberArr[$ii]['member_name']."</option>\n";
			}
		}
		$returnStr .= "</select>\n";

		return $returnStr;
	}

    function getMemberCondition($member_id, $condition_type){
        global $mysql;
        $row = $mysql->query("select b.member_name from member_condition a inner join member b on a.member_id_2=b.member_id where a.member_id_1='".$member_id."' and a.club_id='".$_SESSION['club_id']."' and a.condition_type='".$condition_type."' and a.condition_status='Active' and b.member_status='Active' order by CONVERT(b.member_name USING tis620) ASC", true);
        $returnStr = "";
        for($i=0;$i<count($row);$i++){
            $returnStr .= $row[$i]['member_name'].", ";
        }
        return rtrim($returnStr,", ");
    }


	function getLINEProfileNew($datas,$idd)
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
				