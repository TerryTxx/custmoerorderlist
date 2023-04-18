<?php
require 'db.php';
$collection = $database->selectCollection('order');

if (isset($_GET["action"])) {
    $action = $_GET["action"];

    if ($action === "read") {
        $keyword = isset($_GET['keyword'])?$_GET['keyword']:'';
        $regex = new MongoDB\BSON\Regex($keyword, 'i');
        $filter = [ 'userId' => $_GET['userId'] ];
        if ($keyword){
            $filter['_id'] = $keyword;
        }
        $options = [
            'sort' => ['_id' => -1],
        ];
        $orders = array();
        $cursor = $collection->find($filter, $options);
        foreach ($cursor as $document) {
            $document["_id"] = (string) $document["_id"];
            $document["totalItem"] = count($document['goodsArr']);
            $document["totalPrice"] = 0;
            foreach ($document['goodsArr'] as $item) {
                $document["totalPrice"] += $item['price'];
            }
            $orders[] = $document;
        }
        echo json_encode($orders);
    }

    if ($action === "readOne") {
        $customer = $collection->findOne(['_id' => new MongoDB\BSON\ObjectID($_GET["id"])]);
        $customer["_id"] = (string) $customer["_id"];
        echo json_encode($customer);
    }

    // Add modify and verify uniformly
    if ($action === "create" || $action === "update" ) {
        if (empty($_POST["goodsArr"])){
            echo json_encode(['code'=>0,'msg'=>'Select at least one item']);
            die;
        }
    }

    if ($action === "create") {
        $insertOneResult = $collection->insertOne([
            "userId" => $_POST["userId"],
            "goodsArr" => $_POST["goodsArr"],
        ]);
        echo json_encode(['code'=>1]);
    }

    if ($action === "update") {
        $updateResult = $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectID($_POST["id"])],
            ['$set' =>
                [
                    "userId" => $_POST["userId"],
                    "goodsArr" => $_POST["goodsArr"],
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