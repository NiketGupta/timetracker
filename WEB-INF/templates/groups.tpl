<script>
  function chLocation(newLocation) { document.location = newLocation; }
</script>
{$forms.subgroupsForm.open}
<table cellspacing="1" cellpadding="3" border="0" width="720">
{if $group_dropdown}
  <tr>
    <td align="right">{$i18n.label.group}:</td>
    <td>{$forms.subgroupsForm.group.control}</td>
  </tr>
  <tr><td colspan="2">&nbsp;</td></tr>
{/if}
  <tr>
    <td width="40%" class="tableHeader">{$i18n.label.thing_name}</td>
    <td width="40%" class="tableHeader">{$i18n.label.description}</td>
    <td></td>
    <td></td>
  </tr>
{if $subgroups}
  {foreach $subgroups as $subgroup}
  <tr bgcolor="{cycle values="#f5f5f5,#ffffff"}">
    <td>{$subgroup.name|escape}</td>
    <td>{$subgroup.description|escape}</td>
    <td><a href="group_edit.php?id={$subgroup.id}"><img class="table_icon" alt="{$i18n.label.edit}" src="images/icon_edit.png"></a></td>
    <td><a href="group_delete.php?id={$subgroup.id}"><img class="table_icon" alt="{$i18n.label.delete}" src="images/icon_delete.png"></a></td>
  </tr>
  {/foreach}
{/if}
</table>
{$forms.subgroupsForm.close}

<table width="100%">
  <tr>
    <td align="center">
      <br>
      <form>
        <input type="button" onclick="chLocation('group_add.php');" value="{$i18n.button.add}">
      </form>
    </td>
  </tr>
</table>
