<?php require_once('header.php'); ?>
<?php require_once('admin/inc/firebase-auth.php'); ?>

<?php
$verification_message = 'The verification link is invalid or has expired. Please request a new one.';
$verification_success = false;

if (isset($_GET['mode'], $_GET['oobCode']) && $_GET['mode'] === 'verifyEmail' && $_GET['oobCode'] !== '') {
	$verification = firebase_auth_request('accounts:update', array(
		'oobCode' => $_GET['oobCode']
	));

	if ($verification['ok'] && !empty($verification['data']['email']) && !empty($verification['data']['emailVerified'])) {
		$email = $verification['data']['email'];
		$statement = $pdo->prepare("UPDATE tbl_customer SET cust_email_verified=1 WHERE cust_email=?");
		$statement->execute(array($email));

		if ($statement->rowCount() > 0) {
			$verification_success = true;
			$verification_message = 'Your Gmail address is verified. You can now log in.';
		} else {
			$verification_message = 'Your email was verified, but no matching customer account was found. Please contact support.';
		}
	}
}
?>

<div class="page-banner" style="background-color:#444;">
    <div class="inner">
        <h1>Email Verification</h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <p class="<?php echo $verification_success ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($verification_message, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if ($verification_success): ?>
                        <p><a href="<?php echo htmlspecialchars(BASE_URL . 'login.php', ENT_QUOTES, 'UTF-8'); ?>">Go to login</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>