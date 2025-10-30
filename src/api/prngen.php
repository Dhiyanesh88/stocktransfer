<?php
// --------------------
// DB Connection
// --------------------
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "stickers_db";
$port       = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

$message = "";

// --------------------
// Handle Form Submission
// --------------------
if (isset($_POST['add_product'])) {
    $name    = $conn->real_escape_string($_POST['product_name']);
    $cat     = $conn->real_escape_string($_POST['category']);
    $price   = $conn->real_escape_string($_POST['price']);
    $barcode = $conn->real_escape_string($_POST['barcode']);

    $insertSql = "INSERT INTO products (product_name, category, price, barcode) VALUES ('$name', '$cat', '$price', '$barcode')";
    if ($conn->query($insertSql)) {
        $message = "✅ Product added successfully!";
    } else {
        $message = "❌ Error: " . $conn->error;
    }
}
$template = [];
$templateQuery = "SELECT line_name, line_content FROM prn_template_lines";
$templateResult = $conn->query($templateQuery);
if ($templateResult && $templateResult->num_rows > 0) {
    while ($trow = $templateResult->fetch_assoc()) {
        $template[$trow['line_name']] = $trow['line_content'];
    }
}


// --------------------
// Fetch Products
// --------------------
$sql = "SELECT product_name, category, price, barcode FROM products ORDER BY id ASC";
$result = $conn->query($sql);


if ($result && $result->num_rows > 0) {
    $prnContent = "";

    while ($row = $result->fetch_assoc()) {
        $name    = $row["product_name"];
        $cat     = $row["category"];
        $price   = $row["price"];
        $barcode = $row["barcode"];

        // Start PRN for one label
        $prnContent .= $template['start'] . "\n";

        $y = 30;   // start position
        $gap = 40; // vertical gap

        // Product Name
        $prnContent .= $template['field_origin'] . $y . $template['font'] . "Product: $name" . $template['field_stop'] . "\n";
        $y += $gap;

        // Category
        $prnContent .= $template['field_origin'] . $y . $template['font'] . "Category: $cat" . $template['field_stop'] . "\n";
        $y += $gap;

        // Price
        $prnContent .= $template['field_origin'] . $y . $template['font'] . "Price: Rs.$price" . $template['field_stop'] . "\n";
        $y += $gap;

        // Barcode
        $prnContent .= $template['field_origin'] . $y . $template['barcode_setup'] . "\n";
        $prnContent .= $template['barcode_type'] . "\n";
        $prnContent .= "^FD$barcode" . $template['field_stop'] . "\n";

        // End of one label
        $prnContent .= $template['end'] . "\n\n";
    }


    // --------------------
    // Save PRN File
    // --------------------
    $filePath = __DIR__ . "/labels.prn";
    file_put_contents($filePath, $prnContent);
}

$conn->close();
?>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        form { max-width: 400px; margin: auto; }
        label { display: block; margin-top: 15px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #4338ca; }
        .message { text-align: center; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>

<h2>Add New Product</h2>

<?php if($message): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="post" action="">
    <label for="product_name">Product Name:</label>
    <input type="text" name="product_name" id="product_name" required>

    <label for="category">Category:</label>
    <input type="text" name="category" id="category" required>

    <label for="price">Price (Rs.):</label>
<input type="number" name="price" id="price" required step="1" min="0" value="0">



    <label for="barcode">Barcode:</label>
    <input type="text" name="barcode" id="barcode" required>

    <button type="submit" name="add_product">Add Product</button>
</form>

<?php if(file_exists(__DIR__ . "/labels.prn")): ?>
    <p class="message">✅ PRN file updated: <a href="labels.prn" target="_blank">labels.prn</a></p>
<?php endif; ?>

</body>
</html> -->
