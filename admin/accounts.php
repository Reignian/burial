<?php

    include(__DIR__ . '/nav/navigation.php');
    require_once (__DIR__ . '/accounts/accounts.class.php');
    $burialObj = new Accounts_class();

    $cusarray = $burialObj->showALL_account();

    if (isset($_POST['toggle_ban'])) {
        $account_id = $_POST['account_id'];
        $burialObj->toggleBanStatus($account_id);
        header("Location: accounts.php");
        exit();
    }
?>

<div class="container">


    <section id="accounts">
        <h1>ACCOUNTS</h1>
        
        <table class="table table-hover table-bordered table-striped table-primary">
            <tr class="table-dark">
                <th>No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
                $i = 1;
                foreach ($cusarray as $cusarr){
            ?>
            <tr>
                <td><?= $i ?></td>
                <td><?= $cusarr['last_name'] ?>, <?= $cusarr['first_name'] ?></td>
                <td><?= $cusarr['email'] ?></td>
                <td><?= $cusarr['phone_number'] ?></td>
                <td><?= $cusarr['status'] ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="account_id" value="<?= $cusarr['account_id'] ?>">
                        <button type="submit" name="toggle_ban" class="btn btn-<?= $cusarr['status'] == 'Banned' ? 'success' : 'danger' ?>">
                            <?= $cusarr['status'] == 'Banned' ? 'Unban' : 'Ban' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php
                    $i++;
                }
            ?>
        </table>
    </section>



</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
