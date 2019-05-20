import UIFormElement from "./UIFormElement";
import UIElement from "/src/js/ui/UIElement";
import UIElementTemplate from "/src/js/ui/UIElementTemplate";
import UIFEIVText from "./views/UIFEIVText";
import UIFEIVSignature from "./views/UIFEIVSignature";
import UIFEIVRadio from "./views/UIFEIVRadio";
import UIFEIVSelect from "./views/UIFEIVSelect";
import UIFEIVCheckbox from "./views/UIFEIVCheckbox";
import {Core} from "/src/js/namespaces";
import UIFormElementInputView from "./UIFormElementInputView";

/**
 * @type {UIElementTemplate}
 */
let commentTemplate = null;

/**
 * @type {UIElementTemplate}
 */
let commentBodyTemplate = null;

/**
 * Form Builder, Input Element Class
 */
class UIFormElementInput extends UIFormElement {
	/**
	 * Input signature View
	 *
	 * @type {string}
	 */
	static get VIEW_SIGNATURE() {
		return 'signature';
	}

	/**
	 * Input Text View
	 *
	 * @type {string}
	 */
	static get VIEW_TEXT() {
		return 'text';
	}

	/**
	 * Input Radio Button View
	 *
	 * @type {string}
	 */
	static get VIEW_RADIO() {
		return 'radio';
	}

	/**
	 * Input Checkbox View
	 *
	 * @type {string}
	 */
	static get VIEW_CHECKBOX() {
		return 'checkbox';
	}

	/**
	 * Input Select View
	 *
	 * @type {string}
	 */
	static get VIEW_SELECT() {
		return 'select';
	}

	/**
	 * Class constructor
	 *
	 * @param {UIFormBridge} bridge
	 * @param {boolean} [tempElement]
	 */
	constructor(bridge, tempElement = false) {
		super('input', bridge, tempElement);

		/**
		 * @private
		 * @type {string}
		 */
		this._view = '';

		/**
		 * @private
		 * @type {string}
		 */
		this._name = '';

		/**
		 * @private
		 * @type {string}
		 */
		this._groupName = '';

		/**
		 * @private
		 * @type {{
		 * entity_name: string,
		 * entity_caption: string,
		 * read_only: boolean,
		 * maxlength: number,
		 * input_type: string,
		 * field_name: string,
		 * field_caption: string,
		 * field_value: string,
		 * field_options: Object,
		 * field_options_keys: Array,
		 * field_option: string
		 * }}
		 */
		this._entityInfo = {
			'entity_name': '',
			'entity_caption': '',
			'read_only': false,
			'maxlength': 0,
			'required': false,
			'grouped_value': false,
			'input_type': 'text',
			'field_name': '',
			'field_caption': '',
			'field_options': null,
			'field_options_keys': null,
			'field_option': null,
			'field_value': null
		};

		/**
		 * @private
		 * @type {UIFormElementInputView}
		 */
		this._viewInst = null;

		/**
		 * Text align
		 *
		 * @private
		 * @type {string}
		 */
		this._align = 'left';

		/**
		 * Markup lines
		 *
		 * @private
		 * @type {string}
		 */
		this._markup = '';

		/**
		 * @private
		 * @type {boolean}
		 */
		this._textItalic = true;

		/**
		 * @private
		 * @type {boolean}
		 */
		this._textBold = false;

		/**
		 * @private
		 * @type {string}
		 */
		this._textColor = '000000';

		/**
		 * @private
		 * @type {string}
		 */
		this._comment = '';

		/**
		 * @private
		 * @type {UIElement}
		 */
		this._commentElem = null;

		/**
		 * @private
		 * @type {boolean}
		 */
		this._readOnly = false;

		/**
		 * Grouped Value flag
		 *
		 * @private
		 * @type {boolean}
		 */
		this._groupedValue = false;

		/**
		 * Input text max length
		 *
		 * @private
		 * @type {number}
		 */
		this._maxlength = 0;

		/**
		 * Required flag
		 *
		 * @private
		 * @type {boolean}
		 */
		this._required = false;

		/**
		 * Input type
		 *
		 * @private
		 * @type {string}
		 */
		this._inputType = 'text';

		this.el.addClass('form-builder-element_input');
		this._name = this.bridge.getUniqueElementName();
		this.setView('text');
	}

	/**
	 * Returns Element Name
	 *
	 * @returns {string}
	 */
	getName() {
		return this._name;
	}

	/**
	 * Sets Element Name
	 *
	 * @returns {UIFormElementInput}
	 */
	setName(val) {
		this._name = String(val);
		return this;
	}

	/**
	 * Returns Element Group Name
	 *
	 * @returns {string}
	 */
	getGroupName() {
		return this._groupName;
	}

