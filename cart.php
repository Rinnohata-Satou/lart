<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_cart = $row['banner_cart'];
}
?>

<?php
$stock_error = isset($_SESSION['stock_error']) ? $_SESSION['stock_error'] : '';
unset($_SESSION['stock_error']);
$quantity_errors = array();
$submitted_quantities = array();
$cart_updated = false;
$product_stock = array();
$statement = $pdo->prepare("SELECT p_id, p_qty FROM tbl_product");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $product_stock[$row['p_id']] = (int)$row['p_qty'];
}

$proceed_to_checkout = isset($_POST['proceed_to_checkout']);
if(isset($_POST['form1']) || $proceed_to_checkout) {
    $cart_keys = isset($_SESSION['cart_p_id']) ? array_keys($_SESSION['cart_p_id']) : array();
    $posted_ids = isset($_POST['product_id']) && is_array($_POST['product_id']) ? array_values($_POST['product_id']) : array();
    $posted_quantities = isset($_POST['quantity']) && is_array($_POST['quantity']) ? array_values($_POST['quantity']) : array();
    $validated_quantities = array();
    $requested_totals = array();
    if (!$cart_keys || count($posted_ids) !== count($cart_keys) || count($posted_quantities) !== count($cart_keys)) {
        $quantity_errors[] = 'Your cart changed. Please refresh the page and try again.';
    } else {
        foreach ($cart_keys as $position => $cart_key) {
            $product_id = $_SESSION['cart_p_id'][$cart_key];
            $raw_quantity = is_scalar($posted_quantities[$position]) ? (string)$posted_quantities[$position] : '';
            $submitted_quantities[$position + 1] = $raw_quantity;
            $quantity = filter_var($raw_quantity, FILTER_VALIDATE_INT);
            if (!is_scalar($posted_ids[$position]) || (string)$posted_ids[$position] !== (string)$product_id || $quantity === false || $quantity < 1 || !isset($product_stock[$product_id])) {
                $quantity_errors[] = 'Please enter a valid quantity for each cart item.';
                continue;
            }
            $validated_quantities[$cart_key] = $quantity;
            if (!isset($requested_totals[$product_id])) $requested_totals[$product_id] = 0;
            $requested_totals[$product_id] += $quantity;
        }
        foreach ($requested_totals as $product_id => $quantity) {
            if ($quantity > $product_stock[$product_id]) {
                $quantity_errors[] = 'Product #'.(int)$product_id.': only '.max(0, $product_stock[$product_id]).' item(s) remain in stock.';
            }
        }
    }
    if (!$quantity_errors) {
        foreach ($validated_quantities as $cart_key => $quantity) $_SESSION['cart_p_qty'][$cart_key] = $quantity;
        if ($proceed_to_checkout) { header('location: checkout.php'); exit; }
        $cart_updated = true;
    }
}
?>

<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $banner_cart; ?>)">
    <div class="overlay"></div>
    <div class="page-banner-inner">
        <h1><?php echo LANG_VALUE_18; ?></h1>
    </div>
</div>

