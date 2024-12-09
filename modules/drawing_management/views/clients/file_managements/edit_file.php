<!DOCTYPE html>
<html>
<head>
    <title></title>
    <?php hooks()->do_action('head_element_public_document'); ?>
</head>
<body>
    <div class="fixed-toolbar display-flex">
        <div id="toolbar-container" class="w100"></div>
        <button class="btn btn-warning display-flex pt8px" onclick="save_document()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <span class="mtop3 mt5px ml10px">
                <?php echo _l('dmg_save'); ?>                
            </span>
        </button>        
    </div>
    <div id="editor">
        <p><?php echo drawing_htmldecode($html); ?></p>
    </div>    
    <textarea name="html_content" id="html_content" class="hide"></textarea>
</body>
<?php hooks()->do_action('footer_element_public_document'); ?>
<?php require 'modules/drawing_management/assets/js/clients/file_managements/edit_office_js.php';?>
</html>



