function setCurrentCityAndArea()
{
    if (CITY_ID != 0) {
        $('.city_'+CITY_ID).attr('checked','checked');
    }
}

function changeArea(ele, area_url)
{
    var httpText = 'http://';
    var city = $(ele).data('url');

    window.location.href = httpText+city+'.'+SITE_NAME+'/'+area_url;
}

function changeCity(ele, clickFn)
{
    if (clickFn) {
        var httpText = 'http://';
        var city = ele.data('url');

        window.location.href = httpText+city+'.'+SITE_NAME;
    }
    $('#empty_area').html(TEXT_SELECT_CITY);
    var cityId = ele.val();
    $('.area_inp').hide();
    $('.area_in_city_'+cityId).show();
}

function sendSubscribe(form_ele)
{
    var form = $(form_ele);
    var args = {
        'city_id': CITY_ID,
        'area_id': AREA_ID,
        'email': form.find('.input_subscribe').val()
    };

    $.post( URL_SUBSCRIBE, args, function( data ) {
        if (typeof data.error != 'undefined') {
            $.each( data.error, function( key, value ) {
                alert( key + ": " + value );
            });
        } else {
            alert(TEXT_NEWS_SUBSCRIBE);
            form.find('.input_subscribe').val('');
        }
        console.log(data);
    }, "json");


    return false;
}

function goToPage(ele)
{
    var httpText = 'http://';
    var city = $('input[name=city]:checked').data('url');
    var area = $('input[name=area]:checked').data('url');
    var word = $('input[name=word]').val().trim();

    if (city != '') {
        httpText+= city+'.';
    }
    httpText += SITE_NAME;
    if (area != '' && area != null) {
        httpText+= '/'+area;
    }
    if (word != '') {
        httpText+= '/'+word;
    }

    window.location.href = httpText;

    return false;
}

function getAjaxPage()
{
    if (page == 0 || inProgress)
        return false;

    inProgress = true;

    $.getJSON( URL_PAGINATION,
        {
            city: CITY_ID,
            area: AREA_ID,
            word: CURRENT_WORD,
            page: page
        }
    ).done(function( data ) {
            if (data.success) {
                $('#list_block').append(data.html);
                page = data.page;
                setTimeout(function(){
                    setMap();
                },100);
            } else {
                console.log(data.error);
            }
            inProgress = false;
        });
}
$(function(){
    $('.salonPhone a').unbind();
    $('.salonPhone a').click(function(event){
        event.preventDefault();

        $('.salonPhone span').html($('.salonPhone span').data('salon-phone'));
        $('.salonPhone span').after('<span style="font-size: 11px;">'+TEXT_AFTER_PHONE+'</span>');
        $('.salonPhone a').remove();
    });
});