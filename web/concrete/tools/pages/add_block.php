<?
defined('C5_EXECUTE') or die("Access Denied.");

if (!Loader::helper('validation/numbers')->integer($_REQUEST['cID'])) {
	die(t('Access Denied'));
}

$c = Page::getByID($_REQUEST['cID']); 
$cp = new Permissions($c);
if (!$cp->canEditPageContents()) {
	die(t('Access Denied'));
}

if (isset($_REQUEST['arHandle'])) {
	// we are launching this from a specific area, so we check permissions against that.
	$a = Area::get($c, $_REQUEST['arHandle']);
	$ap = new Permissions($a);
}

$btl = new BlockTypeList();
$blockTypes = $btl->getBlockTypeList();
$dsh = Loader::helper('concrete/dashboard');
$dashboardBlockTypes = array();
if ($dsh->inDashboard()) {
	$dashboardBlockTypes = BlockTypeList::getDashboardBlockTypes();
}
$blockTypes = array_merge($blockTypes, $dashboardBlockTypes);

$ih = Loader::helper('concrete/interface');
$ci = Loader::helper('concrete/urls');
?>

<script type="text/javascript">

$(function() {
	CCMEditMode.activateBlockTypesOverlay();
});
</script>

<div class="ccm-ui" id="ccm-dialog-block-types">

<div id="ccm-dialog-block-types-sets">

<form class="form-inline" id="ccm-block-type-search">
	<i class="glyphicon glyphicon-search"></i> <input type="search" />
</form>

	<ul>
<?
$tabs = array();
$sets = BlockTypeSet::getList();
for ($i = 0; $i < count($sets); $i++) { 
	$set = $sets[$i];?>
	<li><a href="#" data-tab="<?=$set->getBlockTypeSetHandle()?>"><?=$set->getBlockTypeSetName()?></a></li>
<? } ?>

</ul>

</div>

<div id="ccm-dialog-block-types-list">
	
	<ul id="ccm-overlay-block-types">

	<? foreach($blockTypes as $bt) { 
		if ($a instanceof Area && (!$ap->canAddBlockToArea($bt))) {
			continue;
		} else if (!$cp->canAddBlockType($bt)) {
			continue;
		}

		$btsets = $bt->getBlockTypeSets();
		$sets = '';
		foreach($btsets as $set) {
			$sets .= $set->getBlockTypeSetHandle() . ' ';
		}
		$sets = trim($sets);
		$btIcon = $ci->getBlockTypeIconURL($bt);

		?>

		<li data-block-type-sets="<?=$sets?>">
			<a <? if (!($a instanceof Area)) { ?> class="ccm-overlay-draggable-block-type" <? } else { ?> class="ccm-overlay-clickable-block-type" data-area-id="<?=$a->getAreaID()?>" data-area-handle="<?=$a->getAreaHandle()?>" <? } ?> data-cID="<?=$c->getCollectionID()?>" data-block-type-handle="<?=$bt->getBlockTypeHandle()?>" data-dialog-title="<?=t('Add %s', $bt->getBlockTypeName())?>" data-dialog-width="<?=$bt->getBlockTypeInterfaceWidth()?>" data-dialog-height="<?=$bt->getBlockTypeInterfaceHeight()?>" data-has-add-template="<?=$bt->hasAddTemplate()?>" data-supports-inline-editing="<?=$bt->supportsInlineEditing()?>" data-btID="<?=$bt->getBlockTypeID()?>" href="javascript:void(0)"><p><img src="<?=$btIcon?>" /><span><?=$bt->getBlockTypeName()?></span></p></a>
		</li>
		
	<? } ?>

	</ul>


</div>


</div>