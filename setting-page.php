<div class="wrap">
    <h1>Auto Create User Settings</h1>

    <?php if($_POST): ?>
    <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
        <p><strong>Settings saved.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
    </div>
    <?php endif; ?>

    <form method="post" action="options-general.php?page=acu-setting" novalidate="novalidate">
        <input type="hidden" name="page" value="acu-setting">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="databaseName">Database Name</label></th>
                <td><input name="databaseName" type="text" id="databaseName" value="<?php echo isset($databaseTable) ? $databaseTable : null ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="tableName">Table Name</label></th>
                <td><input name="tableName" type="text" id="tableName" value="<?php echo isset($dataTable) ? $dataTable : null ?>" class="regular-text"></td>
            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
</div>