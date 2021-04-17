<?php
	session_start();
	ini_set('error_reporting', E_ALL & ~NOTICE);
	ini_set('log_errors', 1);
	ini_set('display_errors', 1);

	include_once('db_connect.php');
	include_once('batch_session.php');

	if($_SESSION['user_name'] != ""){
	switch($_GET['task']){
		case "get_shuttle":
        	$UnpaidAmt=0;
			print "<h2>บัญชีเบิกลูกขนไก่</h2>\n";
            print "<a href='javascript:newShuttleCredit()' class='myButtonGreen'>New</a><br/><br/>\n";
			print "<table class='tblpayment' width='95%'>\n";
			print "<thead><tr>\n";
            print "<th width='5%'></th>\n";
			//print "<th width='5%'>ลำดับ</th>\n";
			print "<th width='10%'>วันที่เบิก</th>\n";
			print "<th width='10%' align='center'>จำนวนหลอด</th>\n";
			print "<th width='15%'>สถานะการชำระเงิน</th>\n";
			print "<th width='15%' align='center'>หมายเหตุ</th>\n";
			print "<th width='15%'>วันที่ชำระ</th>\n";
			print "<th width='15%'>วันที่สร้าง</th>\n";
            print "<th width='15%'>วันที่แก้ไข</th>\n";
			print "</thead></tr>\n";
        	$row = $mysql->query("select * from shuttlecock_credit where club_id='".$_SESSION['club_id']."' order by created_date desc limit 0,10", true);
     
    		for($i=0; $i<count($row); $i++){
              	if($row[$i]['transaction_status']=="Pending"){
                 	$UnpaidAmt = $UnpaidAmt +  $row[$i]['transaction_amt'];
                }
             	print "<tr>\n"; 
                print "<td><img src='images/icon_edit.png' height='20px' style='cursor: pointer;' width='20px' onclick='editShuttleCredit(\"".$row[$i]['transaction_id']."\")'>&nbsp;&nbsp;<img src='images/trash.png' style='cursor: pointer;' height='20px' width='20px' onclick='deleteShuttleCredit(\"".$row[$i]['transaction_id']."\")'></td>\n";
              	//print "<td align='center'>".($i+1)."</td>\n";
              	print "<td align='center'>".$row[$i]['transaction_date']."</td>\n";
              	print "<td align='center'>".$row[$i]['transaction_amt']."</td>\n";
				if($row[$i]['transaction_status']=="Paid"){
					print "<td align='center'><img src='images/icon_ok.png' style='cursor: pointer;' width='20px' height='20px' onclick='setPaymentStatusShuttleCredit(\"".$row[$i]['transaction_id']."\", \"Pending\");'></td>\n";
				}else{
					print "<td align='center'><img src='images/icon_cancel.png' style='cursor: pointer;' width='20px' height='20px' onclick='setPaymentStatusShuttleCredit(\"".$row[$i]['transaction_id']."\", \"Paid\");'></td>\n";
				}
				
				print "<td align='left'>".$row[$i]['remark']."</td>\n";
				print "<td align='center'>".$row[$i]['paid_date']."</td>\n";
                print "<td align='left'>".$row[$i]['created_date']."</td>\n";
                print "<td align='left'>".$row[$i]['modified_date']."</td>\n";
				print "</tr>\n";
            }
        	print "<tfoot><tr>\n";
        	print "<td colspan='3' align='right'><span style='font-size:16px; color:blue;'>ยอดค้างชำระ</span></td>\n";
        	print "<td align='right'><span style='font-size:16px; color:red;'>".$UnpaidAmt."</span></td>\n";
        	print "<td colspan='4'><span style='font-size:16px; color:blue;'>&nbsp;&nbsp;หลอด</span></td>\n";
        	print "</tr></tfoot>\n";
        	print "</table>\n";
        	//print $mysql->last_query();
			break;
		case "edit_shuttle":
        	print "<h2>บัญชีเบิกลูกขนไก่จากพี่ยุทธ</h2>\n";
			if($_GET['transaction_id']!=""){
				 $cBatchArr = $mysql->query("select * from shuttlecock_credit where transaction_id='".$_GET['transaction_id']."'", true);
			}
			//print $mysql->last_query();
			print "<table class='tblpayment'>\n";
			print "<tr>\n";
			print "	<td width='30%'><strong>Transaction ID</strong></td>\n";
			print "	<td width='5%'></td>\n";
			print "	<td width='65%'><strong>".$cBatchArr[0]['transaction_id']."</strong></td>\n";
			print "</tr>\n";
			print "<tr>\n";
			print "	<td><strong>วันที่เบิก</strong></td>\n";
			print "	<td></td>\n";
			if($_GET['transaction_id']!=""){
				 print "	<td><input type='text' id='vTransactionDate' value='".$cBatchArr[0]['transaction_date']."'></td>\n";
			}else{
				 print "	<td><input type='text' id='vTransactionDate' value='".date('Y-m-d')."'></td>\n";
			}
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>จำนวนหลอด</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='text' id='vTransactionAmt' value='".$cBatchArr[0]['transaction_amt']."' onclick='this.select();'></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>สถานะการชำระเงิน</strong></td>\n";
        	print "	<td></td>\n";
            $selectedPaid="";
            $selectedUnPaid="";
            if($cBatchArr[0]['transaction_status']=="Paid"){
              $selectedPaid="selected";
              $selectedPending="";
            }else{
              $selectedPaid="";
              $selectedPending="selected";
            }
        	print "	<td><select id='vTransactionStatus'><option value='Paid' ".$selectedPaid.">จ่ายแล้ว</option><option value='Pending' ".$selectedPending.">ยังไม่จ่าย</option></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>หมายเหตุ</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='text' id='vRemark' value='".$cBatchArr[0]['remark']."' onclick='this.select();'></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td></td>\n";
        	print "	<td></td>\n";
        	print "	<td><a href='javascript:saveShuttleCredit(\"".$_GET['transaction_id']."\")' class='myButtonBlue'>Save</a>&nbsp;<a href='javascript:getShuttleCredit();' class='myButtonBlue'>Cancel</a></td>\n";
			print "</tr>\n";
			print "</table>";
			break;
		case "save_shuttle":
			if($_GET['transaction_id']!=""){
				$mysql->where(array('transaction_id'=>$_GET['transaction_id']))->update('shuttlecock_credit',array('transaction_date'=>$_GET['transaction_date'], 'transaction_amt'=>$_GET['transaction_amt'], 'transaction_status'=>$_GET['transaction_status'], 'remark'=>$_GET['remark'], 'club_id'=>$_SESSION['club_id']));
			}else{
				$mysql->insert('shuttlecock_credit',array('transaction_id'=> uniqid(),'transaction_date'=>$_GET['transaction_date'], 'transaction_amt'=>$_GET['transaction_amt'], 'transaction_status'=>$_GET['transaction_status'], 'remark'=>$_GET['remark'], 'club_id'=>$_SESSION['club_id']));
			}   
				break;
		case "delete_shuttle":
				$mysql->where(array('transaction_id'=>$_GET['transaction_id']))->delete('shuttlecock_credit');
				break;

		case "set_payment_status":
				if($_GET['transaction_status']=="Paid"){
					$mysql->where(array('transaction_id'=>$_GET['transaction_id']))->update('shuttlecock_credit',array('transaction_status'=>$_GET['transaction_status'], 'paid_date'=>date('Y-m-d H:i:s')));
				}else{
					$mysql->where(array('transaction_id'=>$_GET['transaction_id']))->update('shuttlecock_credit',array('transaction_status'=>$_GET['transaction_status'], 'paid_date'=>null));
				}
				break;

		}
    }

?>
				