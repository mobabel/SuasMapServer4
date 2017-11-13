//knob properties

function knob(id, parentNode, cx, cy, r, value1, angle1, value2, angle2, steps, startVal,lengthIndicator, knobColor, indicStyles, scaleSymbol, scaleDist, gradient, sizeGradient, functionToCall, mouseMoveBool) {
	var nrArguments = 20;
	var createKnob= true;
	if (arguments.length == nrArguments && steps != 1) {
		this.id = id; //internal id
		this.parentNode = parentNode; // //the parentNode, string or nodeReference
		this.cx = cx; //center position x of the knob
		this.cy = cy; //center position y of the knob
		this.r = r; //radius (size) of the knob
		this.initialR = r;//Size of the initially defined radius, to calculate the scale factor for the moveTo -Function
		this.value1 = value1; //smallest value of the knob
		this.angle1 = angle1; //position of the value1  (degree, 0-360: 0=top, 90=right, 180=bottom, 270=left)
		this.value2 = value2; //highest value of the knob
		this.angle2 = angle2; //position of value2, defines the size of the segment of the circle
		this.steps = steps; //0=stepless, 1,2,3,... = number of steps between lowest and highest value
		this.startVal = startVal ; //Initial position of the knob (degree)
		this.value = startVal; //actual value of the knob
		this.lengthIndicator = lengthIndicator; //length of the indicator line
		this.knobColor= knobColor; //Style of the knob
		this.indicStyles = indicStyles; //Style of the indicator line
		this.scaleSymbol = scaleSymbol; //id of the scaling symbols around the knob
		this.scaleDist = scaleDist; //Distance of the scaling symbols from the knob inner circle
		this.gradient = gradient; //id of a predefined gradient
		this.outR = this.r+sizeGradient; //size of the gradient (outer circle)
		this.functionToCall = functionToCall; //Callback function
		this.mouseMoveBool = mouseMoveBool; //boolean value for feedback, 1=immediate feedback, 0=after action
		this.knobStatus=0;
		rangeDegree=this.angle2 - this.angle1;
		rangeValues = this.value2 - this.value1;
		this.valuePerDegree=rangeValues/rangeDegree;
		
		//Converts the angle1 & angle2 values into positive radians
		if (this.steps != 0) {
			this.stepAngle= (this.angle2-this.angle1)/(this.steps-1);
			this.stepValue= (this.value2-this.value1)/(this.steps-1);
			this.stepRadians=DegToRad(this.stepAngle);
			this.firstRadians=DegToRad(this.angle1);
			if (this.firstRadians <0) {
				this.firstRadians = this.firstRadians + 2*Math.PI;
			}
			this.lastRadians=DegToRad(this.angle2);
			if (this.lastRadians <0) {
				this.lastRadians = this.lastRadians + 2*Math.PI;
			}
		}
	}
	else {
		if (arguments.length != nrArguments) {
			createKnob = false;
			alert("Error in knob ("+id+") constructor: wrong nr of arguments! You have to pass over "+nrArguments+" parameters.");
		}
		//Steps must not be equal 1!
		else {
			alert("Error in knob ("+id+") constructor: steps value equal 1! The value should be 0 or bigger than 1!");
		}
	}
	if (createKnob) {
		this.createKnob();
	}
	else {
		alert("Could not create knob with id '"+id+"' due to errors in the constructor parameters");
	}
}
		
