<?php $this->load->view('partials/header', array('page_title' => isset($page_title) ? $page_title : 'Dashboard')); ?>
<?php $this->load->view('partials/nav', ['hide_subnav' => true]); ?>
<div class="container py-4">
    <?php $this->load->view('partials/flash_messages'); ?>
    <?php $this->load->view($content_view, get_defined_vars()); ?>
</div>
<?php $this->load->view('partials/footer'); ?>
