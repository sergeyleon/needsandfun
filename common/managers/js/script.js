/*
 * Author: Ilya Doroshin
 */

var cache = {
    'categories': {},
};

/**
 * cookie helper
 */
var Cookie = {

    /**
     * reading cookie
     * Cookie.create('foo', 'bar', 7);
     */    
    create: function(name, value, days) 
    {
        if (days) 
        {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = name + "=" + value + expires + "; path=/";
    },

    /**
     * reading cookie
     * Cookie.read('foo');
     */    
    read: function(name) 
    {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0, l = ca.length; i < l; i++) 
        {
            var c = ca[i];
            while (c.charAt(0) == ' ') 
            {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) == 0) 
                return c.substring(nameEQ.length, c.length);
        }
        return null;
    },
    
    /**
     * creating empty cookie instead of cookie['name']
     * Cookie.erase('foo');
     */
    erase: function(name) 
    {
        Cookie.create(name, '', -1);
    }
};

(function () {
    var categories = $('.categories');
    if (categories.size())
    {
        var categoriesOffset = $('.categories').offset().top - 30;
    
        $(this).scroll(function(){
            if (window.pageYOffset > categoriesOffset)
            {
                var height = categories.height();
                categories.addClass('follow').height(height);
            }
            else {
                categories.removeClass('follow').height('100%');
            }
        });    
    }
})();

var PopupMessages = {
    elements: false,
    init: function() {
        this.elements = $('.popup-messages');
        this.elements.find('.popup-message').each(function() {
            var closeInterval;
            var message = $(this);
            var closeBtn = message.find('.popup-message-close');
            
            message.hide().slideDown(300);

            message.mouseover(function() {
                clearInterval(closeInterval);
            });

            message.mouseout(function() {
                closeInterval = setTimeout(function() {
                    closeBtn.click();
                }, 5000);
            });

            closeBtn.click(function() {
                clearInterval(closeInterval);
                message.fadeOut(200, function() {
                    message.remove();    
                });
            });

            message.mouseout();            
        });

    }    
};

var Category = function(element) {
    var id = element.attr('data-id');
    var item = this;
    this.goods = [];

    var type = element.attr('data-type') 
        ? element.attr('data-type')
        : 'goods';

    this.delete = function() {
        var url = $('.categories').attr('data-action-delete');

        var params = {
            'deleteCategory': id
        };
        
        $.post(url, params, function(data) {
            if (data) alert(data);
            element.fadeOut(300, function() {
                delete cache['categories'][id];            
            });
        });
    };
    
    this.selectItem = function() {
        for (var i in cache['categories'])
        {
            cache['categories'][i][i == id ? 'select' : 'unselect']();
        }
    };
    
    this.select = function() {
        element.addClass('selected');

        if ('all' == id)
        {
            Cookie.erase(type + '_selectedCategory');
            $('.goods').addClass('all');
        }
        else {
            Cookie.create(type + '_selectedCategory', id);
            $('.goods').removeClass('all');
        }
        
        $('.good-item.visible').removeClass('visible');
        
        for (var i in cache.categories[id].goods)
        {
            if ('undefined' != typeof(cache.categories[id].goods[i]))
            {
                cache.categories[id].goods[i].visible(true);    
            }
        }
    };
    
    this.unselect = function() {
        element.removeClass('selected');
    };
    
    this.show = function() {
        this.visibility(true);
    };
    
    this.hide = function() {
        this.visibility(false);
    };
    
    this.visibility = function(visible) {
        var func, visible;
        var url = $('.categories').attr('data-action-visibility')
        var func = (visible ? 'remove' : 'add') + 'Class';        
    
        var params = {
            'categoryId': id,
            'visible':    visible/1
        };
        
        $.post(url, params, function(data) {
            if(data) console.error(data);
            $(element)[func]('category-item-hidden');
        });
    };
    
    this.init = function() {
        cache['categories'][id] = item;
        element.find('> .category-name .category-op-item').click(function() {
            var action = $(this).attr('data-type');
            
            if ('function' == typeof(cache['categories'][id][action]))
            {
                if ($(this).attr('data-confirm'))
                {
                    if (!window.confirm('Вы уверены?')) return;
                }
                cache['categories'][id][action]();
            }
        });
        
        element.click(function(e) {
            item.selectItem();
            e.stopPropagation();
        }).not('[data-id=all]').droppable({
            tolerance: 'pointer',
            accept: '.good-item',
            hoverClass: 'category-item-hover',
            greedy: true,
            drop: function(ui) {
                cache.good['new' == id ? 'newGood':'addCategory'](id);
            }
        });
    } ();
}

