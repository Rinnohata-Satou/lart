<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
   $about_title = $row['about_title'];
    $about_content = $row['about_content'];
    $about_banner = $row['about_banner'];
}
?>

<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $about_banner; ?>);">
    <div class="inner">
        <h1><?php echo $about_title; ?></h1>
    </div>
</div>

<div class="page solari-about-page">
    <div class="container">
        <div class="row">            
            <div class="col-md-12">
                
                <p><img src="assets/uploads/about-banner.png" alt="Solari solar energy solutions"></p>
                <article class="solari-about-content"><?php echo $about_content; ?></article>

                


            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>