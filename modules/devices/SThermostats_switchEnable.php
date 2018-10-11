<?php

    $disabled=$this->getProperty('disabled');
    if ($disabled) {
        $this->setProperty('disabled',0);
    } else {
        $this->setProperty('disabled',1);
        $this->setProperty('relay_status',0); // turn off
    }