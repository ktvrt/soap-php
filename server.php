<?php
require_once('lib/nusoap.php'); //include required class for build nnusoap web service server

// Define the method as a PHP function
function upload_file($encoded,$name) {
  
  if($name!="")
  {
    $location = "uploads\\".$name;                               // Mention where to upload the file
    $current = file_get_contents($location);                     // Get the file content. This will create an empty file if the file does not exist     
    $current = base64_decode($encoded);                          // Now decode the content which was sent by the client     
    file_put_contents($location, $current);                      // Write the decoded content in the file mentioned at particular location      
    return "File Uploaded successfully...";                      // Output success message                              
  }
  else        
  {
      return "Please upload a file...";
  }
}

function descargar_file($fichero) { 
  $location = "uploads\\".$fichero;
  $gestor = fopen($location, "r");
  $contenido = fread($gestor, filesize($location));
  fclose($gestor);
  $encodeContent   = base64_encode($contenido);     // Encode the file content, so that we code send a binary string to SOAP
	  
  //return $encodeContent;

  $resultado = array([
    "fichero" => $fichero,
    "filezise" => filesize($location),
    "encodeContent" => $encodeContent,
    "estado" => "Arcvhivo listo...",
  ]);
  return json_encode($resultado);
  
    
}

function login( $login, $password )
{
  //return "some string";
  return "success";
}

function doFilter( $params )
{
  return "se filtra";
}

// Create server object
 $server = new nusoap_server();

 // configure  WSDL
 $server->configureWSDL('Upload File','urn:uploadwsdl');

 // Register the method to expose
  $server->register('upload_file',                                 // method
      array('encoded' => 'xsd:string','name' => 'xsd:string'),    // input parameters
      array('return' => 'xsd:string')
    );

  $server->register('descargar_file',                                 // method
    array('fichero' => 'xsd:string'),    // input parameters
    array('return' => 'xsd:string')
  );

  $server->register('login',
    array('usuario' => 'xsd:string', 
      'password' => 'xsd:string'),  //parameter
    array('data' => 'xsd:string')  //output    
  ); 
  
  $server->register('doFilter',
    array('params' => 'xsd:string'),  //parameter
    array('data' => 'xsd:string')  //output    
  );  

  // Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA)? $HTTP_RAW_POST_DATA : '';
$server->service(file_get_contents("php://input"));
?>
