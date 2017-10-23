<?php

$cameraUsername = $this->getProperty('cameraUsername');
$cameraPassword = $this->getProperty('cameraPassword');

$link = '';
$body = '';

if ($this->getProperty('snapshotURL')) {
    $link = $this->getProperty('snapshotURL');
    if ($this->getProperty('streamURL_HQ')) {
        $link = $this->getProperty('streamURL_HQ');
    } elseif ($this->getProperty('streamURL')) {
        $link = $this->getProperty('streamURL');
    }
    $body = '[#module name="thumb" url="' . $this->getProperty('snapshotURL') . '" username="' . $cameraUsername . '" password="' . $cameraPassword . '" width="250"#]';
} elseif ($this->getProperty('streamURL_HQ')) {
    $streamURL = $this->getProperty('streamURL_HQ');
    $link = $streamURL;
    $body = '[#module name="thumb" url="' . $this->getProperty('streamURL_HQ') . '" username="' . $cameraUsername . '" password="' . $cameraPassword . '" width="250"#]';
} elseif ($this->getProperty('streamURL')) {
    $streamURL = $this->getProperty('streamURL');
    $link = $streamURL;
    $body = '[#module name="thumb" url="' . $this->getProperty('streamURL') . '" username="' . $cameraUsername . '" password="' . $cameraPassword . '" width="250"#]';
}

if ($cameraUsername || $cameraPassword) {
    $link = str_replace('://', '://' . $cameraUsername . ':' . $cameraPassword . '@', $link);
}

$html = processTitle('<a href="' . $link . '" target="_blank">' . $body . "</a>", $this);

$this->setProperty('previewHTML', $html);