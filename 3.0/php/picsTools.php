<?
ini_set("memory_limit","128M");

if (!function_exists("commonFtpConnect"))
	include "common.php";

/*
*------------------------------------------------------------
*                    ImageBMP
*------------------------------------------------------------
*            - Creates new BMP file
*
*         Parameters:  $img - Target image
*                      $file - Target file to store
*                            - if not specified, bmp is returned
*
*           Returns: if $file specified - true if OK
                     if $file not specified - image data
*/
if (!function_exists('imagebmp')) 
{
function imagebmp($img,$file="",$RLE=0)
{


$ColorCount=imagecolorstotal($img);

$Transparent=imagecolortransparent($img);
$IsTransparent=$Transparent!=-1;


if($IsTransparent) $ColorCount--;

if($ColorCount==0) {$ColorCount=0; $BitCount=24;};
if(($ColorCount>0)and($ColorCount<=2)) {$ColorCount=2; $BitCount=1;};
if(($ColorCount>2)and($ColorCount<=16)) { $ColorCount=16; $BitCount=4;};
if(($ColorCount>16)and($ColorCount<=256)) { $ColorCount=0; $BitCount=8;};


                $Width=imagesx($img);
                $Height=imagesy($img);

                $Zbytek=(4-($Width/(8/$BitCount))%4)%4;

                if($BitCount<24) $palsize=pow(2,$BitCount)*4;

                $size=(floor($Width/(8/$BitCount))+$Zbytek)*$Height+54;
                $size+=$palsize;
                $offset=54+$palsize;

                // Bitmap File Header
                $ret = 'BM';                        // header (2b)
                $ret .= int_to_dword($size);        // size of file (4b)
                $ret .= int_to_dword(0);        // reserved (4b)
                $ret .= int_to_dword($offset);        // byte location in the file which is first byte of IMAGE (4b)
                // Bitmap Info Header
                $ret .= int_to_dword(40);        // Size of BITMAPINFOHEADER (4b)
                $ret .= int_to_dword($Width);        // width of bitmap (4b)
                $ret .= int_to_dword($Height);        // height of bitmap (4b)
                $ret .= int_to_word(1);        // biPlanes = 1 (2b)
                $ret .= int_to_word($BitCount);        // biBitCount = {1 (mono) or 4 (16 clr ) or 8 (256 clr) or 24 (16 Mil)} (2b)
                $ret .= int_to_dword($RLE);        // RLE COMPRESSION (4b)
                $ret .= int_to_dword(0);        // width x height (4b)
                $ret .= int_to_dword(0);        // biXPelsPerMeter (4b)
                $ret .= int_to_dword(0);        // biYPelsPerMeter (4b)
                $ret .= int_to_dword(0);        // Number of palettes used (4b)
                $ret .= int_to_dword(0);        // Number of important colour (4b)
                // image data

                $CC=$ColorCount;
                $sl1=strlen($ret);
                if($CC==0) $CC=256;
                if($BitCount<24)
                   {
                    $ColorTotal=imagecolorstotal($img);
                     if($IsTransparent) $ColorTotal--;

                     for($p=0;$p<$ColorTotal;$p++)
                     {
                      $color=imagecolorsforindex($img,$p);
                       $ret.=inttobyte($color["blue"]);
                       $ret.=inttobyte($color["green"]);
                       $ret.=inttobyte($color["red"]);
                       $ret.=inttobyte(0); //RESERVED
                     };

                    $CT=$ColorTotal;
                  for($p=$ColorTotal;$p<$CC;$p++)
                       {
                      $ret.=inttobyte(0);
                      $ret.=inttobyte(0);
                      $ret.=inttobyte(0);
                      $ret.=inttobyte(0); //RESERVED
                     };
                   };


if($BitCount<=8)
{

 for($y=$Height-1;$y>=0;$y--)
 {
  $bWrite="";
  for($x=0;$x<$Width;$x++)
   {
   $color=imagecolorat($img,$x,$y);
   $bWrite.=decbinx($color,$BitCount);
   if(strlen($bWrite)==8)
    {
     $retd.=inttobyte(bindec($bWrite));
     $bWrite="";
    };
   };

  if((strlen($bWrite)<8)and(strlen($bWrite)!=0))
    {
     $sl=strlen($bWrite);
     for($t=0;$t<8-$sl;$t++)
      $sl.="0";
     $retd.=inttobyte(bindec($bWrite));
    };
 for($z=0;$z<$Zbytek;$z++)
   $retd.=inttobyte(0);
 };
};

if(($RLE==1)and($BitCount==8))
{
 for($t=0;$t<strlen($retd);$t+=4)
  {
   if($t!=0)
   if(($t)%$Width==0)
    $ret.=chr(0).chr(0);

   if(($t+5)%$Width==0)
   {
     $ret.=chr(0).chr(5).substr($retd,$t,5).chr(0);
     $t+=1;
   }
   if(($t+6)%$Width==0)
    {
     $ret.=chr(0).chr(6).substr($retd,$t,6);
     $t+=2;
    }
    else
    {
     $ret.=chr(0).chr(4).substr($retd,$t,4);
    };
  };
  $ret.=chr(0).chr(1);
}
else
{
$ret.=$retd;
};


                if($BitCount==24)
                {
                for($z=0;$z<$Zbytek;$z++)
                 $Dopl.=chr(0);

                for($y=$Height-1;$y>=0;$y--)
                 {
                 for($x=0;$x<$Width;$x++)
                        {
                           $color=imagecolorsforindex($img,ImageColorAt($img,$x,$y));
                           $ret.=chr($color["blue"]).chr($color["green"]).chr($color["red"]);
                        }
                 $ret.=$Dopl;
                 };

                 };

  if($file!="")
   {
    $r=($f=fopen($file,"w"));
    $r=$r and fwrite($f,$ret);
    $r=$r and fclose($f);
    return $r;
   }
  else
  {
   echo $ret;
  };
};
}


