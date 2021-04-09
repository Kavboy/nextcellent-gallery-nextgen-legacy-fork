/*
Shutter Reloaded for NextGEN Gallery
http://www.laptoptips.ca/javascripts/shutter-reloaded/
Version: 1.4.0
Copyright (C) 2007-2008  Andrew Ozz (Modification by Alex Rabe)
Released under the GPL, http://www.gnu.org/copyleft/gpl.html

Acknowledgement: some ideas are from: Shutter by Andrew Sutherland - http://code.jalenack.com, WordPress - http://wordpress.org, Lightbox by Lokesh Dhakar - http://www.huddletogether.com, the icons are from Crystal Project Icons, Everaldo Coelho, http://www.everaldo.com

*/

shutterOnload = function () {
	shutterReloaded.init('sh');
};

document.addEventListener('DOMContentLoaded', function () {
	shutterReloaded.init('sh');
});

shutterReloaded = {
	settings() {
		const s = shutterSettings;

		this.keyEventlistenerIsSet = false;

		this.imageCount = s.imageCount || 0;
		this.currentTabElement = this.currentTabElement
			? this.currentTabElement
			: 0;
	},

	init(a) {
		let setid, inset;
		(shutterLinks = {}), (shutterSets = {});
		if ('object' !== typeof shutterSettings) {
			shutterSettings = {};
		}

		// If the screen orientation is defined we are in a modern mobile OS
		this.mobileOS = typeof orientation !== 'undefined' ? true : false;

		[...document.links]
			.filter(
				(aLink) =>
					(a == 'sh' && aLink.className.includes('shutterset')) ||
					(a == 'sh' && aLink.className.includes('shutter')) ||
					(a == 'lb' && aLink.rel.includes('lightbox['))
			)
			.forEach((aLink, index) => {
				const img = aLink.children[0];

				if (aLink.className && aLink.className.includes('shutterset')) {
					setid = aLink.className.replace(/\s/g, '_');
				} else if (aLink.rel && aLink.rel.includes('lightbox[')) {
					setid = aLink.rel.replace(/\s/g, '_');
				} else {
					setid = aLink.className;
				}

				if (setid) {
					if (!shutterSets[setid] && setid !== 'shutter') {
						shutterSets[setid] = [];
						inset = 0;
					} else if (setid === 'shutter') {
						const keys = Object.keys(shutterSets).filter(
							(key) =>
								!key.includes('shutterset') &&
								key.includes('shutter')
						);

						let highest = 0;

						for (const key of keys) {
							const setName = shutterSets[key][0].set;

							const number = parseInt(
								setName.substr(
									setName.indexOf('_') + 1,
									setName.length - 1
								),
								10
							);

							if (highest === number) {
								highest++;
							}
						}

						setid = 'shutter_' + highest;
						shutterSets[setid] = [];
						inset = 0;
					} else {
						inset = shutterSets[setid].length;
					}

					const imgFileName = aLink.href
						.slice(aLink.href.lastIndexOf('/') + 1)
						.split('.')[0];

					const alt = img && img.alt ? img.alt : '';

					const description =
						aLink.title && aLink.title != imgFileName
							? aLink.title
							: '';

					const imgObj = {
						src: aLink.href,
						num: inset,
						set: setid,
						description,
						alt,
					};

					shutterSets[setid].push(imgObj);
				}

				aLink.addEventListener('click', (e) => {
					e.stopPropagation();
					e.preventDefault();

					const imgObj = Object.entries(shutterSets)
						.map((el) => el[1][0])
						.find((el) => {
							if (e.target.tagName === 'IMG') {
								return el.src === e.target.parentElement.href;
							} else if (e.target.tagName === 'A') {
								return el.src === e.target.href;
							}
						});

					this.createShutter(imgObj);
					return false;
				});
			});

		this.settings();
	},

	/**
	 * Creates the complete shutter
	 *
	 * @param imgObj
	 * @param fs
	 */
	createShutter(imgObj, fs) {
		if (!this.Top) {
			if (typeof window.pageYOffset !== 'undefined') {
				this.Top = window.pageYOffset;
			} else {
				this.Top =
					document.documentElement.scrollTop > 0
						? document.documentElement.scrollTop
						: document.body.scrollTop;
			}
		}

		if (typeof this.pgHeight === 'undefined') {
			this.pgHeight = Math.max(
				document.documentElement.scrollHeight,
				document.body.scrollHeight
			);
		}

		if (fs) {
			this.FS = fs > 0 ? 1 : 0;
		} else {
			this.FS = shutterSettings.FS || 0;
		}

		if (this.resizing) {
			this.resizing = null;
		}

		// resize event if window or orientation changed (i.e. iOS)
		if (this.mobileOS) {
			window.onorientationchange = function () {
				shutterReloaded.resize(imgObj);
			};
		} else {
			window.onresize = function () {
				shutterReloaded.resize(imgObj);
			};
		}

		document.documentElement.style.overflowX = 'hidden';
		if (!this.VP) {
			this._viewPort();
			this.VP = true;
		}

		const shadowBox = this.createShadowBox();
		shadowBox.style.height = this.pgHeight + 'px';

		let loadError = document.getElementById('shLoadError');
		if (!loadError) {
			loadError = document.createElement('div');
			loadError.id = 'shLoadError';
			loadError.innerText = 'Image load failed, please try again';
			shadowBox.appendChild(loadError);
		}

		loadError.style.top =
			this.Top + (this.wHeight - loadError.clientHeight) * 0.5 + 'px';

		let spinner = document.getElementById('shSpinner');
		if (!spinner) {
			spinner = document.createElement('div');
			spinner.id = 'shSpinner';
			shadowBox.appendChild(spinner);
		}

		spinner.style.display = 'block';

		spinner.style.top =
			this.Top + (this.wHeight - spinner.clientHeight) * 0.5 + 'px';

		let imageBox = document.getElementById('shDisplay');

		if (!imageBox) {
			imageBox = this.createImageBox();
			shadowBox.appendChild(imageBox);
		}
		imageBox.style.visibility = 'hidden';

		let descriptionDiv = document.getElementById('shDescription');
		if (!descriptionDiv) {
			descriptionDiv = document.createElement('div');
			descriptionDiv.id = 'shDescription';
			descriptionDiv.innerText = imgObj.description;
			descriptionDiv.addEventListener('click', (ev) => {
				ev.stopPropagation();
				ev.preventDefault();
			});
		} else {
			descriptionDiv.innerText = imgObj.description;
		}

		let imageWrapperDiv = document.getElementById('shWrap');
		if (!imageWrapperDiv) {
			imageWrapperDiv = document.createElement('div');
			imageWrapperDiv.id = 'shWrap';
		}

		let crossCloseDiv = document.getElementById('shCrossClose');
		if (!crossCloseDiv) {
			crossCloseDiv = this.createCrossCloseDiv();
		}

		const image = document.getElementById('shutterImg');
		if (image) {
			this.setImgAttributes(image, imgObj);
		} else {
			const image = this.createImage(imgObj);
			imageWrapperDiv.appendChild(image);
		}

		const navBar = this.createNavigation(imgObj);

		if (shutterSets[imgObj.set].length > 1) {
			imageWrapperDiv.appendChild(navBar);
		}

		if (imgObj.description && imgObj.description !== ' ') {
			imageWrapperDiv.appendChild(descriptionDiv);
		} else {
			if (descriptionDiv.parentElement) {
				descriptionDiv.parentElement.removeChild(descriptionDiv);
			}
			navBar.style.borderRadius = '0 0 5px 5px';
		}

		imageBox.appendChild(crossCloseDiv);
		imageBox.appendChild(imageWrapperDiv);

		// Only add the event listener one time to the document
		if (!this.keyEventlistenerIsSet) {
			document.addEventListener('keydown', this.functionEventHandler);
			this.keyEventlistenerIsSet = true;
		}
	},

	/**
	 * function handler for the keydown eventlistener, to be able to remove it later
	 *
	 * @param ev
	 */
	functionEventHandler(ev) {
		ev.stopPropagation();
		shutterReloaded.handleKeys(ev);
	},

	/**
	 * Creates the close button for the shutter
	 *
	 * @return {HTMLButtonElement}
	 */
	createCrossCloseDiv() {
		const crossCloseDiv = document.createElement('button');
		crossCloseDiv.id = 'shCrossClose';
		(crossCloseDiv.role = 'button'),
			(crossCloseDiv['aria-label'] = 'Close shutter');
		crossCloseDiv.tabIndex = 19;
		crossCloseDiv.innerText = 'X';
		crossCloseDiv.addEventListener('click', (ev) => {
			ev.stopPropagation();
			ev.preventDefault();
			this.hideShutter();
		});

		return crossCloseDiv;
	},

	/**
	 * If shadowBox does not exist already creates the div with id
	 * and appends it to the body element and returns the element.
	 * Else returns the already existing element.
	 *
	 * @return {HTMLElement} div
	 */
	createShadowBox() {
		let shadowBox = document.getElementById('shShutter');
		if (!shadowBox) {
			shadowBox = document.createElement('div');
			shadowBox.setAttribute('id', 'shShutter');
			document.getElementsByTagName('body')[0].appendChild(shadowBox);
			this.hideTags();
			shadowBox.addEventListener('click', (ev) => {
				ev.stopPropagation();
				this.hideShutter();
			});
		}
		return shadowBox;
	},

	/**
	 * Creates the image box div with id and returns it.
	 *
	 * @return {HTMLElement}
	 */
	createImageBox() {
		let imageBox = document.getElementById('shDisplay');
		if (!imageBox) {
			imageBox = document.createElement('div');
			imageBox.id = 'shDisplay';
		}
		return imageBox;
	},

	/**
	 * Creates the image element
	 *
	 * @param imgObj
	 * @return {HTMLImageElement}
	 */
	createImage(imgObj) {
		const image = document.createElement('img');

		this.setImgAttributes(image, imgObj);

		image.addEventListener('load', (ev) => {
			ev.stopPropagation();
			this.showImg();
		});
		image.addEventListener('error', (ev) => {
			ev.stopPropagation();
			this.hideSpinner();

			const loadError = document.getElementById('shLoadError');
			loadError.style.display = 'block';
		});
		image.addEventListener('click', (ev) => {
			ev.stopPropagation();
			ev.preventDefault();
		});
		return image;
	},

	/**
	 * function to hide the spinner with css
	 */
	hideSpinner() {
		const spinner = document.getElementById('shSpinner');
		spinner.style.display = 'none';
	},

	/**
	 * Sets all attributes of the image element
	 *
	 * @param image
	 * @param imgObj
	 */
	setImgAttributes(image, imgObj) {
		image.src = imgObj.src;
		image.id = 'shutterImg';
		image.alt = imgObj.alt;
		image.title = imgObj.description;
	},

	/**
	 * Returns the imgObj before the current one
	 *
	 * @param imgObj
	 * @return {Object}
	 */
	getPreviousImage(imgObj) {
		return shutterSets[imgObj.set].find((el) => el.num === imgObj.num - 1);
	},

	/**
	 * Returns the imgObj after the current one
	 *
	 * @param imgObj
	 * @return {Object}
	 */
	getNextImage(imgObj) {
		return shutterSets[imgObj.set].find((el) => el.num === imgObj.num + 1);
	},

	/**
	 * Creates and returns the image Count text in the format:
	 * (Number / Number)
	 *
	 * Or an empty String
	 *
	 * @param imgObj
	 * @return {string}
	 */
	getImgCountText(imgObj) {
		let text = '';

		if (imgObj.num >= 0 && this.imageCount) {
			text = '(';
			text += imgObj.num + 1;
			text += ' / ';
			text += shutterSets[imgObj.set].length;
			text += ')';
		}

		return text;
	},

	/**
	 * Creates the complete navigation bar and returns it
	 *
	 * @param imgObj
	 * @return {HTMLElement}
	 */
	createNavigation(imgObj) {
		const prevImage = this.getPreviousImage(imgObj);

		const nextImage = this.getNextImage(imgObj);

		let navBar = document.getElementById('shNavBar');
		if (!navBar) {
			navBar = document.createElement('div');
			navBar.id = 'shNavBar';
			navBar.addEventListener('click', (ev) => {
				ev.stopPropagation();
				ev.preventDefault();
			});
		}

		let prevDiv = document.getElementById('shPrev');
		if (!prevDiv) {
			prevDiv = document.createElement('div');
			prevDiv.id = 'shPrev';
		}

		let nextDiv = document.getElementById('shNext');
		if (!nextDiv) {
			nextDiv = document.createElement('div');
			nextDiv.id = 'shNext';
		}

		let imgCountDiv = document.getElementById('shCount');
		if (!imgCountDiv) {
			imgCountDiv = document.createElement('div');
			imgCountDiv.id = 'shCount';
		}
		imgCountDiv.innerText = this.getImgCountText(imgObj);

		let prevLink = document.getElementById('prevpic');
		if (prevImage) {
			if (!prevLink) {
				prevLink = document.createElement('button');
				prevLink.id = 'prevpic';
				prevLink.innerText = '<<';
				prevLink.tabIndex = '20';
				prevLink['aria-lable'] = 'Previous picture';
				prevLink.addEventListener('click', (ev) => {
					ev.stopPropagation();
					ev.preventDefault();
					this.createShutter(prevImage);
				});
				prevDiv.appendChild(prevLink);
			} else {
				const newPrevLink = prevLink.cloneNode(true);
				newPrevLink.addEventListener('click', (ev) => {
					ev.stopPropagation();
					ev.preventDefault();
					this.createShutter(prevImage);
				});
				prevLink.parentNode.replaceChild(newPrevLink, prevLink);
			}
		} else if (prevLink) {
			prevLink.parentNode.removeChild(prevLink);
		}

		let nextLink = document.getElementById('nextpic');
		if (nextImage) {
			if (!nextLink) {
				nextLink = document.createElement('button');
				nextLink.id = 'nextpic';
				nextLink.innerText = '>>';
				nextLink.tabIndex = '20';
				nextLink['aria-lable'] = 'Next picture';
				nextLink.addEventListener('click', (ev) => {
					ev.stopPropagation();
					ev.preventDefault();
					this.createShutter(nextImage);
				});
				nextDiv.appendChild(nextLink);
			} else {
				const newNextLink = nextLink.cloneNode(true);
				newNextLink.addEventListener('click', (ev) => {
					ev.stopPropagation();
					ev.preventDefault();
					this.createShutter(nextImage);
				});
				nextLink.parentNode.replaceChild(newNextLink, nextLink);
			}
		} else if (nextLink) {
			nextLink.parentNode.removeChild(nextLink);
		}

		navBar.appendChild(prevDiv);
		navBar.appendChild(imgCountDiv);
		navBar.appendChild(nextDiv);

		return navBar;
	},

	/**
	 * Removes all Shutter elements and event listeners
	 */
	hideShutter() {
		let imageBox, shadowBox, spinner;
		if ((imageBox = document.getElementById('shDisplay'))) {
			imageBox.parentNode.removeChild(imageBox);
		}
		if ((spinner = document.getElementById('shSpinner'))) {
			spinner.parentNode.removeChild(spinner);
		}
		if ((shadowBox = document.getElementById('shShutter'))) {
			shadowBox.parentNode.removeChild(shadowBox);
		}
		this.hideTags(true);
		window.scrollTo(0, this.Top);
		window.onresize = this.FS = this.Top = this.VP = null;
		document.documentElement.style.overflowX = '';

		// Only remove the event listener if it is defined
		if (this.keyEventlistenerIsSet) {
			document.removeEventListener('keydown', this.functionEventHandler);
			this.keyEventlistenerIsSet = false;
		}
	},

	/**
	 * Recalculates all widths and heights of the shutter elements
	 *
	 * @param imgObj
	 */
	resize(imgObj) {
		const shadowBox = document.getElementById('shShutter');
		const imageBox = document.getElementById('shDisplay');
		const image = document.getElementById('shutterImg');
		const navBar = document.getElementById('shNavBar');
		const titleDiv = document.getElementById('shDescription');
		const crossCloseDiv = document.getElementById('shCrossClose');

		if (!shadowBox) {
			return;
		}
		this._viewPort();

		imageBox.style.top = this.Top + 'px';

		if (image.height > this.wHeight) {
			image.width = image.width * (this.wHeight / image.height);
			image.height = this.wHeight;
		}
		const height = image.naturalHeight * ((this.wWidth - 40) / image.width);
		const width = this.wWidth - 40;

		if (this.wWidth <= image.naturalWidth) {
			image.height =
				height < image.naturalHeight ? height : image.naturalHeight;
			image.width =
				width < image.naturalWidth ? width : image.naturalWidth;
		}

		if (navBar) {
			navBar.style.width = image.width + 4 + 'px';
		}

		if (titleDiv) {
			titleDiv.style.width = image.width + 4 + 'px';
		}

		crossCloseDiv.style.left =
			(this.wWidth - image.width) / 2 +
			(image.width - crossCloseDiv.clientWidth / 2) +
			'px';

		const maxHeight = this.Top + image.height + 10;

		if (maxHeight > this.pgHeight) {
			shadowBox.style.height = maxHeight + 'px';
		}

		const imgTop = (this.wHeight - image.height) * 0.2;
		const minTop = imgTop > 30 ? Math.floor(imgTop) : 30;

		imageBox.style.top = this.Top + minTop + 'px';
	},

	/**
	 * Calculates the window width and height and adds them to this
	 *
	 * @private
	 */
	_viewPort() {
		const winInnerHeight = window.innerHeight ? window.innerHeight : 0;
		const docBodCliHeight = document.body.clientHeight
			? document.body.clientHeight
			: 0;
		const docElHeight = document.documentElement
			? document.documentElement.clientHeight
			: 0;

		if (winInnerHeight > 0) {
			this.wHeight =
				winInnerHeight - docBodCliHeight > 1 &&
				winInnerHeight - docBodCliHeight < 30
					? docBodCliHeight
					: winInnerHeight;
			this.wHeight =
				this.wHeight - docElHeight > 1 &&
				this.wHeight - docElHeight < 30
					? docElHeight
					: this.wHeight;
		} else {
			this.wHeight = docElHeight > 0 ? docElHeight : docBodCliHeight;
		}

		const docElWidth = document.documentElement
			? document.documentElement.clientWidth
			: 0;
		const docBodyWidth = window.innerWidth
			? window.innerWidth
			: document.body.clientWidth;
		this.wWidth = docElWidth > 1 ? docElWidth : docBodyWidth;
	},

	/**
	 * Calls resize and hideSpinner and then sets the visibility of the imageBox
	 */
	showImg() {
		const shadowBox = document.getElementById('shShutter');
		const imageBox = document.getElementById('shDisplay');

		if (!shadowBox) {
			return;
		}

		this.resize();
		this.hideSpinner();
		imageBox.style.visibility = 'visible';
	},

	hideTags(arg) {
		const sel = document.getElementsByTagName('select');
		const obj = document.getElementsByTagName('object');
		const emb = document.getElementsByTagName('embed');
		const ifr = document.getElementsByTagName('iframe');

		const vis = arg ? 'visible' : 'hidden';

		for (i = 0; i < sel.length; i++) {
			sel[i].style.visibility = vis;
		}
		for (i = 0; i < obj.length; i++) {
			obj[i].style.visibility = vis;
		}
		for (i = 0; i < emb.length; i++) {
			emb[i].style.visibility = vis;
		}
		for (i = 0; i < ifr.length; i++) {
			ifr[i].style.visibility = vis;
		}
	},

	/**
	 * returns an array of the currently tabbable elements of the shutter
	 *
	 * @return {[]}
	 */
	getCurrentTabElements() {
		const nextlink = document.getElementById('nextpic');
		const prevlink = document.getElementById('prevpic');
		const closelink = document.getElementById('shCrossClose');

		const array = [];

		if (nextlink) {
			array.push(nextlink);
		}

		if (prevlink) {
			array.push(prevlink);
		}

		if (closelink) {
			array.push(closelink);
		}

		return array;
	},

	/**
	 * handles the key events for the shutter
	 * Arrow / tab keys
	 *
	 * @param event
	 */
	handleKeys(event) {
		let code = 0;
		if (!event) {
			const event = window.event;
		}
		if (event.keyCode) {
			code = event.keyCode;
		} else if (event.which) {
			code = event.which;
		}

		const nextlink = document.getElementById('nextpic');
		const prevlink = document.getElementById('prevpic');
		const closelink = document.getElementById('shShutter');

		switch (code) {
			case 39: // right arrow key
				if (nextlink) {
					nextlink.click();
				}
				break;
			case 37: // left arrow key
				if (prevlink) {
					prevlink.click();
				}
				break;
			case 27: // Esc key
				if (closelink) {
					this.hideShutter();
				}
				break;
			case 9: // Tab key
				const tabElements = this.getCurrentTabElements();

				if (this.currentTabElement === 0) {
					// without timeout focus is not changed to the element
					window.setTimeout(() => {
						tabElements[this.currentTabElement].focus();
					}, 0);

					this.currentTabElement++;
				} else if (
					this.currentTabElement !== 0 &&
					this.currentTabElement >= tabElements.length - 1
				) {
					window.setTimeout(() => {
						tabElements[this.currentTabElement].focus();
					}, 0);

					this.currentTabElement = 0;
				} else {
					window.setTimeout(() => {
						tabElements[this.currentTabElement].focus();
					}, 0);
					this.currentTabElement++;
				}
		}
	},
};