	/**
	 * Sets Element Group Name
	 *
	 * @returns {UIFormElementInput}
	 */
	setGroupName(val) {
		this._groupName = String(val);
		return this;
	}

	/**
	 * Returns full name of the input: group_name + entity_name + field_name
	 *
	 * @returns {string}
	 */
	getFullName() {
		return this._groupName + '::' + this._entityInfo.entity_name + '::' + this._name;
	}

	/**
	 * Sets entity info
	 *
	 * @param {object} info
	 * @returns {UIFormElementInput}
	 */
	setEntityInfo(info) {
		this._name = info.field_name;
		let entityInfo = this._entityInfo;
		entityInfo.entity_name = info.entity_name;
		entityInfo.entity_caption = info.entity_caption;
		entityInfo.required = info.required === true;
		entityInfo.grouped_value = info.grouped_value === true;
		entityInfo.read_only = info.read_only === true;
		entityInfo.maxlength = info.maxlength;
		entityInfo.input_type = info.input_type;
		entityInfo.field_name = info.field_name;
		entityInfo.field_caption = info.field_caption;
		entityInfo.field_option = info.field_option;
		entityInfo.field_value = info.field_value;
		entityInfo.field_options = JSON.parse(JSON.stringify(info.field_options));
		entityInfo.field_options_keys = info.field_options_keys;

		if (entityInfo.field_options) {
			// select first option
			for (let opt in entityInfo.field_options) {
				if (entityInfo.field_options.hasOwnProperty(opt)) {
					entityInfo.field_option = opt;
					break;
				}
			}
			if (this._viewInst && (this._viewInst instanceof UIFEIVSelect)) {
				this._viewInst.setDefaultValue(entityInfo.field_option);
				this._viewInst.setOptions(entityInfo.field_options, entityInfo.field_options_keys);
			}
		}
		this.el.toggleClass('form-builder-element_required', this.getRequiredFlag() > 0);
		this._viewInst && this._viewInst.update();
		return this;
	}

	/**
	 * Returns assigned entity info
	 *
	 * @returns {Object}
	 */
	getEntityInfo() {
		return this._entityInfo;
	}

	/**
	 * Sets option value
	 *
	 * @param {string} val
	 * @returns {UIFormElementInput}
	 */
	setOptionValue(val) {
		this._entityInfo.field_option = String(val);
		if (this._viewInst && (this._viewInst instanceof UIFEIVSelect)) {
			this._viewInst.setDefaultValue(this._entityInfo.field_option);
		}
		return this;
	}

	/**
	 * Returns option value
	 *
	 * @returns {string}
	 */
	getOptionValue() {
		return this._entityInfo.field_option;
	}

	/**
	 * Sets option value
	 *
	 * @param {string} val
	 * @returns {UIFormElementInput}
	 */
	setFieldValue(val) {
		this._entityInfo.field_value = String(val);
		return this;
	}

	/**
	 * Returns option value
	 *
	 * @returns {string}
	 */
	getFieldValue() {
		return this._entityInfo.field_value;
	}

	/**
	 * Returns input type
	 *
	 * @returns {string}
	 */
	getInputType() {
		return this._entityInfo.entity_name !== '' ? (this._entityInfo.input_type || 'text') : (this._inputType || 'text');
	}

	/**
	 * Returns input text max length
	 *
	 * @returns {number}
	 */
	getMaxlength() {
		return this._entityInfo.maxlength > 0 ? this._entityInfo.maxlength : this._maxlength;
	}

	/**
	 * Sets input type
	 *
	 * @param {string} value
	 * @returns {UIFormElementInput}
	 */
	setInputType(value) {
		this._inputType = String(value);
		if (this._viewInst) {
			this._viewInst.update();
		}
		return this;
	}

	/**
	 * Returns text align
	 *
	 * @returns {string}
	 */
	getTextAlign() {
		return this._align;
	}

	/**
	 * Sets text align
	 *
	 * @param {string} value
	 * @returns {UIFormElementInput}
	 */
	setTextAlign(value) {
		this._align = String(value);
		if (this._viewInst) {
			this._viewInst.update();
		}
		return this;
	}

	/**
	 * Returns Markup Lines type
	 *
	 * @returns {string}
	 */
	getMarkupType() {
		return this._markup;
	}

	/**
	 * Sets Markup Lines type
	 *
	 * @param {string} value
	 * @returns {UIFormElementInput}
	 */
	setMarkupType(value) {
		this._markup = String(value);
		if (this._viewInst) {
			this._viewInst.update();
		}
		return this;
	}

	/**
	 * Returns TRUE if the italic style of the text is enabled
	 *
	 * @returns {boolean}
	 */
	isItalicText() {
		return this._textItalic;
	}

