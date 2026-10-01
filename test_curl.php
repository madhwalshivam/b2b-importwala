<?php
$ch = curl_init('https://www.bulkflowai.com/api/img?i=3-sityAKdw0GLgQXXE4dCtwVG-h94g4oj621U1Bx_CmAsdiIAZSaZnGthbG1mYgWW2w3rn1sAPHJX64JZySSS5OrEeGAN8oM_1ODAFBNOq9WDl4HmezBgmY7nUjMYBlYbMIYFxjFk1eCQuJT89vuOw&t=WWW.IMPORTWALE.COM&p=center&v=3');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$mh = curl_multi_init();
curl_multi_add_handle($mh, $ch);
do {
    curl_multi_exec($mh, $active);
    if ($active) curl_multi_select($mh);
} while ($active);
var_dump(curl_getinfo($ch, CURLINFO_HTTP_CODE));
var_dump(strlen(curl_multi_getcontent($ch)));
