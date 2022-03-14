<?php
    include_once 'sms.php';

    $sms = new Sms('+34256781');
    $recipients = $sms->fetchRecipients();
    $response = $sms->sendSMS("We have slashed our transaction fees by 50%", $recipients);
    echo json_encode($response);
?>