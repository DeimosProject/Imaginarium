<!DOCTYPE HTML>
<html>
    <head>
        <title>{$title|default:'Imaginarium'}</title>
        <meta charset="utf-8" />
        <link href="/css/style.css" rel="stylesheet">
    </head>
    <body>
        <section class="wrapper">
            <div class="outer">
                <div class="inner">
                    <h1>Imaginarium download example</h1>
                </div>
                <div class="inner">
                    <div id="userpic" class="userpic">
                        <div class="js-preview userpic__preview"></div>
                        <div class="btn btn-success js-fileapi-wrapper">
                            <div class="js-browse">
                                <span class="btn-txt">Choose</span>
                                <input type="file" name="filedata">
                            </div>
                            <div class="js-upload" style="display: none;">
                                <div class="progress progress-success"><div class="js-progress bar"></div></div>
                                <span class="btn-txt">Uploading</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div id="popup" class="popup" style="display: none;">
            <div class="popup__body"><div class="js-img"></div></div>
            <div style="margin: 0 0 5px; text-align: center;">
                <div class="js-upload btn btn_browse btn_browse_small">Upload</div>
            </div>
        </div>

        {literal}
            <link href="/css/jquery.Jcrop.min.css" rel="stylesheet">
            <script>
                var FileAPI = {
                    debug: true
                    , media: true
                    , staticPath: '/js/FileAPI/'
                };
            </script>
            <script src="/js/jquery-2.2.4.min.js"></script>
            <script src="/js/FileAPI.min.js"></script>
            <script src="/js/FileAPI.exif.js"></script>
            <script src="/js/jquery.fileapi.js"></script>
            <script src="/js/jquery.Jcrop.min.js"></script>
            <script src="/js/jquery.modal.js"></script>
            <script>
                $('#userpic').fileapi({
                    url: '/api/upload',
                    accept: 'image/*',
                    imageSize: { minWidth: 304, minHeight: 304 },
                    elements: {
                        active: { show: '.js-upload', hide: '.js-browse' },
                        preview: {
                            el: '.js-preview',
                            width: 304,
                            height: 304
                        },
                        progress: '.js-progress'
                    },
                    onSelect: function (evt, ui){
                        var file = ui.files[0];
                        if( !FileAPI.support.transform ) {
                            alert('Your browser does not support Flash :(');
                        }
                        else if( file ){
                            $('#popup').modal({
                                closeOnEsc: true,
                                closeOnOverlayClick: false,
                                onOpen: function (overlay){
                                    $(overlay).on('click', '.js-upload', function (){
                                        $.modal().close();
                                        $('#userpic').fileapi('upload');
                                    });
                                    $('.js-img', overlay).cropper({
                                        file: file,
                                        bgColor: '#fff',
                                        maxSize: [$(window).width()-100, $(window).height()-100],
//                                        minSize: ['10%', '10%'],
//                                        selection: '90%',
                                        onSelect: function (coords){
                                            $('#userpic').fileapi('crop', file, coords);
                                        }
                                    });
                                }
                            }).open();
                        }
                    }
                });
            </script>
        {/literal}
    </body>
</html>