/*
*------------------------------------------------------------
*                    ImageCreateFromBmp
*------------------------------------------------------------
*            - Reads image from a BMP file
*
*         Parameters:  $file - Target file to load
*
*            Returns: Image ID
*/

if (!function_exists('imagecreatefrombmp')) 
{
function imagecreatefrombmp($file)
{
global  $CurrentBit, $echoMode;

$f=fopen($file,"r");
$Header=fread($f,2);

if($Header=="BM")
{
 $Size=freaddword($f);
 $Reserved1=freadword($f);
 $Reserved2=freadword($f);
 $FirstByteOfImage=freaddword($f);

 $SizeBITMAPINFOHEADER=freaddword($f);
 $Width=freaddword($f);
 $Height=freaddword($f);
 $biPlanes=freadword($f);
 $biBitCount=freadword($f);
 $RLECompression=freaddword($f);
 $WidthxHeight=freaddword($f);
 $biXPelsPerMeter=freaddword($f);
 $biYPelsPerMeter=freaddword($f);
 $NumberOfPalettesUsed=freaddword($f);
 $NumberOfImportantColors=freaddword($f);

if($biBitCount<24)
 {
  $img=imagecreate($Width,$Height);
  $Colors=pow(2,$biBitCount);
  for($p=0;$p<$Colors;$p++)
   {
    $B=freadbyte($f);
    $G=freadbyte($f);
    $R=freadbyte($f);
    $Reserved=freadbyte($f);
    $Palette[]=imagecolorallocate($img,$R,$G,$B);
   };




if($RLECompression==0)
{
   $Zbytek=(4-ceil(($Width/(8/$biBitCount)))%4)%4;

for($y=$Height-1;$y>=0;$y--)
    {
     $CurrentBit=0;
     for($x=0;$x<$Width;$x++)
      {
         $C=freadbits($f,$biBitCount);
       imagesetpixel($img,$x,$y,$Palette[$C]);
      };
    if($CurrentBit!=0) {freadbyte($f);};
    for($g=0;$g<$Zbytek;$g++)
     freadbyte($f);
     };

 };
};


if($RLECompression==1) //$BI_RLE8
{
$y=$Height;

$pocetb=0;

while(true)
{
$y--;
$prefix=freadbyte($f);
$suffix=freadbyte($f);
$pocetb+=2;

$echoit=false;

if($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>";
if(($prefix==0)and($suffix==1)) break;
if(feof($f)) break;

while(!(($prefix==0)and($suffix==0)))
{
 if($prefix==0)
  {
   $pocet=$suffix;
   $Data.=fread($f,$pocet);
   $pocetb+=$pocet;
   if($pocetb%2==1) {freadbyte($f); $pocetb++;};
  };
 if($prefix>0)
  {
   $pocet=$prefix;
   for($r=0;$r<$pocet;$r++)
    $Data.=chr($suffix);
  };
 $prefix=freadbyte($f);
 $suffix=freadbyte($f);
 $pocetb+=2;
 if($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>";
};

for($x=0;$x<strlen($Data);$x++)
 {
  imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]);
 };
$Data="";

};

};


if($RLECompression==2) //$BI_RLE4
{
$y=$Height;
$pocetb=0;

/*while(!feof($f))
 echo freadbyte($f)."_".freadbyte($f)."<BR>";*/
while(true)
{
//break;
$y--;
$prefix=freadbyte($f);
$suffix=freadbyte($f);
$pocetb+=2;

$echoit=false;

if($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>";
if(($prefix==0)and($suffix==1)) break;
if(feof($f)) break;

while(!(($prefix==0)and($suffix==0)))
{
 if($prefix==0)
  {
   $pocet=$suffix;

   $CurrentBit=0;
   for($h=0;$h<$pocet;$h++)
    $Data.=chr(freadbits($f,4));
   if($CurrentBit!=0) freadbits($f,4);
   $pocetb+=ceil(($pocet/2));
   if($pocetb%2==1) {freadbyte($f); $pocetb++;};
  };
 if($prefix>0)
  {
   $pocet=$prefix;
   $i=0;
   for($r=0;$r<$pocet;$r++)
    {
    if($i%2==0)
     {
      $Data.=chr($suffix%16);
     }
     else
     {
      $Data.=chr(floor($suffix/16));
     };
    $i++;
    };
  };
 $prefix=freadbyte($f);
 $suffix=freadbyte($f);
 $pocetb+=2;
 if($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>";
};

for($x=0;$x<strlen($Data);$x++)
 {
  imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]);
 };
$Data="";

};

};


 if($biBitCount==24)
{
 $img=imagecreatetruecolor($Width,$Height);
 $Zbytek=$Width%4;

   for($y=$Height-1;$y>=0;$y--)
    {
     for($x=0;$x<$Width;$x++)
      {
       $B=freadbyte($f);
       $G=freadbyte($f);
       $R=freadbyte($f);
       $color=imagecolorexact($img,$R,$G,$B);
       if($color==-1) $color=imagecolorallocate($img,$R,$G,$B);
       imagesetpixel($img,$x,$y,$color);
      }
    for($z=0;$z<$Zbytek;$z++)
     freadbyte($f);
   };
};
return $img;

};


fclose($f);


};
}





