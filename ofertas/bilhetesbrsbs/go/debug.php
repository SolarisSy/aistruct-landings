<?php
header("Content-Type: text/plain");
$keys = ["REMOTE_ADDR","HTTP_X_FORWARDED_FOR","HTTP_X_REAL_IP","HTTP_CF_CONNECTING_IP",
         "HTTP_TRUE_CLIENT_IP","HTTP_X_CLIENT_IP","HTTP_CLIENT_IP","HTTP_FORWARDED",
         "HTTP_X_ENVOY_EXTERNAL_ADDRESS","HTTP_DO_CONNECTING_IP","HTTP_FASTLY_CLIENT_IP",
         "HTTP_CDN_REQUESTCOUNTRYCODE","HTTP_CDN_REQUESTID","HTTP_X_BUNNY_CLIENTIP"];
foreach ($keys as $k) {
    $v = isset($_SERVER[$k]) ? $_SERVER[$k] : "(not set)";
    echo "$k = $v\n";
}
echo "\n--- ALL HTTP_ headers ---\n";
foreach ($_SERVER as $k => $v) {
    if (strpos($k, "HTTP_") === 0 || $k === "REMOTE_ADDR") {
        echo "$k = $v\n";
    }
}
