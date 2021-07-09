var AddFilesModule = (function () {

    var newItem = {};
    var classItems = [];

    var _classes = {
        FILE_INPUT_CONTAINER: '.js-add-files__input-container',
        LABEL_LIST: '.js-add-files__label-list',
        ADD_FILE_BUTTON: '.js-add-files__button_add',
        ADD_FILE_BUTTON_TEXT: '.js-add-files__button-text'
    };

    var _eventAddFile = function (fileItem, classItem, id) {
        if (fileItem.files && fileItem.files[0]) {
            var counter = Object.keys(classItem.$_fields).length;
            var cnt = Object.keys(fileItem.files).length;
            // В safari Object.keys(input.files) добавляет в конец массива 'length'
            {
                if (Object.keys(fileItem.files)[cnt - 1] == 'length') {
                    cnt--;
                }
            }
            for (var i = 0; i < cnt; i++) {
                if (!(counter + i + 1 <= classItem.options.maxCount)) {
                    alert(t('Превышено максимальное количество файлов.'));
                    fileItem.value = '';
                    return false;
                }
                if (fileItem.files[i].size > classItem.options.maxSizeReal) {
                    alert(t('Размер файла не должен превышать {{0}} МБ', [classItem.options.maxSize]));
                    fileItem.value = '';
                    return false;
                }
            }
            _addLabelForNewFile(0, fileItem, classItem, id);
            _setButtonText(classItem);
        }
    };

    var _addLabelForNewFile = function (fileNum, input, classItem, id) {
        var fileName = input.files[fileNum].name;
        var size = input.files[fileNum].size;

        var $fileName = $('<input type="hidden" name="inputName__' + id + '" value ="sizeFile' + fileName + size + '">');
        _addLabel(fileName, id, $fileName, false, classItem);
    };

    var _getFileIco = function (fileName) {
        var len = fileName.length;
        var symb3 = fileName.substr(len - 3, len).toLowerCase();
        var symb4 = fileName.substr(len - 4, len).toLowerCase();
        var ico = '';
        if ($.inArray(symb3, ['doc', 'xls', 'rtf', 'txt']) != -1 || $.inArray(symb4, ['docx', 'xlsx']) != -1) {
            ico = 'doc';
        } else if ($.inArray(symb3, ['zip', 'rar']) != -1) {
            ico = 'zip';
        } else if ($.inArray(symb3, ['png', 'jpg', 'gif', 'psd']) != -1 || $.inArray(symb4, ['jpeg']) != -1) {
            ico = 'image';
        } else if ($.inArray(symb3, ['mp3', 'wav', 'avi']) != -1) {
            ico = 'audio';
        } else {
            ico = 'zip';
        }

        return ico;
    };

    var _addLabel = function (fileName, id, $extAppend, isOld, classItem) {
        var ico = _getFileIco(fileName);
        var $fileNameBlock = $('<span class="dib v-align-m ml10 '+(allow?'':'color-red')+'">').text(fileName);

        var $delFileBlock = "";
        $delFileBlock = $('<a class="remove-file-link" data-old="' + isOld + '" data-id="' + id + '"></a>');
        $delFileBlock.bind('click', function() {
            _deleteFile($(this), classItem);
        });

        var $labelContainer = $('<div class="mt10 file-item"><i class="ico-file-' + ico + ' dib v-align-m"></i></div>');
        $labelContainer.append($fileNameBlock).append($delFileBlock).append($extAppend);
        classItem.$_fields[id].label = $labelContainer;
        classItem.$addFilesContainer.find(_classes.LABEL_LIST).append($labelContainer);
    };

    var _addField = function (classItem, id) {
        var $field = $('<input data-id="' + id + '" name="' + classItem.options['input-name'] + '_files[]" type="file" >');
        $field.bind('change', function(){
            _eventAddFile(this, classItem, id);
        });
        classItem.$_fields[id].input = $field;
        classItem.$addFilesContainer.find(_classes.FILE_INPUT_CONTAINER).append($field);
    };

    var _setEventHandlers = function (classItem) {
        classItem.$addFilesContainer.on('click', _classes.ADD_FILE_BUTTON, function () {
            if (classItem.$addFilesContainer.find(_classes.FILE_INPUT_CONTAINER + ' input:last-child').val() == '') {
                classItem.$addFilesContainer.find(_classes.FILE_INPUT_CONTAINER + ' input:last-child').trigger('click');
            }else{
                var newId = classItem._inputIdGen.getID();
                classItem.$_fields[newId] = {};
                _addField(classItem, newId);
                classItem.$addFilesContainer.find(_classes.FILE_INPUT_CONTAINER + ' input:last-child').trigger('click');
            }
        });
    };

    var _deleteFile = function (button, classItem) {
        if ($(button).data('old') == true) {
            classItem.$addFilesContainer.find(_classes.FILE_INPUT_CONTAINER).prepend($('<input>').attr({
                type: 'hidden',
                name: 'remove_' + classItem.options['input-name'] + '_files[]',
                value: $(button).data('id')
            }));
        }

        var id = $(button).data('id');
        classItem.$_fields[id].label.remove();
        classItem.$_fields[id].input.remove();
        delete classItem.$_fields[id];
        _setButtonText(classItem);
    };

    var _setButtonText = function (classItem) {
        var $addBtn = classItem.$addFilesContainer.find(_classes.ADD_FILE_BUTTON);
        if (Object.keys(classItem.$_fields).length > 0) {
            classItem.$addFilesContainer.find(_classes.ADD_FILE_BUTTON_TEXT).text($addBtn.data('withFiles'));
        } else {
            classItem.$addFilesContainer.find(_classes.ADD_FILE_BUTTON_TEXT).text($addBtn.data('withoutFiles'));
        }
    };

    var _showLoadedFile = function (file, classItem) {
        classItem.$_fields[file.id] = {};
        _addLabel(file.fname, file.id, {}, true, classItem);
        _setButtonText(classItem);
    };
    
    var _construct = function (container, options) {
        var newItem = {
            options: JSON.parse(JSON.stringify(options)),
            $_fields: {},
            $addFilesContainer: container,
            _inputIdGen: new IDGenerator('id-')
        };
        if (!newItem.options['input-name']) {
            newItem.options['input-name'] = $(container).data('input-name');
        }
        classItems.push(newItem);
    }

    return {
        init: function (o) {
            $('.js-add-files').each(function (index) {
                _construct($(this), o);
                _setEventHandlers(classItems[index]);
                _setButtonText(classItems[index]);
                
                if (typeof classItems[index].options.files !== 'undefined' && classItems[index].options.files.length > 0) {
                    for (var i = 0; i < classItems[index].options.files[index].length; i++) {
                        _showLoadedFile(classItems[index].options.files[index][i], classItems[index]);
                    }
                }
            });
        },
        checkMaxFileSize: function () {
            var totalSize = 0;
            $('.js-kwork-file').each(function () {
                var file = this.files[0];
                if (typeof file != 'undefined') {
                    totalSize += file.size || file.fileSize;
                }
            });

            //totalSize += KworkPhotoModule.getPhotoSize(); //TODO: нужно сделать подсчет размера загружаемых изображений

            return totalSize <= 16 * 1024 * 1024;
        },
    }
})();