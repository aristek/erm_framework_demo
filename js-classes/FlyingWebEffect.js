import UIElement from "../UIElement";
import {Core} from "../../namespaces";
import Utils from "../../Utils";

/**
 * @typedef {Object} T_FlyingWebEffectOptions
 * @property {number} [speed] Movement speed (px per sec)
 * @property {number} [rows] Number of rows in web
 * @property {number} [hStep] Step between columns
 * @property {number} [vStep] Step between rows
 * @property {number} [theta] Step (angle) for wave in radains
 * @property {number} [amplitude] Height of wave
 * @property {number} [scatterX] Length of scatter by X axis
 * @property {number} [scatterY] Length of scatter by Y axis
 * @property {number} [scatterError]
 * @property {string} [dotColor]
 * @property {string} [lineColor]
 */

/**
 * Flying Web Effect
 */
class FlyingWebEffect extends UIElement {
	/**
	 * Class constructor
	 *
	 * @param {T_FlyingWebEffectOptions} options
	 */
	constructor(options = {}) {
		super();

		/**
		 * @type {T_FlyingWebEffectOptions}
		 * @private
		 */
		this._options = Object.assign({
			speed: 16,
			rows: 6,
			amplitude: 20,
			theta: Math.PI / 4,
			hStep: 40,
			vStep: 10,
			scatterX: 20,
			scatterY: 70,
			scatterError: 4,
			dotColor: 'rgba(255, 255, 255, 0.4)',
			lineColor: 'rgba(255, 255, 255, 0.3)',
		}, options);

		/**
		 * @type {{x: number, lastGeneratedX: number, theta: number, acceleration: number}}
		 * @private
		 */
		this._offset = {
			x: 0,
			lastGeneratedX: 0,
			theta: 0,
			acceleration: 0,
			acceleratedSpeed: 0,
		};

		/**
		 * @type {number}
		 * @private
		 */
		this._colsCount = 0;

		/**
		 * @type {HTMLCanvasElement}
		 * @private
		 */
		this._canvas = document.createElement('canvas');

		/**
		 * @type {CanvasRenderingContext2D}
		 * @private
		 */
		this._context = this._canvas.getContext('2d');

		/**
		 * @type {Array<Array<{
		 *  stage: number,
		 *  current: number,
		 *  duration: number,
		 *  x: number,
		 *  y: number,
		 *  baseX: number,
		 *  animFromX: number,
		 *  animFromY: number,
		 *  animToX: number,
		 *  animToY: number,
		 *  fromX: number,
		 *  fromY: number,
		 *  toX: number,
		 *  toX: number}>>}
		 * @private
		 */
		this._points = [];

		/**
		 * @type {Utils}
		 * @private
		 */
		this._utils = Utils.get();

		/**
		 * @type {number}
		 * @private
		 */
		this._timerId = 0;

		/**
		 * @type {number}
		 * @private
		 */
		this._stage = 1;

		this._handlers = {
			animate: this.animate.bind(this),
			render: this.render.bind(this),
		};

		this.addListener(UIElement.EVENT_CHANGE_SIZE, this.updateSize, this);
		this.append(this._canvas);
		this.updateSize();
		this.animate();
		this.render();
	}

	/**
	 * Updates element size
	 *
	 * @returns {void}
	 */
	updateSize() {
		this._canvas.width = this.getWidth() || 100;
		this._canvas.height = this.getHeight() || 100;
		this._generateWeb(true);
	}

	/**
	 * Generates web for the next screen
	 *
	 * @param {boolean} rebuild
	 * @returns {void}
	 * @private
	 */
	_generateWeb(rebuild = false) {
		if (rebuild) {
			this._points = [];
			this._offset.theta = 0;
			this._offset.x = this._options.hStep;
			this._offset.lastGeneratedX = 0;
			this._offset.acceleration = 0;
			this._stage = 1;
		}

		let x, y,
			theta = this._offset.theta,
			offset,
			startY = this._canvas.height - 1,
			rows = this._options.rows,
			hStep = this._options.hStep,
			vStep = this._options.vStep,
			scatterX = this._options.scatterX,
			scatterLowerX = -scatterX / 2,
			scatterY = this._options.scatterY,
			scatterLowerY = -scatterY / 2,
			offsetX = this._offset.lastGeneratedX,
			cols = Math.ceil((this._canvas.width / hStep) * 2),
			offsetTop = vStep * rows;

		this._colsCount = cols;

		for (let c = 0; c < cols; c++) {
			offset = -Math.sin(theta += this._options.theta) * this._options.amplitude;
			y = startY;
			x = offsetX + c * this._options.hStep;
			let col = [];
			for (let r = 0; r < rows; r++) {
				let fx = x + (-scatterLowerX + Math.random() * scatterX),
					fy = (y + offset - offsetTop) + (-scatterLowerY + Math.random() * scatterY),
					tx = x + (-scatterLowerX + Math.random() * scatterX),
					ty = (y + offset - offsetTop) + (-scatterLowerY + Math.random() * scatterY);
				col[r] = {
					stage: 1,
					current: 0,
					duration: 5,
					baseX: x,
					x: fx,
					y: this._canvas.height,
					animFromX: fx,
					animFromY: this._canvas.height,
					animToX: fx,
					animToY: fy,
					fromX: fx,
					fromY: fy,
					toX: tx,
					toY: ty,
				};
				y -= vStep;
			}
			this._points.push(col);
		}
		this._offset.lastGeneratedX += cols * this._options.hStep;
		this._offset.theta += cols * this._options.theta;
	}

