<?
// this is purely for testing. this will delay the loading of the image that was called for by a few seconds to mimic a server hang
sleep(3);

// Get the image information
$dims = @getimagesize($imagePath . $_GET['img']);

// Send the correct HTTP headers
$fileName = trim(substr(strrchr(strtolower($_GET['img']), '/'), 1));
header('Cache-control: max-age=31536000');
header('Expires: ' . gmdate('D, d M Y H:i:s', (TIMENOW + 31536000)) . ' GMT');
header('Content-disposition: inline; filename='.$fileName); 
header('Content-transfer-encoding: binary');
header('content-length: ' .@filesize($imagePath . $_GET['img']));
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $imageinfo['dateline']) . ' GMT');
$extension = trim(substr(strrchr(strtolower($_GET['img']), '.'), 1));
if ($extension == 'jpg' OR $extension == 'jpeg')
{
	header('Content-type: image/jpeg');
}
else if ($extension == 'png')
{
	header('Content-type: image/png');
}
else
{
	header('Content-type: image/gif');
}
// Display the image
readfile($imagePath . $_GET['img']);
?>