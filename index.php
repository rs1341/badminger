<?php
  	session_start();
	//ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
	ini_set('error_reporting', E_ALL);
	ini_set('log_errors', 1);
	ini_set('display_errors', 1);
	include_once('classes/db_classes.php');
	$mysql = new MySQL('localhost', 'jipatas_bmg', 'fduBB6KQ', 'jipatas_badminger');

?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<title><?php print @$_SESSION['club_name'];?> : ตารางประกบคู่</title>
<link rel="stylesheet" href="css/jquery-ui.css" />
<link rel="stylesheet" href="css/badminger_user.css?20200911" />
<script type="text/javascript" src="script/js_badminger_user.js?20200311"></script>
<script type="text/javascript" src="script/jquery.min.js"></script>
<script type="text/javascript" src="script/jquery-ui.js"></script>
<script type="text/javascript" src="script/jquery.ui.touch-punch.min.js"></script>
<script>
	function showCourtNo(matchNo) {
	  document.getElementById("courtDropDown"+matchNo).classList.toggle("show");
	}

	function getMatch() {
		setLoading();
		$('#divMatch').load('response_match.php?task=get_match&is_preview=1&club_id=<?php print $_GET["club_id"];?>', function(){
			refreshCountDown();
			setLoading();
		});
	}


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

	setInterval(blink_text, 500);
</script>

<body onload="getMatch();">

<!-- HTML -->
<div style="float:left; left:5px; top: 0px; height:18px;width:100px; position: fixed;z-index:50001;background-color:white;border-radius:0px 0px 0px 0px;border: 0px solid #4781ad;opacity: 90%; text-align: center" id="divUpdate" class="squareQueue"><span class='txtSmallGreen'><a href='line://msg/text/?http://www.ibadclub.com/badminger/index.php?club_id=<?php print $_GET["club_id"];?>' target="_blank"><img src='images/share-now-logo.png'></a></span></div>
<div style="position: fixed;  top:25px; left:120px; width:500px; height:88px; background-color: yellow;text-align:left; font-size:12px;z-index:9999" id="divQueue" class="squareQueue"><strong>&nbsp;++ ประกาศ ประกาศ ประกาศ ++</strong><br/>&nbsp;1.กรณี Event ยังไม่จบ ระบบจะรีเฟรชหน้านี้อัตโนมัติทุก 15 วินาที เปิดค้างไว้เลยครับ  <br/>&nbsp;2.ช่วยกันอัพเดทคะแนนของตัวเอง โดยการคลิ้กไปที่คะแนน จะมีช่องให้กรอกครับ <br/>&nbsp;3.เปิดหน้านี้ได้ง่ายๆ ด้วยการยิง QR Code ด้านซ้าย นี้ได้เลย หรือจะส่งเป็นรูปให้เพื่อนก็ได้ครับ <br/>&nbsp;4.หากรอคิวนาน หรือ ไม่มีรายชื่อในคิว แจ้งผู้จัดได้ที่โต๊ะเลยครับ<br/></div>
<div style="position: fixed; top:15px; left:0px; z-index:9999 "><a href='line://msg/text/?http://www.ibadclub.com/badminger/index.php?club_id=<?php print $_GET["club_id"];?>' target="_blank"><img src="qr_generator.php?club_id=<?=$_GET["club_id"];?>"/></a></div>
<div style="position: fixed; top:0px; height:120px; width:100%; z-index:150; background-color: white;"></div>
<div style="position: absolute; top:110px; float:left; scroll; height:500px; z-index:1" id="divMatch"></div>
<div id="scoreEdit" title="Update Score"></div>
<div class="loader" style="z-index:50000"></div>
<div style="float:left; left:120px; top: 0px; height:18px;width:499px; position: fixed;z-index:50001;background-color:white;border-radius:0px 0px 0px 0px;border: 1px solid #4781ad;opacity: 90%; text-align: center" id="divUpdate" class="squareQueue"><span id='autoSec' class='txtSmallLightGray'>(Auto Refresh in 15 seconds)</span></div>
</body>
</html>
