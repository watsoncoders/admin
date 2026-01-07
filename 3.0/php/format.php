<?

/* --------------------------------------------------------------------------------------------	*/
/* formatBoolean																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatBoolean ($value)
{
	if ($value == "1")
		$value = "כן";
	else
		$value = "-";

	return commonPhpEncode ($value);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatBoxType 																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatBoxType ($value)
{
	global $cookie_guiLang;

	$textValue = "";
	
	switch ($value)
	{
		case "links"			: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת קישורים" : "Links box");
								  break;
		case "html"				: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת HTML" : "HTML box");
								  break;
		case "news"				: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת חדשות" : "News box");
								  break;
		case "music"			: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת מוזיקה" : "Music box");
								  break;
		case "shopCategories"	: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת קטגוריות חנות/קטלוג" : "");
						  		  break;
		case "forumCategories"	: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת קטגוריות פורומים" : "");
						  	 	  break;
		case "galleryCategories": $textValue =  (($cookie_guiLang == "HEB") ? "תיבת קטגוריות גלריה" : "");
						  		  break;
		case "pageCategories"	: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת קטגוריות דפים" : "");
						  		  break;
		case "essayCategories"	: $textValue =  (($cookie_guiLang == "HEB") ?  "תיבת קטגוריות כתבות" : "");
						  		  break;
		case "cart"				: $textValue =  (($cookie_guiLang == "HEB") ? "עגלת קניות" : "");
						  		  break;
		case "freeCategoris"	: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת קטגוריות חופשיות" : "");
						  		  break;
		case "innerSearch"		: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת חיפוש פנימי" : "");
						  		  break;
		case "googleSearch"		: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת חיפוש גוגל" : "");
						  		  break;
		case "googleAds"		: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת מודעות גוגל" : "");
						  		  break;
		case "login"			: $textValue =  (($cookie_guiLang == "HEB") ? "תיבת User Login" : "");
						  		  break;
	}
	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatBoxPlace 																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatBoxPlace ($value)
{
	global $cookie_guiLang;

	$textValue = "";
	switch ($value)
	{
		case "l"	: $textValue =  (($cookie_guiLang == "HEB") ? "צד שמאל" : "Left");
					  break;
		case "r"	: $textValue =  (($cookie_guiLang == "HEB") ? "צד ימין" : "Right");
					  break;
		case "t"	: $textValue =  (($cookie_guiLang == "HEB") ? "למעלה" : "Up");
				  	  break;
		case "b"	: $textValue =  (($cookie_guiLang == "HEB") ? "למטה" : "Down");
				  	  break;
	}
	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatLinkType 																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatLinkType ($value)
{
	global $cookie_guiLang;

	$textValue = "";
	
	switch ($value)
	{
		case "page"				: $textValue =  (($cookie_guiLang == "HEB") ? "קישור לדף" : "Page link");
					  			  break;
		case "url"				: $textValue =  (($cookie_guiLang == "HEB") ? "קישור URL" : "URL link");
					  			  break;
		case "urlNoFollow"		: $textValue =  (($cookie_guiLang == "HEB") ? "קישור URL-No-Follow" : "URL No-Follow");
					  			  break;
		case "forum"			: $textValue =  (($cookie_guiLang == "HEB") ? "קישור לפורום" : "Forum link");
					 			  break;
		case "gallery"			: $textValue =  (($cookie_guiLang == "HEB") ? "קישור לגלריה" : "Gallery link");
					 			  break;
		case "questionnaire"	: $textValue =  (($cookie_guiLang == "HEB") ? "קישור לשאלון" : "Questionnaire link");
					 			  break;
		case "album"			: $textValue =  (($cookie_guiLang == "HEB") ? "קישור לאלבום" : "Album link");
					 			  break;
		case "essay"			: $textValue =  (($cookie_guiLang == "HEB") ? "קישור לכתבה" : "Essay link");
						 		  break;
		case "nadlan"			: $textValue =  (($cookie_guiLang == "HEB") ? "קישור ללוח נדל\"ן" : "Nadlan tablet link");
						 		  break;
		case "file"				: $textValue =  (($cookie_guiLang == "HEB") ? "קישור לקובץ" : "File link");
					 	  		  break;
		case "addToFavorite"	: $textValue =  (($cookie_guiLang == "HEB") ? "הוסף למועדפים" : "Add to favorites");
							 	  break;
		case "makeHomePage"		: $textValue =  (($cookie_guiLang == "HEB") ? "הפוך לדף הבית" : "Make homepage");
							 	  break;
		case "onclick"			: $textValue =  (($cookie_guiLang == "HEB") ? "קישור onclick" : "Onclick link");
							 	  break;
		case "staticName"		: $textValue =  (($cookie_guiLang == "HEB") ? "קישור לפי כתובת סטטית" : "Rewrite name link");
							 	  break;
	}
	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatUrlTarget 																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatUrlTarget ($value)
{
	global $cookie_guiLang;

	$textValue = "";
	
	switch ($value)
	{
		case "_blank"	: $textValue =  (($cookie_guiLang == "HEB") ? "בדף חדש" : "Open in new window");
					  	  break;
		case "_self"	: $textValue =  (($cookie_guiLang == "HEB") ? "באתר" : "Open in the same window");
					  	  break;
	}
	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatCurrency																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatCurrency ($currency)
{
	global $cookie_guiLang;

	$textValue = "";
	switch ($currency)
	{
		case "ILS"		: $textValue = (($cookie_guiLang == "HEB") ? "ש\"ח" : "NIS");
						  break;
		case "USD"		: $textValue = (($cookie_guiLang == "HEB") ? "דולר" : "USD");
						  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatAmount																					*/
