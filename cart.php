<?php include 'header.php' ?>

<?php
if (!isset($_SESSION['applied_voucher'])) {
    $_SESSION['VoucherNames'] = [];
    $_SESSION['applied_voucher'] = null;
    $_SESSION['discounted_total'] = null;
}
// Add this to handle voucher removal
if (isset($_POST['remove_voucher'])) {
    if ($_SESSION['applied_voucher']) {
        // Reset voucher in database to unused
        $update_sql = "UPDATE vouchers 
                      SET UsedStatus = 0, Username = NULL 
                      WHERE VoucherID = '" . $_SESSION['applied_voucher']['VoucherID'] . "'";
        Database::NonQuery($update_sql);

        // Clear voucher session data
        $_SESSION['applied_voucher'] = null;
        $_SESSION['discounted_total'] = null;

        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Voucher removed successfully!',
                icon: 'success'
            });
        </script>";
    }
}

// Thêm sản phẩm vào giỏ hàng
if (isset($_GET['id'])) {
    $sql = "INSERT INTO carts VALUES ('" . $_GET['id'] . "', '" . $_SESSION['Username'] . "', 1, NOW(3))";
    Database::NonQuery($sql);
}

// Cập nhật số lượng sản phẩm
if (isset($_POST['update_amount'])) {
    $isbn = isset($_POST['isbn']) ? $_POST['isbn'] : '';
    $amount = isset($_POST['amount']) ? $_POST['amount'] : '';

    // Lấy số lượng trong kho
    $sql = "SELECT InventoryNumber FROM books WHERE ISBN = '$isbn'";
    $inventoryNumber = Database::GetData($sql, ['row' => 0, 'cell' => 0]);

    if ($amount <= $inventoryNumber) {
        // Nếu số lượng yêu cầu không vượt quá hàng trong kho, thực hiện cập nhật
        $sql = "UPDATE carts SET Amount = $amount WHERE ISBN = '$isbn' AND Username = '" . $_SESSION['Username'] . "'";
        Database::NonQuery($sql);
    }
}

// Xoá sản phẩm trong giỏ hàng
if (isset($_GET['del-cart-id'])) {
    $isbn = $_GET['del-cart-id'];

    $sql = "DELETE FROM carts WHERE ISBN = '$isbn' AND Username = '" . $_SESSION['Username'] . "'";
    Database::NonQuery($sql);
}

function CreateOrderID()
{
    $str = 'BHT';
    for ($i = 1; $i < 8; $i++) {
        $str .= rand(0, 9);
    }
    return $str;
}

