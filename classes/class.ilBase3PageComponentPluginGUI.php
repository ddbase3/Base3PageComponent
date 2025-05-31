<?php

/**
 * GUI Igor2Example plugin
 *
 * @author Daniel Dahme qualitus@qualitus.de
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilBase3PageComponentPluginGUI: ilPCPluggedGUI
 */
class ilBase3PageComponentPluginGUI extends ilPageComponentPluginGUI {

	public function executeCommand(): void {
		$cmd = $GLOBALS['DIC']['ilCtrl']->getCmd();
		if (method_exists($this, $cmd)) $this->$cmd();
	}

	public function insert(): void {
		$tpl = $GLOBALS['DIC']['tpl'];

		$form = $this->initForm(true);
		$html = $form->getHTML();

	        $tpl->setContent($html);
	}

	public function edit(): void {
		$tpl = $GLOBALS['DIC']['tpl'];

		$form = $this->initForm(false);
		$html = $form->getHTML();

	        $tpl->setContent($html);
	}

	public function create(): void {
		$props = [];
		$this->createElement($props);
		$this->returnToParent();
	}

	public function update(): void {
		$props = [];
		$this->updateElement($props);
		$this->returnToParent();
	}

	public function save(): void {
		$tpl = $GLOBALS['DIC']['tpl'];
	        $tpl->setContent('<b>save called</b>');
	}

	public function cancel(): void {
	        $this->returnToParent();
	}

	public function initForm($a_create = false): ilPropertyFormGUI {
		$ilCtrl = $GLOBALS['DIC']['ilCtrl'];

		$form = new \ilPropertyFormGUI();
		$form->setTitle('BASE3 Page Component configuration');

		if ($a_create) {
			$this->addCreationButton($form);
			$form->addCommandButton('cancel', 'cancel');
		} else {
			$form->addCommandButton('update', 'save');
			$form->addCommandButton('cancel', 'back');
		}

		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}

	public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string {
		$html = 'unknown mode';
		switch ($a_mode) {
			case 'edit':

				$html = 'BASE3 Page Component';
				break;

			case 'presentation':

				$content = '<h1>BASE3 Page Component</h1>';
				$content .= '<h2>Plugin List</h2>';

				$classmap = $GLOBALS['DIC'][\Base3\Api\IClassMap::class];
				$plugins = $classmap->getInstancesByInterface(\Base3\Api\IPlugin::class);
				$pluginNames = [];
				foreach ($plugins as $plugin) $pluginNames[] = '<li>' . $plugin::getName() . '</li>';

				$content .= '<ul>' . implode('', $pluginNames) . '</ul>';

				$pageModule = $classmap->getInstanceByInterfaceName(\Base3\Page\Api\IPageModule::class, 'pagecontent');
				$pageModule->setData([ 'content' => $content ]);
				$html = $pageModule->getHtml();

				break;
		}
		return $html;
	}
}
