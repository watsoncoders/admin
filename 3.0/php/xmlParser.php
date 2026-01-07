<?
function xmlParser_parse ($xml)
{
   	$xml_parser = xml_parser_create();


   // use this option only for remove new lines and spaces !!!
   // xml_parser_set_option($xml_parser,XML_OPTION_SKIP_WHITE,1);

	$result = xml_parse_into_struct($xml_parser, $xml, $arrVals);

   	xml_parser_free($xml_parser);

   	return $arrVals;
}

function xmlParser_getValue ($xmlArray, $tagName)
{
	global $isUTF8;

	$value   = "";

	$tagName = strtoupper($tagName);

	$tags = "";
	for ($i = 0; $i < count($xmlArray); $i++)
	{
		if ($xmlArray[$i]["type"] == "complete" && $xmlArray[$i]["tag"] == $tagName)
		{
			if (isset($xmlArray[$i]["value"]))
				$value = $xmlArray[$i]["value"];

			if (!$isUTF8)
				$value = iconv("utf-8", "windows-1255",$value);

			break;
		}
	}
	return $value;
}

function xmlParser_getValues ($xmlArray, $tagName)
{
	$values = array();

	$tagName = strtoupper($tagName);

	for ($i = 0; $i < count($xmlArray); $i++)
	{
		if ($xmlArray[$i]["type"] == "complete" && $xmlArray[$i]["tag"] == $tagName)
		{
			if (isset($xmlArray[$i]["value"]))
				array_push ($values,$xmlArray[$i]["value"]);
		}
	}
	return $values;
}

function xmlParser_getDummyTags ($xmlArray)
{
	$tags = "";

	for ($i = 0; $i < count($xmlArray); $i++)
	{
		if ($xmlArray[$i]["type"] == "complete" && strpos(strtoupper($xmlArray[$i]["tag"]), "DUMMY_") !== false)
		{
			if (isset($xmlArray[$i]["value"]))
			{
				$tagName	= str_replace("DUMMY_", "", strtoupper($xmlArray[$i]["tag"]));
				$tags	   .= "<$tagName>" . $xmlArray[$i]["value"] . "</$tagName>";
			}
		}
	}
	return $tags;
}
?>
