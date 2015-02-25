<?php
require_once 'google-api-php-client/src/Google/autoload.php';


if($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET['code']) || (isset($_SESSION['access_token']) && $_SESSION['access_token']) ){

	//---------------------------------------------------------------
	// 1. uploading file to server first
	//---------------------------------------------------------------
	if(isset($_POST['submit'])){
		$target_path='a.jpg';
		$image =$_FILES["uploaded"]["tmp_name"];
		if(is_uploaded_file($_FILES['uploaded']['tmp_name'])){
		if(file_exists($target_path)) {
			chmod($target_path,0755); //Change the file permissions if allowed
			unlink($target_path); //remove the file
		}
		move_uploaded_file($_FILES['uploaded']['tmp_name'], $target_path);
		}
	}
	//---------------------------------------------------------------
	// 2. now uploading to the drive 
	//---------------------------------------------------------------
	$client = new Google_Client();
	// Get your credentials from the console
	$client->setClientId('<CLIENT_ID>');
	$client->setClientSecret('<CLIENT_SECRET>');
	
	//set the URL of this same file & set the same url in google developer console.
	$client->setRedirectUri('<URL_OF_THIS_PAGE_MUST_BE_SAME_TO_Redirect_URIs_IN_GOOGLE_DEVELOPER_CONSOLE>');
	
	$client->setScopes(array('https://www.googleapis.com/auth/drive.file'));
	session_start();
	
	// if already has access token
	if (isset($_GET['code']) || (isset($_SESSION['access_token']) && $_SESSION['access_token'])) {
		
		if (isset($_GET['code'])) {
			$client->authenticate($_GET['code']);
			$_SESSION['access_token'] = $client->getAccessToken();
		} else
			$client->setAccessToken($_SESSION['access_token']);

		$service = new Google_Service_Drive($client);

		//Insert a file
		$file = new Google_Service_Drive_DriveFile();
		$file->setTitle(uniqid().'.jpg');
		$file->setDescription('A test document');
		$file->setMimeType('image/jpeg');

		$data = file_get_contents('a.jpg');

		$createdFile = $service->files->insert($file, array(
			  'data' => $data,
			  'mimeType' => 'image/jpeg',
			  'uploadType' => 'multipart'
			));
		echo "<h1>File Uploaded Successfully..</h1><br>Below is the details of file:<br><br><br>";
		print_r($createdFile);
		//---------------------------------------------------------------
		// 3. removing the file from own server
		//---------------------------------------------------------------
		unlink('a.jpg'); //remove the file

	}
	// if no token
	else {
		$authUrl = $client->createAuthUrl();
		header('Location: ' . $authUrl);
		exit();
	}
}
else{
?>
	<form enctype="multipart/form-data" method="post" action="#">
		<input name="uploaded" type="file"><br>
		<input type="submit" class="submit_button" value="upload" name="submit">
	</form>
<?php
}
?>