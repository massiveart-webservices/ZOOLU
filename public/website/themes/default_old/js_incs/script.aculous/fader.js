//java -jar app/js.jar app/run.js -t=templates/sweet -p -d=docs D:\Libraries\Prototype.Widgets\src\js\fader.js
//remove -p to remove private functions
/**
 * @fileoverview Widget.Fader <br />
 * Loads an array of images and fades them in and out in sequence.<br />
 * <br />
 * Requires Prototype  1.6 (http://www.prototypejs.org) or later <br />
 * and Scriptaculous 1.8 (http://script.aculo.us) or later. <br />
 * <br />
 * Widget.Fader is licensed under the Creative Commons Attribution 2.5 South Africa License<br />
 * (more information at: http://creativecommons.org/licenses/by/2.5/za/)<br />
 * Under this license you are free to<br />
 * - to copy, distribute and transmit the work<br />
 * - to adapt the work<br />
 * However you must<br />
 * - You must attribute the work in the manner specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).<br />
 * - That is by providing a link back to http://www.eternal.co.za or to the specific script page. This link need not be on the page using the script, or even nescessarily even on the same domain, as long as it's accessible from the site.<br />
 * I'd also like an email telling me where you are using the script, although this is not required. More often than not I will link back to the site using the script.<br />
 * Other than that, you may use this class in any way you like, but don't blame me if things go <br />
 * pear shaped. If you use this library I'd like a mention on your website, but <br />
 * it's not required. If you like it, send me an email. If you find bugs, send <br />
 * me an email. If you don't like it, don't tell me: you'll hurt my feelings. <br />
 * <br />
 * Change History:<br />
 * Version 1.2.0: 11 Feb 2007<br />
 * - Fixed a bug: minified version of Prototype broke Ajax (see http://www.eternal.co.za/blog/?p=50)<br />
 * - Added builder option and imageBuilder (so that default behaviour does not change)<br />
 * - Added textBuilder<br />
 * - Updated included Prototype to 1.6.0.2<br />
 * Version 1.1.1: 17 Nov 2007<br />
 * - Fixed a bug: options.attributes should have been an associative array object<br />
 * Version 1.1.0: 11 Nov 2007<br />
 * - Updated script for Prototype 1.6 and Scriptaculous 1.8<br />
 * - Fader is now in the Widget namespace (Widget.Fader).<br />
 * - Fader no longer requires Scriptactulous Builder and uses Prototypes new Element(...) instead.<br />
 * - Minified script and minified single script files added to download.<br />
 * - New license: Creative Commons Attribution 2.5 South Africa License.<br />
 *  see more at: http://creativecommons.org/licenses/by/2.5/za/<br />
 * - new Fader(...) has been deprecated and will be removed in the next major version<br />
 * Version 1.0.2: 20 Sep 2007<br />
 * - Added dir option<br />
 * - Added beforeFade callback option<br />
 * - Added startIndex option<br />
 * Version 1.0.1: 15 Aug 2007<br />
 * - Added attributes option<br />
 * - Now requires builder.js from Scriptaculous<br />
 * - Fixed a bug that started the blend with the 3rd image in the list<br />
 * Version 1.0.0: 11 Aug 2007<br />
 * - First version<br />
 * @class Widget.Fader
 * @version 1.2.0
 * @author Marc Heiligers marc@eternal.co.za http://www.eternal.co.za
 */
if(typeof Widget == "undefined") Widget = {};
/**
 * The Widget.Fader class constructor. <br />
 * @constructor Widget.Fader
 * @param {string|img element} img The id of or actual image element to be faded
 * @param {array(string)} list  An array of paths (relative or absolute) of the images
 * @param {object} [options] An object of options.
 */
