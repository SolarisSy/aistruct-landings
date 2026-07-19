<?php if ($bloquear_inspecionar) { ?>
    <script>
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        }, false);
        document.onkeydown = function(e) {
            if (e.keyCode == 123) {
                window.location.replace("<?php echo $config_link_escape ?>");
                return false;
            }
            if (e.ctrlKey && e.shiftKey && e.keyCode == 73) {
                window.location.replace("<?php echo $config_link_escape ?>");
                return false;
            }
            if (e.ctrlKey && e.keyCode == 85) {
                window.location.replace("<?php echo $config_link_escape ?>");
                return false;
            }
        };
    </script>
<?php } ?>

<?php if ($bloquear_desktop) { ?>
    <script>
        if (window.innerWidth > 800) {
            window.location.replace("<?php echo $config_link_escape ?>");
        }
    </script>
<?php } ?>

<?php
if ($tipo_ambiente != 'local') {
    echo " <script> const hostname = window.location.hostname; if (hostname === 'localhost' || hostname === '127.0.0.1') { window.location.replace('" . $config_link_escape . "'); } </script>";
}
?>