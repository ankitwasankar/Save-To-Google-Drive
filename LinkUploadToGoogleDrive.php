<?php
require_once 'google-api-php-client/src/Google/autoload.php';
session_start();
if($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET['code']) || (isset($_SESSION['access_token']) && $_SESSION['access_token']) ){

	//---------------------------------------------------------------
	// 1. uploading file to server first
	//---------------------------------------------------------------
	if(isset($_POST['submit'])){		
		$url = $_POST['url'];
		$extension = "";
		$filename = basename($url);
		$arr = explode(".",$url);
		foreach($arr as $t){
			$extension = $t;
		}
		$_SESSION['filename']=$filename;
		$_SESSION['extension']=$extension;
		unlink($_SESSION['filename']); //remove the file if exist
		$newfname = $filename;
		$file = fopen ($url, "rb");
		if ($file) {
		  $newf = fopen ($newfname, "wb");
		  if ($newf)
		  while(!feof($file)) {
			fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
		  }
		}
		if ($newf) {
			fclose($newf);
		}
	}
	
	//---------------------------------------------------------------
	// 2. uploading to the drive 
	//---------------------------------------------------------------
	$client = new Google_Client();
	// Get your credentials from the console
	$client->setClientId('<CLIENT_ID>');
	$client->setClientSecret('<CLIENT_SECRET>');
	//set the URL of this same file & set the same url in google developer console.
	$client->setRedirectUri('<URL_OF_THIS_PAGE_MUST_BE_SAME_TO_Redirect_URIs_IN_GOOGLE_DEVELOPER_CONSOLE>');
	$client->setScopes(array('https://www.googleapis.com/auth/drive.file'));
	
	
	if (isset($_GET['code']) || (isset($_SESSION['access_token']) && $_SESSION['access_token'])) {
		
		if (isset($_GET['code'])) {
			$client->authenticate($_GET['code']);
			$_SESSION['access_token'] = $client->getAccessToken();
		} else
			$client->setAccessToken($_SESSION['access_token']);

		$service = new Google_Service_Drive($client);

		//Insert a file
		$file = new Google_Service_Drive_DriveFile();
		$file->setTitle($_SESSION['filename']);
		$file->setDescription('Document uploaded through link | save2drive.MyTechBlog.in');

		$data = file_get_contents($_SESSION['filename']);

		$createdFile = $service->files->insert($file, array(
			  'data' => $data,
			  'uploadType' => 'multipart'
			));
		echo "<h1>File Uploaded Successfully..</h1><br>Below is the details of file:<br><br><br>";
		//---------------------------------------------------------------
		// 3. removing the file from own server
		//---------------------------------------------------------------
		unlink($_SESSION['filename']); //remove the file
		session_destroy();
	} else {
		$authUrl = $client->createAuthUrl();
		header('Location: ' . $authUrl);
		exit();
	}
}
else{
?>
	<html>
		<form method="post" action="#" >
			<input name="url" size="50" />
			<input name="submit" type="submit" />
		</form>
	</html>
<?php
}
?>