/*
* Helping functions:
*-------------------------
*
* freadbyte($file) - reads 1 byte from $file
* freadword($file) - reads 2 bytes (1 word) from $file
* freaddword($file) - reads 4 bytes (1 dword) from $file
* freadlngint($file) - same as freaddword($file)
* decbin8($d) - returns binary string of d zero filled to 8
* RetBits($byte,$start,$len) - returns bits $start->$start+$len from $byte
* freadbits($file,$count) - reads next $count bits from $file
* RGBToHex($R,$G,$B) - convert $R, $G, $B to hex
* int_to_dword($n) - returns 4 byte representation of $n
* int_to_word($n) - returns 2 byte representation of $n
*/

function freadbyte($f)
{
 return ord(fread($f,1));
};

function freadword($f)
{
 $b1=freadbyte($f);
 $b2=freadbyte($f);
 return $b2*256+$b1;
};


function freadlngint($f)
{
return freaddword($f);
};

function freaddword($f)
{
 $b1=freadword($f);
 $b2=freadword($f);
 return $b2*65536+$b1;
};



function RetBits($byte,$start,$len)
{
$bin=decbin8($byte);
$r=bindec(substr($bin,$start,$len));
return $r;

};



$CurrentBit=0;
function freadbits($f,$count)
{
 global $CurrentBit,$SMode;
 $Byte=freadbyte($f);
 $LastCBit=$CurrentBit;
 $CurrentBit+=$count;
 if($CurrentBit==8)
  {
   $CurrentBit=0;
  }
 else
  {
   fseek($f,ftell($f)-1);
  };
 return RetBits($Byte,$LastCBit,$count);
};



