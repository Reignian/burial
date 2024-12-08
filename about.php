<?php
    include('includes/header.php');
    require_once __DIR__ . '/website/website.class.php';

    $burialObj = new CMS();
    $array1 = $burialObj->About();
    $array2 = $burialObj->About_2();
    $array3 = $burialObj->About_main();
    $array4 = $burialObj->About_team();

?>


<div class="about-section">
    <!-- Hero Section -->
    <div class="about-hero">
        <div class="container">
            <h1>Sto. Niño Parish Cemetery</h1>
            <p class="lead">A Sacred Place of Rest, Remembrance, and Peace</p>
        </div>
    </div>

    <!-- History Section -->
    <div class="container mt-5">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h2><?= htmlspecialchars($array1[0]['section_title']) ?></h2>
                <p class="lead"><?= htmlspecialchars($array1[0]['sub_title']) ?></p>
                <p><?=($array3[0]['text']) ?></p>
            </div>
            <div class="col-md-6">
                <img src="<?= htmlspecialchars($array3[0]['image']) ?>" alt="Historical view of cemetery" class="img-fluid rounded shadow">
            </div>
        </div>

        <!-- Mission & Values Section -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2><?= htmlspecialchars($array1[1]['section_title']) ?></h2>
                <p class="lead"><?= htmlspecialchars($array1[1]['sub_title']) ?></p>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <i class="<?= htmlspecialchars($array2[0]['card_icon']) ?>"></i>
                    <h3><?= htmlspecialchars($array2[0]['card_title']) ?></h3>
                    <p><?= htmlspecialchars($array2[0]['card_text']) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <i class="<?= htmlspecialchars($array2[1]['card_icon']) ?>"></i>
                    <h3><?= htmlspecialchars($array2[1]['card_title']) ?></h3>
                    <p><?= htmlspecialchars($array2[1]['card_text']) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <i class="<?= htmlspecialchars($array2[2]['card_icon']) ?>"></i>
                    <h3><?= htmlspecialchars($array2[2]['card_title']) ?></h3>
                    <p><?= htmlspecialchars($array2[2]['card_text']) ?></p>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2><?= htmlspecialchars($array1[2]['section_title']) ?></h2>
                <p class="lead"><?= htmlspecialchars($array1[2]['sub_title']) ?></p>
            </div>
            <div class="col-md-6">
                <div class="service-card">
                    <h3><i class="<?= htmlspecialchars($array2[3]['card_icon']) ?>"></i> <?= htmlspecialchars($array2[3]['card_title']) ?></h3>
                    <p><?= ($array2[3]['card_text']) ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-card">
                    <h3><i class="<?= htmlspecialchars($array2[4]['card_icon']) ?>"></i> <?= htmlspecialchars($array2[4]['card_title']) ?></h3>
                    <p><?= ($array2[4]['card_text']) ?></p>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="row mb-5" style="justify-content: center;">
            <div class="col-12 text-center mb-4">
                <h2><?= htmlspecialchars($array1[3]['section_title']) ?></h2>
                <p class="lead"><?= htmlspecialchars($array1[3]['sub_title']) ?></p>
            </div>

            <?php foreach ($array4 as $arr) { ?>

            <div class="col-md-4">
                <div class="team-card">
                    <img src="<?= $arr['image'] ?>" alt="Team Member" class="rounded-circle">
                    <h3><?= $arr['name'] ?></h3>
                    <p><?= $arr['position'] ?></p>
                </div>
            </div>

            <?php } ?>

        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>