//create knob
knob.prototype.createKnob=function() {
	var result =  this.testParent();
	if (result) {
			
		//lower circle (shadow --> 3D-effect), consists of a lower circle containing the gradient and an upper transparent one
		this.visKnobGrad = document.createElementNS(svgNS,"circle");
		this.visKnobGrad.setAttributeNS(null,"cx",this.cx);
		this.visKnobGrad.setAttributeNS(null,"cy",this.cy);
		this.visKnobGrad.setAttributeNS(null,"r",this.outR);
		this.visKnobGrad.setAttributeNS(null,"fill",this.gradient);
		this.parentGroup.appendChild(this.visKnobGrad);
		this.visKnobShad = document.createElementNS(svgNS,"circle");
		this.visKnobShad.setAttributeNS(null,"cx",this.cx);
		this.visKnobShad.setAttributeNS(null,"cy",this.cy);
		this.visKnobShad.setAttributeNS(null,"r",this.outR);
		this.visKnobShad.setAttributeNS(null,"fill",this.knobColor);
		this.visKnobShad.setAttributeNS(null,"opacity","0.6");
		this.parentGroup.appendChild(this.visKnobShad);
		//circle
		this.visKnob = document.createElementNS(svgNS,"circle");
		this.visKnob.setAttributeNS(null,"cx",this.cx);
		this.visKnob.setAttributeNS(null,"cy",this.cy);
		this.visKnob.setAttributeNS(null,"r",this.r);
		this.visKnob.setAttributeNS(null,"fill",this.knobColor);
		this.visKnob.setAttributeNS(null,"pointer-events","none");
		this.parentGroup.appendChild(this.visKnob);
		//indicator line
		this.indicX = this.cx;
		this.indicY1 = this.cy - this.r;
		this.indicY2 = this.cy - this.r + this.lengthIndicator;
		this.visIndic = document.createElementNS(svgNS,"line");
		this.visIndic.setAttributeNS(null, "x1", this.indicX)
		this.visIndic.setAttributeNS(null, "y1", this.indicY1)
		this.visIndic.setAttributeNS(null, "x2", this.indicX)
		this.visIndic.setAttributeNS(null, "y2", this.indicY2)
		for (var attrib in this.indicStyles) {
			this.visIndic.setAttributeNS(null,attrib,this.indicStyles[attrib]);
		}
		var myTransformString = "rotate("+this.startVal+","+this.cx+","+this.cy+")";
		this.visIndic.setAttributeNS(null,"transform",myTransformString);
		this.parentGroup.appendChild(this.visIndic);
		//Invisible Circle, for mouse events
		this.invisCircle = document.createElementNS(svgNS,"circle");
		this.invisCircle.setAttributeNS(null,"cx",this.cx);
		this.invisCircle.setAttributeNS(null,"cy",this.cy);
		this.invisCircle.setAttributeNS(null,"r",(this.outR+15));
		this.invisCircle.setAttributeNS(null,"fill","black");
		this.invisCircle.setAttributeNS(null,"opacity","0");
		this.invisCircle.addEventListener("mousedown",this, false);
		this.parentGroup.appendChild(this.invisCircle);
		
		//Indicator symbols around the knob, just in case that the knob is divided into steps
		if (this.steps !=0) {
			this.groupSymbol = document.createElementNS(svgNS, "g")
			this.myAngleArray = new Array();
			for (i = 0; i < this.steps; i++) {
				var radAngle= (this.firstRadians+(i*this.stepRadians))-Math.PI/2;
				var dist = this.r+this.scaleDist;
				var degAngle = RadToDeg(radAngle)+90;
				var x= toRectX(radAngle,dist)//+this.cx;
				var y= toRectY(radAngle,dist)//+this.cy;
				if (degAngle < 0) {
					degAngle = degAngle + 360;
				}
				this.myAngleArray[i] = degAngle;
				visSymbol = document.createElementNS(svgNS,"use");
				visSymbol.setAttributeNS(xlinkNS,"xlink:href", "#"+this.scaleSymbol);
				var myTransformString = "rotate("+degAngle+","+x+","+y+") translate("+x+","+y+")";
				visSymbol.setAttributeNS(null,"transform",myTransformString);
				this.groupSymbol.appendChild(visSymbol);
			}
			//All the symbols are grouped --> necessary to use the moveTo-Function
			this.groupSymbol.setAttributeNS(null,"transform", "translate("+this.cx+","+this.cy+")");
			this.parentGroup.appendChild(this.groupSymbol);
		}
		
	}
	else {
		alert("could not create or reference 'parentNode' of knob with id '"+this.id+"'");			
	}
}
	

