jQuery(document).ready(function( $ ) {

    // image
    jQuery('.upload_image_button').click(function() {
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        wp.media.editor.send.attachment = function(props, attachment) {
            jQuery(button).parent().prev().attr('src', attachment.url);
            jQuery(button).prev().val(attachment.id);
            wp.media.editor.send.attachment = send_attachment_bkp;
        }
        wp.media.editor.open(button);
        return false;
    });

    jQuery('.remove_image_button').click(function() {
        var src = $(this).parent().prev().attr('data-src');
        jQuery(this).parent().prev().attr('src', src);
        jQuery(this).prev().prev().val('');
        return false;
    });

    // video
    jQuery('.upload_video_button').click(function() {
        var link = prompt('можно было сделать красивее, но не было времени(. Insert link to youtube video:', '');
        if (link) {
            var button = $(this);
            var linkarray = link.split('/');
            var code = linkarray[linkarray.length - 1];

            if(code.match(/watch/)){
                var clearcode = code.split('=');
                src = 'https://img.youtube.com/vi/' + clearcode[1] + '/0.jpg';
                jQuery(button).parent().prev().attr('src', src);
                jQuery(button).prev().val(link);
            }else{
                $.ajax({
                    type:'GET',
                    url: 'http://vimeo.com/api/v2/video/' + code + '.json',
                    jsonp: 'callback',
                    dataType: 'jsonp',
                    success: function(data){
                        var thumbnail_src = data[0].thumbnail_large;
                        jQuery(button).parent().prev().attr('src', thumbnail_src);
                        jQuery(button).prev().val(link);
                    }
                });
            }
        }

        return false;
    }); 

    jQuery('.remove_video_button').click(function() {
        var src = 'https://img.youtube.com/vi//0.jpg';
        jQuery(this).parent().prev().attr('src', src);
        jQuery(this).prev().prev().val('');
        return false;
    });

    // clear field
    jQuery(document).ajaxComplete(function(event, xhr, settings) {
        var queryStringArr = settings.data.split('&');
        if( jQuery.inArray('action=add-tag', queryStringArr) !== -1 ){
            var xml = xhr.responseXML;
            $response = jQuery(xml).find('term_id').text();
            if($response != ""){
                var src = jQuery('#thumbnail').attr('data-src');
                jQuery('#thumbnail').attr('src', src);
                jQuery('#video_thumbnail').attr('src', 'https://img.youtube.com/vi//0.jpg');
            }
        }
     });
});
