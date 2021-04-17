<?php
	session_start();
	ini_set('error_reporting', E_ALL & ~NOTICE);
	ini_set('log_errors', 1);
	ini_set('display_errors', 1);

	include_once('db_connect.php');
	include_once('batch_session.php');

	if($_SESSION['user_name'] != ""){
	switch($_GET['task']){
		case "get_payment":
			print "<h2>สรุปค่าใช้จ่ายรายบุคคล : Event No. ".$_SESSION['batch_no']."</h2>&nbsp;&nbsp;<div id='divSelectedTotal' style='width:95%;background-color:#7FFFD4;'></div>\n";
        	print "<table class='tblpayment' width='95%'>\n";
        	print "<thead><tr>\n";
            print "<th width='5%' valign='top'>เลือก<br/><input type='checkbox' onclick='checkPaidAll(this.checked, \"chkFee\")'></th>\n";
        	print "<th width='5%' valign='top'>ลำดับ</th>\n";
        	print "<th width='8%' valign='top' align='left'>ชื่อ</th>\n";
            print "<th width='5%' valign='top' align='left'>ประเภท</th>\n";
			print "<th width='5%' valign='top' align='right'>ส่วนลด(บาท)</th>\n";
			print "<th width='5%' valign='top' align='right'>เสียน้ำ(ขวด)</th>\n";
        	print "<th width='10%' valign='top' align='right'>ยอดที่ต้องชำระ(บาท)</th>\n";
        	print "<th width='8%' valign='top'>สถานะ<br/><span style='font-size:8px;font-weight: normal;'>(คลิ้กเพื่อเปลี่ยนสถานะ)</span></th>\n";
        	print "<th width='8%' valign='top'>พร้อมเพย์<br/><span style='font-size:8px;font-weight: normal;'>(คลิ้กเพื่อเปลี่ยนสถานะ)</span></th>\n";
			print "<th width='15%' valign='top'>หมายเหตุ</th>\n";
        	print "<th width='5%' valign='top'>เกมส์</th>\n";
        	print "<th width='5%' valign='top' align='right'>ลูก</th>\n";
        	print "<th width='8%' valign='top'>ค่าสนาม</th>\n";
        	print "<th width='5%' valign='top' align='right'>ค่าลูกแบด&nbsp;&nbsp;</th>\n";
			print "</thead></tr>\n";
        	$row = $mysql->query("select a.member_id, b.member_name, mt.member_type_name, b.member_photo, cls.class_text_color, sum(a.no_of_shuttle) as sum_of_shuttle, count(distinct match_id) as no_of_match, 
            												mt.court_fee as court_fee, 
                                                            mt.shuttle_fee as shuttlecock_fee,(sum(a.no_of_shuttle)*mt.shuttle_fee) as total_shuttle, pm.payment_status, pm.total_amount as paid_amount,pm.payment_method,
                                                            mt.court_fee+(sum(a.no_of_shuttle)*mt.shuttle_fee)-ifnull(pm.discount,0)+(pm.lose_gambling*".$_SESSION['bet_rate'].") as total_amount, ifnull(pm.discount,0) as discount, pm.payment_id, mt.court_fee_disc_1match,
															ifnull(pm.lose_gambling,0) as lose_gambling, b.line_id
                                                     	from 
                                                            (select member_id_11 as member_id, no_of_shuttle, match_id from matchq where batch_id='".$_SESSION['batch_id']."' and match_status != 'cancelled' union
            												select member_id_12 as member_id, no_of_shuttle, match_id  from matchq where batch_id='".$_SESSION['batch_id']."' and match_status != 'cancelled' union
                                                    		select member_id_21 as member_id, no_of_shuttle, match_id  from matchq where batch_id='".$_SESSION['batch_id']."' and match_status != 'cancelled' union
                                                    		select member_id_22 as member_id, no_of_shuttle, match_id  from matchq where batch_id='".$_SESSION['batch_id']."' and match_status != 'cancelled' ) as a 
                                                    inner join member b on a.member_id=b.member_id 
													inner join member_type mt on b.member_type_id=mt.member_type_id
													left join payment pm on pm.batch_id='".$_SESSION['batch_id']."' and b.member_id=pm.member_id
													left join class cls on b.class_id=cls.class_id
													where b.club_id='".$_SESSION['club_id']."'
                                                    group by a.member_id, b.member_name, b.member_photo, mt.member_type_name, cls.class_text_color, mt.court_fee, mt.shuttle_fee, pm.payment_status, pm.total_amount, pm.payment_method, pm.discount, pm.payment_id, mt.court_fee_disc_1match, b.line_id order by CONVERT(b.member_name USING tis620) ASC", true);
        	//print $mysql->last_query();
			$grandTotal = 0;
			$grandPaid = 0;
        	$grandPaidPP = 0;
			$ppMembers = 0;

    		for($i=0; $i<count($row); $i++){
				if($row[$i]['payment_id'] == ""){
					$temp_payment_id = uniqid();

					$temp_discount = 0;
					if($row[$i]['no_of_match']==1){ //ให้ส่วนลดค่าคอร์ท กรณีตีแค่เกมส์เดียว  ขึ้นอยู่กับการเซ็ตค่าในตาราง member_type ของแต่ละชมรม
						$temp_discount = $row[$i]['court_fee_disc_1match'];
						$temp_has_discount = '1';
					}else{
						$temp_discount = 0;
						$temp_has_discount = '0';
					}

					$mysql->insert('payment', array('payment_id' => $temp_payment_id ,'batch_id' => $_SESSION['batch_id'], 'member_id'=>$row[$i]['member_id'], 'no_of_match'=>$row[$i]['no_of_match'], 'no_of_shuttle'=>$row[$i]['no_of_shuttle'], 'court_fee'=>$row[$i]['court_fee'], 'shuttlecock_fee'=>$row[$i]['shuttle_fee'], 'total_amount'=>($row[$i]['total_amt']-$temp_discount), 'payment_status'=>'Unpaid', 'created_by'=>$_SESSION['user_name'], 'discount'=>$temp_discount, 'has_1match_discount'=>$temp_has_discount));

					$row[$i]['payment_id'] = $temp_payment_id;
				}else{
					if($row[$i]['no_of_match']>1){ //ยกเลิกส่วนลดค่าคอร์ท กรณีตีสองเกมส์ขึ้นไป
						$mysql->where(array('batch_id'=>$_SESSION['batch_id'], 'member_id'=>$row[$i]['member_id'], 'has_1match_discount'=>'1'))->update('payment', array('discount'=>0, 'has_1match_discount'=>'0'));
					}
				}

             	print "<tr>\n"; 
                print "<td align='center'>\n";
				if($row[$i]['payment_status'] != "Paid"){
					print "<input type='checkbox' id='chkFee' onclick='getTotalAmount(this);' value='".$row[$i]['member_id']."_".$row[$i]['member_name']."_".$row[$i]['no_of_match']."_".$row[$i]['sum_of_shuttle']."_".$row[$i]['court_fee']."_".$row[$i]['shuttlecock_fee']."_".$row[$i]['total_amount']."_".$row[$i]['payment_status']."'>\n";
				}else{
					print "จ่ายแล้ว";
				}
				print "</td>\n";
              	print "<td align='center'>".($i+1)."</td>\n";
              	print "<td>";
				print "<span style='font-size:14px;color:".$row[$i]['class_text_color'].";font-weight:bold' id='mmm_".$row[$i]['member_id']."'>";
				print $row[$i]['member_name'];
				print "</span>";
				if($row[$i]['line_id'] != '' && $row[$i]['payment_status'] != 'Paid' && $row[$i]['total_amount'] > 0 && $_SESSION['line_token'] != ""){
					print "&nbsp;&nbsp;<a href='javascript:notifyLinePayment(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\",\"".$row[$i]['total_amount']."\",\"".$row[$i]['sum_of_shuttle']."\",\"".$row[$i]['no_of_match']."\",\"".$row[$i]['court_fee']."\",\"".$row[$i]['shuttlecock_fee']."\",\"".$row[$i]['lose_gambling']."\",\"".$row[$i]['discount']."\");'><img src='images/line_logo.png' title='แจ้งค่าใช้จ่ายผ่านไลน์'></a>";
				}
				
				if($row[$i]['member_photo'] != ""){
					print "<script>$(document).ready(function() {Tipped.create('#mmm_".$row[$i]['member_id']."', '<img src=\"images/members/".$row[$i]['member_photo']."\" width=60 height=60>');});</script>";
				}
				print "</td>\n";
                print "<td align='left'>".$row[$i]['member_type_name']."</td>\n";
				print "<td align='right' title='ส่วนลด'><div id='disc_".$row[$i]['payment_id']."' style='display:inline-block'>".$row[$i]['discount']."</div>&nbsp;<a href='javascript:editDiscount(\"".$row[$i]['discount']."\",\"".$row[$i]['payment_id']."\")'><img src='images/icon_pencil.png' width='15px' height='15px'></a></td>\n";
				print "<td align='right' title='เสียน้ำ'><div id='lose_".$row[$i]['payment_id']."' style='display:inline-block'>".$row[$i]['lose_gambling']."</div>&nbsp;<a href='javascript:editLoseGambling(\"".$row[$i]['lose_gambling']."\",\"".$row[$i]['payment_id']."\")'><img src='images/icon_pencil.png' width='15px' height='15px'></a></td>\n";
                print "<td align='right'><span style='color:black;font-size:14px;font-weight:bold'>".$row[$i]['total_amount'];
				if($row[$i]['payment_status']=='Paid' && $row[$i]['total_amount'] != $row[$i]['paid_amount']){
					print "&nbsp;(<span style='color:red'>".$row[$i]['paid_amount']."</span>)";
				}
				print "</span></td>\n";
              	print "<td align='center' title='คลิ้ก เพื่อเปลี่ยนสถานะ'>";
				if($row[$i]['payment_status']=='Paid'){
					print "<img src='images/icon_ok.png' width='20px' height='20px' style='cursor: pointer;' onclick='setPaid(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\",\"".$row[$i]['no_of_match']."\",\"".$row[$i]['sum_of_shuttle']."\",\"".$row[$i]['court_fee']."\",\"".$row[$i]['shuttlecock_fee']."\",\"".$row[$i]['total_amount']."\",\"Unpaid\")'>";
				}else{
					print "<img src='images/icon_cancel.png' width='20px'  style='cursor: pointer;' height='20px' onclick='setPaid(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\",\"".$row[$i]['no_of_match']."\",\"".$row[$i]['sum_of_shuttle']."\",\"".$row[$i]['court_fee']."\",\"".$row[$i]['shuttlecock_fee']."\",\"".$row[$i]['total_amount']."\",\"Paid\")'>";
				}
				print "</td>\n";
              	print "<td align='center'>";
              	if($row[$i]['payment_method']=="Cash" || $row[$i]['payment_method']==""){
                  	print "<img src='images/icon_promptpay_no.png' width='58px'  style='cursor: pointer;' height='20px' onclick='setPaymentMethod(\"".$row[$i]['member_id']."\",\"Bank Transfer\")'>";
                }else{
                 	print "<img src='images/icon_promptpay.png' width='58px'  style='cursor: pointer;' height='20px' onclick='setPaymentMethod(\"".$row[$i]['member_id']."\",\"Cash\")'>"; 
                }
                print "</td>\n";
				print "<td>";
				if($row[$i]['paid_amount'] != "" && $row[$i]['paid_amount'] > 0 && $row[$i]['paid_amount'] != $row[$i]['total_amount']){
					if($row[$i]['paid_amount'] > $row[$i]['total_amount']){
						print "<span style='color:blue'>- จ่ายเกิน ".($row[$i]['paid_amount']-$row[$i]['total_amount'])." บาท</span>";
					}else{
						print "<span style='color:blue'>- จ่ายขาด ".($row[$i]['total_amount']-$row[$i]['paid_amount'])." บาท</span>";
					}
				}
				print getNoShuttleMatch($row[$i]['member_id']);
				print "</td>\n";
              	print "<td align='center'>".$row[$i]['no_of_match']."</td>\n";
              	print "<td align='right'>".$row[$i]['sum_of_shuttle']."</td>\n";
              	print "<td align='center'>".$row[$i]['court_fee']."</td>\n";
              	print "<td align='right'>".$row[$i]['total_shuttle']." (".$row[$i]['shuttlecock_fee'].")&nbsp;&nbsp;</td>\n";  	
				
              	print "<tr>\n";
				$grandTotal = $grandTotal + $row[$i]['total_amount'];
				
				if($row[$i]['payment_status'] == "Paid"){
					$grandPaid = $grandPaid + $row[$i]['paid_amount'];
					if($row[$i]['payment_method']=="Bank Transfer"){
						$grandPaidPP = $grandPaidPP + $row[$i]['paid_amount'];
						$ppMembers = $ppMembers + 1;
					}
				}

              	
            }
			print "<tfoot>";
			print "<tr>";
			print "<td colspan='6' align='right'>ยอดรายได้ทั้งหมด:</td>\n";
			print "<td align='right'><span style='font-size:14px;font-color:blue;font-weight:bold'>".number_format($grandTotal,0)."</span></td>\n";
			print "<td colspan='7' align='right'></td>\n";
			print "</tr>";
			print "<tr>";
			print "<td colspan='6' align='right'>ชำระแล้ว:</td>\n";
			print "<td align='right'><span style='font-size:14px;font-color:blue;font-weight:bold'>".number_format($grandPaid,0)."</span></td>\n";
			print "<td></td>\n";
        	print "<td>จ่ายด้วย PromptPay:</td>\n";
       		print "<td colspan='3' align='left'><span style='font-size:14px;font-color:blue;font-weight:bold'>".number_format($grandPaidPP)." (".$ppMembers." คน)</span></td>\n";
        	print "<td colspan='2' align='right'></td>\n";
			print "</tr>";
			print "<tr>";
			print "<td colspan='6' align='right'>ยังไม่ชำระ:</td>\n";
			print "<td align='right'><span style='font-size:14px;font-color:blue;font-weight:bold'>".number_format($grandTotal-$grandPaid,0)."</span></td>\n";
			print "<td colspan='7' align='right'></td>\n";
			print "</tr>";
			print "</tfoot>";
        	print "</table>\n";
        	//print $mysql->last_query();
        	print "<br/>";
			print "<strong>หมายเหตุ :</strong>";
			print "<br/>";
			print "1. <img src='images/line_logo.png' title='แจ้งค่าใช้จ่ายผ่านไลน์'> : คลิ้กที่ไอคอนไลน์ หลังชื่อ เพื่อส่งสรุปยอดค่าใช้จ่ายผ่านไลน์รายคน";
        	print "<br/>";
        	print "<br/>";
			break;
		case "set_paid":
			$row = $mysql->query("select * from payment where batch_id='".$_SESSION['batch_id']."' and member_id='".$_GET['member_id']."'", true);

			if(count($row)==0){
				$mysql->insert('payment', array('payment_id' => uniqid() ,'batch_id' => $_SESSION['batch_id'], 'member_id'=>$_GET['member_id'], 'no_of_match'=>$_GET['no_of_match'], 'no_of_shuttle'=>$_GET['no_of_shuttle'], 'court_fee'=>$_GET['court_fee'], 'shuttlecock_fee'=>$_GET['shuttle_fee'], 'total_amount'=>$_GET['total_amt'], 'payment_status'=>$_GET['payment_status'], 'created_by'=>$_SESSION['user_name']));
			}else{
				if($_GET['payment_status']=="Unpaid"){
					$paidAmt = 0;
				}else{
					$paidAmt = $_GET['total_amt'];
				}

				$mysql->where(array('batch_id' => $_SESSION['batch_id'], 'member_id' => $_GET['member_id']))->update('payment', array('no_of_match'=>$_GET['no_of_match'], 'no_of_shuttle'=>$_GET['no_of_shuttle'], 'court_fee'=>$_GET['court_fee'], 'shuttlecock_fee'=>$_GET['shuttle_fee'], 'total_amount'=>$paidAmt, 'payment_status'=>$_GET['payment_status'], 'modified_by'=>$_SESSION['user_name'], 'payment_method'=>'Cash'));
			}

			break;
		case "notify_line_payment":
			$row = $mysql->query("select a.*, b.line_id from payment a inner join member b on a.member_id=b.member_id where a.batch_id='".$_SESSION['batch_id']."' and a.member_id='".$_GET['member_id']."'", true);
			if($row[0]['line_id'] != "" && $_SESSION['line_token'] != ""){
				$strMessage = "";
				$accessToken = $_SESSION['line_token'];//copy ข้อความ Channel access token ตอนที่ตั้งค่า
				$content = file_get_contents('php://input');
				$arrayJson = json_decode($content, true);
				$arrayHeader = array();
				$arrayHeader[] = "Content-Type: application/json";
				$arrayHeader[] = "Authorization: Bearer {$accessToken}";

				$strMessage .="สรุปค่าตีแบดประจำวันที่ ".date('d/m/Y', strtotime($row[0]['created_date']))."\n\n";
				$strMessage .="1.จำนวนเกมส์ ".$row[0]['no_of_match']." เกมส์ \n";
				$strMessage .="2.จำนวนลูกใช้ไป ".$_GET['no_of_shuttle']." ลูก\n";
				if($_GET['loss_gambling'] != "" && $_GET['loss_gambling'] != "0"){
					$strMessage .="3.เสียน้ำ ".$_GET['loss_gambling']." ขวด\n";
				}
				$strMessage .="----------------------------------\n";
				if($_GET['discount'] != "" && $_GET['discount'] != "0"){
					$strMessage .="#ส่วนลด ".$_GET['discount']." บาท  \n";
				}
				$strMessage .="#รวมเงินที่ต้องชำระ ".$_GET['total_amount']." บาท  \n";
				$strMessage .="----------------------------------\n";
				$strMessage .="[อัตราค่าสนาม ".$_GET['court_fee']." บาท ค่าลูกขีดละ ".$_GET['shuttle_fee']." บาท]\n";
				if($_SESSION['club_id'] == "1" || $_SESSION['club_id'] == "3"){
					$strMessage .="\nหมายเหตุ วันเสาร์ อัตราค่าบริการจะไม่เท่ากับวันอื่นๆ เนื่องจากใช้อัตราเดิมของ Step Up\n";
				}
				$strMessage .="----------------------------------\n";
				$strMessage .="ชมรม ".$_SESSION['club_name']." ขอบคุณค่าาาา";


				$arrayPostData['to'] = $row[0]['line_id'];
				$arrayPostData['messages'][0]['type'] = "text";
				$arrayPostData['messages'][0]['text'] = $strMessage;
				pushMsg($arrayHeader,$arrayPostData);
			}

			break;
		case "set_payment_method":
		  	$row = $mysql->query("select * from payment where batch_id='".$_SESSION['batch_id']."' and member_id='".$_GET['member_id']."'", true);
			if(count($row) != 0 && $row[0]['payment_status']=="Paid"){
				$mysql->where(array('batch_id'=>$_SESSION['batch_id'], 'member_id'=>$_GET['member_id']))->update('payment', array('payment_method'=>$_GET['payment_method']));
              	print "Success";
			}else{
            	print "สมาชิกยังไม่ชำระเงิน";  
            }
		    break;

		case "save_payment_discount":
			$mysql->where(array('payment_id'=>$_GET['payment_id'], 'batch_id' => $_SESSION['batch_id']))->update('payment',array('discount' => $_GET['discount']));
            print "Success";
			break;
		
		case "save_payment_lg":
			$mysql->where(array('payment_id'=>$_GET['payment_id'], 'batch_id' => $_SESSION['batch_id']))->update('payment',array('lose_gambling' => $_GET['lose_gambling'], 'bet_rate'=>$_SESSION['bet_rate'], 'bet_cost'=>$_SESSION['bet_cost']));
            print "Success";
			break;
		case "line_notify":
			print "Success";
			break;
		}
    }

	function getNoShuttleMatch($memberID){
		global $mysql;
		$returnStr = "";
		$rowMem = $mysql->query("select match_no from matchq where match_status in('in queue', 'completed', 'playing') and (member_id_11='".$memberID."' or member_id_12='".$memberID."' or member_id_21='".$memberID."' or member_id_22='".$memberID."') and no_of_shuttle=0 and batch_id='".$_SESSION['batch_id']."'",true);

		if(count($rowMem)>0){
			$returnStr .= "<br/><span style='color:red;'>";
			$returnStr .= "- แมทช์ที่ ";
			for($i=0;$i<count($rowMem);$i++){
				$returnStr .= ($rowMem[$i]['match_no']+1);
				if(($i+1)<count($rowMem)){
					$returnStr .= ", ";
				}
			}
			$returnStr .= "&nbsp;ไม่ได้ใส่จำนวนลูกขนไก่";
			$returnStr .= "<br/><br/></span>";
		}
		return $returnStr;
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

?>