	/**
	 * Sets italic text style
	 *
	 * @param {boolean} value
	 * @returns {UIFormElementInput}
	 */
	setItalicText(value) {
		this._textItalic = Boolean(value);
		if (this._viewInst) {
			this._viewInst.update();
		}
		return this;
	}

	/**
	 * Returns TRUE if the bold style of the text is enabled
	 *
	 * @returns {boolean}
	 */
	isBoldText() {
		return this._textBold;
	}

	/**
	 * Sets bold text style
	 *
	 * @param {boolean} value
	 * @returns {UIFormElementInput}
	 */
	setBoldText(value) {
		this._textBold = Boolean(value);
		if (this._viewInst) {
			this._viewInst.update();
		}
		return this;
	}

	/**
	 * Sets font style as string which contains the following chars:
	 *      'B' - font weight is bold,
	 *      'I' - font style is italic
	 *
	 * @param {string} value
	 * @returns {UIFormElementInput}
	 */
	setFontStyle(value) {
		value = String(value);
		this._textBold = value.indexOf('B') > -1;
		this._textItalic = value.indexOf('I') > -1;
		if (this._viewInst) {
			this._viewInst.update();
		}
		return this;
	}

	/**
	 * Returns font style as string which contains the following chars:
	 *      'B' - font weight is bold,
	 *      'I' - font style is italic
	 *
	 * @returns {string}
	 */
	getFontStyle() {
		let value = [];
		if (this._textBold) {
			value.push('B');
		}
		if (this._textItalic) {
			value.push('I');
		}
		return value.join(',');
	}

	/**
	 * Returns color of the text
	 *
	 * @returns {string}
	 */
	getTextColor() {
		return this._textColor;
	}

