<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* Define $CCobjectName, $CCobjectsNam, $CCHEBobjectName, $CCfieldsList  before including this file	*/
/* ----------------------------------------------------------------------------------------------------	*/

$CCObjectName = ucfirst($CCobjectName);

$CCObjectsName = ucfirst($CCobjectsName);

$CCglobals = "";
$i=0;
foreach ($CCfieldsList as $val)
{
	if ($i == 0)
		$CCglobals .= "global ";
	else
		$CCglobals .= ", ";
	$CCglobals .= "\$".$val;
	$i++;
}
if ($i > 0)
       $CCglobals .= ";";

$CCfieldNames = "";
$i=0;
foreach ($CCfieldsList as $val)
{
	if ($i > 0)
	       $CCfieldNames .= ", ";
	$CCfieldNames .= $val;
	$i++;
}

$CCfieldVals = "";
$i=0;
foreach ($CCfieldsList as $val)
{
	if ($i > 0)
	       $CCfieldVals .= ", ";
	$CCfieldVals .= "'\$".$val."'";
	$i++;
}

$CCfieldSets = "";
$i=0;
foreach ($CCfieldsList as $val)
{
	if ($i > 0)
	       $CCfieldSets .= ", ";
	$CCfieldSets .= $val." = '\$".$val."'";
	$i++;
}

eval ("function CC_get".$CCobjectName."NextId (\$xmlRequest, \$idFieldName = \"id\")
	{
		\$queryStr	= \"select max(\$idFieldName) from ".$CCobjectsName."\";
		\$result	= commonDoQuery (\$queryStr);
		\$row		= commonQuery_fetchRow (\$result);
		\$id 		= \$row[0] + 1;
	
		return \"<\$idFieldName>\$id</\$idFieldName>\";
	}");

eval ("function CC_does".$CCobjectName."Exist (\$id, \$idFieldName = \"id\")
	{
		\$queryStr   = \"select count(*) from ".$CCobjectsName." where \$idFieldName=\$id\";
		\$result     = commonDoQuery (\$queryStr);
		\$row	     = commonQuery_fetchRow(\$result);
		\$count	     = \$row[0];

		return (\$count > 0);
	}");

eval ("function CC_delete".$CCobjectName." (\$xmlRequest, \$idFieldName = \"id\")
	{
		\$id = xmlParser_getValue (\$xmlRequest, \$idFieldName);

		if (\$id == \"\")
			trigger_error (\"חסר קוד ".$CCHEBobjectName." לביצוע הפעולה\");

		\$queryStr =  \"delete from ".$CCobjectsName." where \$idFieldName=\$id\";
		commonDoQuery (\$queryStr);

		return \"\";
	}");

eval ("function CC_edit".$CCobjectName." (\$xmlRequest, \$editType, \$idFieldName = \"id\")
	{
		".$CCglobals."
		\$id = xmlParser_getValue(\$xmlRequest, \$idFieldName);
		
		if (\$id == \"\")
			trigger_error (\"חסר קוד ".$CCHEBobjectName." לביצוע הפעולה\");
		
		# ------------------------------------------------------------------------------------------------------
		if (\$editType == \"add\")
		{
			if (CC_does".$CCobjectName."Exist(\$id))
			{
				trigger_error (\"יש ".$CCHEBobjectName." עם קוד זהה במערכת\");
			}
			
			\$queryStr = \"insert into ".$CCobjectsName." (".$CCfieldNames .") values (". $CCfieldVals .")\";
		}
		else // update
		{
			\$queryStr = \"update ".$CCobjectsName." set ". $CCfieldSets ." where \$idFieldName=\$id\";
		}

		commonDoQuery (\$queryStr);

		return \"\";
	}");

/* Under development
eval ("function getClubMailingListDetails (\$xmlRequest, \$idFieldName = \"id\")
	{
		\$id = xmlParser_getValue(\$xmlRequest, \$idFieldName);
		
		if (\$id == \"\")
			trigger_error (\"חסר קוד ".$CCHEBobjectName." לביצוע הפעולה\");

		\$queryStr = \"select * from ".$CCobjectsName." where \$idFieldName='\$id'\";
		\$result  = commonDoQuery(\$queryStr);

		if (commonQuery_numRows(\$result) == 0)
			trigger_error (\"אין ".$CCHEBobjectName." (\$id) במערכת. לא ניתן לבצע את העדכון\");

		\$xmlResponse = \"\";

		\$row = commonQuery_fetchRow(\$result);

		\$name	= commonValidXml(\$row['name'], true);
		\$senderName 	= commonValidXml(\$row['senderName'], true);
		\$senderEmail 	= commonValidXml(\$row['senderEmail'], true);

		\$xmlResponse .=	\"<id>\$id</id>\"	 			. 
				\"<name>\$name</name>\"	.
				\"<senderName>\$senderName</senderName>\"	.
				\"<senderEmail>\$senderEmail</senderEmail>\";

		return (\$xmlResponse);
	}");
*/
?>
