import {Core} from '../namespaces';
import UIElement from "./UIElement";
import UIElementTemplate from "./UIElementTemplate";
import Component from "../Component";
import UIPopupController from "./UIPopupController";
import Application from "../Application";
import UIWindow from "./UIWindow";
import RootController from "../RootController";

/**
 * @typedef {Object} T_UIContextMenuOptions
 * @property {string} [position] Position relative to target element/point when menu is appearing.
 * See constants of UIPopupController. Default is UIPopupController.POSITION_BOTTOM
 * @property {string} [align] Horizontal alignment relative to target element/point when menu is appearing.
 * See constants of UIPopupController. Default is UIPopupController.ALIGN_RIGHT
 * @property {Array<T_UIContextMenuItemOptions|null>} [items]
 */

/**
 * @typedef {Object} T_UIContextMenuItemOptions
 * @property {string} caption
 * @property {string} [value]
 * @property {function(value:string, menu:UIContextMenu):void} [onClick]
 * @property {string} [icon]
 * @property {boolean} [isEnabled]
 * @property {boolean} [isChecked]
 * @property {T_UIContextMenuOptions} [submenu]
 */

/**
 * @type {UIElementTemplate}
 */
let template = null;

/**
 * Context Menu Element
 */
class UIContextMenu extends Component {
	/**
	 * On select menu item event
	 *
	 * @type {string}
	 */
	static get SELECT() {
		return 'select';
	}

	/**
	 * On close menu event
	 *
	 * @type {string}
	 */
	static get CLOSE() {
		return 'close';
	}

	/**
	 * Class Constructor
	 *
	 * @param {T_UIContextMenuOptions} [options]
	 */
	constructor(options = {}) {
		super();

		/**
		 * @type {T_UIContextMenuOptions}
		 * @private
		 */
		this._options = options;

		/**
		 * @type {UIElement}
		 * @private
		 */
		this._target = null;

		/**
		 * @type {boolean}
		 * @private
		 */
		this._isFakeTarget = false;

		/**
		 * @type {UIPopupController}
		 * @private
		 */
		this._popupController = null;

		/**
		 * @type {boolean}
		 * @private
		 */
		this._shown = false;

		/**
		 * @type {Map<T_UIContextMenuItemOptions, UIContextMenu>}
		 * @private
		 */
		this._subMenus = new Map();

		/**
		 * @type {UIContextMenu}
		 * @private
		 */
		this._parentMenu = null;

		if (options.items) {
			options.items.map(item => {
				if (item && typeof item.value === 'undefined') {
					item.value = this.utils.generateUID();
				}
			});
		}

		if (template === null) {
			template = new UIElementTemplate(require('./templates/context-menu.ejs'));
		}
	}

	/**
	 * Shows context menu over ths target element
	 *
	 * @param {Element|jQuery|UIElement|string|Node|Component|HTMLElement} el
	 * @returns {UIContextMenu}
	 */
	showOverElement(el) {
		if (!(el instanceof UIElement)) {
			el = new UIElement(el);
		}

		this._isFakeTarget = false;
		setTimeout(() => {
			if (this._shown) {
				this.app.addListener(Application.EVENT_PAGE_UNLOAD, this.hide, this);
				if (this.app.window) {
					this.app.window.addListener(UIWindow.EVENT_CLOSE, this.hide, this);
					this.app.window.addListener(UIWindow.EVENT_DESTROYED, this.hide, this);
				}
				this.app.root.addListener(RootController.EVENT_GLOBAL_POINTER_DOWN, this.hide, this, document);
				this.app.addListener(Application.EVENT_PAGE_SCROLL, this._updatePosition, this);
				this.app.addListener(Application.EVENT_DOM_CHANGED, this._updatePosition, this);
			}
		}, 20);

		this._target = el;
		this._shown = true;
		this._render();
		return this;
	}

	/**
	 * Shows context menu over the specified point
	 *
	 * @param {number} x
	 * @param {number} y
	 * @returns {Promise}
	 */
	async showOverPoint(x, y) {
		// create fake target element
		let el = new UIElement();
		el.addClass('dropdown-menu-fake-target');
		el.setStyle({
			left: x + 'px',
			top: y + 'px',
		});
		el.appendTo(document.body);
		this.showOverElement(el);
		this._isFakeTarget = true;
		return this;
	}

	/**
	 * Returns TRUE if the item checked
	 *
	 * @param {string} itemValue
	 * @returns {boolean}
	 */
	isItemChecked(itemValue) {
		let item = this._getItemByValue(itemValue);
		if (!item) {
			return false;
		}
		return item.isChecked === true;
	}

	/**
	 * Checks/unchecks the specified menu item
	 *
	 * @param {string} itemValue
	 * @param {boolean} flag
	 * @returns {UIContextMenu}
	 */
	checkItem(itemValue, flag) {
		let item = this._getItemByValue(itemValue);
		if (!item) {
			return this;
		}
		item.isChecked = flag === true;
		this._render();
		return this;
	}