	/**
	 * Sets color of the text
	 *
	 * @param {string} value
	 * @returns {UIFormElementInput}
	 */
	setTextColor(value) {
		if (!value) {
			value = '000000';
		}
		this._textColor = String(value).replace(/#/g, '');
		if (this._viewInst) {
			this._viewInst.update();
		}
		return this;
	}

	/**
	 * Sets input view (see constants of this class)
	 *
	 * @param {string} value
	 * @returns {UIFormElementInput}
	 */
	setView(value) {
		this._view = value;
		if (this._viewInst) {
			this._viewInst.destroy(true);
			this._viewInst = null;
		}
		switch (this._view) {
			default:
			case UIFormElementInput.VIEW_TEXT:
				this._viewInst = new UIFEIVText(this, this.bridge);
				break;
			case UIFormElementInput.VIEW_SIGNATURE:
				this._viewInst = new UIFEIVSignature(this, this.bridge);
				break;
			case UIFormElementInput.VIEW_RADIO:
				this._viewInst = new UIFEIVRadio(this, this.bridge);
				break;
			case UIFormElementInput.VIEW_CHECKBOX:
				this._viewInst = new UIFEIVCheckbox(this, this.bridge);
				break;
			case UIFormElementInput.VIEW_SELECT:
				this._viewInst = new UIFEIVSelect(this, this.bridge);
				if (this._entityInfo && this._entityInfo.field_options) {
					this._viewInst.setOptions(this._entityInfo.field_options, this._entityInfo.field_options_keys);
					if (this._entityInfo.field_option) {
						this._viewInst.setDefaultValue(this._entityInfo.field_option);
					}
				}
				break;
		}
		this._viewInst.el.appendTo(this.getContainer());
		this._viewInst.update();
		return this;
	}

	/**
	 * Returns Input Element View Type
	 *
	 * @returns {string}
	 */
	getView() {
		return this._view;
	}

	/**
	 * Returns Input Element View Instance
	 *
	 * @returns {UIFormElementInputView}
	 */
	getViewInstance() {
		return this._viewInst;
	}

	/**
	 * Unfocuses element
	 *
	 * @returns {UIFormElementInput}
	 */
	blur() {
		super.blur();
		this._viewInst.blur();
		return this;
	}

	/**
	 * Sets comment text
	 *
	 * @param {string} text
	 * @returns {UIFormElementInput}
	 */
	setComment(text) {
		if (text) {
			this._comment = String(text);
		} else {
			this._comment = '';
		}
		this._updateCommentElement();
		return this;
	}

	/**
	 * Returns text of the comment
	 *
	 * @returns {string}
	 */
	getComment() {
		return this._comment;
	}

	/**
	 * Updates Comment DOM Element
	 *
	 * @private
	 * @returns {void}
	 */
	_updateCommentElement() {
		if (this._comment === '') {
			if (this._commentElem) {
				this._commentElem.destroy();
				this._commentElem = null;
			}
			this.el.removeClass('form-builder-element_has-comment');
		} else {
			if (commentTemplate === null) {
				commentTemplate = new UIElementTemplate(require('./templates/comment.ejs'));
				commentBodyTemplate = new UIElementTemplate(require('./templates/comment_message.ejs'));
			}
			this._commentElem = commentTemplate.createElement({}).appendTo(this.el);

			this._commentElem.addListener('click', (e) => {
				new Core.UIBalloon({
					message: commentBodyTemplate.createElement({message: this._comment}),
					autoClose: true,
					showCloseButton: true,
					inTopFrame: true,
				}).show(this._commentElem);
			});
			this.el.addClass('form-builder-element_has-comment');
		}
	}

	/**
	 * Sets user defined readonly mode
	 *
	 * @param {string} value
	 * @returns {UIFormElementInput}
	 */
	setReadOnlyByUser(value) {
		this._readOnly = value === 'Y';
		this._viewInst && this._viewInst.update();
		return this;
	}

	/**
	 * Returns 'Y' if readonly mode is ON by user
	 *
	 * @returns {string}
	 */
	getReadOnlyByUser() {
		return this._readOnly ? 'Y' : '';
	}

	/**
	 * Sets user defined required flag
	 *
	 * @param {string} value
	 * @returns {UIFormElementInput}
	 */
	setRequiredFlagByUser(value) {
		this._required = value === 'Y';
		this.el.toggleClass('form-builder-element_required', this.getRequiredFlag() > 0);
		return this;
	}

	/**
	 * Returns 'Y' if required flag is ON by user
	 *
	 * @returns {string}
	 */
	getRequiredFlagByUser() {
		return this._required ? 'Y' : '';
	}

	/**
	 * Returns ReadOnly flag:
	 *  0 - writable,
	 *  1 - read only (user defined)
	 *  2 - read only (entity defined and cannot be changed)
	 *
	 *  @returns {number}
	 */
	getReadOnlyFlag() {
		if (this._entityInfo && this._entityInfo.read_only) {
			return 2;
		} else if (this._readOnly) {
			return 1;
		}
		return 0;
	}

	/**
	 * Sets user defined GroupedValue mode
	 *
	 * @param {string} value
	 * @returns {UIFormElementInput}
	 */
	setGroupedValue(value) {
		this._groupedValue = value === 'Y';
		this._viewInst && this._viewInst.update();
		return this;
	}

	/**
	 * Returns GroupedValue flag:
	 *  0 - writable,
	 *  1 - read only (user defined)
	 *  2 - read only (entity defined and cannot be changed)
	 *
	 *  @returns {number}
	 */
	getGroupedValueFlag() {
		if (this._entityInfo && this._entityInfo.grouped_value) {
			return 2;
		} else if (this._groupedValue) {
			return 1;
		}
		return 0;
	}

	/**
	 * Returns Required flag:
	 *  0 - optional,
	 *  1 - required (user defined)
	 *  2 - required (entity defined and cannot be changed)
	 *
	 *  @returns {number}
	 */
	getRequiredFlag() {
		if (this._entityInfo && this._entityInfo.required) {
			return 2;
		} else if (this._required) {
			return 1;
		}
		return 0;
	}

	/**
	 * Sets item data
	 *
	 * @param {Object} data
	 * @returns {UIFormElementInput|this}
	 */
	setData(data) {
		super.setData(data);
		if (typeof data.name !== 'undefined') {
			this._name = data.name;
			this._groupName = data.group;
		}

		this._readOnly = data.read_only === true;
		this._required = data.required === true;
		this._groupedValue = data.grouped_value === true;

		if (data.input_type) {
			this._inputType = data.input_type;
		}

		if (data.maxlength) {
			this._maxlength = Number(data.maxlength);
		}

		if (data.entity) {
			this._entityInfo = JSON.parse(data.entity);
		}

		this.setView(data.view);

		if (data.align) {
			this._align = String(data.align);
			this._markup = String(data.markup);
		} else {
			this._align = 'left';
			if (data.view === 'text') {
				this._markup = 'lines';
			}
		}

		if (data.italic !== undefined) {
			this._textItalic = data.italic;
			this._textBold = data.bold;
			this._textColor = data.color;
		}
		this.el.toggleClass('form-builder-element_required', this.getRequiredFlag() > 0);
		this._viewInst.update();
	}

	/**
	 * Returns data of the item
	 *
	 * @returns {Object}
	 */
	getData() {
		let data = super.getData();
		data.view = this._view;
		data.name = this._name;
		data.group = this._groupName;
		data.align = this._align;
		data.markup = this._markup;
		data.italic = this._textItalic;
		data.bold = this._textBold;
		data.color = this._textColor;
		data.read_only = this._readOnly;
		data.maxlength = this._maxlength;
		data.required = this._required;
		data.grouped_value = this._groupedValue;
		data.input_type = this._inputType;
		data.entity = JSON.stringify(this._entityInfo);
		return data;
	}
}

Core.UIFormElementInput = UIFormElementInput;
export default UIFormElementInput;