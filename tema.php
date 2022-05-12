<?php

include 'upload.php';

require_once 'storage-blobs-php-quickstart/vendor/autoload.php';
require_once "storage-blobs-php-quickstart/random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=tema3blobstorage;AccountKey=VBflUvLvPmpcbm3JtPjRhdHIV/0MxNUJHYWfvfdUSKNICeeYtJNOznGp9560wjvJuCkG6bw3pWccGhlmCSKmOw==;EndpointSuffix=core.windows.net";

$blobClient = BlobRestProxy::createBlobService($connectionString);

$fileToUpload = $target_file;

if (!isset($_GET["Cleanup"])) {
    $createContainerOptions = new CreateContainerOptions();
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");

    $containerName = "tema3container";

    try {       
        $content = fopen($fileToUpload, "r");
        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix($fileToUpload);
	
        
	
        do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob)
            {
                $addr = $blob->getUrl();
            }
        
            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());
    }
    catch(ServiceException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
} 
else 
{
    try{
        echo "Deleting Container".PHP_EOL;
        echo $_GET["containerName"].PHP_EOL;
        echo "<br />";
        $blobClient->deleteContainer($_GET["containerName"]);
    }
    catch(ServiceException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}
unlink($target_file);
$textAnalysis= $_POST["textAnalysis"];
function CallAPI($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'User-Agent: PostmanRuntime/7.29.0',
        'Ocp-Apim-Subscription-Key: eecc3a8ddca84764b3e24b2bfdd0cfd8',
        'Connection: Keep-Alive',
	'Content-Type: application/json'
    ));

    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    
    curl_close($curl);

    return $result;
}

try {
    $conn = new PDO("sqlsrv:server = tcp:servertema3.database.windows.net,1433; Database = databasetema3", "raicu", "Parola123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    print("Error connecting to SQL Server.");
    die(print_r($e));
}
$sql = "INSERT INTO fileinfo (filename, blob_store_addr, file_text) VALUES (?,?,?)";
$result = $conn->prepare($sql);
$result->execute([$fileToUpload, $addr, $textAnalysis]);



$xmlcode = '{
  "documents": [
    {
      "language": "en",
      "id": "1",
      "text": "' .$textAnalysis. '"
    }
  ]
}';

$aux = CallAPI("POST", "https://sentimentanalystics.cognitiveservices.azure.com/text/analytics/v3.0/sentiment", $xmlcode);
file_put_contents("test.txt", $aux);
$display = file_get_contents("test.txt");
$digit = (int)filter_var($fileToUpload, FILTER_SANITIZE_NUMBER_INT);  
$fileOut = 'uploads/test'. $digit .'_results.txt';
file_put_contents($fileOut, $display);
if (!isset($_GET["Cleanup"])) {
    $createContainerOptions = new CreateContainerOptions();
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");

    $containerName = "tema3container";

    try {       
        $content = fopen($fileOut, "r");
        $blobClient->createBlockBlob($containerName, $fileOut, $content);
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix($fileOut );
		
        do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob)
            {
                $addr1 = $blob->getUrl();
            }
        
            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());
    }
    catch(ServiceException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
} 
else 
{
    try{
        echo "Deleting Container".PHP_EOL;
        echo $_GET["containerName"].PHP_EOL;
        echo "<br />";
        $blobClient->deleteContainer($_GET["containerName"]);
    }
    catch(ServiceException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}



$sql = "INSERT INTO fileinfo (filename, blob_store_addr, file_text) VALUES (?,?,?)";
$result = $conn->prepare($sql);
$result->execute([$fileOut , $addr1, $display]);

echo "\nThese are the blobs present in the container: \n";
$sql1 = "SELECT id, filename, blob_store_addr, file_text from fileinfo";
echo "<br/>";
echo '<table border=1px>';
echo '<thead><tr><th>ID</th><th>File Name</th><th>Link</th><th>Upload Date</th></tr></thead><tbody>';
      foreach ($conn->query($sql1) as $row) {
        echo '<tr><td>' . $row['id'] . '</td>';
        echo '<td>' . $row['filename'] . '</td>';
        echo "<td><a href='" . $row["blob_store_addr"] . "'>" . $row["blob_store_addr"] . "</a></td>";
        $date = date_create($row['time']);
        echo '<td>' . date_format($date, "d.m.Y H:i:s") . '</td>';
      }
      echo '</tbody>';
echo '</table>';
