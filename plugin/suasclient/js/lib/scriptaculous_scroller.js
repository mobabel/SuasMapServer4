/*
http://www.jools.net/projects/javascript/scrollable-divs/
Class: Scroller
	Adds a scrollbar to a specific div. The scrollbar is implemented using a Script.aculo.us slider.
	The class reparents the original div, creates a slider and ties the reparented div to the slider,
	setting any properties necessary on the divs to make it all work. The scrollbar can be styled using
	CSS. The track of the scrollbar has class 'scroll-track', the thumb has class 'scroll-handle'.
	
properties:
	myIndex - an integer used to generate a unique ID for use in, for example, div ids.
	outerBox - the div that holds the scrollpane + scrollbar
	innerBox - the div that holds the scrollpane
	innerHeight - the height of the inner box.
	viewportHeight - the height of the view onto the scrolled div.
	track - a div that holds the script.aculo.us slider (the scrollbar)
	trackHeight - the height of the slider
	handle - the div for the 'thumb' of the scrollbar
	handleHeight - the height of the thumb
	slider - the script.aculo.us slider itself
	ieDecreaseBy - a fudge factor used when calculating the width of innerBox
	
*/
var Scroller = Class.create();

/*
property: Scroller.ids
	A cache of Scrollers indexed by the ID of the original div.
 */
Scroller.ids = new Object();

/*
property: Scroller.i
	A unique ID generator.
 */
Scroller.i = 0;

