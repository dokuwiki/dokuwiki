
//
// SMTP options visibility
//
(function($){
    $(document).ready(function(){
        console.log('config plugin loaded');
        //console.log($('#config___mailer____transport').val());
        $transport = $('#config___mailer____transport');

        if ($transport.val() != 'SMTP') {
            $transport.closest('table').find('tr:not(:first)').hide();
        }

        $transport.on('change', function(e) {
           console.log($transport.val());

            if ($transport.val() != 'SMTP') {
                $transport.closest('table').find('tr:not(:first)').hide();
            } else {
                $transport.closest('table').find('tr:not(:first)').show();
            }
        });

    });

})(jQuery);