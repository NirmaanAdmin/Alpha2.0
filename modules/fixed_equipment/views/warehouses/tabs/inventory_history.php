<div class="row">
  <div class="col-md-12" id="small-table">
    <?php render_datatable(array(
      _l('id'),
      _l('fe_transaction_type'),
      _l('fe_transaction_id'),
      _l('fe_item'),
      _l('fe_opening_stock'),
      _l('fe_closing_stock'),
      _l('fe_warehouse'),
      _l('fe_date_created')
    ),'table_inventory_history',['delivery_sm' => 'delivery_sm']); ?>
  </div>
</div>
<script>var hidden_columns = [3,4,5];</script>