Widget.Fader = Class.create(/** @scope Widget.Fader **/{
	initialize: function(img, list, options) {
		this.img = $(img);
		this.list = list;

		/**
		 * The default options object.
		 * @class
		 * @param {string} [id] The id used as queue scope. (default: img.id)
		 * @param {float} [fadeInDuration] The time in seconds of the fade in. (default: 2.5)
		 * @param {float} [fadeOutDuration] The time in seconds of the fade out. (default: 1.5)
		 * @param {float} [displayDuration] The time in seconds that the image is not faded out after being faded in. (default: 2.5)
		 * @param {bool} [autoSize] Set true if the image should be sized to it's container. Maintains aspect ratio. (default: false)
		 * @param {bool} [autoStart] If false the Blender will not start until Blender#start is called. (default: true)
		 * @param {object} [attributes] An associative array of attributes given to the image. (default: {})
		 * @param {string} [dir] The directory that all images reside in. Used as a prefix for the image src. (default: null)
		 * @param {function} [beforeFade] A function that is called before the image is faded. 2 parameters are passed: 1. the image; 2. a boolean indicating if the image is being faded in (true) or out (false) (default: null)
		 * @param {int} [startIndex] The index of the first new image to be shown. (default: 0)
		 * @param {function} [builder] The function called to build the items. (default: Widget.Fader.imageBuilder)
		 */
		this.options = Object.extend({
			id: this.img.id,
			fadeInDuration: 2.5,
			fadeOutDuration: 1.5,
			displayDuration: 2.5,
			autoSize: false,
			autoStart: true,
			attributes: {},
			dir: "",
			beforeFade: null,
			startIndex: 0,
			builder: Widget.Fader.imageBuilder
		}, options || {});
		this.options.attributes["id"] = this.options.id;

		this.index = this.options.startIndex;
		this.container = $(this.img.parentNode);
		this.loadedObserver = this.loaded.bind(this);
		this.fadeInObserver = this.fadeIn.bind(this);
		this.nextObserver = this.next.bind(this);

		if(this.options.autoStart) {
			setTimeout(this.start.bind(this), this.options.displayDuration * 1000);
		}
	},
	/**
	 * Starts the fading if the autoStart option was set to false or after a call to stop.
	 * @function
	 */
	start: function() {
		this.stopped = false;
		this.next();
	},
	/**
	 * Stops the fading and sets the opacity of the current image to 100%.
	 * @function
	 */
	stop: function() {
		this.stopped = true;
		try { clearTimeout(this.timeout); } catch(ex) { }
		try { Effect.Queues.get(this.options.id).each(function(effect) { effect.cancel() }) } catch(ex) { }
		if(this.oldImg) {
			this.img = this.oldImg;
			--this.index;
		}
		Element.setOpacity(this.img, 1);
	},
	/**
	 * Loads the next image in list
	 * @private
	 * @function
	 */
	next: function() {
		this.oldImg = this.img;
		if(this.stopped || this.list.length == 0) {
			return;
		}
		++this.index;
		if(this.index >= this.list.length) {
			this.index = 0;
		}
		/*this.img = new Element("img", this.options.attributes);
		Event.observe(this.img, "load", this.loadedObserver);
		this.img.src = this.options.dir + this.list[this.index];*/
		this.img = this.options.builder(this, this.list[this.index], this.loadedObserver);

	},
	/**
	 * Event listener for image loaded
	 * @private
	 * @function
	 */
	loaded: function() {
		Event.stopObserving(this.img, "load", this.loadedObserver);
		if(typeof this.options.beforeFade == "function") {
			this.options.beforeFade(this.oldImg, false);
		}
		new Effect.Opacity(this.oldImg, { duration: this.options.fadeOutDuration, from: 1.0, to: 0.05, queue: { scope: this.options.id } });
		this.timeout = setTimeout(this.fadeInObserver, this.options.fadeOutDuration * 1000);
	},
	/**
	 * Event listener for fadeIn
	 * @private
	 * @function
	 */
	fadeIn: function() {
		if(typeof this.options.beforeFade == "function") {
			this.options.beforeFade(this.img, true);
		}
		//this.img.id = this.id;
		Element.setOpacity(this.img, 0);
		if(this.options.autoSize) {
			this.resize(this.img);
		}
		this.container.replaceChild(this.img, this.oldImg);
		this.oldImg = null;
		new Effect.Opacity(this.img, { duration: this.options.fadeInDuration, from: 0.05, to: 1.0, queue: { scope: this.options.id } });
		this.timeout = setTimeout(this.nextObserver, (this.options.fadeInDuration + this.options.displayDuration) * 1000);
	},
	/**
	 * Resize the image to the container while maintaining aspect ratio
	 * @private
	 * @function
	 */
	resize: function(img) {
		var dim = this.container.getDimensions();
		dim.width -= parseInt(this.container.getStyle("padding-left")) +
			parseInt(this.container.getStyle("padding-right")) +
			parseInt(this.container.getStyle("border-left-width")) +
			parseInt(this.container.getStyle("border-right-width"));
		dim.height -= parseInt(this.container.getStyle("padding-top")) +
			parseInt(this.container.getStyle("padding-bottom")) +
			parseInt(this.container.getStyle("border-top-width")) +
			parseInt(this.container.getStyle("border-bottom-width"));

		var dw = dim.width / img.width;
		var dh = dim.height / img.height;
		var w1 = img.width * dh;
		var h1 = img.height * dw;

		if(dw > dh) {
			img.width = w1;
			img.height = dim.height;
		} else {
			img.width = dim.width;
			img.height = h1;
		}
	}
});

/**
 * Builds an image item out the item passed by fader.
 * This is the default builder.
 * @function
 * @param {object} fader The calling Widget.Fader
 * @param {object} item The current item
 * @param {object} loaded A callback bound to the fader for when the item has loaded.
 **/
Widget.Fader.imageBuilder = function(fader, item, loaded) {
	var img = new Element("img", fader.options.attributes);
	img.observe("load", loaded);
	img.src = fader.options.dir + item;
	return img;
};

/**
 * Builds div containing the text from the item passed by fader.
 * The Widget.Fader.options.dir is ignored.
 * @function
 * @param {object} fader The calling Widget.Fader
 * @param {object} item The current item
 * @param {object} loaded A callback bound to the fader for when the item has loaded.
 **/
Widget.Fader.textBuilder = function(fader, item, loaded) {
	var div = new Element("div", fader.options.attributes).update(item);
	loaded.defer();
	return div;
};

/**
 * @class
 * @deprecated
 **/
var Fader = Widget.Fader;