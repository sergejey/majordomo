<?php

include_once DIR_MODULES . 'plans/plan_component.class.php';

class gauge extends plan_component {

    function __construct($id)
    {
        $this->name=str_replace('.class.php','',basename(__FILE__));
        parent::__construct($id);
    }

    function getProperties()
    {

        $properties = parent::getProperties();
        $properties[] = array(
            'NAME' => 'value',
            'TITLE' => LANG_VALUE,
            'TYPE' => 'linked_property'
        );

        $properties[] = array(
            'NAME' => 'title',
            'TITLE' => LANG_TITLE,
            'TYPE' => 'text',
            'DEFAULT' => 'Data'
        );

        $properties[] = array(
            'NAME' => 'units',
            'TITLE' => 'Units',
            'TYPE' => 'text',
            'DEFAULT' => '\u00B0C'
        );

        $properties[] = array(
            'NAME' => 'value_min',
            'TITLE' => 'Min',
            'TYPE' => 'float',
            'DEFAULT' => '-20'
        );

        $properties[] = array(
            'NAME' => 'value_max',
            'TITLE' => 'Max',
            'TYPE' => 'float',
            'DEFAULT' => '40'
        );

        $properties[] = array(
            'NAME' => 'ring_color',
            'TITLE' => LANG_COLOR.' (ring)',
            'TYPE' => 'rgb',
            'DEFAULT' => 'aqua'
        );
        $properties[] = array(
            'NAME' => 'face_color',
            'TITLE' => LANG_COLOR.' (face)',
            'TYPE' => 'rgb',
            'DEFAULT' => 'lightblue'
        );

        $properties[] = array(
            'NAME' => 'orange_min',
            'TITLE' => 'Orange zone (from)',
            'TYPE' => 'float',
            'DEFAULT' => '-20'
        );

        $properties[] = array(
            'NAME' => 'orange_max',
            'TITLE' => 'Orange zone (to)',
            'TYPE' => 'float',
            'DEFAULT' => '18'
        );

        $properties[] = array(
            'NAME' => 'red_min',
            'TITLE' => 'Red zone (from)',
            'TYPE' => 'float',
            'DEFAULT' => '28'
        );
        $properties[] = array(
            'NAME' => 'red_max',
            'TITLE' => 'Red zone (to)',
            'TYPE' => 'float',
            'DEFAULT' => '40'
        );

        $properties[] = array(
            'NAME' => 'green_min',
            'TITLE' => 'Green zone (from)',
            'TYPE' => 'float',
            'DEFAULT' => '18'
        );
        $properties[] = array(
            'NAME' => 'green_max',
            'TITLE' => 'Green zone (to)',
            'TYPE' => 'float',
            'DEFAULT' => '28'
        );





        $this->processProperties($properties);
        
        return $properties;
    }
    
    function getSVG($attributes)
    {

        $x=(int)$attributes['x'];
        $y=(int)$attributes['y'];

        $data=$this->getData();

        $current_value=(float)$data['value']['VALUE'];


        $width=(int)$attributes['width'];
        if (!$width) $width=200;
        $height=(int)$attributes['height'];
        if (!$height) $height=200;

        $scale=round($width/175,2);

$svg=<<<EOD
<svg x='$x' y='$y'>
<g id="gauge{$this->component_id}GaugeContainer" transform="scale($scale $scale)" width="$width" height="$width">
</g>
</svg>
EOD;

        foreach($data as $k=>$v) {
            $svg=str_replace('%'.$k.'%',$v['VALUE'],$svg);
        }
        return $svg;
    }
    