var Good = function(element) {
    this.element = element;

    var id = element.attr('data-id');
    var good = this;

    var exclusive = element.attr('data-exclusive');
    
    this.addCategory = function(category) {

        if (exclusive)
        {
            if (exclusive != category)
            {
                var good = cache['categories'][exclusive]['goods'][id];
                good.removeCategory(exclusive);
                good.visible(0);

                exclusive = category;
            }
            else
            {
                return;
            }
        }

        cache['categories'][category]['goods'][id] = good;
        this.send('addCategory', category);
    };

    this.newGood = function() {
        cache['categories']['new']['goods'][id] = good;        
        this.send('newGood');
    };    
    
    this.removeCategory = function(category) {
        delete cache['categories'][category]['goods'][id];
        this.send('removeCategory');
    };
    
    this.send = function(action, value) {
        var params = {
            'action': action,
            'goodId': id
        }
        
        if ('addCategory' == action || 'removeCategory' == action || 'deleteFromCategory' == action)
        {
            params['categoryId'] = value;
        }
        
        $.post($('.goods').attr('data-url'), params, function(data) {
            if (data) alert(data);
        });
    };
    
    this.visible = function(dir) {
        element[(dir ? 'add' : 'remove') + 'Class']('visible');
    };
    
    this.show = function() {
        this.send('show');
        element.removeClass('notAvailable');
    };
    
    this.hide = function() {
        this.send('hide');    
        element.addClass('notAvailable');
    };    
    
    this.delete = function() {
        var category = $('.category-item.selected').attr('data-id');
        
        if ('all' == category) 
        {
            this.send('deleteGood');
        }
        else if ('new' == category) 
        {
            this.send('notNewGood');
        }
        else 
        {
            delete cache['categories'][category]['goods'][id];
            this.send('deleteFromCategory', category);
        }
        
        element.fadeOut(function() {        
            if ('all' == category) element.remove();   
            else element.removeClass('visible').removeAttr('style').css('position', 'relative');
        });
    };
    
    this.init = function() {
        element.find('.tools-item').click(function() {
            var action = $(this).attr('data-action');
            good[action]();
        });
        
        var categories = element.attr('data-category').replace(/\s+/img, '').split(',');

        for (var i in categories)
        {
            var category = categories[i];

            if (category != 0)
            {
                cache.categories[category].goods[id] = good;            
            }
        }
        
        element.draggable({
            start: function() {
                cache['good'] = good;
                element.css('z-index', 1000);
            },
            stop: function() {
                delete cache['good'];            
                element.css('z-index', 1);
            },
            revert: true
        });
    } ();
    this.visible(false);

};

var Goods = {
    el: $('.goods'),
    init: function() {
        this.initItems();
        Categories.selectDefault();
    },
    
    initItems: function() {
        this.el.find('.good-item').each(function() {
            new Good($(this));
        });
    }
};

