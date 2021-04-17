<?php
	session_start();
	ini_set('error_reporting', E_ALL & ~NOTICE);
	ini_set('log_errors', 1);
	ini_set('display_errors', 1);

	include_once('db_connect.php');
	include_once('batch_session.php');
	
	switch($_GET['task']){
		case "create_club":
            if($_SESSION['user_name'] != ""){
                $clubID = uniqid();
                if($_GET['club_name'] != "" && $_GET['user_name'] !=""){
                    $row = $mysql->query("select * from club where club_name='".$_GET['club_name']."'", true);
                    if(count($row)==0){
                        $mysql->insert('club', array('club_id' => $clubID, 'club_name' => $_GET['club_name'], 'product_id' => '1', 'shuttle_cost'=> '56', 'club_status' => 'Active', 'created_by'=>$_SESSION['user_name']));

                        $mysql->insert('member_type', array('member_type_id' => uniqid(), 'member_type_name' => 'Adult', 'court_fee' => '100', 'shuttle_fee' => '18', 'club_id' => $clubID, 'created_by' => $_SESSION['user_name']));
                        $mysql->insert('member_type', array('member_type_id' => uniqid(), 'member_type_name' => 'Student', 'court_fee' => '50', 'shuttle_fee' => '18', 'club_id' => $clubID, 'created_by' => $_SESSION['user_name']));
                        $mysql->insert('member_type', array('member_type_id' => uniqid(), 'member_type_name' => 'Organizer', 'court_fee' => '0', 'shuttle_fee' => '0', 'club_id' => $clubID, 'created_by' => $_SESSION['user_name']));

                        $mysql->insert('user', array('user_id' => uniqid(), 'user_name' => $_GET['user_name'], 'user_password' => md5($_GET['user_name'].'1234'), 'club_id' => $clubID, 'user_status' => 'Active', 'user_expiry_date' => date("Y-m-d", strtotime("+1 month", strtotime(date("Y-m-d",strtotime("+7 hours"))))), 'created_by'=>$_SESSION['user_name']));
                        print "Created Club Successfully!!</br></br/>";
						print "URL:  http://www.ibadclub.com/badminger/admin.php</br>";
						print "Username: ".$_GET['user_name']."</br>";
						print "Password: ".$_GET['user_name']."1234</br>";
                        print "<br/>";
                        print "[<a href='javascript:location.href=\"admin.php\"'>Back</a>]";
                    }else{
                        print "ชื่อ club ซ้ำ";
                    }
                }else{
                    print "กรุณาระบุ club_name และ user_name";

                }
            }

			break;
		case "set_member_photo":
			$mysql->where(array('club_id' => $_SESSION['club_id']))->update('club', array('show_member_photo'=>$_GET['show_member_photo']));
			$_SESSION['show_member_photo'] = $_GET['show_member_photo'];
			break;
	}

?>
