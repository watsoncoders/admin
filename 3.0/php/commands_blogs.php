<?php

include "$ibosHomeDir/php/picsTools.php";

$bloggerTags = Array("id", "username", "password", "firstName", "lastName",  "status", "profile");

$pageTags	 = Array("id", "layoutId", "membersOnly");
$blogTags	 = Array("id", "name", "bloggers", "language",  "numPostsInPage", "archiveType", "archiveBy", "postTitleInArchive", "status");

$postTags	 = Array("id", "blogId", "bloggerId", "title", "text", "isReady", 
					 "extraData1", "extraData2", "extraData3", "extraData4", "extraData5", "winTitle", "metaKeywords", "metaDescription", "rewriteName");

/* ____________________________________________________________________________________________________ */
/*																										*/
/*                                           B L O G G E R S                                            */
/* ____________________________________________________________________________________________________ */


/* ----------------------------------------------------------------------------------------------------	*/
/* getBloggers																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBloggers ($xmlRequest)
{
	return getBloggersXml ($xmlRequest, "");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBloggersXml																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBloggersXml ($xmlRequest, $conditions)
{	
	// get total
	$queryStr	 = "select count(*) from bloggers where 1 $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select bloggers.*
				 from bloggers 
				 where 1 $conditions
				 order by id desc " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows     = commonQuery_numRows($result);

	$pressText   = commonPhpEncode("לחץ כאן");

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	commonConnectToUserDB ($domainRow);
	
	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$username	= commonValidXml ($row['username']);
		$password	= commonValidXml ($row['password']);
		$firstName	= commonValidXml ($row['firstName']);
		$lastName	= commonValidXml ($row['lastName']);
		$fullname	= commonValidXml ($row['firstName'] . " " . $row['lastName']);
		$profile    = commonValidXml ($row['profile']);
		$status		= $row['status'];
		$statusText	= formatActiveStatus($status);
		
		$picFile   	= commonValidXml($row['picFile']);
		$picSource	= commonCData(commonEncode($row['picSource']));

		$linkToFile	    = "$siteUrl/bloggersFiles/$row[picFile]";

		$show	 = "";
		$delete  = "";

		if ($row['picFile']  != "") 
		{
			$show   = $pressText;
			$delete = $pressText;
		}

		$queryStr	= "select count(id) from blogs where bloggers like '%$id%'";
		$blogsResult= commonDoQuery($queryStr);
		$blogsRow	= commonQuery_fetchRow($blogsResult);
		$countBlogs	= $blogsRow[0];


		$xmlResponse .=	"<item>
							<bloggerId>$id</bloggerId>
				 			<username>$username</username>
							<password>$password</password>
							<firstName>$firstName</firstName>
							<lastName>$lastName</lastName>
							<fullname>$fullname</fullname>
							<profile>$profile</profile>
							<status>$status</status>
							<statusText>$statusText</statusText>
							<countBlogs>$countBlogs</countBlogs>
					 		<picFile>$picFile</picFile>
							<formSourceFile>$picSource</formSourceFile>
							<show>$show</show>
							<delete>$delete</delete>
							<linkToFile>$linkToFile</linkToFile>
							<dimensionId></dimensionId>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBlogBloggers																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBlogBloggers ($xmlRequest)
{
	$blogId = xmlParser_getValue($xmlRequest, "blogId");

	if ($blogId == "")
		trigger_error ("חסר מזהה בלוג בקבלת בלוגרים של בלוג");

	$queryStr    = "select bloggers from blogs where id = $blogId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$bloggers	 = trim($row[0]);

	$conditions  = " and bloggers.id in (" . join(",",explode(" ", $bloggers)) . ") ";

	return getBloggersXml ($xmlRequest, $conditions);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBloggersNames																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBloggersNames ($bloggers)
{
	$queryStr = "select firstName, lastName
				 from bloggers 
				 where id in (" . join(",",explode(" ", $bloggers)) . ") 
				 order by id desc ";
	$result	     = commonDoQuery ($queryStr);

	$names = "";

	while ($row = commonQuery_fetchRow($result))
	{
		if ($names != "") $names .= ", ";

		$names .= $row['firstName'] . " " . $row['lastName'];
	}

	return commonValidXml($names);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addBlogger																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addBlogger ($xmlRequest)
{
	return (editBlogger ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesBloggerExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesBloggerExist ($id)
{
	$queryStr		= "select count(*) from bloggers where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBloggerNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBloggerNextId ()
{
	$queryStr	= "select max(id) from bloggers";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateBlogger																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateBlogger ($xmlRequest)
{
	editBlogger ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editBlogger																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editBlogger ($xmlRequest, $editType)
{
	global $bloggerTags;
	global $userId;
	global $ibosHomeDir;

	for ($i=0; $i < count($bloggerTags); $i++)
	{
		eval ("\$$bloggerTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$bloggerTags[$i]\"));");	
	}

	$profile = commonFixText(addslashes($profile));

	if ($editType == "update")
	{
		$id = xmlParser_getValue($xmlRequest, "bloggerId");

		if (!doesBloggerExist($id))
		{
			trigger_error ("בלוגר עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getBloggerNextId ();
	}

	// handle picture 
	$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));	
	$picSource		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	

	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded  	= false;

	$picFile 		= "";

	$suffix 		= "";
	if ($picSource != "")
	{
		$fileLoaded = true;
		
		$suffix		= commonFileSuffix($picSource);

		$picFile = "${id}_size0.jpg";
	}
	list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
	
	$vals = Array();

	for ($i=0; $i < count($bloggerTags); $i++)
	{
		eval ("array_push (\$vals,\$$bloggerTags[$i]);");
	}
	
	if ($editType == "update")
	{
		$queryStr = "update bloggers set ";

		for ($i=1; $i < count($bloggerTags); $i++)
		{
			$queryStr .= "$bloggerTags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		if ($fileLoaded)
		{
			$queryStr .= ", picFile 	= '$picFile',
							picSource	= '$picSource'	";
		}
		else if ($fileDeleted)
		{
			$queryStr .= ", picFile 	= '',
							picSource	= ''	";
		}

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "insert into bloggers (" . join(",",$bloggerTags) . ", picFile, picSource) 
					 values ('" . join("','",$vals) . "', '$picFile', '$picSource')";
		commonDoQuery ($queryStr);
	}

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	if ($fileLoaded)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY); 

		if (!$upload) 
		   	echo "FTP upload has failed!";

		$fileName = "${id}_size0.jpg";

		picsToolsResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);
		
		$upload = ftp_put($connId, "bloggersFiles/$fileName", "/../../tmp/$fileName", FTP_BINARY);

		unlink("$filePath/$picSource");

		commonFtpDisconnect ($connId);
	}
	else if ($fileDeleted)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		$fileName = "${id}_size0.jpg";

		@ftp_delete($connId, "bloggersFiles/$fileName");
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteBlogger																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteBlogger ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "bloggerId");

	if ($id == "")
		trigger_error ("חסר קוד בלוגר לביצוע הפעולה");

	$queryStr = "delete from bloggers where id = $id";
	commonDoQuery ($queryStr);

	// TBD - delete all blogger's blogs !!!!!!!!!
	
	return "";	
}

/* ____________________________________________________________________________________________________ */
/*																										*/
/*                                             B L O G  S                                               */
/* ____________________________________________________________________________________________________ */

