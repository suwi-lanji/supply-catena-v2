Dear <?php echo \App\Models\Customer::where('id', $record->customer_id)->pluck('company_display_name')->first();?>,

Thanks for your interest in our Services. Please find our sales order attached with this mail.<?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/mail/sales-order.blade.php ENDPATH**/ ?>