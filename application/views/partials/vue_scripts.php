<?php
// $scripts is an array of entry names, e.g. ['product', 'search']
if (!empty($scripts)):
    foreach ($scripts as $script):
?>
<script type="module" src="<?= base_url('assets/js/' . $script . '.js') ?>"></script>
<?php
    endforeach;
endif;
?>
