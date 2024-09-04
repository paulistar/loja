<?php
if (!ACC_MANAGER):
    require REQUIRE_PATH . '/404.php';
else:
    ?>
    <div class="container" id="acc">
        <div class="content" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; padding: 40px 0 50px 0;">
            <?php require '_cdn/widgets/account/account.php'; ?>
            <div class="clear"></div>
        </div>
    </div>
<?php
endif;