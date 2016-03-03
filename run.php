<?php

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'user@domain.com';
$password = 'password';
$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Email: ' . imap_last_error());

$emails = getEmails($inbox, 'UNSEEN');

$alert = false;

foreach ($emails as $em) {
    if (ckstr($em->from, "someone@important.com")) {
        $alert = true;
    }
}

if ($alert) {
    $cmd = 'flite -voice rms -t "you have email"';
    exec($cmd);
    sleep(1);
} else {
    // do nothing
}

function getEmails($inbox, $criteria) {
    $emails = imap_search($inbox, $criteria);
    $out = array();
    if ($emails) {
        rsort($emails);
        foreach ($emails as $emid) {
            $headers = imap_fetch_overview($inbox, $emid, 0);
            $message = imap_fetchbody($inbox, $emid, 2, FT_PEEK);

            $em = new stdClass;
            $em->number = $emid;
            $em->seen = ($headers[0]->seen ? 'read' : 'unread');
            $em->subject = $headers[0]->subject;
            $em->from = $headers[0]->from;
            $em->date = $headers[0]->date;
            $em->message = $message;
            $out[] = $em;
        }
    }
    return $out;
}

function ckstr($str, $needle) {
    return !(strpos($str, $needle) === false);
}

imap_close($inbox);

