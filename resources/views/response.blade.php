<?php
header("Cache-Control: no-cache");
$result_message = !empty($result_message) ? $result_message : "";
?><!DOCTYPE html>
<html lang=tr>
<head>
    <meta charset=utf-8>
    <meta http-equiv=X-UA-Compatible content="IE=edge">
    <meta name=mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=viewport content="width=device-width,user-scalable=yes,initial-scale=1,maximum-scale=5,minimum-scale=1">
    <meta name=author content="Medya-T Yazılım Ltd. Şti.">
    <meta name=languages content=tr>
    <title>Parapos Response</title>
</head>
<body>
<div>Parapos Response</div>
<script>
    var message = {
        'type': 'paymentResponse',
        'id': '<?php if (isset($id)) echo $id; ?>',
        'result_message': '<?php if (isset($result_message)) echo $result_message; ?>',
        'result_code': '<?php echo isset($result_code) ? $result_code : 'FAIL'; ?>'
    };


    window.addEventListener('load', (event) => {
        if (typeof window.parent === 'object')
            if (typeof window.parent.postMessage === 'function')
                window.parent.postMessage(message, "*");

        if (typeof window.ReactNativeWebView === 'object')
            if (typeof window.ReactNativeWebView.postMessage === 'function')
                window.ReactNativeWebView.postMessage(JSON.stringify(message));
    });
</script>
</body>
</html>
