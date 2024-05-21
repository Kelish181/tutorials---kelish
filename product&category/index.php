<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="custom.css">
    <title>Categories CRUD</title>

</head>
<body>
    <div>
          <a href="index.php?action=product" class="div1" style="margin:50px 0 0 20px ;">Products</a>
          <a href="index.php?action=categories" class="div1" style="margin:50px 0 0px 10px;">Categories</a>
    </div>
       <?php if ($_SERVER["REQUEST_METHOD"]=="GET" && isset($_GET['action'])):?>
    <?php if ($_GET['action'] === 'categories'): ?>
        <!-- Show Add Categories button -->
        <div class="d-grid gap-4 col-2">
            <a href="index.php?action=add_form"class="div1" style="text-align:center;">Add Categories</a>
        </div>
    <?php elseif ($_GET['action'] === 'product'): ?>
        <!-- Show Add New Product button -->
        <div class="d-grid gap-5 col-2">
            <a href="index.php?action=add"class="div1" style="text-align:center;">Add Product</a>
        </div>
    <?php endif;?>
<?php endif; ?>
      <?php
      $servername = 'localhost';
      $username = 'root';
      $password = '';
      $dbname = "product&category";
      $con = mysqli_connect($servername,$username,$password,$dbname) or die("Connection failed: " . mysqli_connect_error());

      // Fetching categories
      $categoriesData = mysqli_query($con,"SELECT * FROM  categories ");
      // Fetching products
      $productsData = mysqli_query($con, "SELECT * FROM product");

      if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            if ($action === 'add') {
                ?>
                <form action="index.php?action=add" method="post">
                    <h2>Add product</h2>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required style="width: 400px;height: 40px; margin-left :30px;"><br><br>
    
                    <label for="parent_id">Category:</label>
                    <select id="parent_id" name="parent_id" style="width: 400px; height: 40px; margin-left :10px;">
                    <option value="">select categories</option>
                    <?php
                    $categoryData = categoryChild(0, 0, $con);
                    $selectedCategoryId = $product['c_id'];
                    $options = '';
                
                    // Loop through category data to generate options with full hierarchy
                    foreach ($categoryData as $category) {
                        $categoryId = $category['id'];
                        $categoryName = $category['name'];
                        $categoryParentId = $category['parent_id'];
                        $indentation = '';
                        $parentCategory = '';
                        while ($categoryParentId != 0) {
                            $parentCategoryData = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, parent_id FROM categories WHERE id = $categoryParentId"));
                            $parentCategory = " ({$parentCategoryData['name']})" . $parentCategory;
                            $categoryParentId = $parentCategoryData['parent_id'];
                            $indentation .= "-";
                        }
                        $selected = ($categoryId == $selectedCategoryId) ? 'selected' : '';
                        $options .= "<option value='$categoryId' $selected>{$indentation} {$categoryName}</option>";
                    }
                    echo $options;
                    ?>
                    
                    </select><br><br>
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="0" required style="width: 400px; height:40px; margin-left :15px;"><br><br>
                    <label for="description">Description:</label>
                    <input type="text" id="description" name="description" required style="width: 400px; height: 40px; margin-left :-2px;"><br><br>
                    <button type="submit" name="add_product">Add Product</button>
                </form>
                <?php
            } elseif ($action === 'update') {
                // Handle update functionality here
            } elseif ($action === 'delete') {
                // Handle delete functionality here
            }
        }
    }
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['action']) && $_GET['action'] === 'Delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Construct the delete query
        $deleteQuery = "DELETE FROM product WHERE id='$id'";
        
        // Execute the delete query
        if (mysqli_query($con, $deleteQuery)) {
            header("location:index.php?action=product");
            exit;
        } else {
            echo "Error: " . mysqli_error($con);
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $productId = $_POST['id'];
    $productName = $_POST['name'];
    $productParent = $_POST['p_categories'];
    $productDescription = $_POST['description'];
    $productQuantity = isset($_POST['quantity']) ? $_POST['quantity'] : '';

    // Escape single quotes in input data
    $productName = mysqli_real_escape_string($con,$productName);
    $productParent = mysqli_real_escape_string($con, $productParent);
    $productDescription = mysqli_real_escape_string($con, $productDescription);

    // Construct the update query
    $updateQuery = "UPDATE product SET name='$productName', c_id='$productParent', quantity='$productQuantity', description='$productDescription' WHERE id='$productId'";

    // Execute the update query
    if (mysqli_query($con, $updateQuery)) {
        header("location:index.php?action=product");
        exit;
    } else {
        echo "Error updating product: " . mysqli_error($con);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])){
    // Collect form data
    $productName = $_POST['name'];
    $categoryId = $_POST['parent_id'];
    $productQuantity = $_POST['quantity'];
    $productDescription = $_POST['description'];

    // Prepare the insert query using a prepared statement
    $insertQuery = "INSERT INTO product (name, c_id, quantity, description) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insertQuery);

    if ($stmt) {
        // Bind parameters to the prepared statement
        mysqli_stmt_bind_param($stmt, 'siss', $productName, $categoryId, $productQuantity, $productDescription);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to the product list page
            header("Location: index.php?action=product");
            exit;
        } else {
            echo "Error adding product: " . mysqli_stmt_error($stmt);
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        echo " " . mysqli_error($con);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] === 'product') {
    $productData = mysqli_query($con, "SELECT p.id, p.name, p.c_id, c.name AS c_name, p.quantity, p.description, c.parent_id
    FROM product p 
    LEFT JOIN categories c ON p.c_id = c.id");


    if ($productData && mysqli_num_rows($productData) > 0) {
        ?>
        <div class="container">  
            <h3><b>Product list</b></h3>
            <div class="col-10 m-auto mt-3">
                <table class="table table-responsive">
                    <thead>
                        <tr>         
                            <th>Id</th>
                            <th>Product name</th>
                            <th>Category name</th> 
                            <th>Quantity</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                        while($row = mysqli_fetch_assoc($productData)) {
                            ?>
                            <tr>
                              <td><?php echo $row['id']; ?></td>
                              <td><?php echo $row['name']; ?></td>
                              <td>
                                  <?php
                                  $categoryId = $row['c_id'];
                                  $categoryName = $row['c_name'];
                                  $categoryParentId = $row['parent_id'];
                                  $isParentCategory = ($categoryParentId === null) ? true : false;                               
                                  if (!$isParentCategory) {              
                                      echo $categoryName;
                                  }
                                  ?>
                              </td>
                              <td><?php echo $row['quantity']; ?></td>
                              <td><?php echo $row['description']; ?></td>
                              <td>
                                  <form action="index.php" method="post" style="display:inline-block;margin-left:-50px;">
                                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                      <button type="submit" name="update_product" class="btn btn-primary">Update</button>
                                  </form>
                                  <a href="?action=Delete&id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                              </td>
                            </tr>
                            <?php 
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    } else {
        echo "<div class='container'><p>No products found.</p></div>";
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $productId = $_POST['id'];
    header("Location: index.php?action=update_product&id=$productId");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] ==='update_product' && isset($_GET['id'])) {
    $productId = $_GET['id'];
    $productData = mysqli_query($con, "SELECT * FROM product WHERE id = $productId");
    $product = mysqli_fetch_assoc($productData);
    ?>
    <form action="index.php" method="post">
    <div class="containerr">
        <h3 style="margin: 80px 50px 30px 150px;"><b>Update Product</b></h3>
        <div class="col-10 m-auto mt-3">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name"style="margin-left: 55px; width: 400px; height: 40px;" value="<?php echo $product['name']; ?>"><br><br>
            <label for="p_categories">Categories:</label>
            <select id="p_categories" name="p_categories" style="margin-left: 20px; height: 40px; width: 400px;">  
            <option value="">Select category</option>
            <?php
            $categoryData = categoryChild(0, 0,$con);
            $selectedCategoryId = $product['c_id'];
            $options = ''; 
            foreach($categoryData as $category) {
                $categoryId = $category['id'];
                $categoryName = $category['name'];
                $categoryParentId = $category['parent_id'];
                $indentation = '';
                $parentCategory = '';
              
              while ($categoryParentId != 0) {
                $parentCategoryData = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, parent_id FROM categories WHERE id = $categoryParentId"));
                $parentCategory = " ({$parentCategoryData['name']})" . $parentCategory;
                $categoryParentId = $parentCategoryData['parent_id'];
                $indentation .= "-";
            }
            $selected = ($categoryId == $selectedCategoryId) ? 'selected' : '';
            $options .= "<option value='$categoryId' $selected>{$indentation} {$categoryName}</option>";
        }

            echo $options;
           ?>

</select><br><br>
            <label for="update-quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" style="margin-left: 38px; width: 400px; height: 40px;" value="<?php echo $product['quantity']; ?>"><br><br>
            <label for="update-description">Description:</label>
            <input type="text" id="description" name="description" style="margin-left: 20px; width: 400px; height: 40px;" value="<?php echo $product['description']; ?>" required><br><br>
            <button type="submit" name="update" style="margin-left: 20px; width: 200px; height: 40px;">Update</button>
        </div>
    </div>
</form>
    </div>
  </div>
 <?php } ?>

 <!-- <!....categories form....> -->
 <?php
 
 if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_submit'])) {
    
        $id = $_POST['id'];
        $categoryName = $_POST['name'];
        $categoryParent = $_POST['parent_id'];
        $categoryDescription = $_POST['description'];

        if ($categoryName != $categoryParent) {   
          $updateQuery = "UPDATE categories SET name ='$categoryName', parent_id ='$categoryParent', description = '$categoryDescription' WHERE id = '$id'";

          
            if (mysqli_query($con, $updateQuery)) { 
                header("Location:index.php?action=categories");
                exit;
            } else {
                    echo "Error: " . mysqli_error($con);
                    header("Location:index.php?action=categories");
                    exit;
            }
        } else {
            echo "";
            header("Location:index.php?action=categories");
            exit;
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['action']) && $_GET['action'] ==='delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $deleteQuery ="DELETE FROM  categories WHERE id='$id'";
        if (mysqli_query($con, $deleteQuery)) {
            echo "";
            header("location:index.php?action=categories");
            exit;
        } else {
            echo "" .mysqli_error($con)."');</script>";
            
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'add_form') {
        ?>
    <form action="index.php" method="post">
    <h2>Add categories</h2>
    <label for="name" style="margin-left:35px;">Name:</label>
    <input type="text" id="name" name="name" required style="width:400px; height:40px; margin-left: 102px;"><br><br>
    <label for="parent_id" style="margin-left:30px;">Parent Category:</label>
    <select id="parent_id" name="parent_id" style="margin-left: 35px; height: 40px; width: 400px;">
        <option value="0">Select Parent Category</option>
        <?php
                    $categoryData = categoryChild(0, 0, $con);
                    $selectedCategoryId = $product['c_id'];
                    $options = '';
                
                    // Loop through category data to generate options with full hierarchy
                    foreach ($categoryData as $category) {
                        $categoryId = $category['id'];
                        $categoryName = $category['name'];
                        $categoryParentId = $category['parent_id'];
                        $indentation = '';
                        $parentCategory = '';
                        while ($categoryParentId != 0){
                            $parentCategoryData = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, parent_id FROM categories WHERE id = $categoryParentId"));
                            $parentCategory = " ({$parentCategoryData['name']})" . $parentCategory;
                            $categoryParentId = $parentCategoryData['parent_id'];
                            $indentation .= "-";
                        }
                        $selected = ($categoryId == $selectedCategoryId) ? 'selected' : '';
                        $options .= "<option value='$categoryId' $selected>{$indentation} {$categoryName}</option>";
                    }
                    echo $options;
                    ?>
    </select><br><br>
    <label for="description" style="margin-left:30px;">Description:</label>
    <input type="text" id="description" name="description" required style="width: 400px; margin-left: 70px; height: 40px;"><br><br><br><br>
    <!-- <label for="short_order">Short Order:</label>
    <input type="number" id="short_order" name="short_order" required><br><br> -->
    <button type="submit" name="submit">Add categories</button>
    </form>
        <?php
    } elseif ($action === 'update') {
        // Handle update functionality here
    } elseif ($action === 'delete') {
        // Redirect to the add form to handle delete functionality
        header("Location: index.php?action=add_form");
        exit;
    }
}
?>
<?php
function categoryChild($parent_id = 0, $level = 0, $con){
    global $cid_edit;
    $query = "SELECT c.*, pc.name AS parent_name 
              FROM categories c 
              LEFT JOIN categories pc ON c.parent_id = pc.id    
              WHERE c.parent_id = $parent_id";

    $result = mysqli_query($con, $query);
    $categoryData = array();
    if (mysqli_num_rows($result) > 0) {
        $i = 1;
        while ($cat_row = mysqli_fetch_assoc($result)){
            if ($cid_edit != $cat_row["id"]) {
                $cat_row['level'] = $level;
                $categoryData[$cat_row['parent_id'] . "_" . $i] = $cat_row;
                $categoryData = array_merge($categoryData, categoryChild($cat_row['id'], $level + 1 ,$con));
                $i++;
            }
        }
    }
    return $categoryData;
}

// Check for form submission
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] === 'categories') {
?>
<div class="container">
    <h3><b>Category List</b></h3>
    <div class="col-10 m-auto mt-3">
        <table class="table table-responsive">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Short Order</th>
                    <th>Level</th>
                    <th>Parent Category</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $categoryData = categoryChild(0,0,$con);
            foreach ($categoryData as $key => $cat_row) {
                $level = isset($cat_row["level"]) ? $cat_row["level"] : 1;
                $parentCategory = ($cat_row['parent_name'] !== '') ? $cat_row['parent_name'] : '-';
                $symbol = str_repeat(" -", $level); 
            ?>      
            <tr>
                <td><?php echo $cat_row['id']; ?></td>
                <td><?php echo $symbol . $cat_row['name']; ?></td>
                <td>
                    <?php echo $cat_row['short_order'];?>
                    <?php
                    $div = explode("_",$key);
                    $integer = intval(end($div));
                    $parentKey = implode("_", array_slice($div,0, -1));
                    $previousKey = $parentKey . "_" .($integer - 1);
                    $nextKey = $parentKey . "_" . ($integer + 1);

                    if (isset($categoryData[$previousKey])){
                        $previousCat = $categoryData[$previousKey];
                        echo "<a href='?s_order=move&firstCat={$cat_row['id']}&secondCat={$previousCat['id']}&direction=up' class='btn btn-primary' style='width: 35px;'>&#8593;</a>";
                    }
                    if (isset($categoryData[$nextKey])) {
                        $nextCat = $categoryData[$nextKey];
                        echo "<a href='?s_order=move&firstCat={$cat_row['id']}&secondCat={$nextCat['id']}&direction=down' class='btn btn-danger' style='width: 35px;'>&#8595;</a>";
                    }
                    ?>
                </td>
                <td><?php echo $level; ?></td>
                <td><?php echo $parentCategory; ?></td>
                <td><?php echo $cat_row['description']; ?></td>
                <td>
                    <form action='index.php?action=update' method='post' style='display:inline-block;'>
                        <input type='hidden' name='id' value='<?php echo $cat_row['id']; ?>'>
                        <button type='submit' name='edit_submit' class='btn btn-primary'>Update</button>
                    </form>
                    <a href='?action=delete&id=<?php echo $cat_row['id']; ?>' class='btn btn-danger'>Delete</a>
                </td>
            </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}
?>
<?php
// Fetch category data function 
$categoryData = categoryChild(0, 0, $con);
if (isset($_GET['s_order']) && $_GET['s_order'] === "move"){
    if (isset($_GET['firstCat'],$_GET['secondCat'])) {
        // Validate and sanitize inputs
        $firstCat = intval($_GET['firstCat']);
        $secondCat = intval($_GET['secondCat']);

        // Fetch the categories data from the database
        $query ="
            SELECT id, short_order,name, description, parent_id
            FROM `product&category`.`categories`
            WHERE id IN ($firstCat, $secondCat)";
        
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) == 2) {
            $firstCategory = '';
            $secondCategory = '';
            // Assign fetched data to the appropriate variables
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['id'] == $firstCat) {
                    $firstCategory = $row;
                } else {
                    $secondCategory = $row;
                }
            }
            if ($firstCategory && $secondCategory) {
                $firstShortOrder = $firstCategory['short_order'];
                $firstName = $firstCategory['name'];
                $firstDescription = $firstCategory['description'];
                $secondShortOrder = $secondCategory['short_order'];
                $secondName = $secondCategory['name'];
                $secondDescription = $secondCategory['description'];

                $updates = [];

                // Swap main categories attributes
                $updates[] ="
                    UPDATE `product&category`.`categories`
                    SET short_order = CASE WHEN id = $firstCat THEN $secondShortOrder WHEN id = $secondCat THEN $firstShortOrder END,
                        name = CASE WHEN id = $firstCat THEN '$secondName' WHEN id = $secondCat THEN '$firstName' END,
                        description = CASE WHEN id = $firstCat THEN '$secondDescription' WHEN id = $secondCat THEN '$firstDescription' END
                    WHERE id IN ($firstCat, $secondCat)";

                // Swap child categories
                $updates[] = "
                    UPDATE `product&category`.`categories`
                    SET parent_id = CASE 
                        WHEN parent_id = $firstCat THEN $secondCat
                        WHEN parent_id = $secondCat THEN $firstCat 
                    END
                    WHERE parent_id IN ($firstCat, $secondCat)";
         
                // Execute the updates
                foreach ($updates as $updateQuery) {
                    if (!mysqli_query($con, $updateQuery)) {
                        echo " " . mysqli_error($con);
                    }
                }

                // Redirect to categories page
                header("Location: index.php?action=categories");
                exit;
            }
        } else {
            echo "Categories not found.";
        }
    }
}
?>
<?php
if (isset($_POST['edit_submit'])) {
    $editedCategoryId = $_POST['id'];
    $editedCategoryData = mysqli_query($con, "SELECT * FROM categories WHERE id = $editedCategoryId");
    $editedCategory = mysqli_fetch_assoc($editedCategoryData);
?>

<form method="POST" action="index.php?action=update">
    <div id="Updateformcontainer">
        <h2 style="margin: 80px 50px 30px 10px;"><b>Update categories</b></h2>
        <input type="hidden" name="id" value="<?php echo $editedCategory['id'];?>">
        <label for="name-categories" style="margin-left:38px;">Name:</label>
        <input type="text" id="name-categories" name="name" value="<?php echo $editedCategory['name'];?>" required style="width: 400px; height: 40px; margin-left:60px;"><br><br>
        <label for="parent_id" style="margin-left: 27px;">Category:</label>
        <select id="parent_id" name="parent_id" style="margin-left:48px; height: 40px; width:400px;">
        <option value="0">Select Parent Category</option>
          <?php
      $categoryData = categoryChild(0, 0, $con);
      $selectedCategoryId = $editedCategory['id'];
      
      foreach ($categoryData as $cat_row) {
          $symbol = str_repeat(" - ", $cat_row["level"]);
          $isDescendant = false;

    // Check if the category is not the selected category
    if ($cat_row['id'] != $selectedCategoryId) {
        $parent_id = $cat_row['parent_id'];
        while ($parent_id !== null) {
            if ($parent_id == $selectedCategoryId){
                $isDescendant = true;
                break;
            }
            // Get the parent ID of the current category
            $parent_query = mysqli_query($con,"SELECT parent_id  FROM categories WHERE id = $parent_id");
            $parent_data = mysqli_fetch_assoc($parent_query);
            $parent_id = $parent_data['parent_id'];
        }

        // If the category is not a descendant of the selected category, include it in the dropdown options
        if (!$isDescendant) {
            $selected = ($cat_row['id'] == $editedCategory['parent_id']) ? "selected" : "";
            echo "<option value='{$cat_row['id']}' $selected>{$symbol}{$cat_row['name']}</option>";
        }
    }
}
?>
</select><br><br>
<label for="description" style="margin-left: 27px;">Description:</label>
<input type="text" id="description" name="description" value="<?php echo $editedCategory['description']; ?>" required style="width: 400px; margin-left: 35px; height: 40px;"><br><br>
<!-- <label for="short_order" style="margin-left: 27px;">Short Order:</label>
<input type="number" id="short_order" name="short_order" required style="width:400px; margin-left: 35px;height: 40px;"><br><br> -->

<button type="submit" name="update_submit">Update</button>
</div>
</form>
<?php } ?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit'])) {
        if (isset($_POST['parent_id'])) {
            $categoryName = $_POST['name'];
            $categoryParent = $_POST['parent_id'];
            $categoryDescription = $_POST['description'];
            //$shortOrder = $_POST['short_order']; 
            $insertQuery = "INSERT INTO categories (name, parent_id, description) 
                            VALUES ('$categoryName', '$categoryParent', '$categoryDescription')";
            
            if (mysqli_query($con, $insertQuery)){
                echo "";
                header("location:index.php?action=categories");
                exit;
            } else {
                echo "".mysqli_error($con);
            }
        } 
    }
}

?>  