function RGBToHex($Red,$Green,$Blue)
  {
   $hRed=dechex($Red);if(strlen($hRed)==1) $hRed="0$hRed";
   $hGreen=dechex($Green);if(strlen($hGreen)==1) $hGreen="0$hGreen";
   $hBlue=dechex($Blue);if(strlen($hBlue)==1) $hBlue="0$hBlue";
   return($hRed.$hGreen.$hBlue);
  };

        function int_to_dword($n)
        {
                return chr($n & 255).chr(($n >> 8) & 255).chr(($n >> 16) & 255).chr(($n >> 24) & 255);
        }
        function int_to_word($n)
        {
                return chr($n & 255).chr(($n >> 8) & 255);
        }


function decbin8($d)
{
return decbinx($d,8);
};

function decbinx($d,$n)
{
$bin=decbin($d);
$sbin=strlen($bin);
for($j=0;$j<$n-$sbin;$j++)
 $bin="0$bin";
return $bin;
};

function inttobyte($n)
{
return chr($n);
};

# ------------------------------------------------------------------------------------------------------
function picsToolsResize ($origFile, $suffix, $newW, $newH, $destFile, $bgColor, $quality = 95, $allowCrop = false)
{
		list($width_orig, $height_orig, $imageType) = getimagesize($origFile);

		if ($newH == $height_orig && $newW == $width_orig && $imageType == IMAGETYPE_JPEG) // then do nothing
		{
				copy($origFile, $destFile);
				return;
		}

		if ($newW == 0 && $newH == 0)
		{
				echo "New Image dimensions are all zeros";
				return;
		}
		// one freedom degree
		if ($newW == 0)
				$newW = round(1.0 * $newH * $width_orig / $height_orig);
		if ($newH == 0)
				$newH = round(1.0 * $newW * $height_orig / $width_orig);

		$Rcolor = hexdec(substr($bgColor,1,2));
		$Gcolor = hexdec(substr($bgColor,3,2));
		$Bcolor = hexdec(substr($bgColor,5,2));

		switch ($imageType) {
			case IMAGETYPE_JPEG:
					ini_set ('gd.jpeg_ignore_warning', 1);
					$image = @imagecreatefromjpeg($origFile);
					if (!$image)
							$image= imagecreatefromstring(file_get_contents($origFile));
				   	break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($origFile); break;
			case IMAGETYPE_PNG:
				$origImage = imagecreatefrompng($origFile);
				// replace transparency by bgColor
				$image = imagecreatetruecolor($width_orig, $height_orig);
				imagefill($image, 0, 0, imagecolorallocate($image, $Rcolor, $Gcolor, $Bcolor));
				imagealphablending($image, TRUE);
				imagecopy($image, $origImage, 0, 0, 0, 0, $width_orig, $height_orig);
				imagedestroy($origImage);
				break;
			case IMAGETYPE_BMP:
				$image = imagecreatefrombmp($origFile); break;
		}

		if ($newH >= $height_orig && $newW >= $width_orig) { // no need to shrink
			$image_r = $image;
			$crop_x = ($width_orig - $newW) / 2;
			$crop_y = ($height_orig - $newH) / 2;
		}
		else if ($newW >= $width_orig || ( $newH / $height_orig <= $newW / $width_orig)) { // resize by width
			$width_fake = round(($newH / $height_orig) * $width_orig);
			if ($allowCrop)
			{
				$height2Crop = round(($height_orig - ($width_orig / $newW * $newH)) / 2);
				imagecopy($image, $image, 0, 0, 0, 0, $width_orig, $height_orig-2*$height2Crop);// take top part
				$crop_x = 0;
				$crop_y = 0;
				$width_fake = $newW;
				$height_orig -= 2*$height2Crop;
			} else {
				$crop_x = ($width_fake - $newW) / 2;
				$crop_y = 0;
			}
			$image_r = imagecreatetruecolor($width_fake, $newH);
			$resamp = imagecopyresampled($image_r, $image, 0, 0, 0, 0, $width_fake, $newH, $width_orig, $height_orig);
			if (! $resamp) {
				echo "Image resampling failed";
				return;
			}
		}
		else if ($newH >= $height_orig || ( $newW / $width_orig < $newH / $height_orig)) { // resize by height
			$height_fake = round(($newW / $width_orig) * $height_orig);
			if ($allowCrop)
			{
				$width2Crop = round(($width_orig - ($height_orig / $newH * $newW)) / 2);
				imagecopy($image, $image, 0, 0, $width2Crop, 0, $width_orig-$width2Crop, $height_orig);
				$crop_x = 0;
				$crop_y = 0;
				$height_fake = $newH;
				$width_orig -= 2*$width2Crop;
			} else {
				$crop_x = 0;
				$crop_y = ($height_fake - $newH) / 2;
			}
			$image_r = imagecreatetruecolor($newW, $height_fake);
			$resamp = imagecopyresampled($image_r, $image, 0, 0, 0, 0, $newW, $height_fake, $width_orig, $height_orig);
			if (! $resamp) {
				echo "Image resampling failed";
				return;
			}
		}

		// Crop
		$image_c = imagecreatetruecolor($newW, $newH);
		imagecopy($image_c, $image_r, 0, 0, $crop_x, $crop_y, $newW, $newH);

		// Set Background Color
		if ( $crop_x < 0 || $crop_y < 0)
		{
		   $bgc = imagecolorallocate( $image_c, $Rcolor, $Gcolor, $Bcolor );
		   //fill the background with white (not sure why it has to be in this order)
		   $absX = abs($crop_x);
		   $absY = abs($crop_y);
		   imagefilledpolygon($image_c, array(0,0,$absX,0,$absX,$newH-1,0,$newH-1), 4, $bgc);
		   imagefilledpolygon($image_c, array($newW-1,0,$newW-1-$absX,0,$newW-1-$absX,$newH-1,$newW-1,$newH-1), 4, $bgc);
		   imagefilledpolygon($image_c, array(0,0,0,$absY,$newW-1,$absY,$newW-1,0), 4, $bgc);
		   imagefilledpolygon($image_c, array(0,$newH-1,0,$newH-1-$absY,$newW-1,$newH-1-$absY,$newW-1,$newH-1), 4, $bgc);
		  // imagefill( $image_c, 0, 0, $bgc );
		  // imagefill( $image_c, $newW-1, $newH-1, $bgc );
		}

		// Output
		imagejpeg($image_c, $destFile, $quality);
}
 
