<?php
	session_start();
	ini_set('error_reporting', E_ALL & ~NOTICE);
	ini_set('log_errors', 1);
	ini_set('display_errors', 1);

	if($_SESSION['user_name']=="" && $_GET['club_id']==""){
		header("Location: login.php");
	}

	include_once('db_connect.php');
	include_once('resize_image.php');
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
                print "<div class='header_h1'>รายชื่อสมาชิกที่กลับบ้านแล้ว <font color='red'>".count($row)."</font> คน</div>";
				
                if(count($row)>0){
					print "<table width='100%' class='tblmatch'>";
                    $kk=0;
                    for($i=0; $i<count($row); $i++){
						if($kk==0){
							print "<tr>";
						 }
						 $kk++;
						 print "<td width='20%'>";
						 print ($i+1).". ";
						 print $row[$i]['member_name']."&nbsp;<font color=gray>[".$row[$i]['match_played']."]</font>&nbsp;&nbsp;&nbsp;";
						 print "</td>";
						 if($kk==5){
							print "</tr>";
							$kk=0;
						 }
                    }
					print "</table>";
                }else{
                    print "---- No Record!----";
                }
            }
			break;
		case "get_queue":
        	if($_SESSION['user_name'] != ""){
                $jj=0;

				$sql = "select distinct c.class_name, b.member_id, b.member_name, b.member_photo, (select count(z.match_id) from matchq z where z.batch_id=a.batch_id and (a.member_id=z.member_id_11 or a.member_id=z.member_id_12 or a.member_id=z.member_id_21 or a.member_id=z.member_id_22) and z.match_status in('in queue','completed','playing')) as match_played from queue a inner join member b on a.member_id=b.member_id inner join class c on b.class_id=c.class_id  left join matchq mq on a.match_id=mq.match_id and mq.match_status in('in queue','completed','playing')
                where a.queue_status='in queue' and b.member_status='Active' and a.batch_id='".$_SESSION['batch_id']."' ";
				
				switch($_SESSION['sorting_queue_by']){
					case "datetime":
						$sql .= "order by ifnull(mq.created_date, a.created_date), CONVERT(b.member_name USING tis620) ASC";
						break;
					case "class_only":
						$sql .= "order by c.class_id";
						break;
					case "class_and_member":
						$sql .= "order by c.class_id, b.member_name";
						break;
					case "member_name":
						$sql .= "order by CONVERT(b.member_name USING tis620) ASC";
						break;
				}
                  
                $row = $mysql->query($sql, true);
                //print $mysql->last_query();
				print "<img src='images/BadmingerLogo.png' width='200' height='41'><br/><br/>\n";
                print "<div class='header_h1'>สมาชิกที่มาแล้ว <font color='red'>".count($row)."</font> คน : ".$_SESSION['club_name'];
				print " (<font size='1px' color='red'>หมดอายุอีก ".$_SESSION['days_expiry']." วัน</font>)<br/>";
				print "<font size='1.5px' color='gray'><input type='radio' name='chkSortBy' value='1' onClick='setQueueSorting(this, \"member_name\")' ";
				if($_SESSION['sorting_queue_by']=="member_name"){
					print "checked";
				}
				print ">เรียงตามตัวอักษร (A-Z)</font>";
				print "<font size='1.5px' color='gray'><input type='radio' name='chkSortBy' value='2' onClick='setQueueSorting(this, \"datetime\")' ";
				if($_SESSION['sorting_queue_by']=="datetime"){
					print "checked";
				}
				print ">เรียงตามระยะเวลารอคอย (นานสุดขึ้นก่อน)</font>";
				print "</div>\n";
				print "<div align='center'><font color='lightgray'>*** ดับเบิ้ลคลิ้กที่ชื่อ หรือ ลากชื่อไปวางในช่องด้านขวา เพื่อจัดมือ ***</font></div>\n";
                print "<div style='clear:both'></div>\n";

                for($i=0; $i<count($row); $i++){
                    $jj++;
					//---20200601: ดับเบิ้ลคลิ้กเพื่อกำหนดให้ member รายนั้นกลับบ้าน
                    //print "<div class='square".$row[$i]['class_name']."' id='member_".$row[$i]['member_id']."' title='See History' ondblclick='removeMember(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\");' onClick='showHist(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\",\"".$row[$i]['match_played']."\");'>".$row[$i]['member_name']."&nbsp;[".$row[$i]['match_played']."]</div>"; 

					//--20200601: ดับเบิ้ลคลิ้กเพื่อสร้างเป็น match
					print "<div class='square".$row[$i]['class_name']."' id='member_".$row[$i]['member_id']."' title='1.คลิ้กหนึ่งครั้งเพื่อดูประวัติการเล่น\n2.คลิ้กสองครั้ง เพื่อเพิ่มสมาชิกรายนี้ไปที่แมทช์สุดท้าย' ondblclick='addMemberToMatch(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\", this.id);' onClick='showHist(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\",\"".$row[$i]['match_played']."\");'>".$row[$i]['member_name']."<span class='bubble'>".$row[$i]['match_played']."</span></div>\n";
                    if($jj==5){
                        print "<div style='clear:both'></div>";
                        $jj=0;
                    }
					if($row[$i]['member_photo'] != "" && $_SESSION['show_member_photo'] == '1'){
						print "<script>$(document).ready(function() {Tipped.create('#member_".$row[$i]['member_id']."', '<img src=\"images/members/".$row[$i]['member_photo']."\" width=60 height=60>');});</script>\n";
					}
                }

                //print "<div style='clear:both'></div>";
                //print "<br/>";
                //if($jj==5){
                    print "<div style='clear:both'></div>";
                //}
                print "<div class='squaretrash' onclick='highlightObj(this.id);showHomeBack();hideAllMember();' id='divBackHome' title='คลิ้กที่ชื่อสมาชิก และลากมาวางในช่องนี้ เพื่อลบสมาชิกออกจากคิว'><img src='images/icon_home.png' width='25' height='25'><br/>กลับบ้าน</div>\n";
                //print "<div class='squaredelete'><img src='images/trash.png' width='25' height='25'><br/>ลบถาวร</div>";
				
				
              	print "<div class='squareempty' valign='bottom'></div>\n";
				print "<div class='squareempty' onclick='setMemberPhoto(\"".$_SESSION['show_member_photo']."\");hideAllMember();' title='คลิ้กเพื่อ เปิด/ปิด การแสดงรูปสมาชิก (เฉพาะในหน้าประกบคู่) สามารถเพิ่มรูปสมาชิกได้ที่เมนู สมาชิกทั้งหมด->แก้ไขข้อมูลสมาชิก'>\n";
				if($_SESSION['show_member_photo']=="1"){
					print "<img src='images/icon_on.png' width='60' height='24'>\n";
				}else{
					print "<img src='images/icon_off.png' width='60' height='24'>\n";
				}
				print "แสดงรูปสมาชิก";
                print "</div>";
                print "<div class='squareempty' onclick='setAutoMatch(\"".$_SESSION['is_auto_pilot']."\");hideAllMember();' title='คลิ้กเพื่อ เปิด/ปิด ระบบประกบคู่อัตโนมัติ  เงื่อนไขการจัดประกบคู่ เข้าเมนู ตั้งค่า'>\n";
				if($_SESSION['is_auto_pilot']=="Y"){
					print "<img src='images/icon_on.png' width='60' height='24'>\n";
				}else{
					print "<img src='images/icon_off.png' width='60' height='24'>\n";
				}
				print "ประกบคู่อัตโนมัติ";
                print "</div>\n";
              	print "<div class='squareempty'  onclick='highlightObj(this.id);isOtherShowing=true;getCondition();hideAllMember();' id='divMemberCond'><img src='images/icon_member_setup.png' width='25' height='25'><br/>ตั้งค่า</div>\n";
                print "<div style='clear:both'></div>\n";


                //print "<div style='clear:both'></div>";
                //print "<br/>";
                print "<br/>";
                print "<div id='divHistory' style='font-size:12px; color:gray'></div>\n";


                //print $mysql->last_query();
                print "<div style='clear:both'></div>\n";
                print "<br/>";
              	print "<div class='header_h1'>สมาชิก 10 อันดับที่รอนานสุด </div>\n";
                print "<div id='divWaiting' style='font-size:12px; color:gray'>\n";
                getWaiting($mysql, $_SESSION['batch_id']);
                print "</div>";

				//print $mysql->last_query();
                print "<div style='clear:both'></div>";
                print "<br/>";
				print "<div class='header_h1'>วันเกิดสมาชิกเดือนนี้ [".date("d/m/Y")."]</div>\n";
                print "<div id='divWaiting' style='font-size:12px; color:gray'>\n";
                getBirthDate($mysql, $_SESSION['club_id']);
                print "</div>";

				//print $mysql->last_query();
                print "<div style='clear:both'></div>";
                print "<br/>";
				print "<div class='header_h1'>ติดต่อเรา</div>";
                print "<div id='divWaiting' style='font-size:12px; color:gray'>\n";
				print "&nbsp;&nbsp;FB:  <a href='https://www.facebook.com/badminger' target='_blank'>Badminger</a><br/>";
				print "&nbsp;&nbsp;Line:  @491abuze<br/>";
                print "</div>";           
			}
				break;
		case "list_all_member_type":
			print "<div align='right'><div class='myButtonGreen' onclick='getAllMember()'>กลับไปหน้าหลัก</div></div>";
			print "<div  style='overflow-y: scroll; height:80%'>";
			print "<h2>ประเภทสมาชิก</h2>\n";
			print "<div class='myButtonGreen' onclick='showEditMemberType(\"\")'>New</div>";
        	print "<table class='tblpayment' width='100%'>\n";
        	print "<thead><tr>\n";
			print "<th width='5%'></th>\n";
        	print "<th width='5%'>No.</th>\n";
			print "<th width='20%' align='left'>ประเภท</th>\n";
        	print "<th width='10%' align='center'>ค่าคอร์ท</th>\n";
        	print "<th width='10%' align='center'>ค่าลูก</th>\n";
        	print "<th width='50%'></th>\n";
			print "</thead></tr>\n";
			$row = $mysql->query("select * from member_type where club_id='".$_SESSION['club_id']."' order by CONVERT(member_type_name USING tis620) ASC", true);

			for($i=0; $i<count($row); $i++){
				print "<tr>\n"; 
              	print "<td align='center'>";
				print "<img src='images/icon_edit.png' height='20' width='20px' onclick='showEditMemberType(\"".$row[$i]['member_type_id']."\")'>&nbsp;";
				print "<img src='images/trash.png'  height='20' width='20px' onclick='deleteMemberTypeFromList(\"".$row[$i]['member_type_id']."\",\"".$row[$i]['member_type_name']."\")'>";
				print "</td>\n";
				print "<td align='center'>".($i+1)."</td>\n";
				print "<td align='left'>".$row[$i]['member_type_name']."</td>\n";
				print "<td align='center'>".$row[$i]['court_fee']."</td>\n";
				print "<td align='center'>".$row[$i]['shuttle_fee']."</td>\n";
				print "<td align='center'></td>\n";
				print "</tr>\n"; 
			}
			print "</table>";
			print "</div>";
			break;
		case "list_all_member":
			if(isset($_GET['keyword']) && $_GET['keyword']!=null){
				$keyword = $_GET['keyword'];
			  }else{
				$keyword = "";
			  }
			print "<div align='right'><div class='myButtonGreen' onclick='getAllMember()'>กลับไปหน้าหลัก</div></div>";
			print "<div class='header_h1'>ค้นหาสมาชิก</div>";
            print "ชื่อ: <input type='text' id='txtMemberName2' size='10' value='".$keyword."'>&nbsp;\n";
			print "<input type='button' id='btnSearch' value='Search' onClick='getAllMember2()'>&nbsp;<input type='button' value='Clear' onClick='clearForm2();getAllMember2();'>";
			print "<div  style='overflow-y: scroll; height:80%'>";
			//print "<h2>แก้ไขข้อมูลสมาชิก</h2>\n";
			print "<br/>";
        	print "<table class='tblpayment' width='100%'>\n";
        	print "<thead><tr>\n";
			print "<th width='5%'></th>\n";
        	print "<th width='5%'>No.</th>\n";
			print "<th width='10%' align='left'>ชื่อสมาชิก</th>\n";
        	print "<th width='5%' align='center'>ระดับฝีมือ</th>\n";
        	print "<th width='10%' align='center'>ประเภทสมาชิก</th>\n";
			print "<th width='10%' align='center'>รูปแบบสมาชิก</th>\n";
			print "<th width='10%' align='center'>ค่าคอร์ท</th>\n";
			print "<th width='10%' align='center'>ค่าลูก</th>\n";
			print "<th width='10%' align='center'>วันเกิด</th>\n";
			print "<th width='10%' align='center'>Line ID</th>\n";
        	print "<th width='10%' align='center'>สถานะ</th>\n";
        	print "<th width='5%'></th>\n";
			print "</thead></tr>\n";
			$sql = "select a.member_id, a.member_name, a.member_photo, b.class_title, b.class_color, c.member_type_name, c.court_fee, c.shuttle_fee, a.member_status, a.birth_date, case when a.member_category='1' then 'ขาประจำ' else 'ขาจร' end member_cate, e.display_name from member a inner join class b on a.class_id=b.class_id inner join member_type c on a.member_type_id=c.member_type_id left join line_user e on a.line_id=e.user_id and a.club_id=e.club_id where a.club_id='".$_SESSION['club_id']."' and a.member_status='Active'";
			if($keyword != ""){
				$sql .= " and a.member_name like '%".$keyword."%'";
			}
			$sql .=" order by CONVERT(a.member_name USING tis620) ASC";
			$row = $mysql->query($sql, true);

			for($i=0; $i<count($row); $i++){
				print "<tr>\n"; 
              	print "<td align='center'>";
				print "<img src='images/icon_edit.png' height='20' width='20px' onclick='showEditMember(\"".$row[$i]['member_id']."\")'>&nbsp;";
				print "<img src='images/trash.png'  height='20' width='20px' onclick='deleteMemberFromList(\"".$row[$i]['member_id']."\",\"".$row[$i]['member_name']."\")'>";
				print "</td>\n";
				print "<td align='center'>".($i+1)."</td>\n";
				print "<td align='left'>";
				print "<span id='mmmm_".$row[$i]['member_id']."'><strong>".$row[$i]['member_name']."</strong></span>";
				if($row[$i]['member_photo'] != ""){
					print "<script>$(document).ready(function() {Tipped.create('#mmmm_".$row[$i]['member_id']."', '<img src=\"images/members/".$row[$i]['member_photo']."\" width=60 height=60>');});</script>";
				}
				print "</td>\n";
				print "<td align='center' style='background-color:".$row[$i]['class_color']."'></td>\n";
				print "<td align='center' style='color:#".substr(str_replace("0","",$row[$i]['court_fee'])."000",3)."408'>".$row[$i]['member_type_name']."</td>\n";
				print "<td align='center'>".$row[$i]['member_cate']."</td>\n";
				print "<td align='center'>".$row[$i]['court_fee']."</td>\n";
				print "<td align='center'>".$row[$i]['shuttle_fee']."</td>\n";
				print "<td align='center'>".$row[$i]['birth_date']."</td>\n";
				print "<td align='center'>".$row[$i]['display_name']."</td>\n";
				print "<td align='center'>".$row[$i]['member_status']."</td>\n";
				print "<td align='center'></td>\n";
				print "</tr>\n"; 
			}
			print "</table>";
			print "</div>";
			break;
		case "edit_member":
			print "<br/>";
			print "<h2>แก้ไขข้อมูลสมาชิก</h2>\n";
			print "<div  style='overflow-y: scroll; height:80%; margin: 10px 10px'>";
			if($_GET['member_id']!=""){
				 $cBatchArr = $mysql->query("select * from member where member_id='".$_GET['member_id']."'", true);
			}
			//print $mysql->last_query();
			print "<table class='tblpayment' width='100%'>\n";
			print "<tr>\n";
			print "	<td width='10%'><strong>Member ID</strong></td>\n";
			print "	<td width='5%'></td>\n";
			print "	<td width='85%'><strong>".$cBatchArr[0]['member_id']."</strong></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>ชื่อสมาชิก</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='text' id='vName' value='".$cBatchArr[0]['member_name']."' onclick='this.select();' maxlength='15'></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>รูปแบบสมาชิก</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td>\n";
			$tempCate1 = "";
			$tempCate2 = "";

			if($cBatchArr[0]['member_category']=='1' || $_GET['member_id']==""){
				$tempCate1 = "selected";
			}
			
			if($cBatchArr[0]['member_category']=='2'){
				$tempCate2 = "selected";
			}
			print "	<select id='vCate' disabled>\n";
			print "		<option value='1' ".$tempCate1.">ขาประจำ </option>\n";
			print "		<option value='2' ".$tempCate2.">ขาจร</option>\n";
			print " </select> (ปรับอัตโนมัติด้วยระบบ)\n";
			print "</td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>ระดับฝีมือ</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td>";
			print "	<select id='vClass'>\n";
			print "	<option value=''>--select--</option>";
			$row = $mysql->query("select * from class order by class_id",true);
			for($i=0;$i<count($row);$i++){
				if($row[$i]['class_id']==$cBatchArr[0]['class_id']){
					print "<option value='".$row[$i]['class_id']."' selected>".$row[$i]['class_title']."</option>";
				}else{
					print "<option value='".$row[$i]['class_id']."'>".$row[$i]['class_title']."</option>";
				}
			}
			print " </select>\n";
			print "	</td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>ประเภทสมาชิก</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td>";
			print "	<select id='vType'>\n";
			print "	<option value=''>--select--</option>";
			$row = $mysql->query("select * from member_type where club_id='".$_SESSION['club_id']."' order by member_type_name",true);
			for($i=0;$i<count($row);$i++){
				if($row[$i]['member_type_id']==$cBatchArr[0]['member_type_id']){
					print "<option value='".$row[$i]['member_type_id']."' selected>".$row[$i]['member_type_name']."</option>";
				}else{
					print "<option value='".$row[$i]['member_type_id']."'>".$row[$i]['member_type_name']."</option>";
				}
			}
			print " </select>\n";
			print "	</td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>วันเกิด </strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='text' id='vBirthDate' value='".$cBatchArr[0]['birth_date']."' onclick='this.select();' maxlength='15'> &nbsp;(yyyy-mm-dd) กรอกเป็นปี พ.ศ.</td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>Line ID</strong></td>\n";
        	print "	<td></td>\n";
        	//print "	<td><input type='text' id='vLineID' value='".$cBatchArr[0]['line_id']."' onclick='this.select();' maxlength='15'></td>\n";
			print "		<td>\n";
			print "	<select id='vLineID'>\n";
			print "	<option value=''>--select--</option>";

			$row = $mysql->query("select * from line_user where club_id='".$_SESSION['club_id']."' and (member_id='".$_GET['member_id']."' or member_id='' or member_id is null) order by display_name",true);
			for($i=0;$i<count($row);$i++){
				if($row[$i]['user_id']==$cBatchArr[0]['line_id']){
					print "<option value='".$row[$i]['user_id']."' selected>".$row[$i]['display_name']."</option>";
				}else{
					print "<option value='".$row[$i]['user_id']."'>".$row[$i]['display_name']."</option>";
				}
			}
			print " </select>\n";
			print "		</td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>รูปสมาชิก <br/>(แนะนำขนาด 200x200px)</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='file' id='vMemberPhoto' name='vMemberPhoto' accept='image/png, image/jpeg'></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td></td>\n";
        	print "	<td></td>\n";
        	print "	<td>\n";
			if($cBatchArr[0]['member_photo'] != ""){
				print "<img src='images/members/".$cBatchArr[0]['member_photo']."?".date("YmdHis",strtotime("+7 hours"))."' id='memberImg_".$_GET['member_id']."'>&nbsp;<a href='javascript:deleteMemberPhoto(\"".$_GET['member_id']."\")'><br/>คลิ้กเพื่อลบรูปนี้</a>&nbsp;(จากนั้นกด Save ด้านล่างอีกครั้งเพื่อดึงรูปใหม่จากไลน์ หรือ กดปุ่ม 'Choose File' เพื่ออัพโหลดรูปเอง)<br/><br/>\n";
			}
			print "		<a href='javascript:saveMember(\"".$_GET['member_id']."\")' class='myButtonBlue'>Save</a>&nbsp;<a href='javascript:showListAllMember(\"\");' class='myButtonBlue'>Cancel</a></td>\n";
			print "</tr>\n";
			print "</table>";
			break;
		case "save_member_photo":
			/* Getting file name */
			$temp = explode(".", $_FILES["vMemberPhoto"]["name"]);
			$filename = $_POST['vMemberID'] . '.' . end($temp);
			/* Location */
			$location = "images/members/".$filename;
			$uploadOk = 1;
			$imageFileType = pathinfo($location,PATHINFO_EXTENSION);
			/* Valid Extensions */
			$valid_extensions = array("jpg","jpeg","png");
			/* Check file extension */
			if( !in_array(strtolower($imageFileType),$valid_extensions) ) {
			   $uploadOk = 0;
			}
			if($uploadOk == 0){
			   echo 0;
			}else{
			   /* Upload file */
			   if(move_uploaded_file($_FILES['vMemberPhoto']['tmp_name'],$location)){
				  echo $location;
				  $mysql->where(array('member_id'=>$_POST['vMemberID']))->update('member', array('member_photo'=>str_replace(".","-resized.",$filename)));
				  resizeImage($location, str_replace(".","-resized.",$location), 200, 200);
				  unlink($location);
			   }else{
				  echo 0;
			   }
			}
			break;
		case "edit_member_type":
			print "<br/>";
			print "<h2>แก้ไขข้อมูลประเภทสมาชิก</h2>\n";
			print "<div  style='overflow-y: scroll; height:80%; margin: 10px 10px'>";
			if($_GET['member_type_id']!=""){
				 $cBatchArr = $mysql->query("select * from member_type where member_type_id='".$_GET['member_type_id']."' and club_id='".$_SESSION['club_id']."'", true);
			}
			//print $mysql->last_query();
			print "<table class='tblpayment'>\n";
			print "<tr>\n";
			print "	<td width='30%'><strong>Member Type ID</strong></td>\n";
			print "	<td width='5%'></td>\n";
			print "	<td width='65%'><strong>".$cBatchArr[0]['member_type_id']."</strong></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>ประเภทสมาชิก</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='text' id='vName' value='".$cBatchArr[0]['member_type_name']."' onclick='this.select();'></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>ค่าคอร์ท</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='text' id='vCourtFee' value='".$cBatchArr[0]['court_fee']."' onclick='this.select();'></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td><strong>ค่าลูก</strong></td>\n";
        	print "	<td></td>\n";
        	print "	<td><input type='text' id='vShuttleFee' value='".$cBatchArr[0]['shuttle_fee']."' onclick='this.select();'></td>\n";
			print "</tr>\n";
			print "<tr>\n";
            print "	<td></td>\n";
        	print "	<td></td>\n";
        	print "	<td><a href='javascript:saveMemberType(\"".$_GET['member_type_id']."\")' class='myButtonBlue'>Save</a>&nbsp;<a href='javascript:showListAllMemberType();' class='myButtonBlue'>Cancel</a></td>\n";
			print "</tr>\n";
			print "</table>";
			break;
		case "get_all_member":
			if($_SESSION['user_name'] != ""){
				if(isset($_GET['keyword']) && $_GET['keyword']!=null){
					$keyword = $_GET['keyword'];
				  }else{
					$keyword = "";
				  }
				//print "<br/>";
				print "<div align='right'>";
                if($_SESSION['user_name']=='Tai'){
                    print "<div class='myButtonOrange' onclick='createNewClub()'>สร้าง Club ใหม่</div>&nbsp;&nbsp;";
                }
                print "<div class='myButtonGreen' onclick='showListAllMember(\"\")'>แก้ไขข้อมูลสมาชิก</div>&nbsp;&nbsp;<div class='myButtonOrange' onclick='showListAllMemberType()'>แก้ไขประเภทสมาชิก</div>";
                print "</div>";

                print "<div class='header_h1'>เพิ่มสมาชิกใหม่</div>";
                print "<div style='clear:both'></div>";
                print "ชื่อ: <input type='text' id='vNewMemberName' size='5' class='css-input' maxlength='15'>\n";
                print "<select id='vNewMemberClass'  class='css-input'>\n";
                $strQuery = "select * from class order by class_id";
                $classArr = $mysql->query($strQuery,true);
				print "<option value=''>----- ระดับฝีมือ ----</option>";
                for ($i=0; $i<count($classArr); $i++){
                    print "<option value='".$classArr[$i]['class_id']."'>".$classArr[$i]['class_title']."</option>";
                }
                print "</select>";
                print "&nbsp;<select id='vNewMemberType'  class='css-input'>\n";
				$strQuery = "select * from member_type where club_id='".$_SESSION['club_id']."' order by member_type_name";
                $typeArr = $mysql->query($strQuery,true);
				print "<option value=''>--------- ประเภทสมาชิก -------</option>";	
                for ($i=0; $i<count($typeArr); $i++){
                    print "<option value='".$typeArr[$i]['member_type_id']."'>".$typeArr[$i]['member_type_name']."&nbsp;[คอร์ท&nbsp;".$typeArr[$i]['court_fee']."/ลูก&nbsp;".$typeArr[$i]['shuttle_fee']."]</option>";
                }
				print "</select>";
				print "	<select id='vNewMemberCate'  class='css-input'>\n";
				print "		<option value='1'>ขาประจำ </option>\n";
				print "		<option value='2' selected>ขาจร</option>\n";
				print " </select>\n";
                print "&nbsp;<a href='javascript:addNewMember()' class='myButtonBlue'>  Add  </a>&nbsp;&nbsp;(หลังจากกดปุ่มเพิ่มแล้ว สมาชิกรายนี้จะถูกเพิ่มเข้าไปในคิวของ event ปัจจุบันโดยอัตโนมัติ)\n";
                print "<div style='clear:both'></div>";
                print "<div style='clear:both'></div>";
                print "<br/>";
                print "<div class='header_h1'>ค้นหาสมาชิก</div>";
                print "ชื่อ: <input type='text' id='txtMemberName' size='10' value='".$keyword."'>&nbsp;\n";
				
				$tempCate0="";
				$tempCate1="";
				$tempCate2="";

				if($_GET['member_category']==""){$_GET['member_category']='1';}
				
				if($_GET['is_first_char']=='1'){
					$tempCate0="checked";
				}else{
					if($_GET['member_category']=='0'){$tempCate0="checked";}
					if($_GET['member_category']=='1'){$tempCate1="checked";}
					if($_GET['member_category']=='2'){$tempCate2="checked";}
				}

				/*print "<select id='cmbMemberCate' class='css-input'>\n";
				print "		<option value='0' ".$tempCate0.">--ทั้งหมด--</option>\n";
				print "		<option value='1' ".$tempCate1.">ขาประจำ</option>\n";
				print "		<option value='2' ".$tempCate2.">ขาจร</option>\n";
				print "</select>\n";*/

				print "<input type='radio' name='cmbMemberCate' value='0' ".$tempCate0.">ทั้งหมด\n";
				print "<input type='radio' name='cmbMemberCate' value='1' ".$tempCate1.">ขาประจำ\n";
				print "<input type='radio' name='cmbMemberCate' value='2' ".$tempCate2.">ขาจร\n";
				print "&nbsp;&nbsp;";

				print "<input type='button' id='btnSearch' value='Search' onClick='getAllMember()'>&nbsp;<input type='button' value='Clear' onClick='clearForm();getAllMember();'>&nbsp;&nbsp;**คลิ้กที่ชื่อ เพื่อเพิ่มสมาชิกรายนั้นลงไปในคิว รอประกบคู่**";
                print "<div style='clear:both'></div>";
                print "<br/>";
				print "<div style='overflow-y: scroll; height:80%' align='center'><br/>\n";

				$thaichar = array('ก','ข','ค','ง','จ','ฉ','ช','ซ','ฌ','ญ','ฐ','ฑ','ฒ','ณ','ด','ต','ถ','ท','ธ','น','บ','ป','ผ','ฝ','พ','ฟ','ภ','ม','ย','ร','ล','ว','ศ','ษ','ส','ห','ฬ','อ','ฮ','EN');   // ถ้า error บรรทัดนี้ก็รวมสามบรรทัดนี้ให้เป็นบรรทัดเดียว ที่ขึ้นบรรทัดใหม่เพราะว่ามันดันหน้าเว็บ
				  
				foreach ($thaichar as &$alphabet) {     
				   echo "<div class='charSearch' onclick='getAllMemberByFirstChar(\"".$alphabet."\")'>".$alphabet."</div>";
				}

				print "<div style='clear:both'></div>";
				print "<br/>";


                $jj=0;

                $strQuery = "select a.*, b.class_name from member a inner join class b on a.class_id=b.class_id where a.member_status='Active' and a.club_id='".$_SESSION['club_id']."' and a.member_id not in(select member_id from queue where queue_status='in queue' and batch_id='".$_SESSION['batch_id']."') ";
                if($keyword!=""){
					$strQuery = $strQuery." and a.member_name like '%".$keyword."%'";
                }

				if($_GET['is_first_char']=='1'){
					if($_GET['keyword2']=='EN'){
						$strQuery = $strQuery." and a.member_name rlike '^[abcdefghijklmnopqrstuvwxyz]'";
					}else{
						$strQuery = $strQuery." and (a.member_name like '".$_GET['keyword2']."%' OR (
a.member_name like '_".$_GET['keyword2']."%' AND SUBSTR(a.member_name,1,1) IN ('โ', 'เ', 'แ', 'ไ', 'ใ')))";
					}
				}

				if(($_GET['member_category'] == '1' || $_GET['member_category'] == '2') && $_GET['is_first_char'] == ''){
                  $strQuery = $strQuery." and a.member_category='".$_GET['member_category']."'";
                }


                $strQuery = $strQuery." order by b.class_id, CONVERT(a.member_name USING tis620) ASC";

				$memberArr = $mysql->query($strQuery,true);
				//print $strQuery;
				for($i=0; $i<count($memberArr); $i++){
				  $jj++;
				  print "<div class='squareFix".$memberArr[$i]['class_name']."' id='m_".$memberArr[$i]['member_id']."' title='Add ".$memberArr[$i]['member_name']." to queue' onClick='addMember(this.id,\"".$memberArr[$i]['member_name']."\",\"\")'><img src='images/add.png' height='15px' width='15px'>&nbsp;".$memberArr[$i]['member_name']."</div>";
				  if($jj==13){
					  print "<div style='clear:both'></div>";
					  $jj=0;
				  }
				  if($memberArr[$i]['member_photo'] != ""){
						print "<script>$(document).ready(function() {Tipped.create('#m_".$memberArr[$i]['member_id']."', '<img src=\"images/members/".$memberArr[$i]['member_photo']."\" width=60 height=60>');});</script>";
				  }
				}

				if($i==0){
					print "-- ไม่พบข้อมูล --";
				}
				print "</div>\n";
			}
			break;
		case "add_member":
        	if($_SESSION['user_name'] != ""){
                if($_SESSION['batch_id'] != "" && $_SESSION['batch_status'] != "Completed"){
                    $mysql->insert('queue', array('queue_id' => uniqid(), 'member_id' => str_replace("m_","",$_GET['member_id']), 'batch_id' => $_SESSION['batch_id'], 'queue_status' => 'in queue', 'created_by' => $_SESSION['user_name']));
                }else{
                    print "Error";
                }
            }
			break;
        case "add_new_member":
        	if($_SESSION['user_name'] != ""){
                $row = $mysql->query("select * from member where member_name='".trim(str_replace(" ","",$_GET['member_name']))."' and member_status='Active' and club_id='".$_SESSION['club_id']."'", true);
                if(count($row)==0){
                  	$tempID = uniqid();
                    $mysql->insert('member', array('member_id' => $tempID, 'member_name' => trim(str_replace(" ","",$_GET['member_name'])), 'class_id' => $_GET['class_id'], 'member_status' => 'Active', 'club_id'=> $_SESSION['club_id'], 'member_type_id' => $_GET['member_type'], 'member_category' => $_GET['member_category']));
                    print "Success|".$tempID;
                }else{
                    print "Duplicate|";
                }
            }
			break;
		case "save_member":
        	if($_SESSION['user_name'] != ""){
                $row = $mysql->query("select * from member where member_name='".trim(str_replace(" ","",$_GET['member_name']))."' and member_status='Active' and club_id='".$_SESSION['club_id']."' and member_id != '".$_GET['member_id']."'", true);
                if(count($row)==0){
                    $mysql->where(array('member_id'=>$_GET['member_id']))->update('member', array('member_name' => trim(str_replace(" ","",$_GET['member_name'])), 'class_id' => $_GET['class_id'],'member_type_id' => $_GET['member_type'], 'member_category' => $_GET['member_category'], 'birth_date' => $_GET['birth_date'], 'line_id' => $_GET['line_id']));

					if($_GET['line_id'] != ""){

						$mysql->where(array('user_id'=>$_GET['line_id'], 'club_id'=>$_SESSION['club_id']))->update('line_user', array('member_id' => $_GET['member_id']));

						//copy photo from line account
						$row2 = $mysql->query("select a.*,b.user_id,b.picture_url from member a left join line_user b on a.line_id=b.user_id where a.member_id = '".$_GET['member_id']."' and a.line_id !='' and a.member_photo=''", true);

						if(count($row2)>0){
							$filename = $row2[0]['member_id'].'.jfif';
							/* Location */
							$location = "images/members/".$filename;
							//copy($row2[0]['picture_url'], $location);
							file_put_contents($location, file_get_contents($row2[0]['picture_url']));
							$mysql->where(array('member_id'=>$row2[0]['member_id']))->update('member', array('member_photo'=>str_replace(".","-resized.",$filename)));
							resizeImage($location, str_replace(".","-resized.",$location), 200, 200);
						}
					}else{
						$mysql->where(array('member_id'=>$_GET['member_id'], 'club_id'=>$_SESSION['club_id']))->update('line_user', array('member_id' => ''));
					}

                    print "Success|".$tempID;
                }else{
                    print "Duplicate|";
                }
            }
			break;
		case "save_member_type":
        	if($_SESSION['user_name'] != ""){
				if($_GET['member_type_id'] != ""){//edit
					$row = $mysql->query("select * from member_type where member_type_name='".trim(str_replace(" ","",$_GET['member_type_name']))."' and club_id='".$_SESSION['club_id']."' and member_type_id != '".$_GET['member_type_id']."'", true);
					if(count($row)==0){
						$mysql->where(array('member_type_id'=>$_GET['member_type_id']))->update('member_type', array('member_type_name' => trim(str_replace(" ","",$_GET['member_type_name'])), 'court_fee' => $_GET['court_fee'],'shuttle_fee' => $_GET['shuttle_fee'], 'modified_by' => $_SESSION['user_name']));
						print "Success|".$tempID;
					}else{
						print "Duplicate|";
					}
				}else{//insert
					$row = $mysql->query("select * from member_type where member_type_name='".trim(str_replace(" ","",$_GET['member_type_name']))."' and club_id='".$_SESSION['club_id']."'", true);
					if(count($row)==0){
						$mysql->insert('member_type', array('member_type_id' => uniqid() ,'member_type_name' => trim(str_replace(" ","",$_GET['member_type_name'])), 'court_fee' => $_GET['court_fee'],'shuttle_fee' => $_GET['shuttle_fee'], 'club_id' => $_SESSION['club_id'], 'created_by' => $_SESSION['user_name']));
						print "Success|".$tempID;
					}else{
						print "Duplicate|";
					}
				}
            }
			break;
		case "delete_member_photo":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('member_id'=>$_GET['member_id']))->update('member', array('member_photo'=>''));
            }
			break;
		case "delete_member":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('member_id'=>$_GET['member_id']))->update('member', array('member_status'=>'Inactive'));
                $mysql->where(array('member_id'=>$_GET['member_id'], 'batch_id' => $_SESSION['batch_id']))->delete('queue');
            }
			break;
		case "delete_member_type":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('member_type_id'=>$_GET['member_type_id'], 'club_id' => $_SESSION['club_id']))->delete('member_type');
            }
			break;
		case "remove_member":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('member_id'=>$_GET['member_id'], 'batch_id' => $_SESSION['batch_id']))->delete('queue');
            }
			break;
		case "set_queue_sorting":
        	if($_SESSION['user_name'] != ""){
                $mysql->where(array('club_id'=>$_SESSION['club_id']))->update('club', array('sorting_queue_by'=>$_GET['sorting_queue_by']));
				$_SESSION['sorting_queue_by'] = $_GET['sorting_queue_by'];
            }
			break;
		
	}

	function getWaiting($vMysql, $vBatchID){
		//$row = $vMysql->query("select a.member_name, TIMESTAMPDIFF(MINUTE,max(b.created_date),now()) as waiting_minute from member a left join matchq b on (b.member_id_11 = a.member_id or b.member_id_12 = a.member_id or b.member_id_21 = a.member_id or b.member_id_22 = a.member_id) and b.match_status in('completed','in queue','in match') where b.batch_id='".$vBatchID."' and a.member_id in(select member_id from queue where batch_id='".$vBatchID."') group by a.member_name order by max(b.created_date) limit 0,5", true);
		//
		//$row = $vMysql->query("select a.member_name, TIMESTAMPDIFF(MINUTE,max(b.created_date),now()) as waiting_minute from member a inner join queue b on b.member_id = a.member_id and b.queue_status in('in queue') where b.batch_id='".$vBatchID."' group by a.member_name order by max(b.created_date) limit 0,10", true);

		$row = $vMysql->query("
			SELECT A.MEMBER_ID, B.member_name, TIMESTAMPDIFF(MINUTE,MAX(A.COMPLETED_DATE),now()) as waiting_minute FROM ( 
				SELECT mm.MEMBER_ID_11 AS MEMBER_ID, mm.COMPLETED_DATE, pm.PAYMENT_STATUS FROM matchq mm INNER JOIN batch bb ON mm.batch_id=bb.batch_id LEFT JOIN payment pm ON mm.batch_id=pm.batch_id AND mm.MEMBER_ID_11=pm.MEMBER_ID WHERE mm.BATCH_ID='".$vBatchID."' AND mm.MATCH_STATUS IN('completed','playing') AND bb.BATCH_STATUS ='Open' UNION ALL 
				SELECT mm.MEMBER_ID_12 AS MEMBER_ID, mm.COMPLETED_DATE, pm.PAYMENT_STATUS FROM matchq mm INNER JOIN batch bb ON mm.batch_id=bb.batch_id LEFT JOIN payment pm ON mm.batch_id=pm.batch_id AND mm.MEMBER_ID_12=pm.MEMBER_ID  WHERE mm.BATCH_ID='".$vBatchID."' AND mm.MATCH_STATUS IN('completed','playing') AND bb.BATCH_STATUS ='Open' UNION ALL 
				SELECT mm.MEMBER_ID_21 AS MEMBER_ID, mm.COMPLETED_DATE, pm.PAYMENT_STATUS FROM matchq mm INNER JOIN batch bb ON mm.batch_id=bb.batch_id LEFT JOIN payment pm ON mm.batch_id=pm.batch_id AND mm.MEMBER_ID_21=pm.MEMBER_ID   WHERE mm.BATCH_ID='".$vBatchID."' AND mm.MATCH_STATUS IN('completed','playing') AND bb.BATCH_STATUS ='Open' UNION ALL 
				SELECT mm.MEMBER_ID_22 AS MEMBER_ID, mm.COMPLETED_DATE, pm.PAYMENT_STATUS FROM matchq mm INNER JOIN batch bb ON mm.batch_id=bb.batch_id LEFT JOIN payment pm ON mm.batch_id=pm.batch_id AND mm.MEMBER_ID_22=pm.MEMBER_ID   WHERE mm.BATCH_ID='".$vBatchID."' AND mm.MATCH_STATUS IN('completed','playing') AND bb.BATCH_STATUS ='Open' UNION ALL
				SELECT qq.MEMBER_ID AS MEMBER_ID, qq.CREATED_DATE AS COMPLETED_DATE, '' PAYMENT_STATUS FROM queue qq INNER JOIN batch bb ON qq.BATCH_ID=bb.BATCH_ID LEFT JOIN matchq mm ON qq.MATCH_ID=mm.MATCH_ID WHERE qq.BATCH_ID='".$vBatchID."' AND ((qq.MATCH_ID IS NULL OR qq.MATCH_ID='') OR (qq.MATCH_ID!='' AND (mm.MATCH_STATUS='cancelled' OR mm.MATCH_STATUS IS NULL))) AND bb.BATCH_STATUS='Open'
			) AS A 
			INNER JOIN member B ON A.MEMBER_ID=B.MEMBER_ID AND A.PAYMENT_STATUS != 'Paid'
			GROUP BY A.MEMBER_ID, B.MEMBER_NAME HAVING TIMESTAMPDIFF(MINUTE,MAX(A.COMPLETED_DATE),now())>0 ORDER BY MAX(A.COMPLETED_DATE), CONVERT(B.MEMBER_NAME USING tis620) LIMIT 0,10",true);
			//print $vMysql->last_query();
            for($i=0; $i<count($row); $i++){
              print ($i+1).". ";
              print "<font color='blue'>".$row[$i]['member_name']."</font>&nbsp;&nbsp;[".$row[$i]['waiting_minute']." นาที]";
              print "<br/>";
            }
	}

	function getBirthDate($vMysql, $vClubID){
		//$row = $vMysql->query("select a.member_name, TIMESTAMPDIFF(MINUTE,max(b.created_date),now()) as waiting_minute from member a left join matchq b on (b.member_id_11 = a.member_id or b.member_id_12 = a.member_id or b.member_id_21 = a.member_id or b.member_id_22 = a.member_id) and b.match_status in('completed','in queue','in match') where b.batch_id='".$vBatchID."' and a.member_id in(select member_id from queue where batch_id='".$vBatchID."') group by a.member_name order by max(b.created_date) limit 0,5", true);
		$row = $vMysql->query("select a.member_name, date_format(birth_date,'%b-%d') as birth_date from member a where a.club_id='".$vClubID."' and a.birth_date != '0000-00-00' and date_format(a.birth_date,'%m')=date_format(now(), '%m') and date_format(a.birth_date,'%d') > (date_format(now(), '%d')-5) order by date_format(a.birth_date,'%d')", true);

            for($i=0; $i<count($row); $i++){
              print ($i+1).". ";
              print "[".$row[$i]['birth_date']."] - ".$row[$i]['member_name'];
			  if($row[$i]['birth_date']==date("M-d")){
				print "&nbsp;<font color='red'>[HBD Today]</font>&nbsp;<img src='images/birth_day_icon.png' width='15px' height='15px'>";
			  }
              print "<br/>";
            }
	}


?>
