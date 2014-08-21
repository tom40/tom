$(document).ready(function()
{
    if ($('.flashmessenger').length > 0)
    {
        var offset = 0;
        $('.flashmessenger').each(function(index, elem)
        {
            $(elem).fadeIn('slow');
            var fade   = 2000 + (offset * 10);
            $(elem).css({top : offset + 'px'});
            offset += $(elem).outerHeight();
            $(elem).on('click', function()
            {
                $(this).fadeOut('slow');
            });
        });

    }
});
