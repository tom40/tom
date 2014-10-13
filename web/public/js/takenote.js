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
    
    // client-list-cell.phtml
    $('img.client-comments').hover(function()
    {
    	$(this).next('span').children('span').eq(0).fadeIn(300);
    }, function()
    {
        $(this).next('span').children('span').eq(0).stop(true, false).fadeOut(300);
    });
    

    // file-name-list-cell.phtml
	$('.job-comments img').hover(function()
	{
		$(this).next('span').fadeIn(300);
	},
    function()
    {
		$(this).next('span').stop(true, false).fadeOut(300);
    });
});
