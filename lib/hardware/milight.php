<?php

/*
 * Milight/LimitlessLED/EasyBulb PHP API
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Yashar Rashedi <info@rashedi.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/

class Milight
{

    private $host;
    private $port;
    private $delay = 100000; //microseconds
    private $rgbwActiveGroup = 0; // 0 means all
    private $whiteActiveGroup = 0; // 0 means all
    private $commandCodes = array(
        //RGBW Bubls commands
        'rgbwAllOn' => array(0x42, 0x00),
        'rgbwAllOff' => array(0x41, 0x00),
        'rgbwGroup1On' => array(0x45, 0x00),
        'rgbwGroup2On' => array(0x47, 0x00),
        'rgbwGroup3On' => array(0x49, 0x00),
        'rgbwGroup4On' => array(0x4B, 0x00),
        'rgbwGroup1Off' => array(0x46, 0x00),
        'rgbwGroup2Off' => array(0x48, 0x00),
        'rgbwGroup3Off' => array(0x4a, 0x00),
        'rgbwGroup4Off' => array(0x4c, 0x00),
        'rgbwBrightnessMax' => array(0x4e, 0x1b),
        'rgbwBrightnessMin' => array(0x4e, 0x02),
        'rgbwDiscoMode' => array(0x4d, 0x00),
        'rgbwDiscoSlower' => array(0x43, 0x00),
        'rgbwDiscoFaster' => array(0x44, 0x00),
        'rgbwAllSetToWhite' => array(0xc2, 0x00),
        'rgbwGroup1SetToWhite' => array(0xc5, 0x00),
        'rgbwGroup2SetToWhite' => array(0xc7, 0x00),
        'rgbwGroup3SetToWhite' => array(0xc9, 0x00),
        'rgbwGroup4SetToWhite' => array(0xcb, 0x00),
        'rgbwSetColorToViolet' => array(0x40, 0x00),
        'rgbwSetColorToRoyalBlue' => array(0x40, 0x10),
        'rgbwSetColorToBabyBlue' => array(0x40, 0x20),
        'rgbwSetColorToAqua' => array(0x40, 0x30),
        'rgbwSetColorToRoyalMint' => array(0x40, 0x40),
        'rgbwSetColorToSeafoamGreen' => array(0x40, 0x50),
        'rgbwSetColorToGreen' => array(0x40, 0x60),
        'rgbwSetColorToLimeGreen' => array(0x40, 0x70),
        'rgbwSetColorToYellow' => array(0x40, 0x80),
        'rgbwSetColorToYellowOrange' => array(0x40, 0x90),
        'rgbwSetColorToOrange' => array(0x40, 0xa0),
        'rgbwSetColorToRed' => array(0x40, 0xb0),
        'rgbwSetColorToPink' => array(0x40, 0xc0),
        'rgbwSetColorToFusia' => array(0x40, 0xd0),
        'rgbwSetColorToLilac' => array(0x40, 0xe0),
        'rgbwSetColorToLavendar' => array(0x40, 0xf0),


        //white Bulb commands
        'whiteAllOn' => array(0x35, 0x00),
        'whiteAllOff' => array(0x39, 0x00),
        'whiteBrightnessUp' => array(0x3c, 0x00),
        'whiteBrightnessDown' => array(0x34, 0x00),
        'whiteAllBrightnessMax' => array(0xb5, 0x00),
        'whiteAllNightMode' => array(0xbb, 0x00),
        'whiteWarmIncrease' => array(0x3e, 0x00),
        'whiteCoolIncrease' => array(0x3f, 0x00),
        'whiteGroup1On' => array(0x38, 0x00),
        'whiteGroup1Off' => array(0x3b, 0x00),
        'whiteGroup2On' => array(0x3d, 0x00),
        'whiteGroup2Off' => array(0x33, 0x00), //fixed!
        'whiteGroup3On' => array(0x37, 0x00),
        'whiteGroup3Off' => array(0x3a, 0x00),
        'whiteGroup4On' => array(0x32, 0x00),
        'whiteGroup4Off' => array(0x36, 0x00),
        'whiteGroup1BrightnessMax' => array(0xb8, 0x00),
        'whiteGroup2BrightnessMax' => array(0xbd, 0x00),
        'whiteGroup3BrightnessMax' => array(0xb7, 0x00),
        'whiteGroup4BrightnessMax' => array(0xb2, 0x00),
        'whiteGroup1NightMode' => array(0xbb, 0x00),
        'whiteGroup2NightMode' => array(0xb3, 0x00),
        'whiteGroup3NightMode' => array(0xba, 0x00),
        'whiteGroup4NightMode' => array(0xb6, 0x00),

    );

    /**
     * @param int $delay
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
    }

    /**
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }


    /**
     * @param int $rgbwActiveGroup
     * @throws Exception
     */
    public function setRgbwActiveGroup($rgbwActiveGroup)
    {
        if ($rgbwActiveGroup < 0 || $rgbwActiveGroup > 4) {
            throw new \Exception('Active RGBW Group must be between or equal 0 to 4, note: 0 means all groups');
        }
        $this->rgbwActiveGroup = $rgbwActiveGroup;
    }


    //the same as setRgbwActiveGroup just to make method invocation easier according to convention
    public function rgbwSetActiveGroup($rgbwActiveGroup)
    {
        $this->setRgbwActiveGroup($rgbwActiveGroup);
    }

    /**
     * @throws Exception
     * @return int
     */
    public function getRgbwActiveGroup()
    {
        return $this->rgbwActiveGroup;
    }


    /**
     * @param int $whiteActiveGroup
     * @throws Exception
     */
    public function setWhiteActiveGroup($whiteActiveGroup)
    {
        if ($whiteActiveGroup < 0 || $whiteActiveGroup > 4) {
            throw new \Exception('Active White Group must be between or equal 0 to 4, note: 0 means all groups');
        }
        $this->whiteActiveGroup = $whiteActiveGroup;
    }

    //the same as setWhiteActiveGroup just to make method invocation easier according to convention
    public function whiteSetActiveGroup($whiteActiveGroup)
    {
        $this->setWhiteActiveGroup($whiteActiveGroup);
    }

    /**
     * @throws Exception
     * @return int
     */
    public function getWhiteActiveGroup()
    {
        return $this->whiteActiveGroup;
    }

    public function __construct($host = '10.10.100.254', $port = 8899)
    {
        $this->host = $host;
        $this->port = $port;
    }


    public function sendCommand(Array $command)
    {
        $command[] = 0x55; //last byte always 0x55, will appended to all commands
        $message = vsprintf(str_repeat('%c', count($command)), $command);
        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
            socket_sendto($socket, $message, strlen($message), 0, $this->host, $this->port);
            socket_close($socket);
            usleep($this->getDelay()); //wait 100ms before sending next command
        }
    }

    public function command($commandName)
    {
        $this->sendCommand($this->commandCodes[$commandName]);
    }

    public function rgbwSendOnToActiveGroup()
    {
        if ($this->getRgbwActiveGroup() > 0) {
            $activeGroupOnCommand = 'rgbwGroup' . $this->getRgbwActiveGroup() . 'On';
            $this->command($activeGroupOnCommand);
            return true;
        }
        $this->rgbwAllOn();
        return true;
    }

    public function whiteSendOnToActiveGroup()
    {
        if ($this->getWhiteActiveGroup() > 0) {
            $activeGroupOnCommand = 'whiteGroup' . $this->getWhiteActiveGroup() . 'On';
            $this->command($activeGroupOnCommand);
            return true;
        }
        $this->whiteAllOn();
        return true;
    }


    public function rgbwAllOn()
    {
        $this->command('rgbwAllOn');
    }

    public function rgbwAllOff()
    {
        $this->command('rgbwAllOff');
    }

    public function rgbwGroup1On()
    {
        $this->command('rgbwGroup1On');
    }

    public function rgbwGroup2On()
    {
        $this->command('rgbwGroup2On');
    }

    public function rgbwGroup3On()
    {
        $this->command('rgbwGroup3On');
    }

    public function rgbwGroup4On()
    {
        $this->command('rgbwGroup4On');
    }

    public function rgbwGroup1Off()
    {
        $this->command('rgbwGroup1Off');
    }

    public function rgbwGroup2Off()
    {
        $this->command('rgbwGroup2Off');
    }

    public function rgbwGroup3Off()
    {
        $this->command('rgbwGroup3Off');
    }

    public function rgbwGroup4Off()
    {
        $this->command('rgbwGroup4Off');
    }

    public function rgbwBrightnessMax()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMax');
    }

    public function rgbwBrightnessMin()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMin');
    }

    public function rgbwAllBrightnessMin()
    {
        $this->setRgbwActiveGroup(0);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMin');
    }

    public function rgbwAllBrightnessMax()
    {
        $this->setRgbwActiveGroup(0);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMax');
    }

    public function rgbwGroup1BrightnessMax()
    {
        $this->setRgbwActiveGroup(1);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMax');
    }

    public function rgbwGroup2BrightnessMax()
    {
        $this->setRgbwActiveGroup(2);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMax');
    }

    public function rgbwGroup3BrightnessMax()
    {
        $this->setRgbwActiveGroup(3);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMax');
    }

    public function rgbwGroup4BrightnessMax()
    {
        $this->setRgbwActiveGroup(4);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMax');
    }

    public function rgbwGroup1BrightnessMin()
    {
        $this->setRgbwActiveGroup(1);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMin');
    }

    public function rgbwGroup2BrightnessMin()
    {
        $this->setRgbwActiveGroup(2);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMin');
    }

    public function rgbwGroup3BrightnessMin()
    {
        $this->setRgbwActiveGroup(3);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMin');
    }

    public function rgbwGroup4BrightnessMin()
    {
        $this->setRgbwActiveGroup(4);
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwBrightnessMin');
    }


    public function rgbwBrightnessPercent($brightnessPercent)
    {
        if ($brightnessPercent < 0 || $brightnessPercent > 100) {
            throw new \Exception('Brightness percent must be between 0 and 100');
        }
        $brightness = 0x02;
        $this->rgbwSendOnToActiveGroup();
        if ($brightnessPercent < 14) {
            $brightness = 0x02;
        }
        if ($brightnessPercent >= 14 && $brightnessPercent < 17) {
            $brightness = 0x03;
        }
        if ($brightnessPercent >= 17 && $brightnessPercent < 21) {
            $brightness = 0x04;
        }
        if ($brightnessPercent >= 21 && $brightnessPercent < 24) {
            $brightness = 0x05;
        }
        if ($brightnessPercent >= 24 && $brightnessPercent < 28) {
            $brightness = 0x06;
        }
        if ($brightnessPercent >= 28 && $brightnessPercent < 32) {
            $brightness = 0x07;
        }
        if ($brightnessPercent >= 32 && $brightnessPercent < 35) {
            $brightness = 0x08;
        }
        if ($brightnessPercent >= 35 && $brightnessPercent < 39) {
            $brightness = 0x09;
        }
        if ($brightnessPercent >= 39 && $brightnessPercent < 42) {
            $brightness = 0xa0;
        }
        if ($brightnessPercent >= 42 && $brightnessPercent < 46) {
            $brightness = 0xb0;
        }
        if ($brightnessPercent >= 46 && $brightnessPercent < 50) {
            $brightness = 0xc0;
        }
        if ($brightnessPercent >= 50 && $brightnessPercent < 53) {
            $brightness = 0xd0;
        }
        if ($brightnessPercent >= 53 && $brightnessPercent < 57) {
            $brightness = 0xe0;
        }
        if ($brightnessPercent >= 57 && $brightnessPercent < 60) {
            $brightness = 0xf0;
        }
        if ($brightnessPercent >= 60 && $brightnessPercent < 64) {
            $brightness = 0x10;
        }
        if ($brightnessPercent >= 64 && $brightnessPercent < 68) {
            $brightness = 0x11;
        }
        if ($brightnessPercent >= 68 && $brightnessPercent < 71) {
            $brightness = 0x12;
        }
        if ($brightnessPercent >= 71 && $brightnessPercent < 75) {
            $brightness = 0x13;
        }
        if ($brightnessPercent >= 75 && $brightnessPercent < 78) {
            $brightness = 0x14;
        }
        if ($brightnessPercent >= 78 && $brightnessPercent < 82) {
            $brightness = 0x15;
        }
        if ($brightnessPercent >= 82 && $brightnessPercent < 86) {
            $brightness = 0x16;
        }
        if ($brightnessPercent >= 86 && $brightnessPercent < 89) {
            $brightness = 0x17;
        }
        if ($brightnessPercent >= 89 && $brightnessPercent < 93) {
            $brightness = 0x18;
        }
        if ($brightnessPercent >= 93 && $brightnessPercent < 96) {
            $brightness = 0x19;
        }
        if ($brightnessPercent >= 96 && $brightnessPercent < 100) {
            $brightness = 0x1a;
        }
        if ($brightnessPercent >= 96 && $brightnessPercent <= 100) {
            $brightness = 0x1b;
        }

        $this->sendCommand(array(0x4e, $brightness));

    }


    public function rgbwDiscoMode()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwDiscoMode');
    }

    public function rgbwDiscoSlower()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwDiscoSlower');
    }

    public function rgbwDiscoFaster()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwDiscoFaster');
    }

    public function rgbwAllSetToWhite()
    {
        $this->command('rgbwAllSetToWhite');
    }

    public function rgbwGroup1SetToWhite()
    {
        $this->command('rgbwGroup1SetToWhite');
    }

    public function rgbwGroup2SetToWhite()
    {
        $this->command('rgbwGroup2SetToWhite');
    }

    public function rgbwGroup3SetToWhite()
    {
        $this->command('rgbwGroup3SetToWhite');
    }

    public function rgbwGroup4SetToWhite()
    {
        $this->command('rgbwGroup4SetToWhite');
    }

    public function rgbwSetColorToViolet()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToViolet');
    }

    public function rgbwSetColorToRoyalBlue()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToRoyalBlue');
    }

    public function rgbwSetColorToBabyBlue()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToBabyBlue');
    }

    public function rgbwSetColorToAqua()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToAqua');
    }

    public function rgbwSetColorToRoyalMint()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToRoyalMint');
    }

    public function rgbwSetColorToSeafoamGreen()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToSeafoamGreen');
    }

    public function rgbwSetColorToGreen()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToGreen');
    }

    public function rgbwSetColorToLimeGreen()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToLimeGreen');
    }

    public function rgbwSetColorToYellow()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToYellow');
    }

    public function rgbwSetColorToYellowOrange()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToYellowOrange');
    }

    public function rgbwSetColorToOrange()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToOrange');
    }

    public function rgbwSetColorToRed()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToRed');
    }

    public function rgbwSetColorToPink()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToPink');
    }

    public function rgbwSetColorToFusia()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToFusia');
    }

    public function rgbwSetColorToLilac()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToLilac');
    }

    public function rgbwSetColorToLavendar()
    {
        $this->rgbwSendOnToActiveGroup();
        $this->command('rgbwSetColorToLavendar');
    }

    public function whiteAllOn()
    {
        $this->command('whiteAllOn');
    }

    public function whiteAllOff()
    {
        $this->command('whiteAllOff');
    }

    public function whiteBrightnessUp()
    {
        $this->whiteSendOnToActiveGroup();
        $this->command('whiteBrightnessUp');
    }

    public function whiteBrightnessDown()
    {
        $this->whiteSendOnToActiveGroup();
        $this->command('whiteBrightnessDown');
    }

    public function whiteAllBrightnessMax()
    {
        $this->command('whiteAllBrightnessMax');
    }

    public function whiteAllBrightnessMin()
    {
        $this->setWhiteActiveGroup(0);
        $this->whiteSendOnToActiveGroup();
        for ($i = 0; $i < 10; $i++) {
            $this->command('whiteBrightnessDown');
        }

    }

    public function whiteAllNightMode()
    {
        $this->command('whiteAllNightMode');
    }


    public function whiteWarmIncrease()
    {
        $this->whiteSendOnToActiveGroup();
        $this->command('whiteWarmIncrease');
    }

    public function whiteCoolIncrease()
    {
        $this->whiteSendOnToActiveGroup();
        $this->command('whiteCoolIncrease');
    }

    public function whiteGroup1On()
    {
        $this->command('whiteGroup1On');
    }

    public function whiteGroup1Off()
    {
        $this->command('whiteGroup1Off');
    }

    public function whiteGroup2On()
    {
        $this->command('whiteGroup2On');
    }

    public function whiteGroup2Off()
    {
        $this->command('whiteGroup2Off');
    }

    public function whiteGroup3On()
    {
        $this->command('whiteGroup3On');
    }

    public function whiteGroup3Off()
    {
        $this->command('whiteGroup3Off');
    }

    public function whiteGroup4On()
    {
        $this->command('whiteGroup4On');
    }

    public function whiteGroup4Off()
    {
        $this->command('whiteGroup4Off');
    }

    public function whiteGroup1BrightnessMax()
    {
        $this->command('whiteGroup1BrightnessMax');
    }

    public function whiteGroup2BrightnessMax()
    {
        $this->command('whiteGroup2BrightnessMax');
    }

    public function whiteGroup3BrightnessMax()
    {
        $this->command('whiteGroup3BrightnessMax');
    }

    public function whiteGroup4BrightnessMax()
    {
        $this->command('whiteGroup4BrightnessMax');
    }

    public function whiteGroup1BrightnessMin()
    {
        $this->setWhiteActiveGroup(1);
        $this->whiteSendOnToActiveGroup();
        for ($i = 0; $i < 10; $i++) {
            $this->command('whiteBrightnessDown');
        }
    }

    public function whiteGroup2BrightnessMin()
    {
        $this->setWhiteActiveGroup(2);
        $this->whiteSendOnToActiveGroup();
        for ($i = 0; $i < 10; $i++) {
            $this->command('whiteBrightnessDown');
        }
    }

    public function whiteGroup3BrightnessMin()
    {
        $this->setWhiteActiveGroup(3);
        $this->whiteSendOnToActiveGroup();
        for ($i = 0; $i < 10; $i++) {
            $this->command('whiteBrightnessDown');
        }
    }

    public function whiteGroup4BrightnessMin()
    {
        $this->setWhiteActiveGroup(4);
        $this->whiteSendOnToActiveGroup();
        for ($i = 0; $i < 10; $i++) {
            $this->command('whiteBrightnessDown');
        }
    }

    public function whiteGroup1NightMode()
    {
        $this->command('whiteGroup1NightMode');
    }

    public function whiteGroup2NightMode()
    {
        $this->command('whiteGroup2NightMode');
    }

    public function whiteGroup3NightMode()
    {
        $this->command('whiteGroup3NightMode');
    }

    public function whiteGroup4NightMode()
    {
        $this->command('whiteGroup4NightMode');
    }

    public function rgbwSetColorHsv(Array $hsvColor)
    {
        $milightColor = $this->hslToMilightColor($hsvColor);
        $activeGroupOnCommand = 'rgbwGroup' . $this->getRgbwActiveGroup() . 'On';
        $this->command($activeGroupOnCommand);
        $this->sendCommand(array(0x40, $milightColor));
    }


    public function rgbwSetColorHexString($color)
    {
        $rgb = $this->rgbHexToIntArray($color);
        $hsl = $this->rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
        $milightColor = $this->hslToMilightColor($hsl);
        $this->rgbwSendOnToActiveGroup();
        $this->sendCommand(array(0x40, $milightColor));
    }


    public function rgbHexToIntArray($hexColor)
    {
        $hexColor = ltrim($hexColor, '#');

        $hexColorLenghth = strlen($hexColor);
        if ($hexColorLenghth != 8 && $hexColorLenghth != 6) {
            throw new \Exception('Color hex code must match 8 or 6 characters');
        }
        if ($hexColorLenghth == 8) {
            $r = hexdec(substr($hexColor, 2, 2));
            $g = hexdec(substr($hexColor, 4, 2));
            $b = hexdec(substr($hexColor, 6, 2));
            if (($r == 0 && $g == 0 && $b == 0) || ($r == 255 && $g == 255 && $b == 255)) {
                throw new \Exception('Color cannot be black or white');
            }
            return array($r, $g, $b);
        }

        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        if (($r == 0 && $g == 0 && $b == 0) || ($r == 255 && $g == 255 && $b == 255)) {
            throw new \Exception('Color cannot be black or white');
        }
        return array($r, $g, $b);
    }


    public function rgbToHsl($r, $g, $b)
    {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;
        $h = '';

        if ($d == 0) {
            $h = $s = 0;
        } else {
            $s = $d / (1 - abs(2 * $l - 1));

            switch ($max) {
                case $r:
                    $h = 60 * fmod((($g - $b) / $d), 6);
                    if ($b > $g) {
                        $h += 360;
                    }
                    break;

                case $g:
                    $h = 60 * (($b - $r) / $d + 2);
                    break;

                case $b:
                    $h = 60 * (($r - $g) / $d + 4);
                    break;
            }
        }
        return array($h, $s, $l);
    }

    public function hslToMilightColor($hsl)
    {
        $color = (256 + 176 - (int)($hsl[0] / 360.0 * 255.0)) % 256;
        return $color + 0xfa;
    }

}




