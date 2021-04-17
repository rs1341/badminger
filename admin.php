<?php
  	session_start();
	//ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
	//ini_set('error_reporting', E_ALL & ~NOTICE);
	//ini_set('log_errors', 1);
	//ini_set('display_errors', 1);
	
	if($_SESSION['user_name']==""){
    	header("Location: login.php");
    }

	include_once('db_connect.php');
    if($_SESSION['batch_id'] == ""){
      $batchArr = $mysql->query("select * from batch where batch_status != 'Cancelled' and club_id='".$_SESSION['club_id']."' order by created_date desc limit 0,1", true);
      $_SESSION['batch_id'] = $batchArr[0]['batch_id'];
      $_SESSION['batch_no'] = $batchArr[0]['batch_no'];
      $_SESSION['batch_status'] = $batchArr[0]['batch_status'];
      //$_SESSION['club_id'] = $batchArr[0]['club_id'];
	  $_SESSION['club_auto_level'] = $batchArr[0]['club_auto_level'];
    }
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<title><?php print $_SESSION['club_name'];?> : ระบบจัดมือ</title>
<link rel="stylesheet" href="css/badminger_admin.css?202009162" />
<link rel="stylesheet" href="css/jquery-ui.css" />
<link href="images/favicon.ico" type="image/x-icon" rel="icon"/>
<script type="text/javascript" src="script/js_badminger_admin.js?2021100411259"></script>
<script type="text/javascript" src="script/js_badminger_admin_condition.js?202008162"></script>
<script type="text/javascript" src="script/jquery.min.js"></script>
<script type="text/javascript" src="script/jquery-ui.js"></script>
<script type="text/javascript" src="script/jquery.ui.touch-punch.min.js"></script>

<script type="text/javascript" src="script/tipped.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/tipped.css"/>


<script>
	
	var lastMatchEdit = "";
	var ctr=1;

	/* When the user clicks on the button, 
	toggle between hiding and showing the dropdown content */
	function showCourtNo(matchNo) {
	  document.getElementById("courtDropDown"+matchNo).classList.toggle("show");
	}

	function showShuttle(matchID) {
	  document.getElementById("shuttleDropDown"+matchID).classList.toggle("show");
	}

	$(document).ready(function() {
		getMatch();
		getQueue();
	});
	  
	setInterval(blink_text, 500);
	setInterval(showWaiting, 60000);

	// Close the dropdown if the user clicks outside of it
	window.onclick = function(event) {
	  if (!event.target.matches('.dropbtn')) {
		var dropdowns = document.getElementsByClassName("dropdown-content");
		var i;
		for (i = 0; i < dropdowns.length; i++) {
		  var openDropdown = dropdowns[i];
		  if (openDropdown.classList.contains('show')) {
			openDropdown.classList.remove('show');
		  }
		}
	  }
	}

	$( function() {
		$( "#scoreEdit" ).dialog({
		  autoOpen: false,
		  show: {
			effect: "blind",
			duration: 500
		  },
		  hide: {
			effect: "explode",
			duration: 500
		  },
		  width: "400px"
    });
 
    $( "#opener" ).on( "click", function() {
		  $("#scoreEdit").dialog("open");
		});
	} );

	
</script>

<body>


<!-- HTML -->
<!-- Use any element to open the sidenav -->
<!--div style="float:left; left:3px; top: 0px; height:500px;position: absolute" id="divMenu" class="squareQueue"><img src="images/menu_hamburger2.png" onclick="openNav()" width='28px' height='32px'></div>-->
<div style="float:left; margin-left:25px; top: 5px; height:100%;position: fixed;z-index:10000;background-color:white" id="divQueue" class="squareQueue" onclick="hideAllMember();"></div>
<div style="float:left; margin-left:25px; bottom: 0px; height:28px;width:350; position: fixed;z-index:50001;background-color:white;border-radius:0px 0px 0px 0px;border: 1px solid #4781ad;" id="divMember" class="squareQueue"><table width='100%' height='28' style='background-color:#57b6ff' onclick='showHideAllMember()'><tr><td width='80%'><img src="images/icon_members.png" width="20" height="20">&nbsp;สมาชิกทั้งหมด</td><td width='20%' align='right'><img src='images/icon_arrow_up.png' id='imgAllMember' width='25' height='13' onclick='showHideAllMember()'></td></tr></table><div id='divAllMember' style="width:100%"></div></div>
<div style="position: absolute; left:500px; top:0px; width:100%;position: fixed;z-index:9999;background-color:white;height:35px;">
<div style="position: absolute; left:500px; top:5px; width:110px;position: fixed;z-index:10000;" id="divMenu1" class="labelBox" onclick="highlightObj(this.id);getMatch();hideAllMember();$(window).scrollTop(0);"><img src="images/timetable_icon.png" width="20" height="20">&nbsp;ตารางประกบคู่</div>
<div style="position: absolute; left:615px; top:5px; width:120px;position: fixed;z-index:10000;" id="divMenu2" class="labelBox" onclick="highlightObj(this.id);isOtherShowing=true;getPayment();getPayment();hideAllMember();$(window).scrollTop(0);"><img src="images/payment_icon.png" width="20" height="20">&nbsp;ค่าใช้จ่ายรายคน</div>
<div style="position: absolute; left:740px; top:5px; width:120px;position: fixed;z-index:10000;" id="divMenu3" class="labelBox" onclick="highlightObj(this.id);isOtherShowing=true;getSpending(1);hideAllMember();$(window).scrollTop(0);"><img src="images/menu_hamburger.png" width="20" height="15">&nbsp;สรุปยอดรายวัน</div>
<div style="position: absolute; left:865px; top:5px; width:100px;position: fixed;z-index:10000;" id="divMenu4" class="labelBox" onclick="highlightObj(this.id);isOtherShowing=true;getShuttleCredit();hideAllMember();$(window).scrollTop(0);"><img src="images/icon_shuttle_sheet.png" width="20" height="20">&nbsp;บัญชีเบิกลูก</div>
<div style="position: absolute; left:970px; top:5px; width:130px;position: fixed;z-index:10000;" id="divMenu5" class="labelBox" onclick="highlightObj(this.id);logout()"><img src="images/icon_logout.png" width="20" height="20">&nbsp;Logout [<?php print $_SESSION['user_name'];?>]</div>
</div>
<div style="position: absolute; height:500px; left:500px; top:40px; z-index:0; width:100%; background-color:white" id="divMatch" onclick="hideAllMember();"></div>
<div style="position: absolute; height:100%; left:20px; top:40px; z-index:1; width:100%; background-color:white; display:none;" id="divMatchOther" onclick="hideAllMember();"></div>
<div class="loader" style="z-index:100000"></div>
<div id="scoreEdit" title="Update Score"></div>
</body>
</html>
