(function ($) {

    function validateImageFile(file) {
        return file.size > 0 && /^image/.test(file.type);
    }

    $.fn.ImagesWidget = function (options) {

        var image;

        var $input = this.first();

        var modalDisabled = false;

        var cropperIsInit = false;

        var modalHtml =
            // @formatter:off
            '<div class="image-upload-widget-modal modal inmodal" tabindex="-1" aria-hidden="true">' +
                '<div class="modal-dialog">' +
                    '<div class="modal-content animated bounceInRight">' +
                            '<div class="modal-footer" style="border-top: none">' +
                                '<h5 class="pull-left">Загрузка изображения</h5>' +
                                '<button type="button" class="image-upload-widget-modal-rotate-right-btn btn btn-primary">' +
                                    '<span class="fa fa-rotate-right"></span>' +
                                '</button>' +
                                '<button type="button" class="image-upload-widget-modal-rotate-left-btn btn btn-primary">' +
                                    '<span class="fa fa-rotate-left"></span>' +
                                '</button>' +
                            '</div>' +
                            '<table style="width: 100%">' +
                                '<tbody>' +
                                    '<tr style="vertical-align: top">' +
                                        '<td>' +
                                            '<div class="cropper-wrapper">' +
                                                '<img src="#" style="display: none">' +
                                            '</div>' +
                                        '</td>' +
                                    '</tr>' +
                                '</tbody>' +
                            '</table>' +
                            '<div class="modal-footer" style="border-top: none">' +
                                '<button type="button" class="btn btn-white" data-dismiss="modal">Отмена</button>' +
                                '<button type="button" class="btn btn-primary ladda-button image-upload-widget-modal-upload-btn" data-style="expand-right">' +
                                    'Зарузить выбранное' +
                                '</button>' +
                            '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
            // @formatter:on

        var $modal = $(modalHtml);

        var $uploadBtn = $modal.find('.image-upload-widget-modal-upload-btn').first();

        var $cropperImage = $modal.find('.cropper-wrapper img').first();

        var $rotateLeft = $modal.find('.image-upload-widget-modal-rotate-left-btn');

        var $rotateRight = $modal.find('.image-upload-widget-modal-rotate-right-btn');

        var $widget = $(
            // @formatter:off
            '<div class="image-upload-widget">' +
                '<div class="uploaded-image-container-wrapper">' +
                    '<div class="uploaded-image-container">' +
                        '<div class="uploaded-image-preview">' +
                            '<img src="' + options.emptyImage + '">' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="image-upload-tools">' +
                    '<span class="image-upload-tools-clickable select-image-from-device">Выбрать файл с компьютера</span> ' +
                    '<span class="image-upload-tools-clickable select-image-from-url">или по URL</span>' +
                '</div>' +
            '</div>'
            // @formatter:on
        );
        var $widgetImageContainer = $widget.find('.uploaded-image-container-wrapper');

        function showModal() {
            $modal.modal('show');
        }

        function hideModal() {
            $modal.modal('hide');
        }

        function disableModal() {
            $modal.find('input, button, textarea').prop('disabled', true);
            $cropperImage.cropper('disable');
            modalDisabled = true;
        }

        function enableModal() {
            $modal.find('input, button, textarea').prop('disabled', false);
            $cropperImage.cropper('enable');
            modalDisabled = false;
        }

        function readImageFile(file) {
            var def = $.Deferred();
            var fr = new FileReader;
            fr.onload = function (res) {
                image = {src: res.target.result};
                def.resolve();
            };
            fr.readAsDataURL(file);
            return def.promise();
        }

        function uploadImageFromURL(url) {
            return $.post(options.urls.imageProxy, {url: url}, function (res) {
                if (res.success) {
                    image = {src: res.base64};
                    startUploadImages();
                }
            }, 'json');
        }

        function startUploadImages() {
            showModal();
            if (cropperIsInit) {
                $cropperImage.cropper('replace', image.src);
            } else {
                $cropperImage.attr('src', image.src);
                $cropperImage.cropper({
                    autoCropArea: 1,
                    aspectRatio: options.aspectRatio > 0 ? options.aspectRatio : NaN,
                    checkCrossOrigin: false,
                    guides: false,
                    checkOrientation: false,
                    crop: function () {
                        image.crop = $(this).cropper('getData', true);
                    }
                });
                cropperIsInit = true;
            }
        }

        function uploadImage() {
            var def = $.Deferred();
            $.post(options.urls.imageUpload, {
                source: image.src,
                options: {
                    trim: options.trim,
                    aspectRatio: options.aspectRatio,
                    minWidth: options.minWidth,
                    minHeight: options.minHeight,
                    maxWidth: options.maxWidth,
                    maxHeight: options.maxHeight,
                    supportAC: options.supportAC,
                    bgColor: options.bgColor,
                    crop: image.crop || {}
                }
            }).done(function (uploadRes) {
                image.src = '';
                if (uploadRes.success === true) {
                    showUploadedImage(uploadRes);
                    $input.val(uploadRes.image.id);
                    def.resolve(uploadRes);
                } else {
                    def.reject();
                }
            });
            return def.promise();
        }

        function showUploadedImage(image) {
            var $elem = $(
                // @formatter:off
                    '<div class="uploaded-image-container">' +
                        '<div class="uploaded-image-preview">' +
                            '<a href="' + image.image.url + '" target="_blank" style="display: block">' +
                                '<img src="' + image.preview.url + '">' +
                            '</a>' +
                        '</div>' +
                        '<div class="uploaded-image-toolbar">' +
                            '<span title="Обрезать изображение" class="edit fa fa-crop"></span>' +
                            '<span title="Удалить изображение" class="remove fa fa-times"></span>' +
                            '<div class="clearfix"></div>' +
                        '</div>' +
                    '</div>'
                // @formatter:on
            );
            $widgetImageContainer.html($elem);
            $elem.find('a').magnificPopup({
                type: 'image',
                closeOnContentClick: true,
                mainClass: 'mfp-img-mobile',
                image: {
                    verticalFit: true
                }
            });
            $elem.find('.remove').on('click', function () {
                if (!confirm('Удалить?')) return;
                $input.val('');
                var html = '<div class="uploaded-image-container-wrapper">' +
                    '<div class="uploaded-image-container">' +
                    '<div class="uploaded-image-preview">' +
                    '<img src="' + options.emptyImage + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>';
                $widgetImageContainer.html(html);
            });
            $elem.find('.edit').on('click', function () {
                uploadImageFromURL(image.image.url);
            });
        }

        $input.after($widget);
        $input.after($modal);

        $rotateRight.on('click', function () {
            $cropperImage.cropper('rotate', 45);
        });

        $rotateLeft.on('click', function () {
            $cropperImage.cropper('rotate', -45);
        });

        $uploadBtn.ladda();
        // инициализируем модальное окно

        $modal.modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        $uploadBtn.on('click', function () {
            var _this = $(this);
            disableModal();
            _this.ladda('start');
            _this.find('.ladda-label').text('Идет загрузка...');
            uploadImage().done(function () {
                enableModal();
                _this.find('.ladda-label').text('Загрузить');
                _this.ladda('stop');
                hideModal();
            }).fail(function () {
                alert('Загрузить изображене не удалось');
                enableModal();
                _this.find('.ladda-label').text('Загрузить');
                _this.ladda('stop');
                hideModal();
            });
        });

        $widget.find('.select-image-from-device').on('click', function () {
            var $fileInput = $('<input type="file" style="display:none">');
            $('body').append($fileInput);
            $fileInput.on('change', function () {
                var file = this.files[0];
                if (validateImageFile(file)) {
                    readImageFile(file).done(function () {
                        startUploadImages();
                    });
                }
                $fileInput.remove();
            });
            $fileInput.click();
        });

        $widget.find('.select-image-from-url').on('click', function () {
            var url = prompt('Введите ссылку на изображение', 'http://');
            if (url) {
                uploadImageFromURL(url);
            }
        });

        $widget[0].ondragover = function () {
            return false;
        };

        $widget[0].ondragleave = function () {
            return false;
        };

        $widget[0].ondrop = function (e) {
            var file = e.dataTransfer.files[0];
            if (validateImageFile(file)) {
                readImageFile(file).done(function () {
                    startUploadImages();
                });
            }
            e.preventDefault();
        };

        if ($input.val() > 0) {
            $.post(options.urls.getImage, {id: $input.val()}, function (res) {
                if (res.success === true) {
                    showUploadedImage(res);
                }
            }, 'json');
        }

    };

})(jQuery);