//tests if parent group exists
knob.prototype.testParent = function() {
    //test if of type object
    var nodeValid = false;
    if (typeof(this.parentNode) == "object") {
    	if (this.parentNode.nodeName == "svg" || this.parentNode.nodeName == "g" || this.parentNode.nodeName == "svg:svg" || this.parentNode.nodeName == "svg:g") {
    		this.parentGroup = this.parentNode;
    		nodeValid = true;
    	}
    }
    else if (typeof(this.parentNode) == "string") { 
    	//first tests if button group exists
    	if (!document.getElementById(this.parentNode)) {
        	this.parentGroup = document.createElementNS(svgNS,"g");
        	this.parentGroup.setAttributeNS(null,"id",this.parentNode);
        	document.documentElement.appendChild(this.parentGroup);
        	nodeValid = true;
   		}
   		else {
       		this.parentGroup = document.getElementById(this.parentNode);
       		nodeValid = true;
   		}
   	}
   	return nodeValid;
}

//remove all knob elements
knob.prototype.removeKnob = function() {
	this.parentGroup.removeChild(this.visIndic);
	this.parentGroup.removeChild(this.visKnob);
	this.parentGroup.removeChild(this.visKnobShad);
	this.parentGroup.removeChild(this.visKnobGrad);
	this.parentGroup.removeChild(this.groupSymbol);
	this.parentGroup.removeChild(this.invisCircle);
}

//handle events
knob.prototype.handleEvent = function(evt) {
	this.drag(evt);
}

//drag knob
knob.prototype.drag = function(evt) {
	if (evt.type == "mousedown" || (evt.type == "mousemove" && this.knobStatus == 1) || evt.type == "onclick") {
		//Calculates the angle between the line MousePosition-CenterPoint and positive x-coordinate
		var myMapApp = new mapApp();
		var coordPoint = myMapApp.calcCoord(evt);
		var xdiff = coordPoint.x.toFixed(0) - this.cx;
		var ydiff = coordPoint.y.toFixed(0) - this.cy;
		var azimuth = toPolarDir(xdiff,ydiff);
		var indicXOut = toRectX(azimuth,this.r) 
		var indicXIn = toRectX(azimuth,this.r-this.lengthIndicator) 
		var indicYOut = toRectY(azimuth,this.r) 
		var indicYIn = toRectY(azimuth,this.r-this.lengthIndicator) 
		var rotation = RadToDeg(azimuth)+90;
		if (rotation < 0) {
			rotation = rotation + 360; //Just positive degree values
		}
		
		if (evt.type == "mousedown") {
			this.knobStatus = 1;
			document.documentElement.addEventListener("mousemove",this,false);
			document.documentElement.addEventListener("mouseup",this,false);
		}
		if (this.steps != 0) { //just the values on a step are valid
			
			var near = 360;
			var currentRotation = 0;
			for (i in this.myAngleArray) {
				if ( Math.abs(this.myAngleArray[i]-rotation) < near) {
					near = Math.abs(this.myAngleArray[i]-rotation);
					currentRotation = this.myAngleArray[i];
				}
			}
			
			var rotateString = "rotate("+currentRotation+","+this.cx+","+this.cy+")";
			rangeDegree=this.angle2 - this.angle1;
			rangeValues = this.value2 - this.value1;
			valuePerDegree=rangeValues/rangeDegree;
			this.value = this.value1 + (currentRotation-this.angle1)*this.valuePerDegree;
			this.visIndic.setAttributeNS(null,"transform",rotateString);
			this.fireFunction();
		}	
		
		
		else { //case continous, any value is possible inside the given values (value1 and value2)
			if ( rotation < this.angle1) { //Mouse position is below the lowest angle
				var rotateString = "rotate("+this.angle1+","+this.cx+","+this.cy+")";
				this.visIndic.setAttributeNS(null,"transform",rotateString);
				this.value = this.value1;
				this.fireFunction();
			}
			else if ( rotation > this.angle2)  { //Mouse position is above the highest angle
				var rotateString = "rotate("+this.angle2+","+this.cx+","+this.cy+")";
				this.visIndic.setAttributeNS(null,"transform",rotateString);
				this.value = this.value2;
				this.fireFunction();
			}
			else  {
				var rotateString = "rotate("+rotation+","+this.cx+","+this.cy+")";
				this.visIndic.setAttributeNS(null,"transform",rotateString);
				this.value = this.value1 + (rotation - this.angle1)*this.valuePerDegree;
				this.fireFunction();
			}
		}
	}
	
	if (evt.type == "mouseup") {
		if (this.knobStatus == 1) {
			this.knobStatus = 2;
			document.documentElement.removeEventListener("mousemove",this,false);
			document.documentElement.removeEventListener("mouseup",this,false);
		}
		this.knobStatus = 0;
		
	}

}







