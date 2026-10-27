<?php require_once('header.php'); ?>
<?php
$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$faq_title = 'Frequently asked questions';
$faq_banner = 'about-banner.png';
foreach ($result as $row) {
    $faq_title = $row['faq_title'];
    $faq_banner = !empty($row['about_banner']) ? $row['about_banner'] : 'about-banner.png';
}
// Presentation copy for legacy FAQ entries. Database records remain editable and unchanged.
$solari_faq_copy = array(
    'How to find an item?' => array('How do I find the right product?', '<p>Open <strong>Shop</strong> to browse solar products by category and subcategory. On a computer, hover over Shop; on a phone or tablet, tap it.</p><p>Looking for something specific? Select the search icon in the navigation bar and enter a product name or keyword. Select a product image or name to see its details, specifications and price.</p>'),
    'What is your return policy?' => array('What is your return policy?', '<p>You can request a return or refund within <strong>15 days of delivery</strong>. Keep the product, original packaging and included accessories while your request is reviewed.</p><p>Check the return information on the product page for any item-specific conditions. If your item arrived damaged or defective, see the guidance below.</p>'),
    'I received a defective/damaged item, can I get a refund?' => array('What if my item arrives damaged or defective?', '<p>If your item arrives damaged or defective, contact the store to request a return. Keep it in the condition you received it, together with the original packaging and accessories.</p><p>The returned item will be inspected. If the damage or defect is confirmed, the refund will include the shipping fees incurred, as described in the store’s return policy.</p>'),
    'When are ‘Returns’ not possible?' => array('What can affect return eligibility?', '<p>A return may not be accepted if the request is made more than <strong>15 days after delivery</strong>, the item has been used or altered, serial numbers are missing or tampered with, or packaging and included accessories are incomplete.</p><p>Used or installed consumable items may also be excluded. Items that arrived damaged or defective are assessed under the damaged-item guidance above. For products covered by a manufacturer’s warranty, check the warranty terms for the applicable support process.</p>'),
    'What are the items that cannot be returned?' => array('Are any products excluded from returns?', '<p>Check the product page and offer details before ordering. Items explicitly marked <strong>non-returnable</strong>, including clearance offers with a no-return condition, may be excluded. Used or installed consumable items may also be excluded.</p><p>If an item arrives damaged or defective, contact the store so the issue can be assessed. Keep the original packaging and all included parts.</p>')
);
?>
<section class="page-banner solari-faq-banner" style="background-image:url('assets/uploads/<?php echo htmlspecialchars($faq_banner, ENT_QUOTES, 'UTF-8'); ?>');">
 <div class="inner"><p class="solari-faq-eyebrow">SOLARI SUPPORT</p><h1><?php echo htmlspecialchars($faq_title, ENT_QUOTES, 'UTF-8'); ?></h1><p>Find your next solar solution with confidence.</p></div>
</section>
<main class="page solari-faq-page"><div class="container"><div class="solari-faq-layout">
 <div class="solari-faq-intro"><span class="solari-faq-eyebrow">HERE TO HELP</span><h2>A little clarity,<br>a brighter choice.</h2><p>Answers about shopping, returns and product support, all in one place.</p><a href="index.php" class="solari-faq-back">Back to the store &rarr;</a></div>
 <div class="solari-faq-list" aria-label="Frequently asked questions">
<?php
$statement = $pdo->prepare("SELECT * FROM tbl_faq");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row):
    $question = trim($row['faq_title']);
    $answer = $row['faq_content'];
    if (isset($solari_faq_copy[$question])) {
        list($question, $answer) = $solari_faq_copy[$question];
    }
?>
 <details class="solari-faq-item"><summary><?php echo htmlspecialchars($question, ENT_QUOTES, 'UTF-8'); ?><span class="solari-faq-symbol" aria-hidden="true"></span></summary><div class="solari-faq-answer"><?php echo $answer; ?></div></details>
<?php endforeach; ?>
<?php if (!$result): ?><p class="solari-faq-empty">We’re updating our frequently asked questions. Please check back soon.</p><?php endif; ?>
 </div></div></div></main>
<?php require_once('footer.php'); ?>