// Tạo đơn hàng
if (isset($_GET['type']) && $_GET['type'] == 'payment') {
    $sql = "SELECT * FROM carts WHERE Username = '" . $_SESSION['Username'] . "'";
    $carts = Database::GetData($sql);

    if ($carts) {
        $orderID = CreateOrderID();
        $sql = "SELECT SUM(Amount * Price) FROM carts, books WHERE carts.ISBN = books.ISBN AND Username = '" . $_SESSION['Username'] . "'";
        $totalMoney = Database::GetData($sql, ['row' => 0, 'cell' => 0]);

        $sql = "INSERT INTO orders VALUES ('$orderID', $totalMoney, $totalMoney, 0, NULL, NOW(3), '" . $_SESSION['Username'] . "')";
        Database::NonQuery($sql);

        foreach ($carts as $cart) {
            $sql = "INSERT INTO order_details VALUES (null, '" . $cart['ISBN'] . "', '$orderID', " . $cart['Amount'] . ')';
            Database::NonQuery($sql);
        }

        $sql = "DELETE FROM carts WHERE Username = '" . $_SESSION['Username'] . "'";
        Database::NonQuery($sql);
    }
}
if (isset($_POST['apply_voucher'])) {
    if ($_SESSION['applied_voucher']) {
        echo "<script>
                Swal.fire({
                    title: 'Warning!',
                    text: 'Only one voucher can be used per order. Remove current voucher first.',
                    icon: 'warning'
                });
            </script>";
    } else {

        $voucher_code = $_POST['voucher_code'];
        $current_time = date('Y-m-d H:i:s');
        $username = $_SESSION['Username'];

        // Check if voucher exists and is valid
        $sql = "SELECT * FROM vouchers 
                WHERE VoucherName = '$voucher_code' 
                AND StartTime <= '$current_time' 
                AND EndTime >= '$current_time' 
                AND UsedStatus = 0 
                AND (Username IS NULL)";

        $voucher = Database::GetData($sql, ['row' => 0]);

        if ($voucher) {
            $_SESSION['VoucherNames'][] = $voucher['VoucherName'];
            $_SESSION['applied_voucher'] = $voucher;
            $discount = $voucher['Discount'];
            $new_total = $totalMoney - ($totalMoney * $discount / 100);
            $_SESSION['discounted_total'] = $new_total;

            $update_sql = "UPDATE vouchers 
                          SET UsedStatus = 1, Username = '$username' 
                          WHERE VoucherID = '" . $voucher['VoucherID'] . "'";
            Database::NonQuery($update_sql);


            $_SESSION['discounted_total'] = $new_total;

            echo "<script>
                Swal.fire({
                    title: 'Success!',
                    text: 'Voucher applied successfully! New total: " . number_format($new_total, 0, ".", ".") . " đ',
                    icon: 'success'
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!', 
                    text: 'Invalid or expired voucher code!',
                    icon: 'error'
                });
            </script>";
        }
    }
}
if (isset($_SESSION['applied_voucher']) && $_SESSION['applied_voucher']) {
    $discount = $_SESSION['applied_voucher']['Discount'];
    // $totalMoney = $totalMoney - ($totalMoney * $discount / 100);
}
?>
<div class="product-big-title-area">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="product-bit-title text-center">
                    <h2>Giỏ hàng của bạn</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="product-content-right">
            <div class="woocommerce">
                <form method="post" action="#">
                    <table cellspacing="0" class="shop_table cart">
                        <thead>
                            <tr>
                                <th>ISBN</th>
                                <th>Tên sách</th>
                                <th>Ảnh</th>
                                <th>Giá</th>
                                <th width="125">Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Xoá</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($_SESSION['Username'])) {
                                $sql = "SELECT * FROM carts, books WHERE books.ISBN = carts.ISBN AND Username = '" . $_SESSION['Username'] . "' ORDER BY carts.UpdatedAt DESC";
                                $carts = Database::GetData($sql);

                                if ($carts) {
                                    foreach ($carts as $cart) { ?>
                                        <tr class="cart_item">
                                            <td class="product-name"><?= $cart['ISBN'] ?></td>
                                            <td class="product-name"><?= $cart['BookTitle'] ?></td>
                                            <td class="product-thumbnail"><img class="shop_thumbnail"
                                                    src=".<?= $cart['Thumbnail'] ?>"></td>
                                            <td class="product-name"><?= Helper::Currency($cart['Price']) ?></td>
                                            <td class="product-quantity">
                                                <div class="quantity buttons_added">
                                                    <form method="POST">
                                                        <input name="isbn" value="<?= $cart['ISBN'] ?>" hidden>
                                                        <input name="amount" type="number" size="4" class="input-text qty text"
                                                            min="1" value="<?= $cart['Amount'] ?>">
                                                        <button name="update_amount" class="btn btn-primary"><i
                                                                class="fas fa-save"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td class="product-name"><?= Helper::Currency($cart['Price'] * $cart['Amount']) ?></td>
                                            <td class="product-remove"><a title="Xoá sản phẩm" class="remove"
                                                    href="?del-cart-id=<?= $cart['ISBN'] ?>">×</td>
                                        </tr>
                            <?php }
                                }
                            }
                            ?>
                            <tr>
                                <td class="actions" colspan="6">
                                    <div class="coupon">
                                        <form action="" method="POST">
                                            <label for="coupon_code">Khuyến mãi:</label>
                                            <input type="text" placeholder="Mã khuyến mãi" value="" id="coupon_code"
                                                class="input-text" name="voucher_code">
                                            <input type="submit" value="Kiểm tra mã" name="apply_voucher"
                                                class="button">
                                        </form>
                                    </div>
                                    <?php
                                    if (isset($_SESSION['Username'])) {
                                        echo '<a href="?type=payment" class="btn btn-lg btn-success">Tạo đơn hàng</a>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>

                <div class="cart-collaterals">
                    <div class="cart_totals">
                        <h2>Tổng tiền giỏ hàng</h2>
                        <?php

                        ?>
                        <table cellspacing="0">
                            <tbody>
                                <tr class="cart-subtotal">
                                    <th>Tổng đơn hàng: </th>
                                    <td><span class="amount"><?= number_format($totalMoney) ?> đ</span></td>
                                </tr>
                                <tr class="shipping">
                                    <th>Vận chuyển: </th>
                                    <td>15000 đ</td>
                                </tr>
                                <tr class="order-total">
                                    <th>Tổng tiền: </th>
                                    <td class="text-right p-4 font-semibold"><strong><span
                                                class="amount"><?php if (isset($_SESSION['applied_voucher'])) $tamp3 =  $_SESSION['discounted_total'] + 15000;
                                                                else if (isset($totalMoney)) $tamp3 = $totalMoney + 15000;
                                                                else $tamp3 = 0;
                                                                echo number_format($tamp3, 0, ".", ".") . " đ"; ?>

                                            </span></strong>
                                    </td>
                                    </td>
                                </tr>
                                <?php if (isset($_SESSION['applied_voucher'])) {
                                ?>
                                    <tr>
                                        <th class="text-sm text-left text-gray-400 p-4">Voucher Used</th>
                                        <td class="text-right p-4 font-semibold"><strong><span class="amount">
                                                    <?php
                                                    foreach ($_SESSION['VoucherNames'] as $v) {
                                                        echo $v . "<br>";
                                                    } ?>
                                                </span></strong>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <th class="text-sm text-left text-gray-400 p-4">Total</th>
                                    <td class="text-right p-4 font-semibold"><strong><span
                                                class="amount"><?php if (isset($_SESSION['applied_voucher'])) $tamp3 =  $_SESSION['discounted_total'] + 15000;
                                                                else if (isset($totalMoney)) $tamp3 = $totalMoney + 15000;
                                                                else $tamp3 = 0;
                                                                echo number_format($tamp3, 0, ".", ".") . " đ"; ?>

                                            </span></strong>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php' ?>