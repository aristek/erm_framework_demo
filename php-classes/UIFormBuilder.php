<?php

	/**
	 * Form Builder UI Component
	 *
	 * @method static UIFormBuilder factory(string $name = null)
	 */
	final class UIFormBuilder implements UIElementInterface, UIRootElementInterface {
		use FactoryTrait;

		/**
		 * Instance name
		 *
		 * @var string
		 */
		private $name;

		/**
		 * Form Builder Settings
		 *
		 * @var FBSettings
		 */
		private $settings;

		/**
		 * Form Data
		 *
		 * @var array
		 */
		private $data;

		/**
		 * Class Constructor
		 *
		 * @param string $name
		 */
		public function __construct($name = null) {
			$this->name = $name ? $name : CoreUtils::generateUID();
			$this->settings = FBSettings::factory();
		}

		/**
		 * Appends necessary JavaScript/CSS libraries to the page
		 *
		 * @return void
		 */
		public static function appendJS() {
			FileUtils::getCSSFile('font-freesans.css')->append();
		}

		/**
		 * Sets Form Builder settings
		 *
		 * @param FBSettings $settings
		 * @return UIFormBuilder
		 */
		public function setSettings(FBSettings $settings) {
			$this->settings = $settings;
			return $this;
		}

		/**
		 * Sets form data
		 *
		 * @param string|array $data
		 * @return UIFormBuilder
		 */
		public function setData($data) {
			$this->data = FBDocument::factory($data)->getData();
			return $this;
		}

		/**
		 * Builds and returns  Form Builder Main Toolbar
		 *
		 * @return UIElementInterface
		 */
		private function getToolbar() {

			$jsCode = 'Core.UIFormBuilder.get(' . json_encode($this->name) . ')';

			$elems = UILayout::factory()
				->viewStyle(UILayout::VIEW_TOOLS)
				->addObject(
					FFMenuButton::factory('Document')
						->addItem('New Document', $jsCode . '.newDocument()', 'fa fa-file-o')
						->addLine()
						->addItem('Save File (.' . FBDocument::FILE_EXTENSION . ')', $jsCode . '.saveFile()', 'fa fa-save')
						->addItem('Save File As...', $jsCode . '.saveFile(false, true)')
						->addItem('Open File', $jsCode . '.openFile()', 'fa fa-folder-o')
						->addItem('Download File', $jsCode . '.downloadFile()', 'fa fa-download')
						->addLine()
						->addItem('Preview', $jsCode . '.preview()', 'fa fa-search')
						->addItem('Print', $jsCode . '.print()', 'fa fa-print')
						->leftIcon('fa fa-bars')
						->onClick($jsCode . '.undo()')
						->hideValueOnTablet()
				)
				->addDividingCell()
				->addObject(
					FFButton::factory('Undo')
						->leftIcon('fa fa-undo')
						->name($this->name . '_undo_btn')
						->onClick($jsCode . '.undoLastAction()')
						->toolBarView(true)
				)
				->addObject(
					FFButton::factory('Redo')
						->leftIcon('fa fa-repeat')
						->name($this->name . '_redo_btn')
						->onClick($jsCode . '.redoLastAction()')
						->toolBarView(true)
				)
				->addDividingCell()
				->addObject(
					FFButton::factory('Cursor')
						->leftIcon('fa fa-mouse-pointer')
						->name($this->name . '_cur_btn')
						->onClick($jsCode . '.setTool(Core.UIFormBridge.TOOL_CURSOR)')
						->hideValueOnTablet()
				)
				->addObject(
					FFButton::factory('Add Text')
						->leftIcon('lf lf-text01')
						->name($this->name . '_text_btn')
						->onClick($jsCode . '.setTool(Core.UIFormBridge.TOOL_ADD_ELEMENT)')
						->hideValueOnTablet()
				);

			$tool = FFMenuButton::factory('Add Input Element');

			$tool->addItem('Add Entity Field', $jsCode . '.openEntityWizard(true)', 'input_entity.png')
				->addLine();

			if ($this->settings->isToolEnabled(FBSettings::TOOL_INPUT)) {
				$tool->addItem('Input (text, number, date)', $jsCode . '.setTool(Core.UIFormBridge.TOOL_ADD_ELEMENT, Core.UIFormElementInput, {"setView": Core.UIFormElementInput.VIEW_TEXT})', 'lf lf-input-elem');
			}
			if ($this->settings->isToolEnabled(FBSettings::TOOL_SELECT)) {
				$tool->addItem('Select', $jsCode . '.setTool(Core.UIFormBridge.TOOL_ADD_ELEMENT, Core.UIFormElementInput, {"setView": Core.UIFormElementInput.VIEW_SELECT})', 'lf lf-select-elem');
			}
			if ($this->settings->isToolEnabled(FBSettings::TOOL_SELECT)) {
				$tool->addItem('Checkbox', $jsCode . '.setTool(Core.UIFormBridge.TOOL_ADD_ELEMENT, Core.UIFormElementInput, {"setView": Core.UIFormElementInput.VIEW_CHECKBOX})', 'lf lf-checkbox');
			}
			if ($this->settings->isToolEnabled(FBSettings::TOOL_RADIO)) {
				$tool->addItem('Radio Button', $jsCode . '.setTool(Core.UIFormBridge.TOOL_ADD_ELEMENT, Core.UIFormElementInput, {"setView": Core.UIFormElementInput.VIEW_RADIO})', 'lf lf-radiobutton');
			}
			if ($this->settings->isToolEnabled(FBSettings::TOOL_INPUT)) {
				$tool->addItem('Signature', $jsCode . '.setTool(Core.UIFormBridge.TOOL_ADD_ELEMENT, Core.UIFormElementInput, {"setView": Core.UIFormElementInput.VIEW_SIGNATURE})', 'lf lf-signature01');
			}
			if ($this->settings->isToolEnabled(FBSettings::TOOL_FIELDS_SET)) {
				$tool->addLine()
					->addItem('Fields Set', $jsCode . '.setTool(Core.UIFormBridge.TOOL_ADD_ELEMENT, Core.UIFormElementGroup)', 'lf lf-fieldset');
			}

			$tool->leftIcon('lf lf-controls01')
				->hideValueOnTablet()
				->name($this->name . '_inp_btn');

			$elems->addObject($tool);
			$elems->addObject(
				FFMenuButton::factory('Add Shape/Line')
					->addItem('Horizontal Line', $jsCode . '.setTool(Core.UIFormBridge.TOOL_DRAW_SHAPE, Core.UIFormElementShape, {"setType": Core.UIFormElementShape.TYPE_LINE_HORIZONTAL})')
					->addItem('Vertical Line', $jsCode . '.setTool(Core.UIFormBridge.TOOL_DRAW_SHAPE, Core.UIFormElementShape, {"setType": Core.UIFormElementShape.TYPE_LINE_VERTICAL})')
					->addLine()
					->addItem('Rectangle', $jsCode . '.setTool(Core.UIFormBridge.TOOL_DRAW_SHAPE, Core.UIFormElementShape, {"setType": Core.UIFormElementShape.TYPE_RECT})')
					->addItem('Table', $jsCode . '.setTool(Core.UIFormBridge.TOOL_ADD_ELEMENT, Core.UIFormElementTable)')
					->addLine()
					->addItem('Arrow', $jsCode . '.setTool(Core.UIFormBridge.TOOL_DRAW_SHAPE, Core.UIFormElementShape, {"setType": Core.UIFormElementShape.TYPE_ARROW})')
					->leftIcon('lf lf-shapes01')
					->name($this->name . '_shape_btn')
					->hideValueOnTablet()
			);
			$elems->addObject(
				FFButton::factory('Add Image')
					->leftIcon('fa fa-image')
					->name($this->name . '_image_btn')
					->onClick($jsCode . '.setTool(Core.UIFormBridge.TOOL_ADD_ELEMENT, Core.UIFormElementImage)')
					->hideValueOnTablet()
			);
			$elems->addObject(
				FFButton::factory('Add Page to the End')
					->leftIcon('lf lf-add-page')
					->name($this->name . '_page_btn')
					->onClick($jsCode . '.addNewPage()')
					->hideValueOnTablet()
			);
			$elems->addFreeSpace();
			return $elems;
		}

		/**
		 * Builds and returns Form Builder Properties
		 *
		 * @return UIElementInterface
		 */
		private function getPropertiesBar() {
			$sb = new UISlideBox($this->name . '_sb');
			$sb->height('100%');

			$tabs = UITabs::factory($this->name . '_props_tabs')
				->autoHeight(false);

			$tabs->addTab('Doc', null, 'doc')
				->allowScrolling(true)
				->addObject(
					UIDiv::factory()
						->asBlockElement()
						->append($this->getDocumentProperties())
						->id($this->name . '_doc_props')
				);

			$tabs->addTab('Page', null, 'page')
				->allowScrolling(true)
				->addObject(
					UIDiv::factory()
						->asBlockElement()
						->append($this->getPageProperties())
						->id($this->name . '_page_props')
				);

			$tabs->addTab('Element', null, 'elem')
				->allowScrolling(true)
				->addObject(
					UIDiv::factory()
						->asBlockElement()
						->append($this->getElementProperties())
						->id($this->name . '_elem_props')
				);

			$sb->addBox('Properties')
				->addObject($tabs);

			$tabs = UITabs::factory()
				->autoHeight(false);

			$tabs->addTab('<i class="fa fa-history"></i> History')
				->addObject(
					UIDiv::factory()
						->asBlockElement()
						->className('options-list options-list_history')
						->id($this->name . '_hist_bar')
				);
			$tabs->addTab('Pages')
				->addObject(
					UIDiv::factory()
						->asBlockElement()
						->className('options-list')
						->id($this->name . '_pages_bar')
				);

			$sb->addBox('Document')
				->collapsed()
				->addObject($tabs);

			return $sb;
		}

		/**
		 * Builds and returns Document Properties
		 *
		 * @return UIElementInterface
		 */
		private function getDocumentProperties() {
			$jsCode = 'Core.UIFormBuilder.get(' . json_encode($this->name) . ')';
			$table = new UITable('100%');
			$table->viewStyle(UITable::VIEW_FORM);

			$table->addGroupRow('Sizes');

			$table->addRow(UITableAttr::factory()->attr('data-role', 'grid_size'));
			$table->addCell('Grid: ', '.text-label');
			$table->addCell(
				FFSelect::factory()
					->data([
						'8' => 'Extra Small',
						'10' => 'Small',
						'12' => 'Medium',
						'14' => 'Large',
						'16' => 'Extra Large'
					])
					->onChange($jsCode . '.changeProps("grid_size", this.value)')
					->attr('data-field', 'grid_size')
					->htmlWrap('')
			);


			$table->addRow(UITableAttr::factory()->attr('data-role', 'doc_font_size_factor'));
			$table->addCell('Font: ', '.text-label');
			$table->addCell(
				FFSelect::factory()
					->data([
						'0.75' => 'Small',
						'1' => 'Normal',
						'1.25' => 'Large'
					])
					->onChange($jsCode . '.changeProps("doc_font_size_factor", this.value)')
					->attr('data-field', 'doc_font_size_factor')
					->htmlWrap('')
			);

			$table->addGroupRow('Copyright');

			$table->addRow(UITableAttr::factory()->attr('data-role', 'doc_copyright_mode'));
			$table->addCell('Mode: ', '.text-label');
			$table->addCell(
				FFRadioList::factory()
					->data([
						'd' => 'Default',
						'c' => 'Custom'
					])
					->name('copyright_mode')
					->onChange($jsCode . '.changeProps("doc_copyright_mode", this.value)')
					->attr('data-field', 'doc_copyright_mode')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'doc_copyright'));
			$table->addCell('Text: ', '.text-label');
			$table->addCell(
				FFTextArea::factory()
					->onChange($jsCode . '.changeProps("doc_copyright", this.value)')
					->attr('data-field', 'doc_copyright')
					->css('height', '50px')
					->disabledIf('copyright_mode', 'd')
					->htmlWrap('')
			);

			$table->addGroupRow('Other');

			$table->addRow(UITableAttr::factory()->attr('data-role', 'doc_page_numeration_flag'));
			$table->addCell('Pagination: ', '.text-label');
			$table->addCell(
				FFRadioList::factory()
					->data([
						'N' => 'No',
						'Y' => 'Yes'
					])
					->onChange($jsCode . '.changeProps("doc_page_numeration_flag", this.value)')
					->attr('data-field', 'doc_page_numeration_flag')
					->htmlWrap('')
			);

			return $table;
		}

		/**
		 * Builds and returns Page Properties
		 *
		 * @return UIElementInterface
		 */
		private function getPageProperties() {
			$jsCode = 'Core.UIFormBuilder.get(' . json_encode($this->name) . ')';
			$table = new UITable('100%');
			$table->viewStyle(UITable::VIEW_FORM);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'page_title'));
			$table->addCell('Title: ');
			$table->addCell(
				FFInput::factory()
					->onChange($jsCode . '.changeProps("page_title", this.value)')
					->attr('data-field', 'page_title')
					->maxlength(100)
					->width('100%')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'page_size'));
			$table->addCell('Size: ');
			$table->addCell(
				FFSelect::factory()
					->data([
						'794x1122' => 'A4 (portrait)',
						'1122x794' => 'A4 (landscape)',
						'816x1056' => 'Letter (portrait)',
						'1056x816' => 'Letter (landscape)'
					])
					->width(160)
					->onChange($jsCode . '.changeProps("page_size", this.value)')
					->attr('data-field', 'page_size')
					->htmlWrap('')
			);

			$table->addNoInfoRow('No page selected...', UITableAttr::factory()->attr('data-msg-target', $this->name . '_page_props'));

			return $table;
		}

		/**
		 * Builds and returns Element Properties
		 *
		 * @return UIElementInterface
		 */
		private function getElementProperties() {
			$jsName = json_encode($this->name);
			$jsCode = 'Core.UIFormBuilder.get(' . $jsName . ')';

			$table = new UITable('100%');
			$table->viewStyle(UITable::VIEW_FORM);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'name'));
			$table->addCell('Name: ');
			$table->addCell(
				FFInput::factory()
					->placeHolder('Element Name')
					->onChange($jsCode . '.changeProps("name", this.value)')
					->attr('data-field', 'name')
					->maxlength(25)
					->width('100%')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'read_only_group'));
			$table->addCell('Flag: ');
			$table->addCell(
				FFCheckBoxList::factory()
					->displaySelectAllButton(false)
					->data([
						'Y' => 'Lock to add/del recs'
					])
					->onChange($jsCode . '.changeProps("read_only_group", this.value)')
					->attr('data-field', 'read_only_group')
					->htmlWrap('')
			);

			$table->addGroupRow('Entity', '', UITableAttr::factory()->attr('data-role', 'entity_props'));

			$table->addRow(UITableAttr::factory()->attr('data-role', 'entity_info'));
			$table->addCell('', '');
			$table->addCell(
				UIDiv::factory()
					->append(
						FFButton::factory('Set')
							->viewStyle(FFButton::VIEW_SUCCESS)
							->leftIcon('pencil.png')
							->onClick($jsCode . '.openEntityWizard()')
							->htmlWrap('')
					)
					->append(
						FFButton::factory()
							->hint('Clear Entity')
							->viewStyle(FFButton::VIEW_DANGER_ROUND)
							->leftIcon('trash.png')
							->onClick($jsCode . '.clearEntity()')
							->htmlWrap('')
					)
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'entity_info'));
			$table->addCell('Entity:');
			$table->addCell(
				UIDiv::factory('')
					->attr('data-empty-text', 'not defined')
					->attr('data-field', 'entity_caption')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'entity_info'));
			$table->addCell('Field:');
			$table->addCell(
				UIDiv::factory('')
					->attr('data-empty-text', 'not defined')
					->attr('data-field', 'field_caption')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'field_option'));
			$table->addCell('Option:');
			$table->addCell(
				FFSelect::factory()
					->onChange($jsCode . '.changeProps("field_option", this.value)')
					->attr('data-field', 'field_option')
					->width('100%')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'field_value'));
			$table->addCell('Value:');
			$table->addCell(
				FFInput::factory()
					->onChange($jsCode . '.changeProps("field_value", this.value)')
					->attr('data-field', 'field_value')
					->width('100%')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'required'));
			$table->addCell('');
			$table->addCell(
				FFCheckBoxList::factory()
					->displaySelectAllButton(false)
					->data(['Y' => 'Required'])
					->onChange($jsCode . '.changeProps("required", this.value)')
					->attr('data-field', 'required')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'read_only'));
			$table->addCell('');
			$table->addCell(
				FFCheckBoxList::factory()
					->displaySelectAllButton(false)
					->data(['Y' => 'Read Only'])
					->onChange($jsCode . '.changeProps("read_only", this.value)')
					->attr('data-field', 'read_only')
					->htmlWrap('')
			);

			$table->addGroupRow('Input Field', '', UITableAttr::factory()->attr('data-role', 'input_props'));

			$table->addRow(UITableAttr::factory()->attr('data-role', 'input_type'));
			$table->addCell('Type:');
			$table->addCell(
				FFSelect::factory()
					->data([
						'text' => 'Text',
						'number' => 'Number',
						'date' => 'Date',
						'time' => 'Time',
						'phone' => 'Phone'
					])
					->onChange($jsCode . '.changeProps("input_type", this.value)')
					->attr('data-field', 'input_type')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'text_align'));
			$table->addCell('Text Align:');
			$table->addCell(
				FFSelect::factory()
					->data([
						'left' => 'Left',
						'center' => 'Center',
						'right' => 'Right'
					])
					->onChange($jsCode . '.changeProps("text_align", this.value)')
					->attr('data-field', 'text_align')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'text_style'));
			$table->addCell('Font Style:');
			$table->addCell(
				FFCheckBoxList::factory()
					->displaySelectAllButton(false)
					->data([
						'B' => 'Bold',
						'I' => 'Italic'
					])
					->onChange($jsCode . '.changeProps("text_style", this.value)')
					->attr('data-field', 'text_style')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'text_color'));
			$table->addCell('Text Color:');
			$table->addCell(
				FFColorPicker::factory()
					->showColorPicker(false)
					->showClearButton(false)
					->onChange($jsCode . '.changeProps("text_color", this.value)')
					->attr('data-field', 'text_color')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'font_size_factor'));
			$table->addCell('Text Color:');
			$table->addCell(
				FFSelect::factory()
					->data([
						'0.25' => '25%',
						'0.5' => '50%',
						'0.75' => '75%',
						'1' => '100%',
						'1.25' => '125%',
						'1.5' => '150%',
						'1.75' => '175%',
						'2' => '200%',
						'2.5' => '250%',
						'3' => '300%',
						'3.5' => '350%',
						'4' => '400%',
					])
					->onChange($jsCode . '.changeProps("font_size_factor", this.value)')
					->attr('data-field', 'font_size_factor')
					->htmlWrap('')
			);

			$table->addGroupRow('View Style', '', UITableAttr::factory()->attr('data-role', 'view_props'));

			$table->addRow(UITableAttr::factory()->attr('data-role', 'markup'));
			$table->addCell('Markup:');
			$table->addCell(
				FFSelect::factory()
					->data([
						'' => 'None',
						'lines' => 'Lines'
					])
					->onChange($jsCode . '.changeProps("markup", this.value)')
					->attr('data-field', 'markup')
					->htmlWrap('')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'bg_color'));
			$table->addCell('Background:');
			$table->addCell(
				FFColorPicker::factory()
					->showColorPicker(false)
					->name($this->name . '_bg_color')
					->onChange($jsCode . '.changeProps("bg_color", this.value)')
					->attr('data-field', 'bg_color')
			);

			$table->addGroupRow('Border', '', UITableAttr::factory()->attr('data-role', 'brd_props'));

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Style:');
			$table->addCell(
				FFSelect::factory()
					->data([
						'1' => 'Solid',
						'2' => 'Dotted',
						'3' => 'Dashed'
					])
					->htmlWrap('')
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'style')
			);


			$table->addGroupRow('Border Color', '', UITableAttr::factory()->attr('data-role', 'brd_props'));

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('All:');
			$table->addCell(
				FFColorPicker::factory()
					->showColorPicker(false)
					->showClearButton(false)
					->onChange($jsCode . '.applyBorderColorForAllSides();')
					->attr('data-field', 'color_all')
			);
			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Top:');
			$table->addCell(
				FFColorPicker::factory()
					->showColorPicker(false)
					->showClearButton(false)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'color_top')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Right:');
			$table->addCell(
				FFColorPicker::factory()
					->showColorPicker(false)
					->showClearButton(false)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'color_right')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Bottom:');
			$table->addCell(
				FFColorPicker::factory()
					->showColorPicker(false)
					->showClearButton(false)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'color_bottom')
			);


			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Left:');
			$table->addCell(
				FFColorPicker::factory()
					->showColorPicker(false)
					->showClearButton(false)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'color_left')
			);

			$table->addGroupRow('Border Width', '', UITableAttr::factory()->attr('data-role', 'brd_props'));

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('All:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.applyBorderWidthForAllSides();')
					->attr('data-field', 'width_all')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Top:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'width_top')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Right:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'width_right')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Bottom:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'width_bottom')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Left:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'width_left')
			);

			$table->addGroupRow('Border Radius', '', UITableAttr::factory()->attr('data-role', 'brd_props'));

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('All:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.applyBorderRadiusForAllSides();')
					->attr('data-field', 'radius_all')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Top-Left:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'radius_top')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Top-Right:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'radius_right')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Bottom-Right:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'radius_bottom')
			);


			$table->addRow(UITableAttr::factory()->attr('data-role', 'brd_props'));
			$table->addCell('Bottom-Left:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->className('align-c')
					->width(36)
					->onChange($jsCode . '.changeBorderProps()')
					->attr('data-field', 'radius_left')
			);

			$table->addGroupRow('Fill & Stroke', '', UITableAttr::factory()->attr('data-role', 'shape_props'));

			$table->addRow(UITableAttr::factory()->attr('data-role', 'shape_props'));
			$table->addCell('Fill:');
			$table->addCell(
				FFColorPicker::factory()
					->showColorPicker(false)
					->onChange($jsCode . '.changeShapeProps()')
					->name($this->name . '_shape_props')
					->attr('data-field', 'fill')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'shape_props'));
			$table->addCell('Stroke:');
			$table->addCell(
				FFColorPicker::factory()
					->showColorPicker(false)
					->onChange($jsCode . '.changeShapeProps()')
					->name($this->name . '_stroke_props')
					->attr('data-field', 'stroke')
			);

			$table->addRow(UITableAttr::factory()->attr('data-role', 'shape_props'));
			$table->addCell('Line:');
			$table->addCell(
				FFInput::factory(FFInput::INT_NUMBER)
					->className('align-c')
					->limit(0, 10)
					->maxlength(2)
					->htmlWrap('')
					->width(36)
					->onChange($jsCode . '.changeShapeProps()')
					->attr('data-field', 'width')
			);

			$table->addGroupRow('Image', '', UITableAttr::factory()->attr('data-role', 'img_source'));

			$table->addRow(UITableAttr::factory()->attr('data-role', 'img_source'));
			$table->addCell('Image:');
			$table->addCell(
				FFMenuButton::factory('Select Image')
					->iconsSize(20)
					->addItem('Upload Image', 'Core.FFFileUpload.get(' . json_encode($this->name . '_img_upload') . ').upload()', 'upload.png')
					->addItem('Open File', 'FFFile.get(' . json_encode($this->name . '_img_open') . ').choose()', 'search_folder.png')
					->addLine()
					//->addItem('Select from ClipArt Gallery', 'FFClipArtPicker.onSearch(' . json_encode($this->name . '_img_clipart') . ')')
					->addItem('Take Photo', 'Core.FFPhotoPicker.get(' . json_encode($this->name . '_img_webcam') . ').showPicker()')
					->addItem('Signature', 'Core.FFSignature.get(' . json_encode($this->name . '_img_signature') . ').createSignature()')
					->addLine()
					->addItem('Clear', $jsCode . '.changeProps("image_source", "")', 'clear.png')
					->leftIcon('magnify.png')
					->viewStyle(FFButton::VIEW_INFO)
					->htmlWrap('')
					->append(
						UIDiv::factory()
							->className('hidden')
							->append(
								UIElements::factory()
									->addObject(
										FFFileUpload::factory('')
											->name($this->name . '_img_upload')
											->fileFilters(FileFilter::IMAGES)
											->onChange($jsCode . '.changeImageSource(this.value)')
											->htmlWrap('')
									)
									->addObject(
										FFFile::factory(FFFile::FILE)
											->name($this->name . '_img_open')
											->fileFilters(FileFilter::IMAGES)
											->onChange($jsCode . '.changeImageSource(this.value)')
											->htmlWrap('')
									)
									->addObject(
										FFClipArtPicker::factory()
											->name($this->name . '_img_clipart')
											->onChange($jsCode . '.changeImageSource(this.getAttribute("fileurn"))')
											->htmlWrap('')
									)
									->addObject(
										FFPhotoPicker::factory()
											->displayThumbnailImage(false)
											->displayClearButton(false)
											->name($this->name . '_img_webcam')
											->onChange($jsCode . '.changeImageSource(this.value)')
											->htmlWrap('')
									)
									->addObject(
										FFSignature::factory()
											->displayThumbnailImage(false)
											->displayClearButton(false)
											->name($this->name . '_img_signature')
											->onChange($jsCode . '.changeImageSource(this.value)')
											->htmlWrap('')
									)
							)
					)
			);

			$table->addNoInfoRow(
				'No element selected...',
				UITableAttr::factory()->attr('data-msg-target', $this->name . '_elem_props')
			);

			return $table;
		}

		/**
		 * Returns Dialog Box for selecting field entity
		 *
		 * @param array &$jsData
		 * @return UIDialogBox
		 */
		private function getEntitiesDialogBox(array &$jsData) {
			$box = UIDialogBox::factory($this->name . '_entity_box');
			$box->setTitle('Choose Entity Field');
			$box->setSize(900, 600);

			$nav = UINavigator::factory($this->name . '_nav');

			$jsCode = 'Core.UIFormBuilder.get(' . json_encode($this->name) . ')';
			$list = $this->settings->getEntities();
			$jsData = array();

			$inputTypes = array(
				FBDataField::TYPE_TEXT => 'Input Text Element',
				FBDataField::TYPE_SELECT => 'DropDown Element',
				FBDataField::TYPE_CHECKBOX => 'Checkbox Element',
				FBDataField::TYPE_RADIO_BUTTON => 'Radio Button Element',
			);
			$inputTypeIcons = array(
				FBDataField::TYPE_TEXT => 'lf lf-input-elem',
				FBDataField::TYPE_SELECT => 'lf lf-select-elem',
				FBDataField::TYPE_CHECKBOX => 'lf lf-checkbox',
				FBDataField::TYPE_RADIO_BUTTON => 'lf lf-radiobutton',
			);

			foreach ($list as $entity) {
				$entityName = $entity->getUniqueName();

				$isMultipleModel = $entity->getDataModel() == FBFieldEntity::DATA_MODEL_MULTIPLE;
				$isSingleModel = $entity->getDataModel() == FBFieldEntity::DATA_MODEL_SIGNLE;

				$nav->addGroup(
					UINavigatorGroup::factory()->setTitle($entity->getCaption())
				);

				$fieldsData = array();
				foreach ($entity->getFields() as $field) {
					$fieldName = $field->getUniqueName();
					$fieldTypes = $field->getFieldTypes();
					$fieldTypeNames = [];
					$icon = '';
					foreach ($fieldTypes as $type) {
						if ($icon === '') {
							$icon = $inputTypeIcons[$type];
						}
						$fieldTypeNames[] = $inputTypes[$type];
					}
					if ($icon === '') {
						$icon = $inputTypeIcons[FBDataField::TYPE_TEXT];
					}
					$flags = new UIElements();
					if ($field->isRequired()) {
						$flags->addObject(UIDiv::factory('Required')->viewStyle(UIDiv::VIEW_LABEL_DANGER));
					}
					if ($field->isReadOnly()) {
						$flags->addObject(UIDiv::factory('Read Only')->viewStyle(UIDiv::VIEW_LABEL_WARNING));
					}
					if ($isMultipleModel) {
						$flags->addObject(UIDiv::factory('Multiple Entity')->viewStyle(UIDiv::VIEW_LABEL_INFO));
					}
					if ($isSingleModel) {
						$flags->addObject(UIDiv::factory('Single Entity')->viewStyle(UIDiv::VIEW_LABEL_PRIMARY));
					}

					$nav->addPage(
						UINavigatorPage::factory($fieldName)
							->setTitle($field->getCaption())
							->setIcon($icon)
							->setRequiredFlag($field->isRequired())
							->setContent(
								UIContainer::factory([
									UISubTitle::factory($field->getCaption()),
									UITable::factory('100%')
										->viewStyle(UITable::VIEW_FORM)
										->addRow()
										->addCell('Type:')
										->addCell(implode(', ', $fieldTypeNames))
										->addRow()
										->addCell('Flags:')
										->addCell($flags)
										->addRow()
										->addCell('Description:')
										->addCell($entity->getDescription())
								])
							)
					);
					$opts = $field->getValueOptions();

					$fieldsData[$fieldName] = array(
						'caption' => $field->getCaption(),
						'options' => $opts,
						'options_keys_order' => is_array($opts) ? array_keys($opts) : array(),
						'input_type' => $field->getFieldInputType(),
						'field_types' => $fieldTypes,
						'read_only' => $field->isReadOnly(),
						'maxlength' => $field->getMaxlength(),
						'required' => $field->isRequired(),
						'grouped_value' => $field->isGroupedValue(),
					);
				}
				$jsData[$entityName] = array(
					'caption' => $entity->getCaption(),
					'desc' => $entity->getDescription(),
					'data_model' => $entity->getDataModel(),
					'valid_req_flds' => $entity->getValidateRequiredFieldsFlag(),
					'fields' => $fieldsData
				);
			}

			$box->addObject($nav);

			$box->addButton('Apply', $jsCode . '.assignSelectedEntity()');
			$box->addButton('Cancel');
			return $box;
		}

		/**
		 * Returns Dialog Box with Field Option Picker
		 *
		 * @return UIDialogBox
		 */
		public function getEntityFieldOptionsDialogBox() {
			$box = UIDialogBox::factory($this->name . '_entity_opt_box');
			$box->title('Choose Entity Field Option');
			$box->setSize(400, null);

			$box->addObject(
				UILayout::factory()
					->addObject(
						FFSelect::factory('Please choose field option:')
							->name($this->name . '_entity_field_option')
							->width(300)
							->htmlWrap(FormFieldWrappers::CAPTION_TOP),
						'nowrap center [padding: 30px]'
					)
			);

			$jsCode = 'Core.UIFormBuilder.get(' . json_encode($this->name) . ')';
			$box->addButton('Apply', $jsCode . '.assignSelectedEntity(app.find(' . json_encode('#' . $this->name . '_entity_field_option') . ').getValue())');
			$box->addButton('Cancel');

			return $box;
		}

		/**
		 * Returns HTML code of the element
		 *
		 * @param DBConnection $db
		 * @return string
		 */
		public function toHTML($db = null) {
			self::appendJS();

			$html = '';
			$jsName = json_encode($this->name);

			$html .= UIPageLayout::factory(UIPageLayout::TYPE_HEAD_TWO_FRAMES_RIGHT_SMALL, $this->name)
				->setClassName('form-builder')
				->addFrame(
					UIPageLayoutFrame::factory($this->getToolbar())
				)
				->addFrame(
					UIPageLayoutFrame::factory()
						->setContent(
							UIPageLayout::factory(UIPageLayout::TYPE_STATUS_AND_FRAME)
								->addFrame(
									UIPageLayoutFrame::factory()
										->setContent(
											UIDiv::factory()
												->className('form-builder__status-bar')
												->append('<span>loading...</span>')
										)
								)
								->addFrame(
									UIPageLayoutFrame::factory()
										->setID($this->name . '_cont')
										->setClassName('form-builder__page-cont')
										->allowScrolling(true, true)
								)
						)
				)
				->addFrame(
					UIPageLayoutFrame::factory()
						->setContent(
							$this->getPropertiesBar()
						)
				)
				->toHTML($db);

			$rtp = RTPEmbeddedEditor::factory($this->name . '_ertp');
			$rtp->strictFontFamily('FreeSans');
			$html .= $rtp->toHTML($db);

			$html .= UIDialogBox::factory($this->name . '_grid_setup')
				->setTitle('Grid Properties')
				->addObject(
					UITable::factory('100%')
						->viewStyle(UITable::VIEW_FORM)
						->addCell('Columns:')
						->addCell(
							FFInput::factory(FFInput::INT_NUMBER)
								->width(80)
								->name($this->name . '_grid_cols')
								->value('2')
						)
						->addRow()
						->addCell('Rows:')
						->addCell(
							FFInput::factory(FFInput::INT_NUMBER)
								->width(80)
								->name($this->name . '_grid_rows')
								->value('3')
						)
				)
				->addButton(
					'Create',
					'Core.UIDialogBox.get(' . json_encode($this->name . '_grid_setup') . ').complete()',
					null, FFButton::VIEW_SUCCESS
				)
				->addButton('Cancel')
				->toHTML($db);

			$saveInstr = FileDialogSettings::factory(FileDialogSettings::SAVE_FILE)
				->addFileType('Lumen Touch Form Builder', '*.' . FBDocument::FILE_EXTENSION)
				->windowTitle('Save Document')
				->getDialogInstructions();

			$openInstr = FileDialogSettings::factory(FileDialogSettings::OPEN_FILE)
				->addFileType('Lumen Touch Form Builder', '*.' . FBDocument::FILE_EXTENSION)
				->windowTitle('Open Document')
				->getDialogInstructions();

			$jsData = array();
			$html .= $this->getEntitiesDialogBox($jsData)->toHTML($db);
			$html .= $this->getEntityFieldOptionsDialogBox()->toHTML($db);

			$options = [
				'packPath' => CoreUtils::getVirtualPath('./'),
				'saveDialogInstructions' => $saveInstr,
				'openDialogInstructions' => $openInstr,
				'entitiesData' => $jsData,
				'data' => $this->data,
			];

			$html .= '
				<script type="text/javascript">
					new Core.UIFormBuilder(' . $jsName . ', ' . json_encode($options) . ');
				</script>
			';

			return $html;
		}
	}