/* ----------------------------------------------------------------------------------------------------	*/
/* getBlogs																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBlogs ($xmlRequest)
{	
	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "" || $sortBy == "blogId" || $sortBy == "id")
		$sortBy = "blogs.id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$condition	= "";

	$name = xmlParser_getValue($xmlRequest, "name");
	if ($name != "")
	{
		$condition .= " and name like '%$name%' ";
	}
	
	// get total
	$queryStr	 = "select count(*) from blogs";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select blogs.*, pages.*, pages_byLang.*
				 from blogs, pages, pages_byLang
				 where blogs.id = pages.id
				 and   pages.id = pages_byLang.pageId $condition
				 order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   				= $row['id'];
		$bloggers   		= $row['bloggers'];
		$language			= $row['language'];
		$layoutId   		= $row['layoutId'];
		$name				= commonValidXml ($row['name']);
		$numPostsInPage		= $row['numPostsInPage'];
		$archiveType		= $row['archiveType'];
		$archiveBy			= $row['archiveBy'];
		$postTitleInArchive	= $row['postTitleInArchive'];
		$membersOnly    	= $row['membersOnly'];
		$winTitle			= commonValidXml ($row['winTitle']);
		$description		= commonValidXml ($row['description']);
		$keywords			= commonValidXml ($row['keywords']);
		$rewriteName		= commonValidXml ($row['rewriteName']);

		if ($numPostsInPage == "0") $numPostsInPage = "";

		$status			= $row['status'];
		$statusText		= formatActiveStatus($status);

		$layoutName 	= commonGetLayoutName ($layoutId);
		$bloggersNames	= getBloggersNames    (trim($bloggers));

		$xmlResponse .=	"<item>
							<blogId>$id</blogId>
							<bloggers>$bloggers</bloggers>
							<bloggersNames>$bloggersNames</bloggersNames>
							<blogLang>$language</blogLang>
							<layoutId>$layoutId</layoutId>
							<layoutName>$layoutName</layoutName>
				 			<name>$name</name>
							<status>$status</status>
							<statusText>$statusText</statusText>
							<numPostsInPage>$numPostsInPage</numPostsInPage>
							<archiveType>$archiveType</archiveType>
							<archiveBy>$archiveBy</archiveBy>
							<postTitleInArchive>$postTitleInArchive</postTitleInArchive>
							<membersOnly>$membersOnly</membersOnly>
							<winTitle>$winTitle</winTitle>
							<description>$description</description>
							<keywords>$keywords</keywords>
							<rewriteName>$rewriteName</rewriteName>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBlogDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBlogDetails ($xmlRequest)
{	
	$blogId = xmlParser_getValue($xmlRequest, "blogId");

	$queryStr 	= "select blogs.*, pages.*, pages_byLang.* from blogs, pages, pages_byLang
				   where blogs.id = pages.id and pages.id = pages_byLang.pageId and blogs.id = $blogId";
	$result		= commonDoQuery ($queryStr);
	$row 		= commonQuery_fetchRow($result);
			
	$id   				= $row['id'];
	$bloggers   		= $row['bloggers'];
	$language			= $row['language'];
	$layoutId   		= $row['layoutId'];
	$name				= commonValidXml ($row['name']);
	$numPostsInPage		= $row['numPostsInPage'];
	$archiveType		= $row['archiveType'];
	$archiveBy			= $row['archiveBy'];
	$postTitleInArchive	= $row['postTitleInArchive'];
	$membersOnly    	= $row['membersOnly'];
	$winTitle			= commonValidXml ($row['winTitle']);
	$description		= commonValidXml ($row['description']);
	$keywords			= commonValidXml ($row['keywords']);
	$rewriteName		= commonValidXml ($row['rewriteName']);

	if ($numPostsInPage == "0") $numPostsInPage = "";

	$status			= $row['status'];

	$xmlResponse  	=  "<blogId>$id</blogId>
						<bloggers>$bloggers</bloggers>
						<blogLang>$language</blogLang>
						<layoutId>$layoutId</layoutId>
				 		<name>$name</name>
						<status>$status</status>
						<numPostsInPage>$numPostsInPage</numPostsInPage>
						<archiveType>$archiveType</archiveType>
						<archiveBy>$archiveBy</archiveBy>
						<postTitleInArchive>$postTitleInArchive</postTitleInArchive>
						<membersOnly>$membersOnly</membersOnly>
						<winTitle>$winTitle</winTitle>
						<description>$description</description>
						<keywords>$keywords</keywords>
						<rewriteName>$rewriteName</rewriteName>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addBlog																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addBlog ($xmlRequest)
{
	return (editBlog ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesBlogExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesBlogExist ($id)
{
	$queryStr		= "select count(*) from blogs where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBlogNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBlogNextId ()
{
	$queryStr	= "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateBlog																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateBlog ($xmlRequest)
{
	editBlog ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editBlog																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editBlog ($xmlRequest, $editType)
{
	global $pageTags, $blogTags;

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("\$$pageTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$pageTags[$i]\"));");	
	}

	for ($i=0; $i < count($blogTags); $i++)
	{
		eval ("\$$blogTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$blogTags[$i]\"));");	
	}

	$language = xmlParser_getValue($xmlRequest, "blogLang");

	if ($editType == "update")
	{
		$id = xmlParser_getValue($xmlRequest, "blogId");

		if (!doesBlogExist($id))
		{
			trigger_error ("בלוג עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getBlogNextId ();
	}

	$pageVals = Array();
	$blogVals = Array();

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("array_push (\$pageVals,\$$pageTags[$i]);");
	}
	
	for ($i=0; $i < count($blogTags); $i++)
	{
		eval ("array_push (\$blogVals,\$$blogTags[$i]);");
	}
	
	if ($editType == "update")
	{
		// pages table
		$queryStr = "update pages set ";

		for ($i=1; $i < count($pageTags); $i++)
		{
			$queryStr .= "$pageTags[$i] = '$pageVals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);

		if ($status == "active")
			$isReady = "1";
		else
			$isReady = "0";

		$winTitle 		= commonDecode(xmlParser_getValue($xmlRequest, "winTitle"));
		$description 	= commonDecode(xmlParser_getValue($xmlRequest, "description"));
		$keywords 		= commonDecode(xmlParser_getValue($xmlRequest, "keywords"));
		$rewriteName	= str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName"))));

		// pages by lang table
		$queryStr = "update pages_byLang set isReady  		=  $isReady,
											 winTitle 		= '$winTitle', 
											 description	= '$description',
											 keywords		= '$keywords',
											 rewriteName	= '$rewriteName',
											 title	  		= '$name'
											 where pageId = $id";
		commonDoQuery ($queryStr);

		// blogs table
		$queryStr = "update blogs set ";

		for ($i=1; $i < count($blogTags); $i++)
		{
			$queryStr .= "$blogTags[$i] = '$blogVals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		// pages table
		$queryStr = "insert into pages (" . join(",",$pageTags) . ",type) values ('" . join("','",$pageVals) . "','blog')";
		commonDoQuery ($queryStr);

		// pages by lang table
		$queryStr	= "insert into pages_byLang (pageId, winTitle, title, language, isReady) values ('$id','$name', '$name', '$language', '1')";
		commonDoQuery ($queryStr);

		// blogs table
		$queryStr = "insert into blogs (" . join(",",$blogTags) . ",insertTime) values ('" . join("','",$blogVals) . "',now())";
		commonDoQuery ($queryStr);
	}

	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);

	// Update .htaccess with mod_rewrite rules
	fopen("$domainName/updateModRewrite.php","r");

	return "<blogId>$id</blogId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteBlog																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteBlog ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "blogId");

	if ($id == "")
		trigger_error ("חסר קוד בלוג לביצוע הפעולה");

	$queryStr = "delete from pages where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from pages_byLang where pageId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from blogs where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from blogsPosts where blogId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from categoriesItems where itemId = $id and type = 'blog'";
	commonDoQuery ($queryStr);
	
	return "";	
}

/* ____________________________________________________________________________________________________ */
/*																										*/
/*                                         B L O G   P O S T S                                          */
/* ____________________________________________________________________________________________________ */

