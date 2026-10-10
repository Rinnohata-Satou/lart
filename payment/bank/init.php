<?php
ob_start();
session_start();
include("../../admin/inc/config.php");
include("../../admin/inc/functions.php");
// Getting all language variables into array as global variable
$i=1;
$statement = $pdo->prepare("SELECT * FROM tbl_language");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	define('LANG_VALUE_'.$i,$row['lang_value']);
	$i++;
}
?>
<?php
if( !isset($_REQUEST['msg']) ) {
	if(empty($_POST['transaction_info']) || !isset($_SESSION['customer']) || !isset($_SESSION['cart_p_id'])) {
		header('location: ../../checkout.php');
		exit;
	} else {
		$cart_product_ids = array_values($_SESSION['cart_p_id']);
		$cart_product_names = array_values($_SESSION['cart_p_name']);
		$cart_size_names = array_values($_SESSION['cart_size_name']);
		$cart_color_names = array_values($_SESSION['cart_color_name']);
		$cart_quantities = array_values($_SESSION['cart_p_qty']);
		$cart_prices = array_values($_SESSION['cart_p_current_price']);
		$cart_item_count = count($cart_product_ids);
		if(
			count($cart_product_names) !== $cart_item_count ||
			count($cart_size_names) !== $cart_item_count ||
			count($cart_color_names) !== $cart_item_count ||
			count($cart_quantities) !== $cart_item_count ||
			count($cart_prices) !== $cart_item_count
		) {
			$_SESSION['stock_error'] = 'Your cart could not be processed. Please review it and try again.';
			header('location: ../../cart.php');
			exit;
		}

		for($i=0; $i<$cart_item_count; $i++) {
			$cart_quantities[$i] = filter_var($cart_quantities[$i], FILTER_VALIDATE_INT);
			$cart_product_ids[$i] = filter_var($cart_product_ids[$i], FILTER_VALIDATE_INT);
			if($cart_quantities[$i] === false || $cart_quantities[$i] < 1 || $cart_product_ids[$i] === false || $cart_product_ids[$i] < 1) {
				$_SESSION['stock_error'] = 'Your cart contains an invalid product quantity. Please review it and try again.';
				header('location: ../../cart.php');
				exit;
			}
		}

		$payment_id = time();

		$payment_date = date('Y-m-d H:i:s');

	    $statement = $pdo->prepare("INSERT INTO tbl_payment (   
	                            customer_id,
	                            customer_name,
	                            customer_email,
	                            payment_date,
	                            txnid, 
	                            paid_amount,
	                            card_number,
	                            card_cvv,
	                            card_month,
	                            card_year,
	                            bank_transaction_info,
	                            payment_method,
	                            payment_status,
	                            shipping_status,
	                            payment_id
	                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
	    $statement->execute(array(
	                            $_SESSION['customer']['cust_id'],
	                            $_SESSION['customer']['cust_name'],
	                            $_SESSION['customer']['cust_email'],
	                            $payment_date,
	                            '',
	                            $_POST['amount'],
	                            '', 
	                            '',
	                            '', 
	                            '',
	                            $_POST['transaction_info'],
	                            'Bank Deposit',
	                            'Pending',
	                            'Pending',
	                            $payment_id
	                        ));

		$stock_statement = $pdo->prepare("UPDATE tbl_product SET p_qty=p_qty-? WHERE p_id=? AND p_qty>=?");
		$order_statement = $pdo->prepare("INSERT INTO tbl_order (product_id,product_name,size,color,quantity,unit_price,payment_id) VALUES (?,?,?,?,?,?,?)");
		$stock_updated = array();
		try {
			for($i=0; $i<$cart_item_count; $i++) {
				$quantity = $cart_quantities[$i];
				$product_id = $cart_product_ids[$i];
				$stock_statement->execute(array($quantity, $product_id, $quantity));
				if($stock_statement->rowCount() !== 1) {
					foreach($stock_updated as $updated_item) {
						$restore_statement = $pdo->prepare("UPDATE tbl_product SET p_qty=p_qty+? WHERE p_id=?");
						$restore_statement->execute(array($updated_item['quantity'], $updated_item['product_id']));
					}
					$delete_statement = $pdo->prepare("DELETE FROM tbl_order WHERE payment_id=?");
					$delete_statement->execute(array($payment_id));
					$delete_statement = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
					$delete_statement->execute(array($payment_id));
					$_SESSION['stock_error'] = 'The requested quantity is no longer available. Please review your cart and try again.';
					header('location: ../../cart.php');
					exit;
				}

				$stock_updated[] = array('product_id' => $product_id, 'quantity' => $quantity);
				$order_statement->execute(array(
					$product_id,
					$cart_product_names[$i],
					$cart_size_names[$i],
					$cart_color_names[$i],
					$quantity,
					$cart_prices[$i],
					$payment_id
				));
			}
		} catch(PDOException $exception) {
			foreach($stock_updated as $updated_item) {
				$restore_statement = $pdo->prepare("UPDATE tbl_product SET p_qty=p_qty+? WHERE p_id=?");
				$restore_statement->execute(array($updated_item['quantity'], $updated_item['product_id']));
			}
			$delete_statement = $pdo->prepare("DELETE FROM tbl_order WHERE payment_id=?");
			$delete_statement->execute(array($payment_id));
			$delete_statement = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
			$delete_statement->execute(array($payment_id));
			throw $exception;
		}

	    unset($_SESSION['cart_p_id']);
	    unset($_SESSION['cart_size_id']);
	    unset($_SESSION['cart_size_name']);
	    unset($_SESSION['cart_color_id']);
	    unset($_SESSION['cart_color_name']);
	    unset($_SESSION['cart_p_qty']);
	    unset($_SESSION['cart_p_current_price']);
	    unset($_SESSION['cart_p_name']);
	    unset($_SESSION['cart_p_featured_photo']);

	    header('location: ../../payment_success.php');
	}
}

?>