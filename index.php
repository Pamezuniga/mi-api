<?php 

$routesArray = explode("/", $_SERVER['REQUEST_URI']);
$routesArray = array_filter($routesArray);

class Connection{

	static public function connect(){

		try{

			$link = new PDO("mysql:host=localhost;dbname=datos","root", "");

			$link->exec("set names utf8");

		}catch(PDOException $e){

			die("Error: ".$e->getMessage());

		}

		return $link;
		
	}
}


if(count($routesArray) == 0){

	$json = array(
		'status' => 404,
		"results" => "Not found"
	);

	echo json_encode($json, http_response_code($json["status"]));

	return;

}else{
    
    if(count($routesArray) >= 1 &&
	   isset($_SERVER["REQUEST_METHOD"]) &&
	   $_SERVER["REQUEST_METHOD"] == "GET"){
        
  //si tiene claves de busqueda
     //  $resp =  getConsulta(explode("?", $routesArray[1])[0], $_GET["Columna"], $_GET["igual"], $_GET["select"]);
     //  fncResponse($resp, 'getFiltro');
      //no tiene clave
      $resp = getConsulta2(explode("?", $routesArray[1])[0], $_GET["select"]);

      fncResponse($resp, 'get');
       }
       if(count($routesArray) == 1 &&
	   isset($_SERVER["REQUEST_METHOD"]) &&
	   $_SERVER["REQUEST_METHOD"] == "POST"){
       
       $resp= postData($_GET["tabla"], $_POST);
       fncResponse($resp, 'getFiltro');
    }
     

}

 function postData($table, $data){
		
    $columns = "(";
    $params= "(";

    foreach ($data as $key => $value) {
        
        $columns .= $key.",";
        $params .= ":".$key.",";
        
    }

    $columns = substr($columns, 0, -1);
    $params = substr($params, 0, -1);

    $columns .= ")";
    $params .= ")";

    $link = Connection::connect();
    $stmt = $link->prepare("INSERT INTO $table $columns VALUES $params");

    foreach ($data as $key => $value) {
        
        $stmt->bindParam(":".$key, $data[$key], PDO::PARAM_STR);
        
    }

    if($stmt->execute()){

        $return = array(

            "lastId"=>$link->lastInsertId(),
            "comment"=>"The process was successful"
        
        );

        return $return;

    }else{

        return Connection::connect()->errorInfo();
    
    }

}


function getConsulta($tabla,$colummna,$valor,$select)
{
    $stmt = Connection::connect()->prepare("SELECT $select FROM $tabla WHERE $colummna=$valor");
    
    $stmt -> execute();

	return $stmt -> fetchAll(PDO::FETCH_CLASS);
}
function getConsulta2($tabla,$select)
{
    $stmt = Connection::connect()->prepare("SELECT $select FROM $tabla");
    
    $stmt -> execute();

	return $stmt -> fetchAll(PDO::FETCH_CLASS);
}



function fncResponse($response, $method){

    if(!empty($response)){

        $json = array(
            'status' => 200,
            'total' => count($response),
            "results" => $response
        );

    }else{

        $json = array(
            'status' => 404,
            "results" => "Not Found",
            'method' => $method
        );

    }

    echo json_encode($json, http_response_code($json["status"]));

    return;

}