<?php
	@set_time_limit(0);
	$d=dirname(__FILE__);
	require("$d/incCommon.php");
	include("$d/incHeader.php");

	// get a list of tables
	$arrTables=getTableList();

	// get a list of tables with records that have no owners
	foreach($arrTables as $tn=>$tc){
		$countOwned=sqlValue("select count(1) from membership_userrecords where tableName='$tn'");
		$countAll=sqlValue("select count(1) from `$tn`");

		if($countAll>$countOwned){
			$arrTablesNoOwners[$tn]=($countAll-$countOwned);
		}
	}

	// process ownership request
	if($_POST['assignOwners']!=''){
		ignore_user_abort();
		foreach($arrTablesNoOwners as $tn=>$tc){
			$groupID=intval($_POST["ownerGroup_$tn"]);
			$memberID=makeSafe(strtolower($_POST["ownerMember_$tn"]));
			$pkf=getPKFieldName($tn);

			if($groupID){
				$insertBegin="insert into membership_userrecords (tableName, pkValue, groupID, memberID, dateAdded, dateUpdated) values ";
				$ts=time();

				$res=sql("select `$tn`.`$pkf` from `$tn` left join membership_userrecords on `$tn`.`$pkf`=membership_userrecords.pkValue and membership_userrecords.tableName='$tn' where membership_userrecords.pkValue is null");
				while($row=mysql_fetch_row($res)){
					$insert.="('$tn', '$row[0]', '$groupID', ".($memberID ? "'$memberID'" : "NULL").", $ts, $ts),";
					if(strlen($insert)>50000){
						sql($insertBegin.substr($insert, 0, -1));
						$insert='';
					}
				}
				if($insert!=''){
					sql($insertBegin.substr($insert, 0, -1));
					$insert='';
				}

				$status.="Assigned ".number_format(mysql_num_rows($res))." records of table '$tn' to group '".sqlValue("select name from membership_groups where groupID='$groupID'")."'".($memberID ? ", member '$memberID'" : "").".<br />";
			}
		}

		// refresh the list of tables with records that have no owners
		unset($arrTablesNoOwners);
		foreach($arrTables as $tn=>$tc){
			$countOwned=sqlValue("select count(1) from membership_userrecords where tableName='$tn'");
			$countAll=sqlValue("select count(1) from `$tn`");

			if($countAll>$countOwned){
				$arrTablesNoOwners[$tn]=($countAll-$countOwned);
			}
		}

	}

?>

<h1>Assign ownership to data that has no owners</h1>

<?php

	// if all records of all tables have owners, no need to continue
	if(!is_array($arrTablesNoOwners)){
		echo "<div class=\"status\">All records in all tables have owners now.<br />Back to <a href=\"pageHome.php\">Admin homepage</a>.<div>";
		include("$d/incFooter.php");
		exit;
	}

	// show status of previous assignments
	if($status!=''){
		echo"<div class=\"status\">$status</div>";
	}

	// compose groups drop-down
	$htmlGroups="<option value=\"0\">--- Select group ---</option>";
	$res=sql("select groupID, name from membership_groups order by name");
	while($row=mysql_fetch_row($res)){
		$htmlGroups.="<option value=\"$row[0]\">$row[1]</option>";
	}
	$htmlGroups.="</select>";
?>

<script language="javaScript">
	var members=new Array();
	<?php
		$res=sql("select groupID, lcase(memberID) from membership_users order by groupID, memberID");
		while($row=mysql_fetch_row($res)){
			$members[$row[0]].="'".$row[1]."',";
		}

		foreach($members as $groupID=>$members){
			echo "\n\tmembers[$groupID]=[".substr($members, 0, -1)."];";
		}
	?>

	function populateMembers(memberSelect, groupSelect){
		var m=document.getElementsByName(memberSelect)[0];
		var g=document.getElementsByName(groupSelect)[0];

		if(m.options.length>0){
			var mc=m.options.length;
			for(var i=0; i<mc; i++){
				m.options[0]=null;
			}
		}

		var gval=g.options[g.selectedIndex].value;

		if(gval==0 || members[gval] == undefined){
			return 0;
		}

		for(var j=0; j<members[gval].length; j++){
			m.options[j]=new Option(members[gval][j], members[gval][j], false, false);
		}

		return 0;
	}
	</script>

<form method="post" action="pageAssignOwners.php">
	<table border="0" cellspacing="0" cellpadding="0">
		<tr><td colspan="4" style="width: 700px; padding-bottom: 20px; text-align: left;">
			Sometimes, you might have tables with data that were entered before implementing
			this AppGini membership management system, or entered using other applications
			unaware of AppGini ownership system.
			This data currently has no owners.
			This page allows you to assign owner groups and owner members to this data.
			</td></tr>
		<tr>
			<td class="tdHeader"><div class="ColCaption">Table</div></td>
			<td class="tdHeader"><div class="ColCaption">Records with no owners</div></td>
			<td class="tdHeader"><div class="ColCaption">New owner group</div></td>
			<td class="tdHeader"><div class="ColCaption">New owner member*</div></td>
			</tr>
<?php
	foreach($arrTablesNoOwners as $tn=>$countNoOwners){
		?>
		<tr>
			<td class="tdCaptionCell"><?php echo $arrTables[$tn]; ?></td>
			<td class="tdCell" align="right"><?php echo number_format($countNoOwners); ?>&nbsp;</td>
			<td class="tdCell"><select onchange="populateMembers('ownerMember_<?php echo $tn; ?>', 'ownerGroup_<?php echo $tn; ?>');" name="ownerGroup_<?php echo $tn; ?>"><?php echo $htmlGroups; ?></td>
			<td class="tdCell"><select style="width: 120px;" name="ownerMember_<?php echo $tn; ?>"></select></td>
			</tr>
		<?php
	}
?>
		<tr><td colspan="4" class="tdCaptionCell" align="right">
			<input type="button" value="Cancel" onclick="window.location='pageHome.php';">
			<input type="submit" name="assignOwners" value="Assign new owners" onclick="this.value='Please wait ...'; this.onclick='return FALSE;'; this.disabled=TRUE; return TRUE;">
			</td></tr>
		<tr><td colspan="4" class="tdCell">* If you assign no owner member here, you can still use the <a href="pageTransferOwnership.php">Batch Transfer Wizard</a> later to do so.</td></tr>
		</table>
	</form>

<?php
	include("$d/incFooter.php");
?>