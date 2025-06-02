<?php

class ilBase3PageComponentPlugin extends ilPageComponentPlugin {

	public function getPluginName(): string {
		return 'Base3PageComponent';
	}
	
	function isValidParentType(string $a_type): bool {
		return true;

		/*
		global $DIC;
		$rbacreview = $DIC->rbac()->review();
		$usr_roles = $rbacreview->assignedGlobalRoles($DIC->user()->getId());
		return in_array(2, $usr_roles);
		 */
	}

	function getJavascriptFiles(string $a_mode): array {
		return ['Customizing/global/plugins/Services/COPage/PageComponent/Base3PageComponent/assets/scriptloader.js'];
	}
}