/* --------------------------------------------------------------------------------------------	*/
function formatAmount ($amount)
{
	return (sprintf("%01.2f", $amount));
}

/* --------------------------------------------------------------------------------------------	*/
/* formatFileType																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatFileType ($type)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($type)
	{
		case "pic"	: $textValue = (($cookie_guiLang == "HEB") ? "תמונה" : "Picture");
					  break;
		case "video": $textValue = (($cookie_guiLang == "HEB") ? "סרטון Flash" : "Flash movie");
					  break;
		case "pdf"	: $textValue = (($cookie_guiLang == "HEB") ? "מצגת PDF" : "PDF");
					  break;
		case "doc"	: $textValue = (($cookie_guiLang == "HEB") ? "מסמך" : "Doc");
					  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatProductStatus																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatProductStatus ($status)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($status)
	{
		case "0"	: $textValue = (($cookie_guiLang == "HEB") ? "חדש !" : "New!");
					  break;
		case "1"	: $textValue = (($cookie_guiLang == "HEB") ? "זמין ומוצג" : "Available and shown");
					  break;
		case "2"	: $textValue = (($cookie_guiLang == "HEB") ? "לא זמין ומוצג" : "Not available and shown");
					  break;
		case "3"	: $textValue = (($cookie_guiLang == "HEB") ? "לא מוצג" : "Not shown");
					  break;
		case "4"	: $textValue = (($cookie_guiLang == "HEB") ? "במבצע" : "In sale");
					  break;
		case "5"	: $textValue = (($cookie_guiLang == "HEB") ? "חסר במלאי" : "Stock lacking");
					  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatProductStatus																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatFeaturedProduct ($featured)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($featured)
	{
		case "0"	: $textValue = "-";
					  break;
		case "1"	: $textValue = (($cookie_guiLang == "HEB") ? "נבחר" : "Choosen");
					  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatCategoryType																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatCategoryType ($type)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($type)
	{
		case "global"		: $textValue = (($cookie_guiLang == "HEB") ? "כללי" : "General");
							  break;
		case "shop"			: $textValue = (($cookie_guiLang == "HEB") ? "חנות או קטלוג" : "Shop/Catalog");
							  break;
		case "forum"		: $textValue = (($cookie_guiLang == "HEB") ? "פורומים" : "Forums");
							  break;
		case "page"			: $textValue = (($cookie_guiLang == "HEB") ? "דפים" : "Pages");
					  		  break;
		case "url"			: $textValue = (($cookie_guiLang == "HEB") ? "קישורים" : "Links");
					  		  break;
		case "essay"		: $textValue = (($cookie_guiLang == "HEB") ? "כתבות" : "Essays");
					  		  break;
		case "faq"			: $textValue = (($cookie_guiLang == "HEB") ? "שו\"ת" : "FAQ");
					  		  break;
		case "gallery"		: $textValue = (($cookie_guiLang == "HEB") ? "גלריות" : "Galleries");
					 		  break;
		case "questionnaire": $textValue = (($cookie_guiLang == "HEB") ? "שאלונים" : "Questionnaire");
					 		  break;
		case "album"		: $textValue = (($cookie_guiLang == "HEB") ? "אלבומים" : "Albums");
					 		  break;
		case "banner"		: $textValue = (($cookie_guiLang == "HEB") ? "באנרים" : "Banners");
					 		  break;
		case "survey"		: $textValue = (($cookie_guiLang == "HEB") ? "סקרים" : "Surveys");
					 		  break;
		case "blog"			: $textValue = (($cookie_guiLang == "HEB") ? "בלוגים" : "Blogs");
					 		  break;
		case "blogPost"		: $textValue = (($cookie_guiLang == "HEB") ? "רשומות בלוגים" : "Blog Posts");
					 		  break;
		case "tabletItem"	: $textValue = (($cookie_guiLang == "HEB") ? "הודעות לוח" : "Tablet messages");
					 		  break;
		case "news"			: $textValue = (($cookie_guiLang == "HEB") ? "חדשות" : "News");
					 		  break;
		case "phonesBook"	: $textValue = (($cookie_guiLang == "HEB") ? "ספר טלפונים" : "Phonebook");
					 		  break;
		case "evnet"		: $textValue = (($cookie_guiLang == "HEB") ? "אירועים" : "Events");
					 		  break;
		case "member"		: $textValue = (($cookie_guiLang == "HEB") ? "גולש רשום" : "Member");
					 		  break;
		case "specific"		: $textValue = (($cookie_guiLang == "HEB") ? "יעודי" : "Specific");
					 		  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatCategoryItemType																		*/
