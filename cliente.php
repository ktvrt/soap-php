<?php

class MySoapClient extends SoapClient
{
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $response = parent::__doRequest($request, $location, $action, $version, $one_way);
        // parse $response, extract the multipart messages and so on

        //this part removes stuff
        $start=strpos($response,'<?xml');
        $end=strrpos($response,'>');    
        $response_string=substr($response,$start,$end-$start+1);
        return($response_string);
    }	
}

$wsdl="http://localhost/soap/server.php?wsdl";  // SOAP Server
$client = new MySoapClient( $wsdl, array( 'cache_wsdl' => WSDL_CACHE_NONE ) );

if(isset($_POST['submitDF'])){
  $fichero = $_POST['namefile'];
  $encodeContent = $client->descargar_file($fichero) or die("Error"); 
  $response = json_decode($encodeContent,true);

  $contenido = base64_decode($response[0]['encodeContent']);   // Now decode the content recibido del servidor    
  header("Content-Type: application/octet-stream");
  header("Content-Type: application/force-download");
  header("Content-Length: ".$response[0]['filezise']);
  header("Content-Disposition:attachment; filename=" .basename($response[0]['fichero'])."");
  echo $contenido;
  echo $response[0]['estado'];
}
echo "instanacia cliente </br>";
try {
	//$responseLogin = $client->login( 'test_user', 'test_password' ); // call login() from .wsdl
	$responseLogin = $client->__soapCall('login',[
	  'test_user', 'test_password',
	]);
	echo "llamamos login: $responseLogin </br>";
	if($responseLogin == "success") { // if success
	  $params =  "algo";
	  
	  $response = $client->doFilter( $params ); // call doFilter() from .wsdl
	  ?>
		<pre><?php print_r( $response ); ?></pre>
	  <?php
	}

	if(isset($_POST['submit']))
	{
		$tmpfile = $_FILES["uploadfiles"]["tmp_name"];   // temp filename
		$filename = $_FILES["uploadfiles"]["name"];      // Original filename

		$handle = fopen($tmpfile, "r");                  // Open the temp file
		$contents = fread($handle, filesize($tmpfile));  // Read the temp file
		fclose($handle);                                 // Close the temp file
		$decodeContent   = base64_encode($contents);     // Decode the file content, so that we code send a binary string to SOAP

		$response = $client->upload_file($decodeContent,$filename) or die("Error");  //Send two inputs strings. {1} DECODED CONTENT {2} FILENAME
		echo $response;
	}   
  } catch ( SoapFault $sf ) {
	echo "catch: ".$sf->getMessage();
  
  //Full SoapFault message
    echo '<pre>';
    var_dump( $sf );
    echo '</pre>';
  
  //Analyze last request
   //$xml = $client->__getLastRequest();
   //echo "xml: ".$xml;
  }  
?>

<form name="name1" method="post" action="" enctype="multipart/form-data">
<input type="file" name="uploadfiles"><br />
<input type="submit" name="submit" value="uploadSubmit"><br />
</form>

<form name="form2" method="post" action="">
<input type="text" name="namefile"><br />
<input type="submit" name="submitDF" value="DescargarFichero"><br />
</form>

<?php
// Esto devolverá todos los archivos de esa carpeta
$archivos = scandir("uploads\\");
$num=0;
for ($i=2; $i<count($archivos); $i++)
{$num++;
?>
<!-- Visualización del nombre del archivo !-->
         
    <tr>
      <th scope="row"><?php echo $num;?></th>
      <td><?php echo $archivos[$i]; ?></td>
      <td><a title="Descargar Archivo" href="subidas/<?php echo $archivos[$i]; ?>" download="<?php echo $archivos[$i]; ?>" style="color: blue; font-size:18px;"> 
            <span >descargar</span> 
          </a>
      </td>
      <td><a title="Eliminar Archivo" href="Eliminar.php?name=subidas/<?php echo $archivos[$i]; ?>" style="color: red; font-size:18px;" onclick="return confirm('Esta seguro de eliminar el archivo?');"> 
            <span>eliminar</span> 
          </a>
      </td>
    </tr>
 <?php }?>