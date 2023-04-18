<?php
require 'db.php';
$collection = $database->selectCollection('goods');

if (isset($_GET["action"])) {
    $action = $_GET["action"];

    if ($action === "read") {
        $keyword = isset($_GET['keyword'])?$_GET['keyword']:'';
        $regex = new MongoDB\BSON\Regex($keyword, 'i');
        $filter = [
            '$or' => [
                ['manufacturer' => ['$regex' => $regex]],
                ['model' => ['$regex' => $regex]],
                ['price' => ['$regex' => $regex]],
            ]
        ];
        $options = [
            'sort' => ['_id' => -1],
        ];
        $goods = array();
        $cursor = $collection->find($filter, $options);
        foreach ($cursor as $document) {
            $document["_id"] = (string) $document["_id"];
            $goods[] = $document;
        }
        echo json_encode($goods);
    }

    if ($action === "readOne") {
        $customer = $collection->findOne(['_id' => new MongoDB\BSON\ObjectID($_GET["id"])]);
        $customer["_id"] = (string) $customer["_id"];
        echo json_encode($customer);
    }

    // Add modify and verify uniformly
    if ($action === "create" || $action === "update" ) {
        if (empty($_POST["manufacturer"]) || empty($_POST["model"]) || empty($_POST["price"])){
            echo json_encode(['code'=>0,'msg'=>'Please enter a required field']);
            die;
        }

        if (
            (filter_var($_POST["price"], FILTER_VALIDATE_FLOAT) === false)
            && (filter_var($_POST["price"], FILTER_VALIDATE_INT) === false)
        ) {
            echo json_encode(['code'=>0,'msg'=>'Please enter the correct price']);
            die;
        }
    }

    if ($action === "create") {
        $insertOneResult = $collection->insertOne([
            "manufacturer" => $_POST["manufacturer"],
            "model" => $_POST["model"],
            "price" => $_POST["price"],
        ]);
        echo json_encode(['code'=>1]);
    }

    if ($action === "update") {
        $updateResult = $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectID($_POST["id"])],
            ['$set' =>
                [
                    "manufacturer" => $_POST["manufacturer"],
                    "model" => $_POST["model"],
                    "price" => $_POST["price"],
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