var Tabs = function(el) {
    var items    = el.find('[data-type=items]');
    var contents = el.find('[data-type=contents]');    
    var tabs     = this;
    
    this.init = function() {
        var TABS = this;
        items.find('.tabs-item').each(function(i) {
            var tab = $(this);
            var id  = tab.attr('data-id');

            tab.click(function() {
                if (!tab.hasClass('selected')) 
                {
                    items.find('.selected').removeClass('selected');
                    tab.addClass('selected');
                    TABS.select(i);
                }
            });
        });
        
        items.find('.tabs-item.selected').click();
    };
    
    this.select = function(i)
    {
        var i = i + 1;
        contents.find('.tabs-content.selected').removeClass('selected');
        contents.find('.tabs-content:nth-child(' + i + ')').addClass('selected');
    }
    
    this.init();
};

 
var Categories = {
    el: $('.categories'),
    selected: 'all',
    init:     function () {
        this.selectedCategory = this.el.attr('data-selected');
        this.initItems();
        this.initAdd(this.el.find('.category-add'));

        var sortable = this.el.find('.sortable');
        sortable.nestedSortable({
    		forcePlaceholderSize: true,
    		handle: 'div',
    		helper:	'clone',
    		items: 'li',
    		maxLevels: 2,
    		opacity: .6,
    		placeholder: 'placeholder',
    		revert: 0,
    		tabSize: 20,
    		tolerance: 'pointer',
    		toleranceElement: '> div',
    		stop: function(e, el) {
    		    var params = {};

                el.item.parent().children('.category-item').each(function(i) {
                    params[$(this).attr('data-id')] = {
                        'index':    i,
                        'parentId': el.item.parent().closest('.category-item').attr('data-id')
                    };
                });
                $.post(sortable.attr('data-url'), params, function(data) {
                    if (data) console.error(data);
                });
    		}
        });
    },
    
    initItems: function () {
        this.el.find('.category-item').each(function() {
            new Category($(this));
        });
    },

    selectDefault: function() {
        this.el.find('.category-item[data-id=' + this.selectedCategory + ']').click();
    },
    
    initAdd: function(el) {
        el.find('.category-add-link').click(function() {
            el.addClass('form');
            el.find('.category-add-form input[type=text]').focus().select();
        });        
    }, 
};
 
var Autocomplete = function(el) {
    var El = this;
    var source = el.find('[data-type=source]');
    var unfolded = false;

    var AutocompleteItem = function(item) {
        this.init = function() {
            item.mousedown(function() {
                unfolded = true;
            }).click(function() {
                var values = {};
                
                values.name = item.attr('data-name')
                    ? item.attr('data-name')
                    : item.html().replace(/(^\s+)|(\s+$)/img, '');

                if (item.attr('data-property_id'))
                    values.property_id = item.attr('data-property_id');

                El.setValues(values);
                el.removeClass('focused');
                unfolded = false;                
            });
        } ();
    };
    
    this.setValues = function (values) {
        var pattern = source.attr('name')||'';
        var needle  = source.attr('data-name');
        
        for (var i in values)
        {
            console.log('@' + i);
            el.find('[name="' + pattern.replace('[' + needle + ']', '[' + i + ']') + '"]').val(values[i]);
        }
    };    
    
    this.init = function() {
        source.focus(function() {
            el.addClass('show').addClass('focused');
        }).blur(function(e) {
            if (!unfolded) el.removeClass('focused');
        });        
        
        el.find('.autocomplete-item').each(function() {
            new AutocompleteItem($(this));
        });
    } ();
};

var PropertyType = function(el) {
    var PT = this;
    
//    this.delete = function() {
//        el.remove();
//    };    
//    
    this.copy = function() {
        var cloned = el.clone();
        
        var size = $('.type-properties .type-property').size();
        
        /**
         * обнуляем результаты
         */
        cloned.removeClass('new').attr('data-id', size);
        cloned.find(':input').each(function() {
            $(this).attr('name', $(this).attr('name').replace('[new]', '[' + size + ']'));
        });
        cloned.find('select').val(el.find('select').val());
        el.find(':input').val('');
        
        cloned.insertBefore(el);
        new PropertyType(cloned);
    };
     
//    this.tools = function() {
//        el.find('.tools .tools-item').each(function() {
//            var action = $(this).attr('data-type');
//            
//            $(this).click(function() {
//                PT[action]();
//            });
//        });
//    }();
    
    this.init = function() {}();
};

