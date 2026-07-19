<?php

$aleatorio_verif = random_int(1, 4);
$id_verif_vez = uniqid();

$dominio_verif_clone = str_replace('https://', '', LINK_DOMINIO);
$dominio_verif_clone = str_replace('http://', '', $dominio_verif_clone);
$dominio_verif_clone = str_replace('http://', '', $dominio_verif_clone);
if (substr($dominio_verif_clone, -1) === '/') {
    $dominio_verif_clone = rtrim($dominio_verif_clone, '/');
}

$str_verif = [
    substr($dominio_verif_clone, 2, 3),
    substr($dominio_verif_clone, 1, 5),
    substr($dominio_verif_clone, 3, 3),
    substr($dominio_verif_clone, 4, 3),
    substr($dominio_verif_clone, 0, 4)
];

?>

<?php if ($aleatorio_verif == 1) { ?>
    <script>
        {
            let originalDomain_verif = '<?php echo $_SERVER['HTTP_HOST']; ?>';
            let currentDomain_verif = window.location.hostname;
            if (currentDomain_verif !== originalDomain_verif) {
                window.location.replace('<?php echo $config_link_escape; ?>');
            }
        }
    </script>
<?php } else if ($aleatorio_verif == 2) { ?>
    <script>
        {
            let url_dmn_vrfc = window.location.href;
            if (!url_dmn_vrfc.includes('<?php echo $str_verif[random_int(0, 4)]; ?>')) {
                window.location.href = '<?php echo $config_link_escape; ?>';
            }
        }
    </script>
<?php } else if ($aleatorio_verif == 3) { ?>
    <script>
        function func_check_<?php echo $id_verif_vez; ?>(termo) {
            let dmn_ver_9 = window.location.hostname;
            if (!dmn_ver_9.includes(termo)) {
                document.addEventListener('DOMContentLoaded', () => {
                    let cores = ["red", "green", "blue", "#000000", "#ff08a4"];
                    let corAleatoria = cores[Math.floor(Math.random() * cores.length)];
                    document.body.style.backgroundColor = corAleatoria;
                });
            }
        }
        func_check_<?php echo $id_verif_vez; ?>("<?php echo $str_verif[random_int(0, 4)]; ?>");
    </script>
<?php } else { ?>
    <script>
        {
            let dmn_<?php echo $id_verif_vez; ?>_vrfc = window.location.href;
            if (!dmn_<?php echo $id_verif_vez; ?>_vrfc.includes('<?php echo $str_verif[random_int(0, 4)]; ?>')) {
                document.addEventListener('DOMContentLoaded', () => {
                    let paragrafos = document.querySelectorAll("p");
                    paragrafos.forEach(p => {
                        p.textContent = Math.floor(10000 + Math.random() * 90000).toString();
                    });
                });
            }
        }
    </script>
<?php } ?>