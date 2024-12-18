<?php
// Connect to database
include("db.php");

// Product insertion logic
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $type = $_POST['type'];

    // Generate a unique filename for the uploaded image
    $filename = "product-" . date("dmY") . "-" . randomString(5) . ".png";
    $target_dir = "products_list_uploaded/";
    $target_file = $target_dir . $filename;

    // Insert product into the database
    $sqlloadproduct = "INSERT INTO `products`(`name`, `description`, `price`, `quantity`, `picture`, `type`) 
                       VALUES ('$name', '$description', '$price', '$quantity', '$filename', '$type')";

    try {
        $conn->query($sqlloadproduct);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            echo "<script>alert('Product added successfully.')</script>";
        } else {
            echo "<script>alert('Failed to upload image.')</script>";
        }
        echo "<script>window.location.replace('mainpage.php')</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Failed to add product!')</script>";
    }
}

// Search functionality
$searchResults = [];
$searchQuery = "";
if (isset($_POST['search'])) {
    $searchQuery = trim($_POST['search_query']);
    if ($searchQuery !== '') {
        // Modified SQL to also search by 'type'
        $sqlSearch = "SELECT * FROM `products` WHERE `id` LIKE '%$searchQuery%' OR `name` LIKE '%$searchQuery%' OR `description` LIKE '%$searchQuery%' OR `type` LIKE '%$searchQuery%'";
        $searchResults = $conn->query($sqlSearch)->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Pagination logic
$limit = 10;  // Max products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Get current page from URL (default is page 1)
$offset = ($page - 1) * $limit;  // Calculate the offset

// Fetch total number of products
$sqlCount = "SELECT COUNT(*) FROM `products`";
$totalProducts = $conn->query($sqlCount)->fetchColumn();
$totalPages = ceil($totalProducts / $limit);  // Calculate total pages

// Fetch products with pagination
$sqlloadproducts = "SELECT * FROM `products` LIMIT $limit OFFSET $offset";
$results = $conn->query($sqlloadproducts)->fetchAll(PDO::FETCH_ASSOC);

// Function to generate random string for file names
function randomString($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}

// Check if the form is submitted to update a product
if (isset($_POST['id'])) {
    $editId = $_POST['id'];
    $editName = $_POST['name'];
    $editDescription = $_POST['description'];
    $editPrice = $_POST['price'];
    $editQuantity = $_POST['quantity'];
    $editType = $_POST['type'];
    $editImage = $_FILES['image'];

    // Prepare the SQL update query
    $sqlUpdate = "UPDATE `products` 
                  SET `name` = :name, `description` = :description, `price` = :price, 
                      `quantity` = :quantity, `type` = :type 
                  WHERE `id` = :id";

    // Prepare and execute the query
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bindParam(':name', $editName);
    $stmt->bindParam(':description', $editDescription);
    $stmt->bindParam(':price', $editPrice);
    $stmt->bindParam(':quantity', $editQuantity);
    $stmt->bindParam(':type', $editType);
    $stmt->bindParam(':id', $editId);

    // If the form includes an image (for replacement), handle the image upload
    if ($editImage['error'] === 0) {
        // Generate a unique filename for the uploaded image
        $filename = "product-" . date("dmY") . "-" . randomString(5) . ".png";
        $target_dir = "products_list_uploaded/";
        $target_file = $target_dir . $filename;

        // Move the uploaded file
        if (move_uploaded_file($editImage["tmp_name"], $target_file)) {
            // Update the image filename in the database
            $sqlUpdateImage = "UPDATE `products` SET `picture` = :filename WHERE `id` = :id";
            $stmtImage = $conn->prepare($sqlUpdateImage);
            $stmtImage->bindParam(':filename', $filename);
            $stmtImage->bindParam(':id', $editId);
            $stmtImage->execute();
        } else {
            echo "<script>alert('Failed to upload image.')</script>";
        }
    }

    // Execute the main product update query
    if ($stmt->execute()) {
        echo "<script>alert('Product updated successfully!');</script>";
        echo "<script>window.location.replace('mainpage.php');</script>";
    } else {
        echo "<script>alert('Failed to update product.');</script>";
    }
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        .product-card {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .product-card img {
            max-width: 100px;
            max-height: 100px;
            margin-right: 20px;
            border-radius: 5px;
        }
        .product-details {
            flex-grow: 1;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 16px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <!-- Navigation Menu -->
    <div class="w3-bar w3-black">
        <a href="mainpage.php" class="w3-bar-item w3-button">Home</a>
        <center><h1>Product Management Community</h1></center>
    </div>

    <!-- Product Form -->
    <div class="w3-container w3-teal w3-padding">
        <h2>Add New Product</h2>
        <form action="mainpage.php" method="POST" enctype="multipart/form-data">
            <input class="w3-input w3-border w3-round" type="text" name="name" required placeholder="Product Name"><br>
            <textarea class="w3-input w3-border w3-round" name="description" required placeholder="Product Description" rows="5"></textarea><br>
            <input class="w3-input w3-border w3-round" type="number" name="price" required placeholder="Product Price"><br>
            <input class="w3-input w3-border w3-round" type="number" name="quantity" required placeholder="Product Quantity"><br>
            <input class="w3-input w3-border w3-round" type="text" name="type" required placeholder="Product Type"><br>
            <label for="productimage">Select a file (png):</label>
            <input type="file" id="productimage" name="image" accept=".png" required><br><br>
            <input class="w3-button w3-cyan w3-round" type="submit" name="submit" value="Insert Product">
        </form>
    </div>

    <!-- Search Form -->
    <div class="w3-container w3-padding">
        <form action="mainpage.php" method="POST">
            <input class="w3-input w3-border w3-round" type="text" name="search_query" placeholder="Search by ID, Name, Description, or Type" value="<?php echo $searchQuery; ?>">
            <button type="submit" name="search" class="w3-button w3-teal w3-round">Search</button>
        </form>
    </div>

    <!-- Search Results -->
    <?php if (isset($_POST['search'])): ?>
    <div class="w3-container w3-padding">
        <h2>Search Results</h2>
        <?php if (trim($searchQuery) === ''): ?>
            <p>Please enter a search term.</p>
        <?php elseif (count($searchResults) === 0): ?>
            <p>No products found for "<?php echo htmlspecialchars($searchQuery); ?>"</p>
        <?php else: ?>
            <?php foreach ($searchResults as $product): ?>
                <div class="product-card">
                    <img src="products_list_uploaded/<?php echo $product['picture']; ?>" 
                         alt="Product Image" 
                         onerror="this.onerror=null;this.src='products_list_uploaded/default-placeholder.png';">
                    <div class="product-details">
                        <h3><?php echo $product['name']; ?></h3>
                        <p><strong>Description:</strong> <?php echo $product['description']; ?></p>
                        <p><strong>Price:</strong> RM<?php echo $product['price']; ?></p>
                        <p><strong>Quantity:</strong> <?php echo $product['quantity']; ?></p>
                        <p><strong>Type:</strong> <?php echo $product['type']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Product List Table (Pagination Added) -->
    <div class="w3-container w3-padding">
        <h2>Product List</h2>
        <table class="w3-table-all w3-responsive">
            <tr>
                <th>No</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Type</th>
                <th>Action</th>
            </tr>
            <?php
            $i = $offset + 1;  // Adjust the index to show correct numbering
            foreach ($results as $product) {
                echo "<tr>
                        <td>$i</td>
                        <td>{$product['id']}</td>
                        <td>{$product['name']}</td>
                        <td>" . substr($product['description'], 0, 50) . "...</td>
                        <td>{$product['price']}</td>
                        <td>{$product['quantity']}</td>
                        <td>{$product['type']}</td>
                        <td>
                            <a href='javascript:void(0);' 
                               class='w3-button w3-green w3-round' 
                               onclick='openEditModal(" . json_encode($product) . ")'>Edit</a>
                            <a href='mainpage.php?submit=delete&productid={$product['id']}' 
                               class='w3-button w3-red w3-round' 
                               onclick=\"return confirm('Delete this product?');\">Delete</a>
                        </td>
                      </tr>";
                $i++;
            }
            
            ?>
        </table>

        <!-- Pagination Navigation -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="mainpage.php?page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="mainpage.php?page=<?php echo $p; ?>" class="<?php echo ($p == $page) ? 'active' : ''; ?>"><?php echo $p; ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="mainpage.php?page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="w3-modal">
        <div class="w3-modal-content w3-card-4">
            <header class="w3-container w3-teal">
                <span onclick="document.getElementById('editModal').style.display='none'" 
                    class="w3-button w3-display-topright">&times;</span>
                <h2>Edit Product</h2>
            </header>
            <div class="w3-container">
                <form id="editForm" action="mainpage.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit-id">
                    <label for="edit-name">Name:</label>
                    <input class="w3-input w3-border w3-round" type="text" name="name" id="edit-name" required>
                    <label for="edit-description">Description:</label>
                    <textarea class="w3-input w3-border w3-round" name="description" id="edit-description" rows="5" required></textarea>
                    <label for="edit-price">Price:</label>
                    <input class="w3-input w3-border w3-round" type="number" name="price" id="edit-price" required>
                    <label for="edit-quantity">Quantity:</label>
                    <input class="w3-input w3-border w3-round" type="number" name="quantity" id="edit-quantity" required>
                    <label for="edit-type">Type:</label>
                    <input class="w3-input w3-border w3-round" type="text" name="type" id="edit-type" required>
                    <label for="edit-image">Replace Image (optional):</label>
                    <input class="w3-input w3-border w3-round" type="file" name="image" id="edit-image" accept=".png">
                    <button type="submit" class="w3-button w3-blue w3-round w3-margin-top">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditModal(product) {
        document.getElementById('edit-id').value = product.id;
        document.getElementById('edit-name').value = product.name;
        document.getElementById('edit-description').value = product.description;
        document.getElementById('edit-price').value = product.price;
        document.getElementById('edit-quantity').value = product.quantity;
        document.getElementById('edit-type').value = product.type;
        document.getElementById('editModal').style.display = 'block';
    }
    </script>

    <script>
    document.getElementById('editForm').onsubmit = function () {
        document.getElementById('editModal').style.display = 'none';  // Close the modal
    };
    </script>


</body>
</html>