	/**
	 * Enables/disables the specified menu item
	 *
	 * @param {string} itemValue
	 * @param {boolean} flag
	 * @returns {UIContextMenu}
	 */
	enableItem(itemValue, flag) {
		let item = this._getItemByValue(itemValue);
		if (!item) {
			return this;
		}
		item.isEnabled = flag === true;
		this._render();
		return this;
	}

	/**
	 * Hides context menu
	 *
	 * @returns {UIContextMenu}
	 */
	hide() {
		this._shown = false;
		this.app.removeListener(Application.EVENT_PAGE_UNLOAD, this.hide, this);
		this.app.removeListener(Application.EVENT_DOM_CHANGED, this._updatePosition, this);
		if (this.app.window) {
			this.app.window.removeListener(UIWindow.EVENT_CLOSE, this.destroy, this);
			this.app.window.removeListener(UIWindow.EVENT_DESTROYED, this.destroy, this);
		}
		this.app.root.removeListener(RootController.EVENT_GLOBAL_POINTER_DOWN, this.hide, this, document);
		// hide all submenus
		for (let [item, submenu] of this._subMenus.entries()) {
			submenu.hide();
		}
		this.el.remove();
		if (this._isFakeTarget) {
			this._target.remove();
			this._isFakeTarget = false;
		}
		this.dispatchEvent(UIContextMenu.CLOSE);
		return this;
	}

	/**
	 * Renders the menu element
	 *
	 * @private
	 */
	_render() {
		if (!this._shown) {
			return;
		}
		if (this.el) {
			this.el.remove();
		}
		let items = this._options.items || [];
		this.el = template.createElement({
			items: items,
			hasIcons: typeof items.find(x => x && typeof x.icon !== 'undefined') !== 'undefined',
			hasCheckboxes: typeof items.find(x => x && typeof x.isChecked !== 'undefined') !== 'undefined'
		});
		this.el.appendTo(top.document.body);
		this.el.addListener('click pointerdown mousedown', e => e.stopPropagation());
		this.el.find('.dropdown-menu__item').addListener('click', this._onClickByItem.bind(this));
		this.el.find('.dropdown-menu__item').addListener('mouseover', this._onMouseOverItem.bind(this));

		this._popupController = new UIPopupController({
			element: this.el,
			showOnTop: true,
			direction: this._options.position || UIPopupController.POSITION_BOTTOM,
			align: this._options.align || UIPopupController.ALIGN_RIGHT,
			hide: this.hide.bind(this),
			show: this._setPosition.bind(this)
		});
		this._updatePosition();
	}

	/**
	 * Handles click by item
	 *
	 * @param {UIEvent} e
	 * @private
	 */
	_onClickByItem(e) {
		let value = e.element.getData('value');
		let item = this._getItemByValue(value);
		if (!item) {
			return;
		}

		let isChecked = item.isChecked;
		if (typeof item.onClick === 'function') {
			item.onClick(value, this);
		}
		this.dispatchEvent(UIContextMenu.SELECT, value);
		if (isChecked === item.isChecked) {
			// checked flag not changed, so we just close menu
			this.hide();
			// hide all parent menus
			let menu = this;
			while (menu._parentMenu) {
				menu._parentMenu.hide();
				menu = menu._parentMenu;
			}
		}
	}

	/**
	 * Handles mouse over event on item
	 *
	 * @param {UIEvent} e
	 * @private
	 */
	_onMouseOverItem(e) {
		let value = e.element.getData('value');
		let item = this._getItemByValue(value);
		if (!item) {
			return;
		}

		// hide all submenus
		for (let [item, submenu] of this._subMenus.entries()) {
			submenu.hide();
		}

		if (typeof item.submenu === 'object') {
			let submenu = this._subMenus.get(item);
			if (!submenu) {
				let props = {position: UIPopupController.POSITION_RIGHT};

				if (e.element.getGlobalX() + e.element.getWidth() > this.app.screen.width * 0.75) {
					props.position = UIPopupController.POSITION_LEFT;
				}

				submenu = new UIContextMenu(Object.assign(props, item.submenu));
				submenu._parentMenu = this;
				this._subMenus.set(item, submenu);
			}
			submenu.showOverElement(e.element);
		}
	}

	/**
	 * Finds and returns menu item by the specified value
	 *
	 * @param {string} value
	 * @private
	 * @returns {?T_UIContextMenuItemOptions}
	 */
	_getItemByValue(value) {
		return (this._options.items || []).find(i => i && String(i.value) === String(value)) || null;
	}

	/**
	 * Updates position of the context menu
	 *
	 * @private
	 */
	_updatePosition() {
		if (!this._shown) {
			return;
		}
		if (this._target !== null) {
			this._popupController.showOverTarget(this._target);
		}
	}

	/**
	 * Sets menu position
	 *
	 * @param {number} x
	 * @param {number} y
	 * @private
	 */
	_setPosition(x, y) {
		this.el.setStyle({
			top: y + 'px',
			left: x + 'px'
		});
	}
}

Core.UIContextMenu = UIContextMenu;
export default UIContextMenu;