//this code is executed, after the knob is released
//you can use switch/if to detect which slider was used (use this.id) for that
knob.prototype.fireFunction = function() {
	if (this.knobStatus == 1 && this.mouseMoveBool == true) {
		if (typeof(this.functionToCall) == "function") {
			this.functionToCall("change",this.id,this.value);
		}
		if (typeof(this.functionToCall) == "object") {
			this.functionToCall.getKnobVal("change",this.id,this.value);
		}
		if (typeof(this.functionToCall) == undefined) {
			return;
		}
	}
	if (this.knobStatus == 2) {
		if (typeof(this.functionToCall) == "function") {
			this.functionToCall("release",this.id,this.value);
		}
		if (typeof(this.functionToCall) == "object") {
			this.functionToCall.getKnobVal("release",this.id,this.value);
		}
		if (typeof(this.functionToCall) == undefined) {
			return;
		}
	}
}

//Get the knob value
knob.prototype.getValue = function() {
	return this.value;
}

//this is to set the value from other scripts
knob.prototype.setValue = function(value,fireFunction) {
	//has to be completed
}


//Moves the knob to a specified position and resizes it
knob.prototype.moveTo = function(cx, cy, r) {
	this.cx = cx;
	this.cy = cy;
	this.r = r;
	scale = this.r/this.initialR;
	//Shadow over Gradient
	this.visKnobShad.setAttributeNS(null,"cx",this.cx);
	this.visKnobShad.setAttributeNS(null,"cy",this.cy);
	this.visKnobShad.setAttributeNS(null,"r",(this.r*1.1));
	//Gradient
	this.visKnobGrad.setAttributeNS(null,"cx",this.cx);
	this.visKnobGrad.setAttributeNS(null,"cy",this.cy);
	this.visKnobGrad.setAttributeNS(null,"r",(this.r*1.1));
	//Knob
	this.visKnob.setAttributeNS(null,"cx",this.cx);
	this.visKnob.setAttributeNS(null,"cy",this.cy);
	this.visKnob.setAttributeNS(null,"r",this.r);
	//InvisibleCircle around knob
	this.invisCircle.setAttributeNS(null,"cx",this.cx);
	this.invisCircle.setAttributeNS(null,"cy",this.cy);
	this.invisCircle.setAttributeNS(null,"r",(this.outR+15));
	//Indicator
	this.indicX = this.cx;
	this.indicY1 = this.cy - this.r;
	this.indicY2 = this.cy - this.r + this.r*0.3;
	this.visIndic.setAttributeNS(null, "x1", this.cx)
	this.visIndic.setAttributeNS(null, "y1", this.indicY1)
	this.visIndic.setAttributeNS(null, "x2", this.cx)
	this.visIndic.setAttributeNS(null, "y2", this.indicY2)
	var myTransformString = "rotate("+this.startVal+","+this.cx+","+this.cy+")";
	this.visIndic.setAttributeNS(null,"transform",myTransformString);
	//Dots
	this.groupSymbol.setAttributeNS(null,"transform", "translate("+this.cx+","+this.cy+") scale("+scale+")");
	}
