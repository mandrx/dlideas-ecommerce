<?php $this->load->view('partials/header', array('page_title' => isset($page_title) ? $page_title : 'Account')); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h2 class="mb-4 text-center"><?= isset($page_title) ? htmlspecialchars($page_title) : '' ?></h2>
            <?php $this->load->view('partials/flash_messages'); ?>
            <?php $this->load->view($content_view, get_defined_vars()); ?>
        </div>
    </div>
</div>
<?php $this->load->view('partials/footer'); ?>
