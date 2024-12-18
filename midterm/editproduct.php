<?php
include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $type = $_POST['type'];

    // Handle optional image upload
    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        $filename = "product-" . date("dmY") . "-" . randomString(5) . ".png";
        $target_dir = "products_list_uploaded/";
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $newImage = $filename;
        } else {
            echo "<script>alert('Failed to upload the new image.');</script>";
        }
    }

    // Update query
    $sqlUpdate = "UPDATE `products` SET 
                  `name` = :name, 
                  `description` = :description, 
                  `price` = :price, 
                  `quantity` = :quantity, 
                  `type` = :type";

    if ($newImage) {
        $sqlUpdate .= ", `picture` = :picture";
    }
    $sqlUpdate .= " WHERE `id` = :id";

    $stmt = $conn->prepare($sqlUpdate);

    $params = [
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':quantity' => $quantity,
        ':type' => $type,
        ':id' => $id
    ];
    if ($newImage) {
        $params[':picture'] = $newImage;
    }

    try {
        $stmt->execute($params);
        echo "<script>alert('Product updated successfully!');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Failed to update product.');</script>";
    }

    echo "<script>window.location.replace('mainpage.php');</script>";
}
?>
