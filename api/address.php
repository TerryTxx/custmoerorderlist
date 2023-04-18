<?php
require 'db.php';
$collection = $database->selectCollection('address');

if (isset($_GET["action"])) {
    $action = $_GET["action"];

    if ($action === "read") {
        $keyword = isset($_GET['keyword'])?$_GET['keyword']:'';
        $regex = new MongoDB\BSON\Regex($keyword, 'i');
        $filter = [
            'userId' => $_GET['userId'],
            '$or' => [
                ['homeAddress' => ['$regex' => $regex]],
                ['freightAddress' => ['$regex' => $regex]],
                ['town' => ['$regex' => $regex]],
                ['city' => ['$regex' => $regex]],
                ['eircode' => ['$regex' => $regex]],
            ]
        ];
        $options = [
            'sort' => ['_id' => -1],
        ];
        $address = array();
        $cursor = $collection->find($filter, $options);
        foreach ($cursor as $document) {
            $document["_id"] = (string) $document["_id"];
            $address[] = $document;
        }
        echo json_encode($address);
    }

    if ($action === "readOne") {
        $customer = $collection->findOne(['_id' => new MongoDB\BSON\ObjectID($_GET["id"])]);
        $customer["_id"] = (string) $customer["_id"];
        echo json_encode($customer);
    }

    // Add modify and verify uniformly
    if ($action === "create" || $action === "update" ) {
        if (empty($_POST["homeAddress"]) || empty($_POST["town"])|| empty($_POST["city"])){
            echo json_encode(['code'=>0,'msg'=>'Please enter a required field']);
            die;
        }
    }

    if ($action === "create") {
        $insertOneResult = $collection->insertOne([
            "userId" => $_POST["userId"],
            "homeAddress" => $_POST["homeAddress"],
            "freightAddress" => $_POST["freightAddress"],
            "town" => $_POST["town"],
            "city" => $_POST["city"],
            "eircode" => $_POST["eircode"],
        ]);
        echo json_encode(['code'=>1]);
    }

    if ($action === "update") {
        $updateResult = $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectID($_POST["id"])],
            ['$set' =>
                [
                    "userId" => $_POST["userId"],
                    "homeAddress" => $_POST["homeAddress"],
                    "freightAddress" => $_POST["freightAddress"],
                    "town" => $_POST["town"],
                    "city" => $_POST["city"],
                    "eircode" => $_POST["eircode"],
                ]
            ]
        );
        echo json_encode(['code'=>1]);
    }

    if ($action === "delete") {
        $id = new MongoDB\BSON\ObjectID($_POST["id"]);
        $deleteResult = $collection->deleteOne(['_id' =>$id ]);
    }
}

?>