<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons">       
                <a href="<?php echo admin_url('cash_flow/add'); ?>"
                        class="btn btn-primary pull-left display-block tw-mb-2 sm:tw-mb-4">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('Add'); ?>
                    </a>
                    <div class="clearfix"></div>
                    <div id="cashflow_summary">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>

                            <span>
                                <?php echo _l('Cash Flow Summary'); ?>
                            </span>
                        </h4>
                        <div id="stats-top" class="hide">
                            <hr/>
                            <div id="expenses_total"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>