<div class="page">
	<div class="container">
		<div class="row">
			<div class="col-md-12">

                <?php if(!isset($_SESSION['cart_p_id'])): ?>
                    <?php echo '<h2 class="text-center">Cart is Empty!!</h2></br>'; ?>
                    <?php echo '<h4 class="text-center">Add products to the cart in order to view it here.</h4>'; ?>
                <?php else: ?>
                <?php if($stock_error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($stock_error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if($cart_updated): ?>
                    <div class="alert alert-success" role="status">Cart quantities updated successfully.</div>
                <?php endif; ?>
                <?php if(count($quantity_errors) > 0): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Please adjust the quantity:</strong>
                        <ul>
                            <?php foreach($quantity_errors as $quantity_error): ?>
                                <li><?php echo htmlspecialchars($quantity_error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="" method="post">
                    <?php $csrf->echoInputField(); ?>
				<div class="cart">
                    <table class="table table-responsive table-hover table-bordered">
                        <tr>
                            <th><?php echo '#' ?></th>
                            <th><?php echo LANG_VALUE_8; ?></th>
                            <th><?php echo LANG_VALUE_47; ?></th>
                            <th><?php echo LANG_VALUE_157; ?></th>
                            <th><?php echo LANG_VALUE_158; ?></th>
                            <th><?php echo LANG_VALUE_159; ?></th>
                            <th><?php echo LANG_VALUE_55; ?></th>
                            <th class="text-right"><?php echo LANG_VALUE_82; ?></th>
                            <th class="text-center" style="width: 100px;"><?php echo LANG_VALUE_83; ?></th>
                        </tr>
                        <?php
                        $table_total_price = 0;

                        $i=0;
                        foreach($_SESSION['cart_p_id'] as $key => $value) 
                        {
                            $i++;
                            $arr_cart_p_id[$i] = $value;
                        }

                        $i=0;
                        foreach($_SESSION['cart_size_id'] as $key => $value) 
                        {
                            $i++;
                            $arr_cart_size_id[$i] = $value;
                        }

                        $i=0;
                        foreach($_SESSION['cart_size_name'] as $key => $value) 
                        {
                            $i++;
                            $arr_cart_size_name[$i] = $value;
                        }

                        $i=0;
                        foreach($_SESSION['cart_color_id'] as $key => $value) 
                        {
                            $i++;
                            $arr_cart_color_id[$i] = $value;
                        }

                        $i=0;
                        foreach($_SESSION['cart_color_name'] as $key => $value) 
                        {
                            $i++;
                            $arr_cart_color_name[$i] = $value;
                        }

                        $i=0;
                        foreach($_SESSION['cart_p_qty'] as $key => $value) 
                        {
                            $i++;
                            $arr_cart_p_qty[$i] = $value;
                        }

                        $i=0;
                        foreach($_SESSION['cart_p_current_price'] as $key => $value) 
                        {
                            $i++;
                            $arr_cart_p_current_price[$i] = $value;
                        }

                        $i=0;
                        foreach($_SESSION['cart_p_name'] as $key => $value) 
                        {
                            $i++;
                            $arr_cart_p_name[$i] = $value;
                        }

                        $i=0;
                        foreach($_SESSION['cart_p_featured_photo'] as $key => $value) 
                        {
                            $i++;
                            $arr_cart_p_featured_photo[$i] = $value;
                        }
                        ?>
                        <?php for($i=1;$i<=count($arr_cart_p_id);$i++): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td>
                                <img src="assets/uploads/<?php echo $arr_cart_p_featured_photo[$i]; ?>" alt="">
                            </td>
                            <td><?php echo $arr_cart_p_name[$i]; ?></td>
                            <td><?php echo $arr_cart_size_name[$i]; ?></td>
                            <td><?php echo $arr_cart_color_name[$i]; ?></td>
                            <td><?php echo "&#8369;"; ?><?php echo $arr_cart_p_current_price[$i]; ?></td>
                            <td>
                                <input type="hidden" name="product_id[]" value="<?php echo $arr_cart_p_id[$i]; ?>">
                                <input type="hidden" name="product_name[]" value="<?php echo $arr_cart_p_name[$i]; ?>">
                                <input type="number" class="input-text qty text" step="1" min="1" max="<?php echo isset($product_stock[$arr_cart_p_id[$i]]) ? $product_stock[$arr_cart_p_id[$i]] : 0; ?>" name="quantity[]" value="<?php echo isset($submitted_quantities[$i]) ? htmlspecialchars($submitted_quantities[$i], ENT_QUOTES, 'UTF-8') : $arr_cart_p_qty[$i]; ?>" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" required>
                            </td>
                            <td class="text-right">
                                <?php
                                $row_total_price = (float)str_replace(',', '', $arr_cart_p_current_price[$i]) * (float)$arr_cart_p_qty[$i];
                                $table_total_price = $table_total_price + $row_total_price;
                                ?>
                                <?php echo "&#8369;"; ?><?php echo number_format($row_total_price, 2); ?>
                            </td>
                            <td class="text-center">
                                <a onclick="return confirmDelete();" href="cart-item-delete.php?id=<?php echo $arr_cart_p_id[$i]; ?>&size=<?php echo $arr_cart_size_id[$i]; ?>&color=<?php echo $arr_cart_color_id[$i]; ?>" class="trash"><i class="fa fa-trash" style="color:red;"></i></a>
                            </td>
                        </tr>
                        <?php endfor; ?>
                        <tr>
                            <th colspan="7" class="total-text">Total</th>
                            <th class="total-amount"><?php echo "&#8369;"; ?><?php echo number_format($table_total_price, 2); ?></th>
                            <th></th>
                        </tr>
                    </table> 
                </div>

                <div class="cart-buttons">
                    <ul>
                        <li><input type="submit" value="<?php echo LANG_VALUE_20; ?>" class="btn btn-primary" name="form1"></li>
                        <li><a href="index.php" class="btn btn-primary"><?php echo LANG_VALUE_85; ?></a></li>
                        <li><input type="submit" name="proceed_to_checkout" value="<?php echo LANG_VALUE_23; ?>" class="btn btn-primary"></li>
                    </ul>
                </div>
                </form>
                <?php endif; ?>

                

			</div>
		</div>
	</div>
</div>


<?php require_once('footer.php'); ?>