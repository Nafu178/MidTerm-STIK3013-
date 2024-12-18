<?php
// Include the database connection file
include('db.php'); // Ensure this path is correct based on your project structure

try {
    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data and sanitize it
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if email is already registered
        $checkEmailSql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($checkEmailSql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // If email is already registered
        if ($stmt->rowCount() > 0) {
            $error_message = "This email is already registered. Please use a different email.";
        } else {
            // If email is not registered, insert user into the database
            $sql = "INSERT INTO users (name, email, phone, address, password) VALUES (:name, :email, :phone, :address, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':password', $password);

            if ($stmt->execute()) {
                $success_message = "Registration successful!";
                $showAlert = true; // Set to true to show the success alert
            } else {
                $error_message = "Error: " . $stmt->errorInfo()[2];
                $showAlert = true; // Set to true to show the error alert
            }
        }
    }
} catch (PDOException $e) {
    $error_message = "Connection failed: " . $e->getMessage();
    $showAlert = true; // Set to true to show the error alert
}

// Close the connection (if needed, depending on your db.php handling)
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="universal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script>
        // Show alert if registration was successful or there was an error
        <?php if (isset($showAlert) && $showAlert === true) { ?>
            window.onload = function() {
                <?php if (isset($success_message)) { ?>
                    alert("Registration successful!");
                <?php } elseif (isset($error_message)) { ?>
                    alert("<?php echo $error_message; ?>");
                <?php } ?>
            }
        <?php } ?>
    </script>

</head>

<body>
    <div class="wrapper">
        <form action="register.php" method="POST">
            <h1><b>Register</b></h1>

            <?php if (isset($error_message)) { ?>
                <div class="error-message">
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php } ?>

            <?php if (isset($success_message)) { ?>
                <div class="success-message">
                    <p><?php echo $success_message; ?></p>
                </div>
            <?php } ?>

            <!-- Name input -->
            <div class="input-box">
                <input type="text" id="name" placeholder="Name" name="name" pattern="[A-Za-z\s]+" required>
                <i class="fa fa-user"></i>
            </div>

            <!-- Email input -->
            <div class="input-box">
                <input type="email" id="email" placeholder="Email" name="email" required>
                <i class="fa fa-envelope"></i>
            </div>

            <!-- Phone input -->
            <div class="input-box">
                <input type="text" id="phone" placeholder="Phone" name="phone" required>
                <i class="fa fa-phone"></i>
            </div>

            <!-- Address input -->
            <div class="input-box">
                <input type="text" id="address" placeholder="Address" name="address" required>
                <i class="fa fa-address-book"></i>
            </div>

            <!-- Password input -->
            <div class="input-box">
                <input type="password" id="password" placeholder="Password" name="password" required>
                <i class="fa fa-lock"></i>
            </div>

            <button type="submit" class="btn">Register</button>

            <div class="register-link">
                <p><a href="login.php">Login Page</a></p>
            </div>
        </form>
    </div>


<!--
     Search Form 
    <div class="w3-container w3-padding">
        <form action="mainpage.php" method="POST">
            <input class="w3-input w3-border w3-round" type="text" name="search_query" placeholder="Search by ID, Name, or Description" value="<?php echo $searchQuery; ?>">
            <button type="submit" name="search" class="w3-button w3-teal w3-round">Search</button>
        </form>
    </div>
            -->
<!--
    Search Results
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
-->

</body>
</html>
