<?php

/**
 * An access authority describes the Role a user or group has
 * which is then associated with a given object
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class AccessAuthority extends DataObject {
	public static $db = array(
		'Type'				=> "Enum('Member,Group')",
		'AuthorityID'		=> 'Int',
		'Role'				=> 'Varchar',			// recorded so future role changes can propagate
		'Perms'				=> 'MultiValueField',
		'Grant'				=> "Enum('GRANT,DENY','GRANT')",
		'ItemID'			=> 'Int',
		'ItemType'			=> 'Varchar',
	);
	
	/**
	 * ALTER TABLE `AccessAuthority` ADD INDEX ( `ItemID` , `ItemType` ) ;
	 * 
	 * The following has no effect - you need to manually create the relevant index defined above
	 * 
	 */
//	public static $indexes = array(
//		'ItemID', 'ItemType',
//	);

	public function getAuthority() {
		if ($this->Type && $this->AuthorityID > 0) {
			return DataObject::get_by_id($this->Type, $this->AuthorityID);
		}

		if ($this->AuthorityID == -1) {
			return singleton('PublicMember');
		}
	}
	
	public function getItem() {
		return DataObject::get_by_id($this->ItemType, $this->ItemID);
	}
	
	public function PermList() {
		if($this->Perms->getValues()){
			return '<p>'.implode('</p><p>', $this->Perms->getValues()).'</p>';	
		}
	}
	
	public function onAfterDelete() {
		parent::onBeforeDelete();
		
		if($values = $this->Perms->getValues()){
			foreach ($values as $perm) {
				singleton('PermissionService')->clearPermCacheFor($this->getItem(), $perm);
			}
		}
	}
}
