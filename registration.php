<?php require_once('header.php'); ?>
<?php require_once('admin/inc/firebase-auth.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_registration = $row['banner_registration'];
}
?>

<?php
if (isset($_POST['form1'])) {

    $valid = 1;
    $cust_email = isset($_POST['cust_email']) ? trim($_POST['cust_email']) : '';

    if(empty($_POST['cust_name'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_123."<br>";
    }

    if(empty($cust_email)) {
        $valid = 0;
        $error_message .= LANG_VALUE_131."<br>";
    } else {
        if (filter_var($cust_email, FILTER_VALIDATE_EMAIL) === false || strtolower(substr(strrchr($cust_email, '@'), 1)) !== 'gmail.com') {
            $valid = 0;
            $error_message .= 'Please enter a valid Gmail address ending in @gmail.com.<br>';
        } else {
            $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=?");
            $statement->execute(array($cust_email));
            $total = $statement->rowCount();                            
            if($total) {
                $valid = 0;
                $error_message .= LANG_VALUE_147."<br>";
            }
        }
    }

    if(empty($_POST['cust_phone'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_124."<br>";
    }

    if(empty($_POST['cust_address'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_125."<br>";
    }

    if( empty($_POST['cust_password']) || empty($_POST['cust_re_password']) ) {
        $valid = 0;
        $error_message .= LANG_VALUE_138."<br>";
    }

    if( !empty($_POST['cust_password']) && !empty($_POST['cust_re_password']) ) {
        if($_POST['cust_password'] != $_POST['cust_re_password']) {
            $valid = 0;
            $error_message .= LANG_VALUE_139."<br>";
        }
    }

    if($valid == 1) {

        $firebase_signup = firebase_auth_request('accounts:signUp', array(
            'email' => $cust_email,
            'password' => $_POST['cust_password'],
            'returnSecureToken' => true
        ));

        if (!$firebase_signup['ok']) {
            if ($firebase_signup['error'] === 'EMAIL_EXISTS') {
                $error_message .= 'This Gmail address is already registered.<br>';
            } else {
                $error_message .= 'Firebase sign-up could not be completed. Check the Firebase setup and try again.<br>';
            }
            $valid = 0;
        }
    }

    if($valid == 1) {
        $token = bin2hex(random_bytes(32));
        $cust_datetime = date('Y-m-d h:i:s');
        $cust_timestamp = time();

        try {
            $statement = $pdo->prepare("INSERT INTO tbl_customer (
                                        cust_name,
                                        cust_cname,
                                        cust_email,
                                        cust_phone,
                                        cust_address,
                                        cust_password,
                                        cust_token,
                                        cust_datetime,
                                        cust_timestamp,
                                        cust_status,
                                        cust_email_verified
                                    ) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $statement->execute(array(
                                        strip_tags($_POST['cust_name']),
                                        strip_tags($_POST['cust_cname']),
                                        $cust_email,
                                        strip_tags($_POST['cust_phone']),
                                        strip_tags($_POST['cust_address']),
                                        md5($_POST['cust_password']),
                                        $token,
                                        $cust_datetime,
                                        $cust_timestamp,
                                        1,
                                        0
                                    ));
        } catch (PDOException $exception) {
            firebase_auth_request('accounts:delete', array('idToken' => $firebase_signup['data']['idToken']));
            $error_message .= 'Your account could not be saved. Please try again.<br>';
            $valid = 0;
        }

        if ($valid == 1) {
            $verification_email = firebase_auth_request('accounts:sendOobCode', array(
                'requestType' => 'VERIFY_EMAIL',
                'idToken' => $firebase_signup['data']['idToken']
            ));

            if (!$verification_email['ok']) {
                $statement = $pdo->prepare("DELETE FROM tbl_customer WHERE cust_email=? AND cust_email_verified=0");
                $statement->execute(array($cust_email));
                firebase_auth_request('accounts:delete', array('idToken' => $firebase_signup['data']['idToken']));
                $error_message .= 'The verification email could not be sent. Please try registering again later.<br>';
                $valid = 0;
            } else {
                unset($_POST['cust_name']);
                unset($_POST['cust_cname']);
                unset($_POST['cust_email']);
                unset($_POST['cust_phone']);
                unset($_POST['cust_address']);

                $success_message = 'Registration received. Check your Gmail inbox and verify your address before logging in.';
            }
        }
    }
}
?>

<div class="page-banner" style="background-color:#444;background-image: url(assets/uploads/<?php echo $banner_registration; ?>);">
    <div class="inner">
        <h1><?php echo LANG_VALUE_16; ?></h1>
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
                            <div class="col-md-2"></div>
                            <div class="col-md-8">
                                
                                <?php
                                if($error_message != '') {
                                    echo "<div class='error' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$error_message."</div>";
                                }
                                if($success_message != '') {
                                    echo "<div class='success' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$success_message."</div>";
                                }
                                ?>

                                <div class="col-md-6 form-group">
                                    <label for=""><?php echo LANG_VALUE_102; ?> *</label>
                                    <input type="text" class="form-control" name="cust_name" value="<?php if(isset($_POST['cust_name'])){echo $_POST['cust_name'];} ?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for=""><?php echo LANG_VALUE_103; ?></label>
                                    <input type="text" class="form-control" name="cust_cname" value="<?php if(isset($_POST['cust_cname'])){echo $_POST['cust_cname'];} ?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="cust_email"><?php echo LANG_VALUE_94; ?> *</label>
                                    <input type="email" class="form-control" id="cust_email" name="cust_email" pattern="[^@\s]+@[Gg][Mm][Aa][Ii][Ll]\.com" title="Use a Gmail address ending in @gmail.com" required value="<?php if(isset($_POST['cust_email'])){echo htmlspecialchars($_POST['cust_email'], ENT_QUOTES, 'UTF-8');} ?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for=""><?php echo LANG_VALUE_104; ?> *</label>
                                    <input type="text" class="form-control" name="cust_phone" value="<?php if(isset($_POST['cust_phone'])){echo $_POST['cust_phone'];} ?>">
                                </div>
                                <div class="col-md-12 form-group">
                                    <label for=""><?php echo LANG_VALUE_105; ?> *</label>
                                    <textarea name="cust_address" class="form-control" cols="30" rows="10" style="height:70px;"><?php if(isset($_POST['cust_address'])){echo $_POST['cust_address'];} ?></textarea>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="cust_password"><?php echo LANG_VALUE_96; ?> *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="cust_password" name="cust_password">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" data-password-toggle="cust_password" aria-controls="cust_password" aria-label="Show password" aria-pressed="false"><i class="fa fa-eye" aria-hidden="true"></i><span class="sr-only">Show password</span></button>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="cust_re_password"><?php echo LANG_VALUE_98; ?> *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="cust_re_password" name="cust_re_password">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" data-password-toggle="cust_re_password" aria-controls="cust_re_password" aria-label="Show password" aria-pressed="false"><i class="fa fa-eye" aria-hidden="true"></i><span class="sr-only">Show password</span></button>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for=""></label>
                                    <input type="submit" class="btn btn-danger" value="<?php echo LANG_VALUE_15; ?>" name="form1">
                                </div>
                            </div>
                        </div>                        
                    </form>
                </div>                
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>