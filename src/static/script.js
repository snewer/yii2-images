// https://habrahabr.ru/post/113073/
// http://stackoverflow.com/questions/5627284/pass-in-an-array-of-deferreds-to-when

(function ($) {

    function validateImageFile(file) {
        return file.size > 0 && /^image/.test(file.type);
    }

    $.fn.ImagesWidget = function (options) {
        // id изображения, открытого в модельном окне в данный момент
        var selectedImageIndex = -1;
        var modalDisabled = false;
        var cropperIsInit = false;
        // изображения, с которым работает модальное окно
        var images = [];
        var input = this.first();
        // @formatter:off
        var modalHtml = '<div class="imagesWidget modal inmodal" tabindex="-1" aria-hidden="true">' +
                '<div class="modal-dialog modal-lg">' +
                    '<div class="modal-content animated bounceInRight">' +
                           // '<div style="padding: 15px; font-size: 85%; font-weight: bold">' +
                           //     'Каждое изображение перед загрузкой можно обрезать' +
                           // '</div>' +
                            '<table style="width: 100%">' +
                                '<tbody>' +
                                    '<tr style="vertical-align: top">' +
                                        '<td style="width: 150px">' +
                                            '<div class="previews">' +
                                            '</div>' +
                                        '</td>' +
                                        '<td>' +
                                            '<div class="cropper-wrapper">' +
                                                '<img src="#" style="display: none">' +
                                            '</div>' +
                                        '</td>' +
                                    '</tr>' +
                                '</tbody>' +
                            '</table>' +
                        '<div class="modal-footer" style="border-top: none">' +
                            '<button type="button" class="btn btn-primary"><span class="fa fa-rotate-left"></span></button>' +
                            '<button type="button" class="btn btn-primary"><span class="fa fa-rotate-right"></span></button>' +
                        '</div>' +
                        '<div class="modal-footer" style="border-top: none">' +
                            '<button type="button" class="btn btn-white cancelUpload" data-dismiss="modal">Отмена</button>' +
                            '<button type="button" class="btn btn-primary ladda-button upload" data-style="expand-right">' +
                                'Зарузить выбранное' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        // @formatter:on
        var modal = $(modalHtml);

        var uploadBtn = modal.find('.upload').first();
        var cancelUploadBtn = modal.find('.cancelUpload').first();
        var modalDialog = modal.find('.modal-dialog').first();
        var previews = modal.find('.previews').first();
        var cropperImage = modal.find('.cropper-wrapper img').first();
        var rotateLeft = modal.find('.fa-rotate-left');
        var rotateRight = modal.find('.fa-rotate-right');

        rotateRight.on('click', function () {
            console.log(cropperImage);
            cropperImage.cropper('rotate', 45);
        });
        rotateLeft.on('click', function () {
            cropperImage.cropper('rotate', -45);
        });

        uploadBtn.ladda();
        // инициализируем модальное окно
        input.after(modal);
        modal.modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        function showModal() {
            modal.modal('show');
        }

        function hideModal() {
            modal.modal('hide');
        }

        function disableModal() {
            previews.addClass('disabled');
            modal.find('input, button, textarea').prop('disabled', true);
            cropperImage.cropper('disable');
            modalDisabled = true;
        }

        function enableModal() {
            previews.removeClass('disabled');
            modal.find('input, button, textarea').prop('disabled', false);
            cropperImage.cropper('enable');
            modalDisabled = false;
        }

        function startUploadImages() {
            previews.html('');
            if (images.length > 1) {
                previews.parents('td').first().show();
                modalDialog.addClass('modal-lg');
            } else {
                previews.parents('td').first().hide();
                modalDialog.removeClass('modal-lg');
            }
            $.each(images, function (index, image) {
                image.$preview = $(
                    '<div class="preview">' +
                    '<div class="delete">&times;</div>' +
                    '<div class="arrow"></div>' +
                    '<div class="helper"></div>' +
                    '<img src="' + image.src + '">' +
                    '</div>'
                );
                if (index == 0) {
                    image.$preview.addClass('selected');
                    if (cropperIsInit) {
                        cropperImage.cropper('replace', image.src);
                    } else {
                        cropperImage.attr('src', image.src);
                        cropperImage.cropper({
                            autoCropArea: 1,
                            checkCrossOrigin: false,
                            guides: false,
                            checkOrientation: false,
                            crop: function () {
                                images[selectedImageIndex].crop = $(this).cropper('getData', true);
                            }
                        });
                        cropperIsInit = true;
                    }
                }
                image.$preview.on('click', function () {
                    if (modalDisabled) return;
                    selectedImageIndex = index;
                    //var image = images[index];
                    cropperImage.cropper('replace', image.src);
                    // если ранее изображение обрезали - устанавливаем эти параметры
                    if (image.crop) {
                        cropperImage.cropper('setData', image.crop);
                    }
                    previews.find('.selected').removeClass('selected');
                    $(this).addClass('selected');
                });
                image.$preview.find('.delete').on('click', function () {
                    if (modalDisabled) return;
                    image.deleted = true;
                    image.src = '';
                    image.$preview.remove();
                    if (index == selectedImageIndex) {
                        var imageClicked = false;
                        for (var i = index; i < images.length; ++i) {
                            if (!images[i].deleted) {
                                images[i].$preview.click();
                                imageClicked = true;
                                break;
                            }
                        }
                        if (!imageClicked) {
                            for (i = index; i >= 0; --i) {
                                if (!images[i].deleted) {
                                    images[i].$preview.click();
                                    imageClicked = true;
                                    break;
                                }
                            }
                        }
                        if (!imageClicked) {
                            cancelUploadBtn.click();
                        }
                    }
                });
                previews.append(image.$preview);
            });

            selectedImageIndex = 0;
            showModal();
        }

        function uploadImage(image) {
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
                // ставим метку, что изображение успешно загружено и очищаем src
                image.uploaded = true;
                image.src = '';

                if (uploadRes.success === true) {
                    showUploadedImageInCollection(uploadRes);
                    input.val(uploadRes.image.id);
                    def.resolve(uploadRes);
                } else {
                    def.reject();
                }
            }).fail(function () {
                def.reject('Не удалось загрузить изображение');
            });
            return def.promise();
        }

        // загрузка одного изображения
        uploadBtn.on('click', function () {
            var _this = $(this);
            disableModal();
            _this.ladda('start');
            _this.find('.ladda-label').text('Идет загрузка...');
            if (selectedImageIndex >= 0) {
                uploadImage(images[selectedImageIndex]).done(function () {
                    enableModal();
                    _this.find('.ladda-label').text('Загрузить');
                    _this.ladda('stop');
                    // если в массиве есть не загруженное и не удаленное изображение
                    // то не закрываем модальное окно
                    // иначе - закрываем.
                    for (var i = 0; i < images.length; ++i) {
                        if (!images[i].deleted && !images[i].uploaded) {
                            return;
                        }
                    }
                    hideModal();
                }).fail(function () {
                    alert('Загрузить изображене не удалось');
                });
            }
        });

        var $collection = $('<div class="imagesCollection">' +
            '<div class="images">' +
            '</div>' +
            '<div class="upload-tools">' +
            '<span class="clickable select1">Выбрать файл с компьютера</span> ' +
            '<span class="clickable select2">или по URL</span>' +
            '</div>' +
            '</div>');

        var $imagesCollectionContainer = $collection.find('.images');

        input.after($collection);

        // обновляем порядок изображений коллекции на сервере
        function updateSorting() {
            var $imagesCollectionImages = $imagesCollectionContainer.find('.wrapper');
            var data = {};
            $.each($imagesCollectionImages, function () {
                data[$(this).data('id')] = $(this).index();
            });
            $.post(options.urls.sortImagesCollection, {
                collection_id: input.val(),
                data: data
            });
        }


        function readImageFile(file) {
            var def = $.Deferred();
            var fr = new FileReader;
            fr.onload = function (res) {
                images.push({src: res.target.result});
                def.resolve();
            };
            fr.readAsDataURL(file);
            return def.promise();
        }

        $collection.find('.select1').on('click', function () {
            var $fileInput = $('<input type="file" style="display:none">');
            images = [];
            $('body').append($fileInput);
            $fileInput.on('change', function () {
                var files = this.files;
                var defs = [];
                for (var i = 0; i < files.length; ++i) {
                    if (validateImageFile(files[i])) {
                        defs.push(readImageFile(files[i]));
                    }
                }
                $.when.apply($, defs).done(function () {
                    startUploadImages();
                });
                $fileInput.remove();
            });
            $fileInput.click();
        });

        function uploadImageFromURL(url) {
            return $.post(options.urls.imageProxy, {url: url}, function (res) {
                if (res.success) {
                    images = [{src: res.base64}];
                    startUploadImages();
                }
            }, 'json');
        }

        $collection.find('.select2').on('click', function () {
            var url = prompt('Введите ссылку на изображение', 'http://');
            if (url && url != 'http://') {
                uploadImageFromURL(url);
            }
        });

        $collection[0].ondragover = function () {
            return false;
        };
        $collection[0].ondragleave = function () {
            return false;
        };
        $collection[0].ondrop = function (e) {
            var files = e.dataTransfer.files;
            if (files) {
                var defs = [];
                for (var i = 0; i < files.length; ++i) {
                    if (validateImageFile(files[i])) {
                        defs.push(readImageFile(files[i]));
                    }
                }
                $.when.apply($, defs).done(function () {
                    if (images.length) {
                        startUploadImages();
                    }
                });
                e.preventDefault();
            }
        };

        function showUploadedImageInCollection(image) {
            var $elem = $(
                // @formatter:off
                '<div class="wrapper" data-id="' + image.image.id + '">' +
                    '<div class="icontainer">' +
                        '<div class="image">' +
                            '<img data-src="' + image.image.url + '" src="' + image.preview.url + '" style="cursor: -webkit-zoom-in; cursor: zoom-in">' +
                        '</div>' +
                        '<div class="toolbar">' +
                            '<span title="Обрезать изображение" class="edit glyphicon glyphicon-pencil"></span>' +
                            '<span title="Удалить изображение" class="remove glyphicon glyphicon-remove"></span>' +
                            '<div class="clearfix"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
                // @formatter:on
            );


            $imagesCollectionContainer.html($elem);


            var $img = $elem.find('img');
            var $deleteBtn = $elem.find('.remove');
            var $cropBtn = $elem.find('.edit');

            $deleteBtn.on('click', function () {
                if (!confirm('Удалить?')) return;
                input.val('');
                $elem.remove();
            });


            $cropBtn.on('click', function () {
                uploadImageFromURL(image.image.url);
                //$elem.remove();
            });

        }

        if (input.val() > 0) {

            $.post(options.urls.getImage, {id: input.val()}, function (res) {
                if (res.success === true) {
                    showUploadedImageInCollection(res);
                }
            }, 'json');

        }

    };

})(jQuery);