/* --------------------------------------------------------------------------------------------	*/
function formatCategoryItemType ($type, $domainId = 0)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($type)
	{
		case "shop"			: $textValue = (($cookie_guiLang == "HEB") ? "חנות או קטלוג" : "Shop/Catalog");
							  break;
		case "forum"		: $textValue = (($cookie_guiLang == "HEB") ? "פורום" : "Forum");
							  break;
		case "page"			: $textValue = (($cookie_guiLang == "HEB") ? "דף" : "Page");
					  		  break;
		case "url"			: $textValue = (($cookie_guiLang == "HEB") ? "קישור" : "Link");
					  		  break;
		case "essay"		: $textValue = (($cookie_guiLang == "HEB") ? "כתבה" : "Essay");
					  		  break;
		case "faq"			: $textValue = (($cookie_guiLang == "HEB") ? "שו\"ת" : "FAQ");
					  		  break;
		case "gallery"		: $textValue = (($cookie_guiLang == "HEB") ? "גלריה" : "Gallery");
					 		  break;
		case "questionnaire": $textValue = (($cookie_guiLang == "HEB") ? "שאלון" : "Questionnaire");
					 		  break;
		case "album"		: $textValue = (($cookie_guiLang == "HEB") ? "אלבום" : "Album");
					 		  break;
		case "blog"			: $textValue = (($cookie_guiLang == "HEB") ? "בלוג" : "Blog");
					 		  break;
		case "blogPost"		: $textValue = (($cookie_guiLang == "HEB") ? "רשומת בלוג" : "Blog Post");
					 		  break;
		case "tabletItem"	: $textValue = (($cookie_guiLang == "HEB") ? "הודעת לוח" : "Tablet message");
					 		  break;
		case "news"			: $textValue = (($cookie_guiLang == "HEB") ? "חדשה" : "News");
					 		  break;
		case "phonesBook"	: $textValue = (($cookie_guiLang == "HEB") ? "ספר טלפונים" : "Phonebook");
					 		  break;
		case "event"		: $textValue = (($cookie_guiLang == "HEB") ? "אירוע" : "Event");
					 		  break;
		case "member"		: $textValue = (($cookie_guiLang == "HEB") ? "גולש רשום" : "Member");
					 		  break;
		case "hug"			: $textValue = (($cookie_guiLang == "HEB") ? "חוג" : "Hug");
					 		  break;
		case "specific"		: 
			switch ($domainId)
			{
				case "136"	:	// arie
					$textValue = "קופון";
					break;

				case "105"	:	// studio123
					$textValue = "קופון";
					break;

				case "167"	:	// gs-marketing
					$textValue = "עבודה";
					break;

				case "191"	:	// inugim
				case "247"	:	// tzoona
				case "268"	: 	// made-in-israel
				case "289"	:	// vradim
				case "409"	:	// wbc plus
					$textValue = "עסק";
					break;

				case "254"	:	// hasulam
					$textValue = "תחום לימוד";
					break;

				case "369"	:	// isrlist
					$textValue = "מונחים";
					break;

				case "432"	:	// wannado
					$textValue = "יצרנים";
					break;

				case "442"	:	// metar
					$textValue = "קורסים";
					break;

				case "523"	:	// koasharot
					$textValue = "מומלצים";
					break;
			}
			break;

		case "specific2"		: 
			switch ($domainId)
			{
				case "254"	:	// hasulam
					$textValue = "קורס";
					break;

				case "540"	:	// yelonmoreh
					$textValue	= "סדרה";
					break;
			}
			break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatOrderStatus																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatOrderStatus ($status)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($status)
	{
		case "1"	: $textValue = (($cookie_guiLang == "HEB") ? "התקבלה" : "Received");
					  break;
		case "2"	: $textValue = (($cookie_guiLang == "HEB") ? "סופקה" : "Supplied");
					  break;
		case "3"	: $textValue = (($cookie_guiLang == "HEB") ? "הוחזרה" : "Returned");
					  break;
		case "4"	: $textValue = (($cookie_guiLang == "HEB") ? "בוטלה" : "Canceled");
					  break;
		case "5"	: $textValue = (($cookie_guiLang == "HEB") ? "ממתינה לאיסוף" : "Waiting");
					  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatFeedbackStatus																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatFeedbackStatus ($status)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($status)
	{
		case "new"		: $textValue = (($cookie_guiLang == "HEB") ? "חדשה!" : "New!");
					 	  break;
		case "approved"	: $textValue = (($cookie_guiLang == "HEB") ? "אושרה" : "Confirmed");
						  break;
		case "rejected"	: $textValue = (($cookie_guiLang == "HEB") ? "נדחתה" : "Rejected");
						  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatActiveStatus																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatActiveStatus ($status)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($status)
	{
		case "active"	: $textValue = (($cookie_guiLang == "HEB") ? "פעיל" : "Active");
					 	  break;
	    case "inactive"	: 
		case "unactive"	: $textValue = (($cookie_guiLang == "HEB") ? "לא פעיל" : "Inactive");
					      break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatEmailMsgStatus																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatEmailMsgStatus ($status)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($status)
	{
		case "read"	: $textValue = (($cookie_guiLang == "HEB") ? "נקרא" : "Read");
				 	  break;
		case "new"	: $textValue = (($cookie_guiLang == "HEB") ? "חדש" : "New");
				      break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatBannerType																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatBannerType ($type)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($type)
	{
		case "image"			: $textValue = (($cookie_guiLang == "HEB") ? "תמונה" : "Picture");
					 		  	  break;
		case "movie"			: $textValue = (($cookie_guiLang == "HEB") ? "סרטון Flash" : "Flash movie");
					    	  	  break;
		case "htmlCode"			: $textValue = (($cookie_guiLang == "HEB") ? "קוד HTML" : "HTML code");
					     	 	  break;
		case "text"				: $textValue = (($cookie_guiLang == "HEB") ? "באנר טקסטואלי" : "Text Banner");
					     	 	  break;
		case "background"		: $textValue = (($cookie_guiLang == "HEB") ? "באנר רקע" : "Background Banner");
					      		  break;
		case "backgroundHtml"	: $textValue = (($cookie_guiLang == "HEB") ? "רקע HTML" : "HTML Background");
					      		  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatSuperArea																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatSuperArea ($superArea)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($superArea)
	{
		case "north"	: $textValue = (($cookie_guiLang == "HEB") ? "צפון" : "North");
					 	  break;
		case "south"	: $textValue = (($cookie_guiLang == "HEB") ? "דרום" : "South");
						  break;
		case "center"	: $textValue = (($cookie_guiLang == "HEB") ? "מרכז" : "Center");
						  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatApplTime																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatApplTime ($time)
{
	if ($time == "" || $time == "00:00:00")
		return "";

	return substr($time, 0, 5);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatApplDateTime																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatApplDateTime ($datetime)
{
	if ($datetime == "" || $datetime == "0000-00-00 00:00:00")
		return "";

	$datetime = strtotime($datetime);
	
	$date     = date("d-m-Y", $datetime);
	$time     = date("H:i",   $datetime);
	
//	return ("$date , <font color='gray'>$time</font>");
	return ("$date , $time");
}

/* --------------------------------------------------------------------------------------------	*/
/* formatApplDate																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatApplDate ($datetime)
{
	if ($datetime == "" || $datetime == "0000-00-00" || $datetime == "0000-00-00 00:00:00")
		return "";

	$datetime = strtotime($datetime);
	
	$date     = date("d-m-Y", $datetime);
	
	return ($date);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatApplShortDate																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatApplShortDate ($datetime)
{
	if ($datetime == "" || $datetime == "0000-00-00" || $datetime == "0000-00-00 00:00:00")
		return "";

	$datetime = strtotime($datetime);
	
	$date     = date("d-m-y", $datetime);
	
	return ($date);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatApplFormDateTime																		*/
/* --------------------------------------------------------------------------------------------	*/
function formatApplFormDateTime ($datetime)
{
	if ($datetime == "" || $datetime == "0000-00-00 00:00:00")
		return "";

	$datetime = strtotime($datetime);
	
	$date     = date("d-m-Y", $datetime);
	$time     = date("H:i",   $datetime);
	
	return ("$date $time");
}

/* --------------------------------------------------------------------------------------------	*/
/* formatDayOfWeek																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatDayOfWeek ($date)
{
	global $cookie_guiLang;

	$day	  = date("w", strtotime($date));

	$textValue = "";

	switch ($day)
	{
		case "0"	: $textValue = (($cookie_guiLang == "HEB") ? "ראשון" 	: "Sunday");
					  break;
		case "1"	: $textValue = (($cookie_guiLang == "HEB") ? "שני" 		: "Monday");
					  break;
		case "2"	: $textValue = (($cookie_guiLang == "HEB") ? "שלישי" 	: "Tuesday");
					  break;
		case "3"	: $textValue = (($cookie_guiLang == "HEB") ? "רביעי" 	: "Wednesday");
					  break;
		case "4"	: $textValue = (($cookie_guiLang == "HEB") ? "חמישי" 	: "Thursday");
					  break;
		case "5"	: $textValue = (($cookie_guiLang == "HEB") ? "שישי" 	: "Friday");
					  break;
		case "6"	: $textValue = (($cookie_guiLang == "HEB") ? "שבת" 		: "Saturday");
					  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatApplToDB																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatApplToDB ($datetime)
{
	if ($datetime == "") return "";

	$datetime = preg_replace("/^([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})\s([0-9]{1,2}):([0-9]{2})/", "\\2/\\1/\\3 \\4:\\5", 
						     $datetime);

	$datetime = strtotime($datetime);
	$datetime = date("Y-m-d H:i:00", $datetime);

	return ($datetime);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatTabletType																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatTabletType ($type)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($type)
	{
		case "ads"		: $textValue = (($cookie_guiLang == "HEB") ? "מודעות" : "Messages");
					 	  break;
		case "events"	: $textValue = (($cookie_guiLang == "HEB") ? "אירועים" : "Events");
					      break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatTabletItemStatus																		*/
/* --------------------------------------------------------------------------------------------	*/
function formatTabletItemStatus ($value)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($value)
	{
		case "new"			: $textValue = (($cookie_guiLang == "HEB") ? "חדשה" 	: "New");
							  break;
		case "approved"		: $textValue = (($cookie_guiLang == "HEB") ? "מאושרת"	: "Confirmed");
							  break;
		case "rejected"		: $textValue = (($cookie_guiLang == "HEB") ? "דחויה"	: "Rejected");
							  break;
	}

	return $textValue;
}

/* --------------------------------------------------------------------------------------------	*/
/* formatPopType																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatPopType ($type)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($type)
	{
		case "popunderLoad"		: $textValue = (($cookie_guiLang == "HEB") ? "חלון קופץ מתחת בכניסה לדף" : "Pop-under window on page loading");
					 	  		  break;
		case "popunderUnload"	: $textValue = (($cookie_guiLang == "HEB") ? "חלון קופץ מתחת ביציאה מהדף" : "Pop-under window on exit page");
					 	  		  break;
		case "popupLoad"		: $textValue = (($cookie_guiLang == "HEB") ? "חלון קופץ מעל בכניסה לדף" : "Pop-up window on page loading");
					 	  		  break;
		case "popupUnload"		: $textValue = (($cookie_guiLang == "HEB") ? "חלון קופץ מעל ביציאה מהדף" : "Pop-up window on exit page");
					 	  		  break;
		case "popupMsg"			: $textValue = (($cookie_guiLang == "HEB") ? "הודעה קופצת בכניסה לדף" : "Pop-up message on page loading");
					 	  		  break;
		case "popupOnClose"		: $textValue = (($cookie_guiLang == "HEB") ? "חלון קופץ ביציאה מהאתר" : "Pop-up message on exit page");
					 	  		  break;
		case "chooseLang"		: $textValue = (($cookie_guiLang == "HEB") ? "הודעה קופצת בבחירת שפה" : "Pop-up message for choose language");
					 	  		  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatNewsType																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatNewsType ($type)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($type)
	{
		case ""					: $textValue = (($cookie_guiLang == "HEB") ? "ללא קישור" : "No link");
					 	  		  break;
		case "page"				: $textValue = (($cookie_guiLang == "HEB") ? "קישור לדף" : "Page link");
					 	  		  break;
		case "url"				: $textValue = (($cookie_guiLang == "HEB") ? "URL" : "URL");
					 	  		  break;
		case "file"				: $textValue = (($cookie_guiLang == "HEB") ? "קישור לקובץ" : "File link");
					 	  		  break;
		case "forum"			: $textValue = (($cookie_guiLang == "HEB") ? "קישור לפורום" : "Forum link");
					 	  		  break;
		case "gallery"			: $textValue = (($cookie_guiLang == "HEB") ? "קישור לגלריה" : "Gallery link");
					 	  		  break;
		case "questionnaire"	: $textValue = (($cookie_guiLang == "HEB") ? "קישור לשאלון" : "Questionnaire link");
					 	  		  break;
		case "essay"			: $textValue = (($cookie_guiLang == "HEB") ? "קישור לכתבה" : "Essay link");
					 	  		  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatNewsStatus																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatNewsStatus ($status)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($status)
	{
		case "active"			: $textValue = (($cookie_guiLang == "HEB") ? "פעילה" 	: "Active");
					 	  		  break;
		case "inactive"			: $textValue = (($cookie_guiLang == "HEB") ? "לא פעילה" : "Inactive");
					 	  		  break;
		case "sticky"			: $textValue = (($cookie_guiLang == "HEB") ? "קבועה" 	: "Sticky");
					 	  		  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatPhoneRecordStatus																		*/
/* --------------------------------------------------------------------------------------------	*/
function formatPhoneRecordStatus ($status)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($status)
	{
		case "new"			: $textValue = (($cookie_guiLang == "HEB") ? "חדש"		: "New");
					 	  	  break;
		case "approved"		: $textValue = (($cookie_guiLang == "HEB") ? "מאושר"	: "Confirmed");
					 	  	  break;
		case "rejected"		: $textValue = (($cookie_guiLang == "HEB") ? "דחוי"		: "Rejected");
					 	  	  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatEventStatus																			*/
/* --------------------------------------------------------------------------------------------	*/
function formatEventStatus ($value)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($value)
	{
		case "new"			: $textValue = (($cookie_guiLang == "HEB") ? "חדש"		: "New");
					 	  	  break;
		case "approved"		: $textValue = (($cookie_guiLang == "HEB") ? "מאושר"	: "Confirmed");
					 	  	  break;
		case "rejected"		: $textValue = (($cookie_guiLang == "HEB") ? "דחוי"		: "Rejected");
					 	  	  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatNadlanItemStatus																		*/
/* --------------------------------------------------------------------------------------------	*/
function formatNadlanItemStatus ($status)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($status)
	{
		case "new"			: $textValue = (($cookie_guiLang == "HEB") ? "חדשה"		: "New");
					 	  	  break;
		case "approved"		: $textValue = (($cookie_guiLang == "HEB") ? "אושרה"	: "Confirmed");
					 	  	  break;
		case "rejected"		: $textValue = (($cookie_guiLang == "HEB") ? "נדחתה"	: "Rejected");
					 	  	  break;
		case "deleted"		: $textValue = (($cookie_guiLang == "HEB") ? "נמחקה"	: "Deleted");
					 	  	  break;
	}

	return commonPhpEncode($textValue);
}

/* --------------------------------------------------------------------------------------------	*/
/* formatContinent																				*/
/* --------------------------------------------------------------------------------------------	*/
function formatContinent ($value)
{
	global $cookie_guiLang;

	$textValue = "";

	switch ($value)
	{
		case "europe"			: $textValue = (($cookie_guiLang == "HEB") ? "אירופה"		: "Europe");
					 	  	      break;
		case "asia"				: $textValue = (($cookie_guiLang == "HEB") ? "אסיה"			: "Asia");
					 	  	      break;
		case "africa"			: $textValue = (($cookie_guiLang == "HEB") ? "אפריקה"		: "Africa");
					 	  	      break;
		case "northAmerice"		: $textValue = (($cookie_guiLang == "HEB") ? "צפון אמריקה"	: "North America");
					 	  	      break;
		case "southAmerica"		: $textValue = (($cookie_guiLang == "HEB") ? "דרום אמריקה"	: "South America");
					 	  	      break;
		case "australia"		: $textValue = (($cookie_guiLang == "HEB") ? "אוסטרליה"		: "Australia ");
					 	  	      break;
		case "antarctica"		: $textValue = (($cookie_guiLang == "HEB") ? "אנטארקטיקה"	: "Antarctica");
					 	  	      break;
	}

	return commonPhpEncode($textValue);
}

?>
