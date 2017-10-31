<?php
$link = '';
$body = '';
if ($this->getProperty('snapshotURL')) {
    $link = $this->getProperty('snapshotURL');
    if ($this->getProperty('streamURL_HQ')) {
        $link = $this->getProperty('streamURL_HQ');
    } elseif ($this->getProperty('streamURL')) {
        $link = $this->getProperty('streamURL');
    }
	
    $body = '[#module name="thumb" url="' . $this->getProperty('snapshotURL') .'" width="250" live="1"#]';
} elseif ($this->getProperty('streamURL_HQ')) {
    $streamURL = $this->getProperty('streamURL_HQ');
    $link = $streamURL;
    $body = '[#module name="thumb" url="' . $this->getProperty('streamURL_HQ') . '" width="250" live="1"#]';
} elseif ($this->getProperty('streamURL')) {
    $streamURL = $this->getProperty('streamURL');
    $link = $streamURL;
    $body = '[#module name="thumb" url="' . $this->getProperty('streamURL') . '" width="250" live="1"#]';
}
$html = processTitle('<a href="' . $link . '" target="_blank">' . $body . "</a>", $this);
$this->setProperty('previewHTML', $html);
© 2017 GitHub, Inc.
