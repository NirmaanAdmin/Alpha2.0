<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<dl class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-3 sm:tw-gap-5 tw-mb-0">
    <?php foreach ([
            ['key' => 'received', 'class' => 'text-warning', 'label' => _l('revenue')],
            ['key' => 'spent', 'class' => 'text-success', 'label' => _l('amount_spent')],
            ['key' => 'revenue', 'class' => 'text-warning', 'label' => _l('final_balance')],
    ] as $totalSection) { ?>
    <div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-md tw-bg-white">
        <div class="tw-px-4 tw-py-5 sm:tw-px-4 sm:tw-py-2">
            <dt class="tw-font-medium <?php echo $totalSection['class']; ?>">
                <?php echo $totalSection['label'];?>
            </dt>
            <dd class="tw-mt-1 tw-flex tw-items-baseline tw-justify-between md:tw-block lg:tw-flex">
                <div class="tw-flex tw-items-baseline tw-text-base tw-font-semibold tw-text-primary-600">
                    <?php echo $totals[$totalSection['key']]; ?>
                </div>
            </dd>
        </div>
    </div>
    <?php }?>
</dl>