
<script>
    "use strict";
    $(window).on('load', function() {
        <?php if(isset($send_notify) && $send_notify != 0){ ?>
        var notify_data = {};
        notify_data.rel_id = <?php echo html_entity_decode($send_notify['rel_id']); ?>;
        notify_data.rel_type = '<?php echo html_entity_decode($send_notify['rel_type']); ?>';
        $.post(admin_url+'fixed_equipment/send_notify', notify_data).done(function(response){

        });
    <?php } ?>
    });
</script>