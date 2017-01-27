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

        {literal}
            <script>
                $('#userpic').fileapi({
                    url: 'http://rubaxa.org/FileAPI/server/ctrl.php',
                    accept: 'image/*',
                    imageSize: { minWidth: 200, minHeight: 200 },
                    elements: {
                        active: { show: '.js-upload', hide: '.js-browse' },
                        preview: {
                            el: '.js-preview',
                            width: 200,
                            height: 200
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
                                        minSize: [200, 200],
                                        selection: '90%',
//                                        onSelect: function (coords){
//                                            $('#userpic').fileapi('crop', file, coords);
//                                        }
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