# ------------------------------------------------------------------------------------------------------
function picsToolsResizeAndCropWidth ($origFile, $suffix, $newW, $newH, $destFile, $bgColor)
{
		$ftpDestFile = $destFile;
		$destFile = $origFile . "_resize";

		list($width_orig, $height_orig, $imageType) = getimagesize($origFile);

		if ($newW == 0 || $newH == 0)
		{
				echo "New Image dimensions contain zeros";
				return false;
		}

		// first resize by width = 0
		$saveW = $newW;
		$newW  = 0;

		// one freedom degree
		if ($newW == 0)
				$newW = round(1.0 * $newH * $width_orig / $height_orig);
		if ($newH == 0)
				$newH = round(1.0 * $newW * $height_orig / $width_orig);

		switch ($imageType) {
			case IMAGETYPE_JPEG:
				ini_set ('gd.jpeg_ignore_warning', 1);
				$image = @imagecreatefromjpeg($origFile);
				if (!$image)
						$image= imagecreatefromstring(file_get_contents($origFile));
				break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($origFile); break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($origFile);	break;
			case IMAGETYPE_BMP:
				$image = imagecreatefrombmp($origFile); break;
		}
		if (!$image) {
			echo "image file $origFile is not recognized";
			return;
		}
		$Rcolor = hexdec(substr($bgColor,1,2));
		$Gcolor = hexdec(substr($bgColor,3,2));
		$Bcolor = hexdec(substr($bgColor,5,2));

		if ($newH >= $height_orig && $newW >= $width_orig) 	 // no need to shrink
		{
			$image_r = $image;
			$crop_x = ($width_orig - $newW) / 2;
			$crop_y = ($height_orig - $newH) / 2;
		}
		else if ($newW >= $width_orig || ( $newH / $height_orig <= $newW / $width_orig))  // resize by width
		{
			$width_fake = round(($newH / $height_orig) * $width_orig);
			$image_r = imagecreatetruecolor($width_fake, $newH);
			$resamp = imagecopyresampled($image_r, $image, 0, 0, 0, 0, $width_fake, $newH, $width_orig, $height_orig);
			if (! $resamp) {
				echo "Image resampling failed";
				return;
			}
			$crop_x = ($width_fake - $newW) / 2;
			$crop_y = 0;
		}
		else if ($newH >= $height_orig || ( $newW / $width_orig < $newH / $height_orig)) // resize by height
		{
			$height_fake = round(($newW / $width_orig) * $height_orig);
			$image_r = imagecreatetruecolor($newW, $height_fake);
			$resamp = imagecopyresampled($image_r, $image, 0, 0, 0, 0, $newW, $height_fake, $width_orig, $height_orig);
			if (! $resamp) {
				echo "Image resampling failed";
				return;
			}
			$crop_x = 0;
			$crop_y = ($height_fake - $newH) / 2;
		}

		// Crop
		$image_c = imagecreatetruecolor($newW, $newH);
		imagecopy($image_c, $image_r, 0, 0, $crop_x, $crop_y, $newW, $newH);

		$image_c2 = imagecreatetruecolor($saveW, $newH);

		$crop_x = abs(($newW - $saveW) / 2);

		// now crop by width
		if ($newW > $saveW)
		{
			$image_c2 = imagecreatetruecolor($saveW, $newH);
			imagecopy($image_c2, $image_c, 0, 0, $crop_x, 0, $saveW, $newH);
		}
		else
		{
		    imagecopy($image_c2, $image_c, $crop_x, 0, 0, 0, $newW, $newH);

			$newW = $saveW;

		   	$bgc = imagecolorallocate($image_c, $Rcolor, $Gcolor, $Bcolor );

		   	//fill the background with bg (not sure why it has to be in this order)
		   	$absX = $crop_x;
		   	$absY = 0;
		   	imagefilledpolygon($image_c2, array(0,0,$absX,0,$absX,$newH-1,0,$newH-1), 4, $bgc);
		   	imagefilledpolygon($image_c2, array($newW-1,0,$newW-1-$absX,0,$newW-1-$absX,$newH-1,$newW-1,$newH-1), 4, $bgc);
		   	imagefilledpolygon($image_c2, array(0,0,0,$absY,$newW-1,$absY,$newW-1,0), 4, $bgc);
		   	imagefilledpolygon($image_c2, array(0,$newH-1,0,$newH-1-$absY,$newW-1,$newH-1-$absY,$newW-1,$newH-1), 4, $bgc);
		}

		// Output
		imagejpeg($image_c2, $destFile, 95);
		
		/* [29/6/17 Amir] Removed. This code cannot work since commonFtpConnect takes 1 parameter
		 *
		if (strpos($ftpDestFile, "/") === 0)	// is relative address?
		{
			// copy file with FTP in order to do the copy with correct username and group

			// - ftp connect (do local connection)
			$connId = commonFtpConnect ();

			// - prepare dest file name (as relative address)
			$currDir = explode("/", ftp_pwd ($connId));
			$lastDir = count($currDir) - 1;
			$lastDir = $currDir[$lastDir];

			$matchpoint  = strpos ($ftpDestFile, $lastDir);
			$ftpDestFile = substr ($ftpDestFile, $matchpoint+strlen($lastDir)+1);
			$ftpDestDir	 = substr ($ftpDestFile, 0, strrpos($ftpDestFile, "/"));

			ftp_chmod ($connId, 0777, $ftpDestDir);

			// - copy the file
			$upload = ftp_put($connId, $ftpDestFile, $destFile, FTP_BINARY);

			commonFtpDisconnect ($connId);
		}
		else	// regular copy
		{
		 */
			copy ($destFile, $ftpDestFile);
		//}
}