Scroller.prototype = {
	/*
	constructor: initialize	
		Wrap the passed div in a scrollpane.
	
	parameters:	
		el - the div to add a scrollbar to.
	 */
  initialize: function(el) {
	  this.outerBox = el;
	  this.decorate();
  },
  
  /*
  function: decorate  
  	create the necessary elements to implement the scrollbar and wire up events.
   */
  decorate: function() {
	Element.makePositioned(this.outerBox); // Fix IE
	
	// Seed a unique ID
	Scroller.i = Scroller.i + 1;
	this.myIndex = Scroller.i;
	
	//wrap the existing content in an intermediate inner box
	this.innerBox = document.createElement("DIV");
	this.innerBox.className="scroll-innerBox";
        this.innerBox.id="debugMessageScroll";
	this.innerBox.name="debugMessageScroll";
	Element.makePositioned(this.innerBox);	// Fix IE
	this.innerBox.style.cssFloat=this.innerBox.style.styleFloat='left';	// Need the scrollbar to appear next to the scrollpane
	this.innerBox.innerHTML = this.outerBox.innerHTML;
	this.outerBox.innerHTML="";
	this.outerBox.appendChild(this.innerBox);
	
	//now build a slider, and put it next to the inner box
	this.track=document.createElement("DIV");
	this.track.className="scroll-track";
	Element.makePositioned(this.track);	// Fix IE
	this.track.style.cssFloat=this.track.style.styleFloat='left';	// Need the scrollbar to appear next to the scrollpane
	this.track.id="scroll-track"+Scroller.i;
	this.track.style.display = 'none';
	
	// Save the size of our little window onto the content
	this.viewportHeight = this.getHeight(this.outerBox);
	
	this.trackHeight = this.viewportHeight;
	this.track.style.height=this.trackHeight+"px";
	
	// Now create the 'thumb' of the scrollbar
	this.handle=document.createElement("DIV");
	this.handle.className="scroll-handle";
	this.handle.id="scroll-handle"+Scroller.i;
	
	// Height of thumb is proportional, but minimum height is 10px
	this.innerHeight=this.getHeight(this.innerBox);			
	if (this.innerHeight > 0)
		this.handleHeight = Math.round((this.trackHeight * this.viewportHeight) / this.innerHeight);
	else
		this.handleHeight = 10;
	if(this.handleHeight < 10) this.handleHeight = 10;
	this.handle.style.height = this.handleHeight + "px";
	
	this.track.appendChild(this.handle);
	this.outerBox.appendChild(this.track);
	
	//turn off scrolling on the outer div
	this.outerBox.style.overflow="hidden";
	
	//layout complete.  if you exit here, you get nice looking box with an inactive scroll bar.
	//create the slider functionality
	this.slider = new Control.Slider(this.handle.id, this.track.id, {axis:'vertical',
															minimum:0,
															maximum:this.trackHeight});
	
	//scroll set up is complete. Work through the actual scrolling fuctions
	//run the same function while scrollin, and at the end of scrolling (handles jumping up/down)
	this.slider.options.onSlide = this.slider.options.onChange = this.onChange.bind(this);
	
	// Give the browser 10ms to render the DIVs and resolve their geometry.
    setTimeout(this.resetScrollbar.bind(this, false), 10);
  },
  
  /*
  function: resetScrollbar  
  	Re-calculate the geometry of the scrollbar. Typically called from an event handler.
	
	args:	
		full - if true, re-calculate the geometry of the scrollpane as well as the scrollbar.
   */
  resetScrollbar: function(full) {
	// If its a full reset, set scrollbar to invisible.
	if (full)
		this.track.style.display='none';
	
	//need to get height of innerBox.
	this.innerHeight = this.getHeight(this.innerBox);
	
	this.viewportHeight = this.getHeight(this.outerBox);// Need to refetch height of outerbox too since it might've stretched.
	this.trackHeight = this.viewportHeight;				// One day trackHeight might be different than viewportHeight if we have scroll buttons too.
	this.slider.trackLength = this.trackHeight;			// Reset slider geometry
	this.track.style.height=this.trackHeight+"px";
	
	// Reset thumb geometry
	this.handleHeight = Math.round((this.trackHeight * this.viewportHeight) / this.innerHeight);
	if(this.handleHeight < 10) this.handleHeight = 10;
	this.handle.style.height = this.handleHeight + "px";
	
	// Reset handle height
	this.slider.handleLength = this.handleHeight;
	if (this.handleHeight < this.trackHeight) {
		 // Scrolbar should be displayed.
		 if (Element.getStyle(this.track, "display") == 'none') {
			 // If scrollbar was not previously displayed, we have to squeeze the viewport width by the width of the scrollbar
			this.track.style.display='inline';
			
			//now adjust the size of the inner box to make room for the slider
			//if the outer box has a border on it (common for scroll boxes) we need to compensate for different box models
			//fortunately, mozilla will work by default - so only if IE  has a border do we care.  Which is good, we can only check borders in IE...
			this.ieDecreaseBy=0;
			if (this.outerBox.currentStyle){
				var borderWidth = this.outerBox.currentStyle["borderWidth"].replace("px","");	//no way to isolate left and right border (which is all we care about) so we'll just assume consistent border width
				if(!isNaN(borderWidth)){
					this.ieDecreaseBy=(borderWidth)*2;	//compensate for left and right border
				}
			}
			this.setWidth();
		 }
			
	} else {
		this.track.style.display='none';
	}
  },
  
  /*
  function: setWidth  
  	Set the width of of the scrollpane (aka innerBox).
   */
  setWidth: function() {
	var newWidth = (this.getWidth(this.outerBox) - this.getWidth(this.track) - this.ieDecreaseBy) + "px";
	this.innerBox.style.width = newWidth;
	
	// The sad thing is that all of this might change innerHeight, so need to schedule a refresh
	setTimeout(this.resetScrollbar.bind(this, false), 10);
  },
  
  /*
  function: getHeight  
  	Get the height of the passed element.
	
	args:	
		el - the element to get the height of.
   */
  getHeight: function(el) {
	if (el.currentStyle){
		return el.offsetHeight;									//ie
	}else{
		return Element.getStyle(el,"height").replace("px","");	//moz
	}
  },
  
  /*
  function: getWidth  
  	Get the width of the passed element.
	
	args:	
		el - the element to get the width of.
   */
  getWidth: function(el) {
	var w = "0";
	if (el.currentStyle){
		w = el.offsetWidth;									//ie
	} else {
		w = Element.getStyle(el,"width");
		if (w) {
			w = w.replace("px","");	//moz
		}
	}
	
	return w;
  },
  
  /*
  function: onChange  
  	Called when the script.aculo.us slider has changed (i.e. when it has been dragged). Scroll the inner box.
	
	args:	
		val - not used.
   */
  onChange: function(val) {
	if(this.track){
		//assume 100 ticks in the scrollbar
		//for each tick need to move:  The amount the inner box overruns the outer box, divided by 100
		var moveRatio = (this.innerHeight - this.getHeight(this.outerBox))/100;
		//move the box up (negative) for every TickVal, move the box by moveRatio
		this.innerBox.style.top = (val*100*moveRatio*-1) + "px";
	}
  }
}

/*
function: Scroller.setAll
	Search for divs of the class 'makeScroll' and wrap them in a Scroller.
 */
Scroller.setAll = function () {
	//get all the boxes we want to scroll
	var sliderBoxes = document.getElementsByClassName("makeScroll");
	//build scroll functionality for each scrollable box
	for(i=0; i<sliderBoxes.length; i++){
		Scroller.ids[sliderBoxes[i].id] = new Scroller(sliderBoxes[i]);
	}
}

/*
function: Scroller.reset
	If the passed element has class 'makeScroll', wrap it in a Scroller.
 */
Scroller.reset = function (body_id) {
	if ($(body_id).className.match(new RegExp("(^|\\s)makeScroll(\\s|$)"))) {
		Scroller.ids[body_id] = new Scroller($(body_id));
	}
}

/*
property: Scroller.updateAll
	Reset all of the scrollbars.
 */
Scroller.updateAll = function () {
	for (var key in Scroller.ids) {
		Scroller.ids[key].resetScrollbar(true);
	}
}

/*
	Hook up some global event handlers.
 */
Event.observe(window, "load", Scroller.setAll);
Event.observe(window, "resize", Scroller.updateAll);