var MultipleInputs = function(element) {
    var add      = element.find('[data-type=add]');
    var sample   = element.find('[data-type=sample]').removeAttr('data-type');
    var property = element.attr('data-name');
    var Inputs   = this;
    var size     = 0;
    
    this.add = function() {  
        var row = sample.clone(1).insertBefore(sample).removeClass('onEmpty').attr('data-id', 'new');
        this.initRow(row, size++);
    };
    
    this.initRow = function(row, index) {
        row.find(':input').each(function() {
            var name = $(this).attr('data-name');
            $(this).attr('name', property + '[' + index + '][' + name + ']');
        });
        
        row.find('[data-type=delete]').click(function() {                    
            if (window.confirm('Вы уверены?')) row.remove();
        });
    };
    
    this.init = function() {
        size = element.find('tr[data-id]').each(function(i) {
            Inputs.initRow($(this), i);
        }).size();
        
        add.click(function() {
            Inputs.add();        
        });
        
        element.sortable({
            'axis': 'y',
            'items': '.type-property'
        });
        
        if (!element.hasClass('nonEmpty')) add.click();
    } ();
}

var Gallery = function(el) {
    var Gallery = this;
    var url     = el.attr('data-url');
    var type    = el.attr('data-type'); 
    
    this.controls = function() {
        el.find('.gallery-item').each(function() {
            var el = $(this);
            var id = el.attr('data-id');
            $(this).find('.gallery-item-delete').click(function() {
                $.post(url, {'deletePicture': id, 'type': type}, function() {
                    el.fadeOut(function() {
                        el.remove();
                    });
                });
            });
        })
    };
    this.init = function() {    
        Gallery.controls();
    }();
};

$(function() {
    Categories.init();
    Goods.init();    
    PopupMessages.init();
    
    $('.multipleInputs').each(function() {
        new MultipleInputs($(this));
    });

    $('.tabs').each(function() {
        new Tabs($(this));
    });

    
    $('.gallery-wrap').each(function() {
        var sortable = $(this);
        var type     = sortable.attr('data-type');
        
        sortable.sortable({
            'stop': function() {
                var ids = [];
                sortable.find('[data-id]').each(function() {
                    ids.push($(this).attr('data-id'));
                });
                
                $.post(sortable.attr('data-url'), {'ids': ids.join(','), 'type': type}, function(data) {
                    if (data) alert(data);
                });
            }
        });
        $(this).disableSelection();
    });
    
    $('.gallery-wrap').each(function() {
        new Gallery($(this));
    });
    
    $('.autocomplete').each(function() {
        new Autocomplete($(this));
    });


    $.timepicker.regional['ru'] = {
        timeOnlyTitle: 'Выберите время',
        timeText: 'Время',
        hourText: 'Часы',
        minuteText: 'Минуты',
        secondText: 'Секунды',
        millisecText: 'миллисекунды',
        currentText: 'Сейчас',
        closeText: 'Готово',
        ampm: false
    };

    $.timepicker.setDefaults($.timepicker.regional['ru']);   

    
    $('.jcalendar').each(function() {
        var options = {
            'dateFormat': 'dd.mm.yy'
        };

        if ($(this).hasClass('showTime'))
        {
            options['showTime'] = true;
            $(this).datetimepicker(options);
            console.log(123);
        }
        else
        {
            $(this).datepicker(options);    
        }
    });

    $('[data-confirm] a').click(function(e) {
        if (!window.confirm('Вы уверены?')) e.preventDefault();
    });
    
    $('.type-properties .type-property').each(function() {
        new PropertyType($(this));
    });
    
    $('textarea.wysiwyg').each(function() {
        var common = $(this).attr('data-common');
        $(this).tinymce({
            script_url : common + '/js/mylibs/tinymce/tiny_mce.js',
            mode : "textareas",
            theme : "advanced",
            plugins : "table,inlinepopups",
            // Theme options
            theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,fontselect,fontsizeselect,|,justifyleft,justifycenter,justifyright,justifyfull,|,table,removeformat,code",
            theme_advanced_buttons2 : "",
            theme_advanced_buttons3 : "",
            theme_advanced_buttons4 : "",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : true,
            content_css : common + "/css/style.css"
        });
    });
    
});