/* ----------------------------------------------------------------------------------------------------	*/
/* getBlogPostExtraDataNames																			*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBlogPostExtraDataNames ($xmlRequest)
{
	return (commonGetExtraDataNames("blogPostsExtraData"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBlogsPosts																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBlogsPosts ($xmlRequest)
{	
	$condition = "";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "" || $sortBy == "postId")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$bloggerId  = xmlParser_getValue($xmlRequest, "bloggerId");
	if ($bloggerId != "")
	{
		$condition .= " and bloggerId = $bloggerId ";
	}

	$blogId 	= xmlParser_getValue($xmlRequest, "blogId");
	if ($blogId != "")
	{
		$condition .= " and blogId = $blogId ";
	}

	// get total
	$queryStr	 = "select count(*)
				 	from (blogsPosts, blogs)
					left join bloggers on blogsPosts.bloggerId = bloggers.id
					where blogsPosts.blogId = blogs.id $condition";

	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr	= str_replace("count(*)", "blogsPosts.*, blogs.name, bloggers.firstName, bloggers.lastName ", $queryStr);
	$queryStr  .= " order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$blogId			= $row['blogId'];
		$blogName		= commonValidXml 	 ($row['name']);
		$bloggerName	= commonValidXml 	 ($row['firstName'] . " " . $row['lastName']);
		$insertTime		= formatApplDateTime ($row['insertTime']);
		$fullTitle		= commonValidXml 	 ($row['title']);
		$title			= commonValidXml 	 (commonCutText ($row['title'], 40));
		$isReady		= ($row['isReady'] == "0") ? commonPhpEncode("טיוטה") : commonPhpEncode("מאושרת");

		$xmlResponse .=	"<item>
							<id>$id</id>
							<postId>$id</postId>
							<blogId>$blogId</blogId>
							<blogName>$blogName</blogName>
							<bloggerName>$bloggerName</bloggerName>
							<insertTime>$insertTime</insertTime>
							<title>$fullTitle</title>
							<fullTitle>$fullTitle</fullTitle>
							<isReady>$isReady</isReady>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBlogPostDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBlogPostDetails ($xmlRequest)
{
	global $postTags;

	$id		= xmlParser_getValue($xmlRequest, "postId");

	if ($id == "")
		trigger_error ("חסר קוד רשומה לביצוע הפעולה");

	$queryStr    = "select * from blogsPosts
					where id = $id";

	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("רשומה קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את הפעולה");

	$xmlResponse = "";

	$row = commonQuery_fetchRow($result);
	
	for ($i=0; $i < count($postTags); $i++)
	{
		eval ("\$$postTags[$i] = \$row['$postTags[$i]'];");
		eval ("\$$postTags[$i] = commonValidXml(\$$postTags[$i]);");
		eval ("\$xmlResponse .= \"<$postTags[$i]>\$$postTags[$i]</$postTags[$i]>\";");
	}

	$insertTime = formatApplDateTime($row['insertTime']);

	$xmlResponse	.= "<postId>$id</postId>
						<insertTime>$insertTime</insertTime>
						<insertTimeDB>$row[insertTime]</insertTimeDB>";

	$id		= xmlParser_getValue($xmlRequest, "postId");
	$flags = commonGetItemFlags ($id, "blogPost");

	$xmlResponse .= commonGetItemFlagsXml ($flags, "blogPost");

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addBlogPost																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addBlogPost ($xmlRequest)
{
	return (editBlogPost ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesBlogPostExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesBlogPostExist ($id)
{
	$queryStr	= "select count(*) from blogsPosts where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBlogPostNextId																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBlogPostNextId ()
{
	$queryStr	= "select max(id) from blogsPosts";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateBlogPost																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateBlogPost ($xmlRequest)
{
	editBlogPost ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editBlogPost																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editBlogPost ($xmlRequest, $editType)
{
	global $postTags;
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i < count($postTags); $i++)
	{
		eval ("\$$postTags[$i] = commonDecode(addslashes(xmlParser_getValue(\$xmlRequest,\"$postTags[$i]\")));");	
	}

	$rewriteName	= str_replace(" ", "-", $rewriteName);

	if ($editType == "update")
	{
		$id = xmlParser_getValue($xmlRequest, "postId");

		if (!doesBlogPostExist($id))
		{
			trigger_error ("רשומה עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		$insertTime = xmlParser_getValue($xmlRequest, "insertTimeDB");
	}
	else
	{
		$id = getBlogPostNextId ();
		
		$insertTime = date("Y-m-d H:i", strtotime("now"));

		if ($bloggerId == "" || $bloggerId == 0)
		{
			// get blog first blogger
			$queryStr    = "select bloggers from blogs where id = $id";
			$result	     = commonDoQuery ($queryStr);
			$row	     = commonQuery_fetchRow($result);
			$bloggers	 = explode(" ", trim($row[0]));

			$bloggerId 	 = $bloggers[0];
		}
	}

	$vals = Array();

	for ($i=0; $i < count($postTags); $i++)
	{
		eval ("array_push (\$vals,\$$postTags[$i]);");
	}
	
//	mail ("liat@interuse.com", "Mila Yomit - edit blog post", "Blogger Id = '$bloggerId'\nBlog Id = $blogId\nPost Id = $id\nAction = $editType");

	if ($editType == "update")
	{
		$queryStr = "update blogsPosts set ";

		for ($i=1; $i < count($postTags); $i++)
		{
			$queryStr .= "$postTags[$i] = '$vals[$i]',";
		}

		$queryStr .= "updated = now()
					  where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "insert into blogsPosts (" . join(",",$postTags) . ", insertTime, updated) values ('" . join("','",$vals) . "', now(), now())";
		commonDoQuery ($queryStr);
	}

	commonSaveItemFlags ($id, "blogPost", $xmlRequest);

	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);

	$file = fopen ("$domainName/blogsPostsRSS.php?lang=$langsArray[0]","r");
	fclose ($file);

	// Update .htaccess with mod_rewrite rules
	fopen("$domainName/updateModRewrite.php","r");

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteBlogPost																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteBlogPost ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "postId");

	if ($id == "")
		trigger_error ("חסר קוד רשומה לביצוע הפעולה");

	$queryStr = "delete from blogsPosts where id = $id";
	commonDoQuery ($queryStr);
	
	return "";	
}

?>
