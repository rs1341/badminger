<?php
	function checkCondition($member11, $member12, $member21, $member22){
		global $mysql;
		//Checking: MustPairWith
		$rowTemp = $mysql->query("select a.*, b.member_name as member_name1, c.member_name as member_name2 
									from member_condition a inner join member b on a.member_id_1=b.member_id and b.member_status='Active' 
															inner join member c on a.member_id_2=c.member_id and c.member_status='Active'
									where a.club_id='".$_SESSION['club_id']."' 
										and a.condition_status='Active' and a.condition_type='MustPairWith' 
										and (
                                        			(a.member_id_1='".$member11."' and '".$member12."' not in(select member_id_2 from member_condition where member_id_1=a.member_id_1 and condition_status='Active' and condition_type='MustPairWith' and club_id='".$_SESSION['club_id']."')) or
                                                    (a.member_id_1='".$member12."' and '".$member11."' not in(select member_id_2 from member_condition where member_id_1=a.member_id_1 and condition_status='Active' and condition_type='MustPairWith' and club_id='".$_SESSION['club_id']."')) or
                                                    (a.member_id_1='".$member21."' and '".$member22."' not in(select member_id_2 from member_condition where member_id_1=a.member_id_1 and condition_status='Active' and condition_type='MustPairWith' and club_id='".$_SESSION['club_id']."')) or
                                                    (a.member_id_1='".$member22."' and '".$member21."' not in(select member_id_2 from member_condition where member_id_1=a.member_id_1 and condition_status='Active' and condition_type='MustPairWith' and club_id='".$_SESSION['club_id']."')) 
                                                 )", true);
									//print $mysql->last_query();
		if(count($rowTemp)>0){
        	  	$tempStr = "";
          		$tempArr = array();
                for($i=0;$i<count($rowTemp);$i++){
                    $tempArr[$rowTemp[$i]['member_name1']][] = array('id'=>$rowTemp[$i]['member_id_1'] ,'name'=>$rowTemp[$i]['member_name1']);
                    $tempArr[$rowTemp[$i]['member_name2']][] = array('id'=>$rowTemp[$i]['member_id_2'] ,'name'=>$rowTemp[$i]['member_name2']);
                }
            	$tempMemMax = max($tempArr);
          		unset($tempArr[$tempMemMax[0]['name']]);
               	foreach($tempArr as $key=>$value){
                  	$tempStr .= $key.", ";
                }
         
				return "Error02: [".$tempMemMax[0]['name']."] ต้องเล่นคู่กัน ".rtrim($tempStr,", ")." เท่านั้น";
		}

		//Checking: NoPairWith
		$rowTemp = $mysql->query("select a.*, b.member_name as member_name1, c.member_name as member_name2 
									from member_condition a inner join member b on a.member_id_1=b.member_id and b.member_status='Active' 
															inner join member c on a.member_id_2=c.member_id and c.member_status='Active'
									where a.club_id='".$_SESSION['club_id']."' 
										and a.condition_status='Active' and a.condition_type='NoPairWith' 
										and ((a.member_id_1 in('".$member11."', '".$member12."') and a.member_id_2 in('".$member11."', '".$member12."')) 
											or (a.member_id_1 in('".$member21."', '".$member22."') and a.member_id_2 in('".$member21."', '".$member22."')))", true);
											//print $mysql->last_query();
		if(count($rowTemp)>0){
			return "Error02: [".$rowTemp[0]['member_name1']."] & [".$rowTemp[0]['member_name2']."] ไม่เล่นคู่กัน";
		}

		//Checking: NoOpponentWith
		$rowTemp = $mysql->query("select a.*, b.member_name as member_name1, c.member_name as member_name2 
									from member_condition a inner join member b on a.member_id_1=b.member_id and b.member_status='Active' 
															inner join member c on a.member_id_2=c.member_id and c.member_status='Active'
									where a.club_id='".$_SESSION['club_id']."' 
										and a.condition_status='Active' and a.condition_type='NoOpponentWith' 
										and ((a.member_id_1 in('".$member11."', '".$member12."') and a.member_id_2 in('".$member21."', '".$member22."')) 
											or (a.member_id_2 in('".$member11."', '".$member12."') and a.member_id_1 in('".$member21."', '".$member22."')))", true);
									//print $mysql->last_query();
		if(count($rowTemp)>0){
			return "Error02: [".$rowTemp[0]['member_name1']."] & [".$rowTemp[0]['member_name2']."] ไม่เล่นตรงข้ามกัน";
		}


		return "";
	}
?>