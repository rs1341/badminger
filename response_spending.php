<?php
	session_start();
	ini_set('error_reporting', E_ALL & ~NOTICE);
	ini_set('log_errors', 1);
	ini_set('display_errors', 1);

	include_once('db_connect.php');
	include_once('batch_session.php');
	
	//if($_SESSION['user_name']!="" && $_SESSION['user_name']=="Tai"){
	if($_SESSION['user_name']!=""){
	print "<h2>สรุปรายรับ/รายจ่ายประจำวัน</h2>\n";

	switch($_GET['task']){
		case "get_spending":	
            $current_page = $_GET['p'];
            if($current_page == ""){
                $current_page = 1;
            }
        	print "<table class='tblpayment' width='95%'>\n";
        	print "<thead><tr>\n";
            print "<th width='5%'></th>\n";
        	//print "<th width='5%'>ลำดับ</th>\n";
        	print "<th width='9%' valign='top'>Event No.</th>\n";
        	print "<th width='8%' valign='top'>สมาชิก</th>\n";
			print "<th width='8%' valign='top'>รายรับทั้งหมด</th>\n";
        	print "<th width='8%' valign='top'>ค่าคอร์ท (ส่วนต่าง)</th>\n";
			print "<th width='8%' valign='top'>ค่าน้ำ </th>\n";
        	print "<th width='8%' valign='top'>จำนวนลูก</th>\n";
        	print "<th width='8%' valign='top'>รายรับคงเหลือ<br/><span style='font-size:8px;font-weight: normal;'>(คำนวณโดยระบบ)</span></th>\n";
			print "<th width='8%' valign='top'>รายรับคงเหลือ<br/><span style='font-size:8px;font-weight: normal;'>(นับจริง)</span></th>\n";
			print "<th width='6%' valign='top'>พร้อมเพย์</th>\n";
			print "<th width='8%' valign='top'>ส่วนต่าง/ส่วนเกิน<br/><span style='font-size:8px;font-weight: normal;'>(คำนวณโดยระบบ - พร้อมเพย์)</span></th>\n";
			print "<th width='6%' valign='top'><div class='myButtonGreen' onClick='showHideProfit();'>แสดงกำไร/ขาดทุน</div></th>\n";
        	//print "<th width='10%'>วันที่สร้าง</th>\n";
        	//print "<th width='10%'>วันที่แก้ไข</th>\n";
			print "</thead></tr>\n";
        	$row = $mysql->query("select a.*, b.batch_no as batch, date_format(a.created_date,'%Y-%m-%d %H:%i') as created2 , date_format(a.modified_date,'%Y-%m-%d %H:%i') as modified2, (select sum(dd.court_fee)-sum(dd.discount) from payment dd where dd.batch_id=a.batch_id) as member_court_fee, (select sum(dd.total_amount) from payment dd where dd.batch_id=a.batch_id and dd.payment_method='Bank Transfer') as promptpay,(select sum(dd.total_amount) from payment dd where dd.batch_id=a.batch_id) as grand_total, (select count(dd.payment_id) from payment dd where dd.batch_id=a.batch_id and dd.payment_method='Bank Transfer') as ppmembers from team_spending a inner join batch b on a.batch_id=b.batch_id and b.club_id='".$_SESSION['club_id']."' and b.batch_status in('Open','Completed') order by b.batch_no desc limit ".(($current_page-1) * 10).",10", true);
			//print $mysql->last_query();
            $cntRow = count($row);
    		for($i=0; $i<count($row); $i++){
             	print "<tr>\n"; 
                print "<td><a href='javascript: editSpending(\"".$row[$i]['id']."\",".$current_page.");'><img src='images/icon_edit.png'  height='20px' width='20px'></a></td>\n";
              	//print "<td align='center'>".($i)."</td>\n";
              	print "<td align='center'>".$row[$i]['batch']."</td>\n";
              	print "<td align='center'>".$row[$i]['no_of_members']."</td>\n";
				print "<td align='center'>".number_format($row[$i]['grand_total'],0)."</td>\n";
              	print "<td align='center'>".number_format($row[$i]['court_fee'],0)." (".($row[$i]['member_court_fee']-$row[$i]['court_fee']).")</td>\n";
				print "<td align='center'>".number_format($row[$i]['total_lose_gambling']*$_SESSION['bet_cost'],0)." (".$row[$i]['total_lose_gambling'].")</td>\n";
              	print "<td align='center'>".$row[$i]['no_of_shuttle']."</td>\n";
				print "<td align='center'>".number_format($row[$i]['remaining_amt_cal'],0)."</td>\n";
              	print "<td align='center'>".number_format($row[$i]['remaining_amt'],0)." (".($row[$i]['remaining_amt_cal']-$row[$i]['remaining_amt']).")</td>\n";
				print "<td align='center'>".number_format($row[$i]['promptpay'],0)." (".$row[$i]['ppmembers'].")</td>\n";
				print "<td align='center'>".number_format(($row[$i]['remaining_amt_cal']-$row[$i]['promptpay']-($row[$i]['total_lose_gambling']*$_SESSION['bet_cost'])),0)."</td>\n";
				print "<td align='center'><strong><span id='profit1$i'>xxxx</span><span id='profit2$i' style='display:none'>".number_format($row[$i]['balance'],0)."</span></strong></td>\n";
              	//print "<td align='center'>".$row[$i]['created2']."</td>\n";
                //print "<td align='center'>".$row[$i]['modified2']."</td>\n";
              	print "</tr>\n";
            }
			print "<tfoot><tr>\n";
        	print "<td colspan='12'>หมายเหตุ: สูตรในการคำนวณกำไรขาดทุน คือ  รายรับทั้งหมด  - ค่าคอร์ท  - (จำนวนการเสี่ยงทาย x ต้นทุนค่าน้ำ) - (จำนวนลูก  x ต้นทุนค่าลูก)</td>\n";
        	print "</tr></tfoot>\n";
        	print "</table>\n";
            print "Page: ";
            $row = $mysql->query("select a.*, date_format(b.created_date, '%Y-%m-%d') as batch, date_format(a.created_date,'%Y-%m-%d %H:%i') as created2 , date_format(a.modified_date,'%Y-%m-%d %H:%i') as modified2 from team_spending a inner join batch b on a.batch_id=b.batch_id and b.club_id='".$_SESSION['club_id']."' and b.batch_status in('Open','Completed')", true);
            $allRow = count($row);
            $all_page = ceil($allRow/10);
            for($i=1;$i<=$all_page;$i++){
                if($current_page == $i){
                   print "<strong>".$i."</strong>  ";         
                }else{
                   print "[<strong><a href='javascript:getSpending(".$i.")'>".$i."</a></strong>]  ";     
                }
                
            }
        	//print $mysql->last_query();
			break;
		case "edit_spending":
			$cBatchArr = $mysql->query("select a.*, date_format(b.created_date, '%Y-%m-%d [%a]') as batch_no, b.batch_no as batch from team_spending a inner join batch b on a.batch_id=b.batch_id where a.id='".$_GET['id']."'", true);

			$vBatchArr = $mysql->query("select sum(no_of_shuttle) as no_of_shuttle from matchq where batch_id='".$cBatchArr[0]['batch_id']."' and match_status !='cancelled'", true);
			if($vBatchArr[0]['no_of_shuttle'] != "" && $vBatchArr[0]['no_of_shuttle'] != "0"){
				$noOfShuttle = $vBatchArr[0]['no_of_shuttle'];
			}else{
				$noOfShuttle = $cBatchArr[0]['no_of_shuttle'];
			}

			$bBatchArr = $mysql->query("select sum(lose_gambling) as total_lose_gambling from payment where batch_id='".$cBatchArr[0]['batch_id']."'", true);
			if($bBatchArr[0]['total_lose_gambling'] != "" && $bBatchArr[0]['total_lose_gambling'] != "0"){
				$totalLoseGambling = $bBatchArr[0]['total_lose_gambling'];
			}else{
				$totalLoseGambling = 0;
			}

			print "<table class='tblpayment' width='95%'>\n";
        	print "<tr>\n";
            print "	<td width='15%'><strong>Event No.</strong></td>\n";
        	print "	<td width='5%'></td>\n";
        	print "	<td width='80%'><strong>".$cBatchArr[0]['batch']." (".$cBatchArr[0]['batch_no'].")</strong></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>ค่าคอร์ท</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='text' id='vCourtFee' value='".$cBatchArr[0]['court_fee']."' onclick='this.select();'></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>จำนวนเงินคงเหลือ (นับจริง)</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='text' id='vRemainingAmt' value='".$cBatchArr[0]['remaining_amt']."' onclick='this.select();'></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>จำนวนลูกขนไก่</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td style='color:red'><input type='text' id='vNoOfShuttle' value='".$noOfShuttle."' onclick='this.select();'> !ไม่ต้องกรอก ระบบจะนับลูกที่ใช้ไปในแต่ละแมทช์ให้อัตโนมัติหลังจากกดปุ่มบันทึก</td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>จำนวนการเสี่ยงทาย</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td style='color:red'><input type='text' id='vTotalLoseGambling' value='".$totalLoseGambling."' onclick='this.select();'> !ไม่ต้องกรอก ระบบจะนับจำนวนการเสี่ยงทายให้อัตโนมัติหลังจากกดปุ่มบันทึก</td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td></td>\n";
        	print "	<td></td>\n";
        	print "	<td><a href='javascript:saveSpending(\"".$_GET['id']."\",\"".$cBatchArr[0]['batch_id']."\",".$_GET['p'].")' class='myButtonBlue'>Save</a>&nbsp;<a href='javascript:getSpending(1);' class='myButtonBlue'>Cancel</a></td>\n";
			print "</tr>\n";
			print "</table>";
			break;
		case "save_spending":
			$cBatchArr = $mysql->query("select count(distinct member_id) as no_of_members from (select member_id_11 as member_id from matchq where batch_id='".$_GET['batch_id']."' and match_status !='cancelled' union 
											select member_id_12 as member_id from matchq where batch_id='".$_GET['batch_id']."' and match_status !='cancelled' union 
											select member_id_21 as member_id from matchq where batch_id='".$_GET['batch_id']."' and match_status !='cancelled' union 
											select member_id_22 as member_id from matchq where batch_id='".$_GET['batch_id']."' and match_status !='cancelled') as a", true);
			//print $mysql->last_query();
			$total_bet_earn = $_GET['total_lose_gambling'] * $_SESSION['bet_rate'];
			$total_bet_cost = $_GET['total_lose_gambling'] * $_SESSION['bet_cost'];
			
			$vBatchArr = $mysql->query("select sum(total_amount) as total_amount from payment where batch_id='".$_GET['batch_id']."'", true);
			$balance_amt_cal = $vBatchArr[0]['total_amount']-$_GET['court_fee'];

			$xBatchArr = $mysql->query("select shuttle_cost from club where club_id='".$_SESSION['club_id']."'", true);

			//print $mysql->last_query();

			$balance_amt = ($balance_amt_cal) - ($_GET['no_of_shuttle'] * $xBatchArr[0]['shuttle_cost']) - $total_bet_cost;

			$mysql->where(array('id'=>$_GET['id']))->update('team_spending',array('court_fee' => $_GET['court_fee'], 'remaining_amt' => $_GET['remaining_amt'], 'remaining_amt_cal' => $balance_amt_cal, 'no_of_members' => $cBatchArr[0]['no_of_members'], 'no_of_shuttle' => $_GET['no_of_shuttle'], 'shuttle_cost' => $xBatchArr[0]['shuttle_cost'], 'balance' => $balance_amt, 'total_lose_gambling'=>$_GET['total_lose_gambling'], 'total_bet_earn'=>$total_bet_earn, 'bet_cost'=>$_SESSION['bet_cost']));

			$mysql->insert('team_spending_audit', array('id' => uniqid() , 'spending_id' => $_GET['id'], 'court_fee' => $_GET['court_fee'], 'remaining_amt' => $_GET['remaining_amt'], 'no_of_shuttle' => $_GET['no_of_shuttle']));
			break;
	}
    } else{
	   print "<br/><br/>You don’t have permission";
    }


?>