// this script creates a watermarked image from an image file - can be a .jpg .gif or .png file 
// where watermark.gif is a mostly transparent gif image with the watermark - goes in the same directory as this script 
// where this script is named watermark.php 
// call this script with an image tag 
// <img src="watermark.php?path=imagepath"> where path is a relative path such as subdirectory/image.jpg 

function picsToolsAddWatermark ($imagesource, $watermarkFile, $destFile, $quality = 95)
{
	$filetype = substr($imagesource,strlen($imagesource)-4,4); 
	$filetype = strtolower($filetype); 
	if($filetype == ".gif")  $image = @imagecreatefromgif($imagesource);  
	if($filetype == ".jpg")  $image = @imagecreatefromjpeg($imagesource);  
	if($filetype == ".png")  $image = @imagecreatefrompng($imagesource);  
	if (!$image) die(); 
	
	$watermark = @imagecreatefromgif($watermarkFile); 

	$imagewidth = imagesx($image); 
	$imageheight = imagesy($image);  
	$watermarkwidth =  imagesx($watermark); 
	$watermarkheight =  imagesy($watermark); 
	$startwidth = (($imagewidth - $watermarkwidth)/2); 
	$startheight = (($imageheight - $watermarkheight)/2); 
	imagecopy($image, $watermark, $startwidth, $startheight, 0, 0, $watermarkwidth, $watermarkheight); 
	imagejpeg($image, $destFile, $quality); 
//	imagedestroy($image); 
//	imagedestroy($watermark); 
}