	/**
	 * Completes the animation
	 *
	 * @param {boolean} [withError]
	 * @returns {void}
	 */
	complete(withError = false) {
		if (this._stage === 0 || this._stage === 4) {
			return;
		}
		this._stage = 4;
		this._offset.acceleratedSpeed = this._options.speed / 24;
		this._offset.acceleration = 0.95;
		for (let c = 0; c < this._points.length; c++) {
			let col = this._points[c];
			for (let r = 0; r < col.length; r++) {
				let point = col[r];
				if (withError) {
					point.stage = 5;
					point.duration = 1;
					point.current = 0;
					point.animToX = point.x;
					point.animToY = point.y;
					point.animFromX = point.x;
					point.animFromY = point.y;
					point.fromX = point.x;
					point.fromY = point.y;
				} else if (point.stage === 2) {
					point.stage = 3;
					point.animToX = point.fromX;
					point.animToY = point.fromY;
					point.animFromX = point.toX;
					point.animFromY = point.toY;
					point.current = 0;
					point.duration = 10;
				} else if (point.stage === 3) {
					point.stage = 2;
					point.animToX = point.toX;
					point.animToY = point.toY;
					point.animFromX = point.fromX;
					point.animFromY = point.fromY;
					point.current = 0;
					point.duration = 10;
				}
			}
		}
	}

	/**
	 * Animates all points
	 *
	 * @returns {void}
	 */
	animate() {
		if (this._stage === 0) {
			return;
		}
		if (this._stage === 4) {
			// slow down speed to 0
			this._offset.acceleratedSpeed *= this._offset.acceleration;
			if (this._offset.acceleratedSpeed < 0.01) {
				this._offset.acceleratedSpeed = 0;
			}
			this._offset.x += this._offset.acceleratedSpeed;
		} else {
			// normal linear speed
			this._offset.x += this._options.speed / 24;
		}
		if (this._offset.x + (this._colsCount + 3) * this._options.hStep > this._offset.lastGeneratedX) {
			// generate new screen of points
			this._generateWeb();
		}
		// remove old columns
		let lowerBoundary = this._offset.x - (this._options.hStep * 2);
		columnsLoop: for (let c = 0; c < this._points.length; c++) {
			let col = this._points[c];
			if (col[0].baseX <= lowerBoundary) {
				this._points.splice(c, 1);
				c--;
				continue;
			}
			let value = null;
			for (let r = 0; r < col.length; r++) {
				let point = col[r];
				if (++point.current >= point.duration) {
					// this column finished animation
					// reset animation
					point.current = 0;
					if (point.stage === 1 || point.stage === 3) {
						point.stage = 2;
						point.duration = 300;
						point.animToX = point.toX;
						point.animToY = point.toY;
						point.animFromX = point.fromX;
						point.animFromY = point.fromY;
					} else if (point.stage === 2) {
						point.stage = 3;
						point.duration = 300;
						point.animToX = point.fromX;
						point.animToY = point.fromY;
						point.animFromX = point.toX;
						point.animFromY = point.toY;
					} else if (point.stage === 5) {
						point.duration = 2;
						let scatterErrorHalf = this._options.scatterError / 2;
						point.animToX = point.fromX + (-scatterErrorHalf + Math.random() * this._options.scatterError);
						point.animToY = point.fromY + (-scatterErrorHalf + Math.random() * this._options.scatterError);
						point.animFromX = point.x;
						point.animFromY = point.y;
					}
				}
				if (value === null) {
					// calculate interpolation coefficient (0...1) for the first point in column
					// all point in column have the save coefficient
					value = this._utils.interpolate('linear', point.current, point.duration);
				}
				point.x = point.animFromX + (point.animToX - point.animFromX) * value;
				point.y = point.animFromY + (point.animToY - point.animFromY) * value;
			}
		}
		setTimeout(this._handlers.animate, ~~(1000 / 24));
	}

	/**
	 * Renders element
	 *
	 * @returns {void}
	 */
	render() {
		if (this._stage === 0) {
			return;
		}
		let ctx = this._context, prevCol = null;
		ctx.clearRect(0, 0, this._canvas.width, this._canvas.height);
		ctx.fillStyle = this._options.dotColor;
		ctx.strokeStyle = this._options.lineColor;

		let offsetX = -this._offset.x;

		for (let c = 0; c < this._points.length; c++) {
			let col = this._points[c];
			for (let r = 0; r < col.length; r++) {
				let point = col[r];
				ctx.beginPath();
				ctx.arc(point.x + offsetX, point.y, 2, 0, Math.PI * 2, false);
				ctx.closePath();
				ctx.fill();

				ctx.beginPath();
				if (r > 0) {
					// connect with the previous point
					ctx.moveTo(col[r - 1].x + offsetX, col[r - 1].y);
					ctx.lineTo(point.x + offsetX, point.y);
				}
				if (c > 0) {
					// connect with the previous column and same row
					ctx.moveTo(this._points[c - 1][r].x + offsetX, this._points[c - 1][r].y);
					ctx.lineTo(point.x + offsetX, point.y);
					if (r > 0) {
						// connect with the previous column and previous row
						ctx.moveTo(this._points[c - 1][r - 1].x + offsetX, this._points[c - 1][r - 1].y);
						ctx.lineTo(point.x + offsetX, point.y);
					}
				}
				ctx.stroke();
			}
			prevCol = col;
		}
		window.requestAnimationFrame(this._handlers.render);
	}
}

Core.FlyingWebEffect = FlyingWebEffect;
export default FlyingWebEffect;