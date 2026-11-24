<?php
/**
 * Compatibility layer: pChart 1 API on top of the bundled pChart 2 classes.
 * Used by the legacy pChart/analog.php script.
 */
include_once(dirname(__FILE__) . '/class/pDraw.class.php');
include_once(dirname(__FILE__) . '/class/pImage.class.php');

class pChart extends pImage
{
    var $DataSetObject = null;
    var $legacyPalette = array();

    function setDataSetObject($dataSet)
    {
        $this->DataSetObject = $dataSet;
        $this->setDataSet($dataSet);
    }

    function setColorPalette($index, $r, $g, $b)
    {
        $this->legacyPalette[(int)$index] = array(
            'R' => (int)$r,
            'G' => (int)$g,
            'B' => (int)$b,
            'Alpha' => 100,
        );
    }

    function setLegacyFontProperties($font, $size = 8)
    {
        if (is_array($font)) {
            return parent::setFontProperties($font);
        }
        parent::setFontProperties(array('FontName' => $font, 'FontSize' => (int)$size));
    }

    function drawGraphAreaGradient($r, $g, $b, $alpha = 50, $target = null)
    {
        parent::drawFilledRectangle(0, 0, $this->getWidth(), $this->getHeight(), array(
            'R' => (int)$r,
            'G' => (int)$g,
            'B' => (int)$b,
            'Alpha' => (int)$alpha,
        ));
    }

    function clearShadow()
    {
        $this->setShadow(false);
    }

    function drawLegacyScale($data, $description, $scale = null, $r = 80, $g = 80, $b = 80)
    {
        parent::drawScale(array(
            'R' => (int)$r,
            'G' => (int)$g,
            'B' => (int)$b,
            'LabelR' => (int)$r,
            'LabelG' => (int)$g,
            'LabelB' => (int)$b,
            'GridR' => 230,
            'GridG' => 230,
            'GridB' => 230,
            'DrawXLines' => false,
        ));
    }

    function drawGrid()
    {
        // Grid is drawn by drawScale() in pChart 2.
    }

    function setLineStyle()
    {
        // No direct equivalent in pChart 2; safe to ignore for legacy scripts.
    }

    function drawTitle($x, $y, $text, $r = 150, $g = 150, $b = 150)
    {
        parent::drawText((int)$x, (int)$y, (string)$text, array(
            'R' => (int)$r,
            'G' => (int)$g,
            'B' => (int)$b,
        ));
    }

    function drawPlotGraph($data, $description, $size = null)
    {
        $this->prepareChart();
        parent::drawPlotChart(array(
            'PlotSize' => $size ? (int)$size : 2,
            'OverrideColors' => $this->legacyPalette,
        ));
    }

    function drawCubicCurve()
    {
        $this->prepareChart();
        parent::drawSplineChart(array('OverrideColors' => $this->legacyPalette));
    }

    function drawBarGraph()
    {
        $this->prepareChart();
        parent::drawBarChart(array('OverrideColors' => $this->legacyPalette));
    }

    function drawLineGraph()
    {
        $this->prepareChart();
        parent::drawLineChart(array('OverrideColors' => $this->legacyPalette));
    }

    function drawFilledLineGraph()
    {
        $this->prepareChart();
        parent::drawAreaChart(array('OverrideColors' => $this->legacyPalette));
    }

    function drawFilledCubicCurve()
    {
        $this->prepareChart();
        parent::drawFilledSplineChart(array('OverrideColors' => $this->legacyPalette));
    }

    function setLabel($data, $description, $serie, $index, $label, $r = 221, $g = 230, $b = 174)
    {
        parent::writeLabel((string)$serie, (int)$index, array(
            'R' => (int)$r,
            'G' => (int)$g,
            'B' => (int)$b,
        ));
    }

    function AddBorder($size = 1, $r = 200, $g = 200, $b = 200)
    {
        parent::drawRectangle(0, 0, $this->getWidth() - 1, $this->getHeight() - 1, array(
            'R' => (int)$r,
            'G' => (int)$g,
            'B' => (int)$b,
        ));
    }

    private function prepareChart()
    {
        if ($this->DataSetObject) {
            $this->setDataSet($this->DataSetObject);
        }
    }
}