function resize_png_image($img,$newWidth,$newHeight,$target)
{
    $srcImage=imagecreatefrompng($img);
    if($srcImage==''){
        return FALSE;
    }
    $srcWidth=imagesx($srcImage);
    $srcHeight=imagesy($srcImage);
    $percentage=(double)$newWidth/$srcWidth;
    $destHeight=round($srcHeight*$percentage)+1;
    $destWidth=round($srcWidth*$percentage)+1;
    if($destHeight > $newHeight){
        // if the width produces a height bigger than we want, calculate based on height
        $percentage=(double)$newHeight/$srcHeight;
        $destHeight=round($srcHeight*$percentage)+1;
        $destWidth=round($srcWidth*$percentage)+1;
    }
    $destImage=imagecreatetruecolor($destWidth-1,$destHeight-1);
    if(!imagealphablending($destImage,FALSE)){
        return FALSE;
    }
    if(!imagesavealpha($destImage,TRUE)){
        return FALSE;
    }
    if(!imagecopyresampled($destImage,$srcImage,0,0,0,0,$destWidth,$destHeight,$srcWidth,$srcHeight)){
        return FALSE;
    }
    if(!imagepng($destImage,$target)){
        return FALSE;
    }
    imagedestroy($destImage);
    imagedestroy($srcImage);
    return TRUE;
}