    function getJavascript($attributes)
    {
        $data=$this->getData();
        $current_value=(float)$data['value']['VALUE'];
        $prop_name=strtolower($data['value']['LINKED_OBJECT'].'.'.$data['value']['LINKED_PROPERTY']);

        $width=(int)$attributes['width'];
        if (!$width) $width=200;

        $scale=round($width/175,2);
        $width = $width/$scale;


        if ($data['orange_min']['VALUE']!=0 || $data['orange_max']['VALUE']!=0) {
            $orange_range='['.(float)$data['orange_min']['VALUE'].','.(float)$data['orange_max']['VALUE'].']';
        } else {
            $orange_range='null';
        }
        if ($data['red_min']['VALUE']!=0 || $data['red_max']['VALUE']!=0) {
            $red_range='['.(float)$data['red_min']['VALUE'].','.(float)$data['red_max']['VALUE'].']';
        } else {
            $red_range='null';
        }
        if ($data['green_min']['VALUE']!=0 || $data['green_max']['VALUE']!=0) {
            $green_range='['.(float)$data['green_min']['VALUE'].','.(float)$data['green_max']['VALUE'].']';
        } else {
            $green_range='null';
        }

        $code = <<<EOD
        
    function componentUpdated{$this->component_id}(property_name,property_value) {
    
        if (property_name.toLowerCase()=='$prop_name') {
          gauge{$this->component_id}.redraw(property_value);
        }
    }
    
function Gauge{$this->component_id}(placeholderName, configuration)
{
	this.placeholderName = placeholderName;

	var self = this; // for internal d3 functions

	this.configure = function(configuration)
	{
		this.config = configuration;

		this.config.diameter = this.config.diameter * 0.9;

		this.config.raduis = this.config.diameter * 0.97 / 2;
		this.config.cx = this.config.diameter / 2;
		this.config.cy = this.config.diameter / 2;

		this.config.min = undefined != configuration.min ? configuration.min : 0;
		this.config.max = undefined != configuration.max ? configuration.max : 100;
		this.config.range = this.config.max - this.config.min;

		this.config.majorTicks = configuration.majorTicks || 5;
		this.config.minorTicks = configuration.minorTicks || 2;

		this.config.greenColor 	= configuration.greenColor || "#109618";
		this.config.orangeColor = configuration.orangeColor || "#FF9900";
		this.config.redColor 	= configuration.redColor || "#DC3912";

		this.config.transitionDuration = configuration.transitionDuration || 500;
	}

    this.render = function()
    {
        this.gauge = d3.select("#" + this.placeholderName)
        .attr("class", "gauge")
        .attr("width", this.config.diameter)
        .attr("height", this.config.diameter)
        
        this.gauge.append("svg:circle")
        .attr("cx", this.config.cx)
        .attr("cy", this.config.cy)
        .attr("r", this.config.raduis)
        .style("fill", this.config.ringColor)
        .style("stroke", "#000")
        .style("stroke-width", "0.5px");

        this.gauge.append("svg:circle")
        .attr("cx", this.config.cx)
        .attr("cy", this.config.cy)
        .attr("r", 0.9 * this.config.raduis)
        .style("fill", this.config.faceColor)
        .style("stroke", "#e0e0e0")
        .style("stroke-width", "2px");

        if(this.config.greenZoneRange)
            for (var index in this.config.greenZones)
                this.drawBand(this.config.greenZones[index].from, this.config.greenZones[index].to, self.config.greenColor);

        if(this.config.orangeZoneRange)
    		for (var index in this.config.orangeZones)
    			this.drawBand(this.config.orangeZones[index].from, this.config.orangeZones[index].to, self.config.orangeColor);

        if(this.config.redZoneRange)
    		for (var index in this.config.redZones)
    			this.drawBand(this.config.redZones[index].from, this.config.redZones[index].to, self.config.redColor);

        if (undefined != this.config.label)
        {
            var fontSize = Math.round(this.config.diameter / 9);
            this.gauge.append("svg:text")
            .attr("x", this.config.cx)
            .attr("y", this.config.cy / 2 + fontSize / 2)
            .attr("dy", fontSize / 2)
            .attr("text-anchor", "middle")
            .text(this.config.label)
            .style("font-size", fontSize + "px")
            .style("fill", "#333")
            .style("stroke-width", "0px");
        }
        if (this.config.units)
        {
            var fontSize = Math.round(this.config.diameter / 11);
            this.gauge.append("svg:text")
            .attr("x", this.config.cx)
            .attr("y", this.config.cy + fontSize)
            .attr("dy", fontSize)
            .attr("text-anchor", "middle")
            .text(this.config.units)
            .style("font-size", fontSize + "px")
            .style("fill", "#333")
            .style("stroke-width", "0px");
        }


        var fontSize = Math.round(this.config.diameter / 16);
        var majorDelta = this.config.range / (this.config.majorTicks - 1);
        for (var major = this.config.min; major <= this.config.max; major += majorDelta)
        {
            var minorDelta = majorDelta / this.config.minorTicks;
            for (var minor = major + minorDelta; minor < Math.min(major + majorDelta, this.config.max); minor += minorDelta)
            {
                var point1 = this.valueToPoint(minor, 0.75);
                var point2 = this.valueToPoint(minor, 0.85);

                this.gauge.append("svg:line")
                .attr("x1", point1.x)
                .attr("y1", point1.y)
                .attr("x2", point2.x)
                .attr("y2", point2.y)
                .style("stroke", "#666")
                .style("stroke-width", "1px");
            }

            var point1 = this.valueToPoint(major, 0.7);
            var point2 = this.valueToPoint(major, 0.85);

            this.gauge.append("svg:line")
            .attr("x1", point1.x)
            .attr("y1", point1.y)
            .attr("x2", point2.x)
            .attr("y2", point2.y)
            .style("stroke", "#333")
            .style("stroke-width", "2px");

            if (major == this.config.min || major == this.config.max)
            {
                var point = this.valueToPoint(major, 0.63);

                this.gauge.append("svg:text")
                .attr("x", point.x)
                .attr("y", point.y)
                .attr("dy", fontSize / 2)
                .attr("text-anchor", major == this.config.min ? "start" : "end")
                .text(major)
                .style("font-size", (fontSize+4) + "px")
                .style("fill", "#333")
                .style("stroke-width", "0px");
            }
        }

        var pointerContainer = this.gauge.append("svg:g").attr("class", "pointerContainer");

        var midValue = (this.config.min + this.config.max) / 2;

        var pointerPath = this.buildPointerPath(midValue);

        var pointerLine = d3.line()
        .x(function(d) { return d.x })
        .y(function(d) { return d.y })
        .curve(d3.curveBasis);

        pointerContainer.selectAll("path")
        .data([pointerPath])
        .enter()
        .append("svg:path")
        .attr("d", pointerLine)
        .style("fill", "#dc3912")
        .style("stroke", "#c63310")
        .style("fill-opacity", 0.7)

        pointerContainer.append("svg:circle")
        .attr("cx", this.config.cx)
        .attr("cy", this.config.cy)
        .attr("r", 0.12 * this.config.raduis)
        .style("fill", "#4684EE")
        .style("stroke", "#666")
        .style("opacity", 1);

        var fontSize = Math.round(this.config.diameter / 10);
        pointerContainer.selectAll("text")
        .data([midValue])
        .enter()
        .append("svg:text")
        .attr("x", this.config.cx)
        .attr("y", this.config.diameter - this.config.cy / 4 - fontSize)
        .attr("dy", fontSize)
        .attr("text-anchor", "middle")
        .style("font-size", (fontSize+4) + "px")
        .style("fill", "#000")
        .style("stroke-width", "1px");

        this.redraw(this.config.min, 0);
    }

	this.buildPointerPath = function(value)
	{
		var delta = this.config.range / 13;

		var head = valueToPoint(value, 0.85);
		var head1 = valueToPoint(value - delta, 0.12);
		var head2 = valueToPoint(value + delta, 0.12);

		var tailValue = value - (this.config.range * (1/(270/360)) / 2);
		var tail = valueToPoint(tailValue, 0.28);
		var tail1 = valueToPoint(tailValue - delta, 0.12);
		var tail2 = valueToPoint(tailValue + delta, 0.12);

		return [head, head1, tail2, tail, tail1, head2, head];

		function valueToPoint(value, factor)
		{
			var point = self.valueToPoint(value, factor);
			point.x -= self.config.cx;
			point.y -= self.config.cy;
			return point;
		}
	}

    this.drawBand = function(start, end, color)
    {
        if (0 >= end - start) return;

        this.gauge.append("svg:path")
        .style("fill", color)
        .attr("d", d3.arc()
        .startAngle(this.valueToRadians(start))
        .endAngle(this.valueToRadians(end))
        .innerRadius(0.65 * this.config.raduis)
        .outerRadius(0.85 * this.config.raduis))
        .attr("transform", function() { return "translate(" + self.config.cx + ", " + self.config.cy + ") rotate(270)" });
    }

    this.redraw = function(value, transitionDuration)
    {
        var pointerContainer = this.gauge.select(".pointerContainer");

        pointerContainer.selectAll("text").text(Math.round(value));

        var pointer = pointerContainer.selectAll("path");
        pointer.transition()
        .duration(undefined != transitionDuration ? transitionDuration : this.config.transitionDuration)

        .attrTween("transform", function()
        {
            var pointerValue = value;
            if (value > self.config.max) pointerValue = self.config.max + 0.02*self.config.range;
            else if (value < self.config.min) pointerValue = self.config.min - 0.02*self.config.range;
            var targetRotation = (self.valueToDegrees(pointerValue) - 90);
            var currentRotation = self._currentRotation || targetRotation;
            self._currentRotation = targetRotation;

            return function(step)
            {
                var rotation = currentRotation + (targetRotation-currentRotation)*step;
                return "translate(" + self.config.cx + ", " + self.config.cy + ") rotate(" + rotation + ")";
            }
        });
    }

	this.valueToDegrees = function(value)
	{
		return value / this.config.range * 270 - (this.config.min / this.config.range * 270 + 45);
	}

	this.valueToRadians = function(value)
	{
		return this.valueToDegrees(value) * Math.PI / 180;
	}

	this.valueToPoint = function(value, factor)
	{
		return {x: this.config.cx - this.config.raduis * factor * Math.cos(this.valueToRadians(value)),
				y: this.config.cy - this.config.raduis * factor * Math.sin(this.valueToRadians(value)) 		};
	}

	// initialization
	this.configure(configuration);
}

function createGauge{$this->component_id}(name, label, min, max,majorTicks,minorTicks,ringColor,faceColor,diameter,orangeZoneRange,redZoneRange,greenZoneRange,units)
{
    var config =
    {
        diameter: diameter,
        label: label,
        min: min,
        max: max ,
        majorTicks:majorTicks,
        minorTicks:minorTicks,

        ringColor:ringColor,
        faceColor:faceColor,
        orangeZoneRange: undefined != orangeZoneRange ? orangeZoneRange : false,
        redZoneRange: undefined != redZoneRange ? redZoneRange : false,
        greenZoneRange: undefined != greenZoneRange ? greenZoneRange : false,
        units: undefined != units ? units : false
    }

    var range = config.max - config.min;
    if(config.orangeZoneRange)
    {
        var startOrange=config.orangeZoneRange[0]
        var endOrange=config.orangeZoneRange[1]
        config.orangeZones = [{ from: startOrange, to: endOrange }];
    }
    if(config.redZoneRange)
    {
        var startRed=config.redZoneRange[0]
        var endRed=config.redZoneRange[1]
        config.redZones = [{ from: startRed, to: endRed }];
    }
    if(config.greenZoneRange)
    {
        var startGreen=config.greenZoneRange[0]
        var endGreen=config.greenZoneRange[1]
        config.greenZones = [{ from: startGreen, to: endGreen }];
    }

    gauge{$this->component_id} = new Gauge{$this->component_id}(name + "GaugeContainer", config);
    gauge{$this->component_id}.render();
    gauge{$this->component_id}.redraw($current_value);
}
    
    createGauge{$this->component_id}("gauge{$this->component_id}", "{$data['title']['VALUE']}",{$data['value_min']['VALUE']},{$data['value_max']['VALUE']},5,10,"{$data['ring_color']['VALUE']}","{$data['face_color']['VALUE']}",{$width},{$orange_range},{$red_range},{$green_range},"{$data['units']['VALUE']}");
    
EOD;
        return $code;
    }
}