<script>
    let myEditor;
    DecoupledEditor
    .create( document.querySelector('#editor'))
    .then( editor => {
        const toolbarContainer = document.querySelector( '#toolbar-container' );

        toolbarContainer.appendChild( editor.ui.view.toolbar.element );
        myEditor = editor;
    } )
    .catch( error => {
        console.error( error );
    } );

function htmlEncode(value){
    //create a in-memory div, set it's inner text(which jQuery automatically encodes)
    //then grab the encoded contents back out. The div never exists on the page.
    return $('<div/>').text(value).html();
}
    function save_document(){
        $('#html_content').val(myEditor.getData());
        show_processing('<?php echo _l('dmg_saving'); ?>');
        $.ajax({
            url: '<?php echo site_url(); ?>drawing_management/drawing_management_client/save_document',
            type: "post",
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            data: {
                '<?php echo html_entity_decode($this->security->get_csrf_token_name()); ?>':'<?php echo html_entity_decode($this->security->get_csrf_hash()); ?>',
                'id':'<?php echo html_entity_decode($id); ?>',
                'html_content': encodeURIComponent($('#html_content').val())
            },
            success: function(){

            },
            error:function(){

            }
        }).done(function(response) {
           Swal.fire(
              '<?php echo _l('dmg_saved'); ?>',
              '',
              'success'
              )
       });  
    }
</script>