// A Script by Amir based on http://koivi.com/php-gd-image-watermark/
// imagesource needs to be a JPEG or PNG
// watermark needs to be a PNG-24 image that has alpha transparency and was saved as a PNG
// hPosition can be: 'left', 'center' or 'right'
// vPosition can be: 'top', 'center' or 'bottom'
// wm_size can be: 'larger' (Cover Entire Image), '1' (Shows entire watermark) or '.5' (50% of the watermark)
function picsToolsAddPNGWatermark ($imagesource, $watermarkFile, $destFile, $vPosition = 'center', $hPosition='center', $wm_size ='1')
{
            // file upload success
            $size=getimagesize($imagesource);
            if($size[2]==2 || $size[2]==3){
                // it was a JPEG or PNG image, so we're OK so far
                
                $original=$imagesource;
                $target=$destFile;
                $watermark=$watermarkFile;
                $wmTarget=$watermark.'.tmp';

                $origInfo = getimagesize($original); 
                $origWidth = $origInfo[0]; 
                $origHeight = $origInfo[1]; 

                $waterMarkInfo = getimagesize($watermark);
                $waterMarkWidth = $waterMarkInfo[0];
                $waterMarkHeight = $waterMarkInfo[1];
        
                // watermark sizing info
                if($wm_size=='larger'){
                    $placementX=0;
                    $placementY=0;
                    $hPosition='center';
                    $vPosition='center';
                	$waterMarkDestWidth=$waterMarkWidth;
                	$waterMarkDestHeight=$waterMarkHeight;
                    
                    // both of the watermark dimensions need to be 5% more than the original image...
                    // adjust width first.
                    if($waterMarkWidth > $origWidth*1.05 && $waterMarkHeight > $origHeight*1.05){
                    	// both are already larger than the original by at least 5%...
                    	// we need to make the watermark *smaller* for this one.
                    	
                    	// where is the largest difference?
                    	$wdiff=$waterMarkDestWidth - $origWidth;
                    	$hdiff=$waterMarkDestHeight - $origHeight;
                    	if($wdiff > $hdiff){
                    		// the width has the largest difference - get percentage
                    		$sizer=($wdiff/$waterMarkDestWidth)-0.05;
                    	}else{
                    		$sizer=($hdiff/$waterMarkDestHeight)-0.05;
                    	}
                    	$waterMarkDestWidth-=$waterMarkDestWidth * $sizer;
                    	$waterMarkDestHeight-=$waterMarkDestHeight * $sizer;
                    }else{
                    	// the watermark will need to be enlarged for this one
                    	
                    	// where is the largest difference?
                    	$wdiff=$origWidth - $waterMarkDestWidth;
                    	$hdiff=$origHeight - $waterMarkDestHeight;
                    	if($wdiff > $hdiff){
                    		// the width has the largest difference - get percentage
                    		$sizer=($wdiff/$waterMarkDestWidth)+0.05;
                    	}else{
                    		$sizer=($hdiff/$waterMarkDestHeight)+0.05;
                    	}
                    	$waterMarkDestWidth+=$waterMarkDestWidth * $sizer;
                    	$waterMarkDestHeight+=$waterMarkDestHeight * $sizer;
                    }
                }else{
	                $waterMarkDestWidth=round($origWidth * floatval($wm_size));
	                $waterMarkDestHeight=round($origHeight * floatval($wm_size));
	                if($wm_size==1){
	                    $waterMarkDestWidth-=2*$edgePadding;
	                    $waterMarkDestHeight-=2*$edgePadding;
	                }
                }

                // OK, we have what size we want the watermark to be, time to scale the watermark image
                resize_png_image($watermark,$waterMarkDestWidth,$waterMarkDestHeight,$wmTarget);
                
                // get the size info for this watermark.
                $wmInfo=getimagesize($wmTarget);
                $waterMarkDestWidth=$wmInfo[0];
                $waterMarkDestHeight=$wmInfo[1];

                $differenceX = $origWidth - $waterMarkDestWidth;
                $differenceY = $origHeight - $waterMarkDestHeight;

                // where to place the watermark?
                switch($hPosition){
                    // find the X coord for placement
                    case 'left':
                        $placementX = $edgePadding;
                        break;
                    case 'center':
                        $placementX =  round($differenceX / 2);
                        break;
                    case 'right':
                        $placementX = $origWidth - $waterMarkDestWidth - $edgePadding;
                        break;
                }

                switch($vPosition){
                    // find the Y coord for placement
                    case 'top':
                        $placementY = $edgePadding;
                        break;
                    case 'center':
                        $placementY =  round($differenceY / 2);
                        break;
                    case 'bottom':
                        $placementY = $origHeight - $waterMarkDestHeight - $edgePadding;
                        break;
                }
       
                if($size[2]==3)
                    $resultImage = imagecreatefrompng($original);
                else
				{
					ini_set ('gd.jpeg_ignore_warning', 1);
					$resultImage = @imagecreatefromjpeg($original);
					if (!$resultImage)
							$resultImage= imagecreatefromstring(file_get_contents($original));
				}
                imagealphablending($resultImage, TRUE);
        
                $finalWaterMarkImage = imagecreatefrompng($wmTarget);
                $finalWaterMarkWidth = imagesx($finalWaterMarkImage);
                $finalWaterMarkHeight = imagesy($finalWaterMarkImage);
        
                imagecopy($resultImage,
                          $finalWaterMarkImage,
                          $placementX,
                          $placementY,
                          0,
                          0,
                          $finalWaterMarkWidth,
                          $finalWaterMarkHeight
                );
                
                if($size[2]==3){
                    imagealphablending($resultImage,FALSE);
                    imagesavealpha($resultImage,TRUE);
                    imagepng($resultImage,$target,$quality);
                }else{
                    imagejpeg($resultImage,$target,$quality); 
                }

                imagedestroy($resultImage);
                imagedestroy($finalWaterMarkImage);

                unlink($wmTarget);
            }
}

# ------------------------------------------------------------------------------------------------------
function picsToolsForceResize ($origFile, $suffix, $newW, $newH, $destFile, $quality = 95)
{
		list($width_orig, $height_orig, $imageType) = getimagesize($origFile);

		if ($newW == 0 || $newH == 0)
		{
				echo "New Image dimensions contain zeros";
				return;
		}

		switch ($imageType) 
		{
			case IMAGETYPE_JPEG:
				ini_set ('gd.jpeg_ignore_warning', 1);
				$image = @imagecreatefromjpeg($origFile);
				if (!$image)
					$image= imagecreatefromstring(file_get_contents($origFile));
			   	break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($origFile); break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($origFile); break;
			case IMAGETYPE_BMP:
				$image = imagecreatefrombmp($origFile); break;
		}
		if (!$image) {
			echo "image file $origFile is not recognized";
			return;
		}

		$image_r = imagecreatetruecolor($newW, $newH);
		$resamp = imagecopyresampled($image_r, $image, 0, 0, 0, 0, $newW, $newH, $width_orig, $height_orig);
		if (! $resamp) {
			echo "Image resampling failed";
			return;
		}

		// Output
		imagejpeg($image_r, $destFile, $quality);
}

?>
