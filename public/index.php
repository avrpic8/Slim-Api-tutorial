<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once ("../vendor/autoload.php");
require_once ("../includes/DbOperations.php");


/**
 *   for enable error handling in slim framework,
 *   if this not enabled the error occurred not showing.
 */
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);


$app->post('/createUser', function (Request $request, Response $response){

    if( !haveEmptyParameters(array('email','password','name','school','api_key'), $request, $response) ){

        $db = new DbOperations();

        $data = $request->getParsedBody();
        $email = $data['email'];
        $pass  = $data['password'];
        $name  = $data['name'];
        $school = $data['school'];
        $api_key = $data['api_key'];
        $hash_pass = password_hash($pass, PASSWORD_DEFAULT);


        $result = $db->createUser($email, $hash_pass, $name, $school, $api_key);
        if($result == USER_CREATE){
            $message = array();
            $message['error'] = false;
            $message['msg'] = "User created successfully";

            $response->write(json_encode($message));
            return $response
                        ->withHeader("Content-type", "application/json")
                        ->withStatus(201);

        }
        elseif ($result == USER_NOT_CREATE){

            $message = array();
            $message['error'] = true;
            $message['msg'] = "Some error occurred";

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(422);

        }
        elseif ($result == USER_EXIST){

            $message = array();
            $message['error'] = true;
            $message['msg'] = "User already exist";

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(422);
        }
    }

    return $response
        ->withHeader("Content-type", "application/json")
        ->withStatus(422);
});
$app->post("/userLogin", function (Request $request, Response $response){

    if(!haveEmptyParameters(array('email', 'password'), $request, $response)){

        $db = new DbOperations();

        $data = $request->getParsedBody();
        $email = $data['email'];
        $pass  = $data['password'];

        $result = $db->userLogin($email, $pass);
        if($result == USER_AUTHENTICATED){

            $user = $db->getUserByEmail($email);

            $message = array();
            $message['error'] = false;
            $message['msg'] = "Login Successfully";
            $message['user'] = $user;

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(200);

        }
        else if($result == USER_NOT_FOUND){

            $message = array();
            $message['error'] = true;
            $message['msg'] = "User not exist";

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(200);
        }
        else if($result == PASSWORD_NOT_MATCH){

            $message = array();
            $message['error'] = true;
            $message['msg'] = "Invalid password";

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader("Content-type", "application/json")
        ->withStatus(422);
});
$app->get("/allUsers", function (Request $request, Response $response){

    $db = new DbOperations();

    $users = $db->getAllUser();
    $message = array();
    $message['error'] = false;
    $message['users'] = $users;

    $response->write(json_encode($message));
    return $response
        ->withHeader("Content-type", "application/json")
        ->withStatus(200);
});
$app->put("/updateUser/{id}", function (Request $request, Response $response, array $args){


    if(!haveEmptyParameters(array('email', 'name', 'school'), $request, $response)){

        $db = new DbOperations();

        $data = $request->getParsedBody();
        $email = $data['email'];
        $name  = $data['name'];
        $school  = $data['school'];
        $id = $args['id'];

        if($db->updateUser($id, $email, $name, $school)){

            $user = $db->getUserByEmail($email);

            $message = array();
            $message['error'] = false;
            $message['msg'] = "User updated successfully";
            $message['user'] = $user;

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(200);

        }
        else{

            $message = array();
            $message['error'] = false;
            $message['msg'] = "User not updated, please try again.";

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader("Content-type", "application/json")
        ->withStatus(422);
});
$app->put("/updatePass", function (Request $request, Response $response){

    $db = new DbOperations();

    if(!haveEmptyParameters(array('currentPass', 'newPass', 'email'), $request, $response)){

        $data = $request->getParsedBody();
        $currentPass = $data['currentPass'];
        $newPass     = $data['newPass'];
        $email       = $data['email'];

        $result = $db->updatePassword($currentPass, $newPass, $email);

        if($result == PASSWORD_CHANGED){
            $message = array();
            $message['error'] = false;
            $message['msg'] = "Password successfully changed";

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(200);

        }
        else if($result == PASSWORD_NOT_MATCH){
            $message = array();
            $message['error'] = true;
            $message['msg'] = "You have given wrong password";

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(200);
        }
        else if($result == PASSWORD_NOT_CHANGED){
            $message = array();
            $message['error'] = true;
            $message['msg'] = "Some error occurred";

            $response->write(json_encode($message));
            return $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader("Content-type", "application/json")
        ->withStatus(422);
});
$app->delete("/deleteUser/{id}", function (Request $request, Response $response, array $args){

    $id = $args['id'];
    $message = array();

    $db = new DbOperations();
    if($db->deleteUser($id)){
        $message['error'] = false;
        $message['msg'] = "User deleted successfully";
    }else{
        $message['error'] = true;
        $message['msg'] = "User cant not delete.";
    }

    $response->write(json_encode($message));

    return $response
        ->withHeader("Content-type", "application/json")
        ->withStatus(200);
});

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "secure" => false,
    "users" => [
        "saeed" => "123321"
    ]
]));

function haveEmptyParameters($required_params, $request, $response){

    $error = false;
    $error_params = "";
    $request_params = $request->getParsedBody();

    foreach ($required_params as $param) {

        if(!isset($request_params[$param]) or strlen($request_params[$param]) <=0){
            $error = true;
            $error_params .= $param . ', ';
        }
    }

    if($error){
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = "Required params " . substr($error_params, 0, -2) . " are missing";

        $response->write(json_encode($error_detail));
    }

    return $error;
}

$app->run();
