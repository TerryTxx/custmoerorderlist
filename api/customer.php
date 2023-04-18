<?php
require 'db.php';
$collection = $database->selectCollection('customer');

if (isset($_GET["action"])) {
    $action = $_GET["action"];

    if ($action === "read") {
        $keyword = isset($_GET['keyword'])?$_GET['keyword']:'';
        $regex = new MongoDB\BSON\Regex($keyword, 'i');
        $filter = [
            '$or' => [
                ['title' => ['$regex' => $regex]],
                ['firstName' => ['$regex' => $regex]],
                ['lastName' => ['$regex' => $regex]],
            ]
        ];
        $options = [
            'sort' => ['_id' => -1],
        ];
        $contacts = array();
        $cursor = $collection->find($filter, $options);
        foreach ($cursor as $document) {
            $document["_id"] = (string) $document["_id"];
            $contacts[] = $document;
        }
        echo json_encode($contacts);
    }

    if ($action === "readOne") {
        $customer = $collection->findOne(['_id' => new MongoDB\BSON\ObjectID($_GET["id"])]);
        $customer["_id"] = (string) $customer["_id"];
        echo json_encode($customer);
    }

    // Add modify and verify uniformly
    if ($action === "create" || $action === "update" ) {
        if (empty($_POST["firstName"]) || empty($_POST["lastName"]) || empty($_POST["phone"]) || empty($_POST["email"])){
            echo json_encode(['code'=>0,'msg'=>'Please enter a required field']);
            die;
        }
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
            echo json_encode(['code'=>0,'msg'=>'Invalid email']);
            die;
        }
    }

    if ($action === "create") {
        $insertOneResult = $collection->insertOne([
            "title" => $_POST["title"],
            "firstName" => $_POST["firstName"],
            "lastName" => $_POST["lastName"],
            "phone" => $_POST["phone"],
            "email" => $_POST["email"]
        ]);
        echo json_encode(['code'=>1]);
    }

    if ($action === "update") {
        $updateResult = $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectID($_POST["id"])],
            ['$set' =>
                [
                    "title" => $_POST["title"],
                    "firstName" => $_POST["firstName"],
                    "lastName" => $_POST["lastName"],
                    "phone" => $_POST["phone"],
                    "email" => $_POST["email"]
                ]
            ]
        );
        echo json_encode(['code'=>1]);
    }

    if ($action === "delete") {
//        $id = new MongoId($_POST["id"]);
        $id = new MongoDB\BSON\ObjectID($_POST["id"]);
        $deleteResult = $collection->deleteOne(['_id' =>$id ]);
    }
}

?>