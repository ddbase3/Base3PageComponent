<?php

use Base3\Api\IClassMap;
use Base3\Page\Api\IPageModuleContent;

/**
 * GUI Base3 plugin
 * docu: https://docu.ilias.de/ilias.php?baseClass=illmpresentationgui&cmd=layout&ref_id=42&obj_id=56942
 *
 * @author Daniel Dahme qualitus@qualitus.de
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilBase3PageComponentPluginGUI: ilPCPluggedGUI
 */
class ilBase3PageComponentPluginGUI extends ilPageComponentPluginGUI {

	protected ilCtrl $ilCtrl;
	protected ilGlobalTemplateInterface $tpl;
	protected ilLanguage $lng;
	protected \ILIAS\DI\UIServices $ui;
	protected IClassMap $classmap;

	public function __construct() {

		/** @var ILIAS\DI\Container $DIC */
		$DIC = $GLOBALS['DIC'];

		$this->ilCtrl = $DIC['ilCtrl'];
		$this->tpl = $DIC['tpl'];
		$this->lng = $DIC['lng'];
		$this->ui = $DIC->ui();
		$this->classmap = $DIC[IClassMap::class];
	}

	public function executeCommand(): void {
		$cmd = $this->ilCtrl->getCmd();
		if (method_exists($this, $cmd)) $this->$cmd();
	}

	public function insert(): void {
		$form = $this->initForm(true);
		$this->tpl->setContent($form->getHTML());
	}

	public function edit(): void {
		$form = $this->initForm(false);
		$this->tpl->setContent($form->getHTML());
	}

	public function create(): void {
		$form = $this->initForm(true);
		if ($form->checkInput()) {
			$props = [
				'pagemodule' => $form->getInput('pagemodule'),
				'data' => trim($form->getInput('data')) . ' '  // ILIAS-Bug/sanitation: JSON needs a trailing space
			];
			if ($this->createElement($props)) {
				$this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
				$this->returnToParent();
			}
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHtml());
	}

	public function update(): void {
		$form = $this->initForm(true);
		if ($form->checkInput()) {
			$props = [
				'pagemodule' => $form->getInput('pagemodule'),
				'data' => trim($form->getInput('data')) . ' '  // ILIAS-Bug/sanitation: JSON needs a trailing space
			];
			if ($this->updateElement($props)) {
				$this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
				$this->returnToParent();
			}
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHtml());
	}

	public function cancel(): void {
	        $this->returnToParent();
	}

	private function initForm($a_create = false): \ilPropertyFormGUI {

		$mainTemplate = $this->ui->mainTemplate();
		$mainTemplate->addJavaScript('Customizing/global/plugins/Services/COPage/PageComponent/Base3PageComponent/assets/dynamicform.js');

		$defaultProps = [
			'pagemodule' => '',
			'data' => ''
		];
		$props = array_merge($defaultProps, $this->getProperties());

		$form = new \ilPropertyFormGUI();
		$form->setTitle('BASE3 Page Component configuration');

		$values = [];
		$pageModules = $this->classmap->getInstancesByInterface(IPageModuleContent::class);
		foreach ($pageModules as $pageModule) $values[$pageModule::getName()] = $pageModule::getName();
		$selectPageModuleControl = new ilSelectInputGUI('Page Module', 'pagemodule');
		$selectPageModuleControl->setOptions($values);
		$selectPageModuleControl->setRequired(true);
		$selectPageModuleControl->setValue($props['pagemodule']);
		$form->addItem($selectPageModuleControl);

		$pageModuleDataControl = new ilTextInputGUI('Data', 'data');
		$pageModuleDataControl->setValue($props['data']);
		$form->addItem($pageModuleDataControl);

		if ($a_create) {
			$this->addCreationButton($form);
			$form->addCommandButton('cancel', $this->lng->txt('cancel'));
		} else {
			$form->addCommandButton('update', $this->lng->txt('save'));
			$form->addCommandButton('cancel', $this->lng->txt('cancel'));
		}

		$form->setFormAction($this->ilCtrl->getFormAction($this));
		return $form;
	}

	public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string {
		switch ($a_mode) {
			case 'edit':

				$html = '<div style="display: flex; align-items: center; justify-content: space-between; background: #f9f9f9; border: 1px solid #ddd; padding: 12px 16px; border-radius: 8px; font-family: sans-serif;">';
				$html .= '<div>';
				$html .= '<div style="font-size: 1.1em; font-weight: bold; color: #333;">' . htmlspecialchars($a_properties['pagemodule']) . '</div>';
				$html .= '<div style="font-size: 0.9em; color: #666;"><i>BASE3 Page Component</i></div>';
				$html .= '</div>';
				$html .= '<img src="Customizing/global/plugins/Services/COPage/PageComponent/Base3PageComponent/assets/logo.svg" style="width:48px; height:auto; margin-left: 16px;" />';
				$html .= '</div>';
				return $html;

			case 'presentation':

				$mainTemplate = $this->ui->mainTemplate();
				$mainTemplate->addJavaScript('components/Base3/ClientStack/assetloader/assetloader.min.js');

				$pageModule = $this->classmap->getInstanceByInterfaceName(IPageModuleContent::class, $a_properties['pagemodule']);

				$dataRaw = trim($a_properties['data']);
				if (strlen($dataRaw)) try {
					$data = json_decode($a_properties['data'], true, 512, JSON_THROW_ON_ERROR);
					$pageModule->setData($data);
				} catch (JsonException $e) {
					return 'Error decoding json: ' . $e->getMessage();
				}

				return $pageModule->getHtml();
		}
		return 'Unknown mode';
	}
}
