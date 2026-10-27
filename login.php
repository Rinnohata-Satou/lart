<?php require_once('header.php'); ?>
<!-- fetching row banner login -->
<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_login = $row['banner_login'];
}
?>
<!-- login form -->
<?php
if(isset($_POST['form1'])) {
        
    if(empty($_POST['cust_email']) || empty($_POST['cust_password'])) {
        $error_message = LANG_VALUE_132.'<br>';
    } else {
        
        $cust_email = strip_tags($_POST['cust_email']);
        $cust_password = strip_tags($_POST['cust_password']);

        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=?");
        $statement->execute(array($cust_email));
        $total = $statement->rowCount();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $row) {
            $cust_status = $row['cust_status'];
            $row_password = $row['cust_password'];
        }

        if($total==0) {
            $error_message .= LANG_VALUE_133.'<br>';
        } else {
            //using MD5 form
            if( $row_password != md5($cust_password) ) {
                $error_message .= LANG_VALUE_139.'<br>';
            } else {
                if($cust_status == 0) {
                    $error_message .= LANG_VALUE_148.'<br>';
                } elseif ((int)$row['cust_email_verified'] !== 1) {
                    $error_message .= 'Please verify your email address using the link we emailed you before logging in.<br>';
                } else {
                    $_SESSION['customer'] = $row;
                    header("location: ".BASE_URL."dashboard.php");
                }
            }
            
        }
    }
}
?>

<div class="page-banner" style="background-color:#444;background-image: url(assets/uploads/<?php echo $banner_login; ?>);">
    <div class="inner">
        <h1><?php echo LANG_VALUE_10; ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">

                    
                    <form action="" method="post">
                        <?php $csrf->echoInputField(); ?>                  
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <?php
                                if($error_message != '') {
                                    echo "<div class='error' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$error_message."</div>";
                                }
                                if($success_message != '') {
                                    echo "<div class='success' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$success_message."</div>";
                                }
                                ?>
                                <div class="form-group">
                                    <label for=""><?php echo LANG_VALUE_94; ?> *</label>
                                    <input type="email" class="form-control" name="cust_email">
                                </div>
                                <div class="form-group">
                                    <label for="cust_password"><?php echo LANG_VALUE_96; ?> *</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" class="form-control" id="cust_password" name="cust_password">
                                        <button type="button" class="password-toggle-button" data-target="cust_password" aria-label="Show password" aria-pressed="false" onclick="var input = document.getElementById(this.getAttribute('data-target')); input.type = input.type === 'password' ? 'text' : 'password'; this.setAttribute('aria-pressed', input.type === 'text'); this.setAttribute('aria-label', input.type === 'text' ? 'Hide password' : 'Show password'); this.querySelector('i').className = input.type === 'text' ? 'fa fa-eye-slash' : 'fa fa-eye';">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for=""></label>
                                    <input type="submit" class="btn btn-success" value="<?php echo LANG_VALUE_4; ?>" name="form1">
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-default" id="google-sign-in" data-endpoint="<?php echo htmlspecialchars(BASE_URL . 'firebase-google-login.php', ENT_QUOTES, 'UTF-8'); ?>" data-csrf="<?php echo htmlspecialchars($csrf->getToken(), ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-google" aria-hidden="true"></i> Continue with Google</button>
                                    <p id="google-auth-message" role="status" aria-live="polite"></p>
                                </div>
                            </div>
                        </div>                        
                    </form>
                </div>                
            </div>
        </div>
    </div>
</div>

<script type="module" src="assets/js/firebase-google-login.js"></script>
<?php require_once('footer.php'); ?>