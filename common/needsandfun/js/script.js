/*
 * Author: Ilya Doroshin
 */

var PopupMessages = {
    elements: false,
    init: function() {
        this.elements = $('.popup-messages');
        this.sample   = this.elements.find('[data-type=sample]');
        this.elements.find('.popup-message').not('[data-type=sample]').each(function() {
            PopupMessages.show($(this));
        });
    }, 

    add: function(text) {
        var message = this.sample.clone().removeAttr('data-type');
        message.find('[data-type=msg]').html(text).removeAttr('data-type');

        this.elements.append(message);
        PopupMessages.show(message);
    },

    show: function(message) {
        var closeInterval;

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
    }
};

var Filtered = function(el) {
    var name  = el.attr('data-name');
    var items = $('[data-name=' + name + '][data-type=filterable]');
    var handles = el.find('[data-type=filter-item]');

    handles.click(function() {
        handles.removeClass('selected');
        var id = $(this).addClass('selected').attr('data-id');

        if ('all' == id)
        {
            items.find('[data-type]').show();
        }
        else
        {
            items.find('[data-type]').hide();
            items.find('[data-type=' + id + ']').show();    

            console.log('[data-type=' + id + ']')
        }
        
    });

}

var Login = {
    modal: false,

    init: function() {
        var modal = $('#login-form');
        var handle = $('#login-handle');

        modal.find('.login-close').click(function() {
            modal.addClass('closed');
        });


        handle.click(function(e) {
            modal.removeClass('closed');
            e.preventDefault();
        });
    }
};
Login.init();

var CategoryItem = function(element) {
    var category = this;
    this.fold = function(dir) {
        var func = dir
            ? 'removeClass'
            : 'addClass';
        
        element[func]('selected');
        
    };
    this.init = function() {
        element.find('.category-item-icon').click(function() {
            category.fold(element.hasClass('selected'));
        });
    }();
}; 

var Categories = {
    el: $('.categories'),
    init: function () {
        this.el.find('.category-item').each(function() {
            new CategoryItem($(this));
        });
    }
};

var BigBanners = function() {
    if ( arguments.callee._singletonInstance )
        return arguments.callee._singletonInstance;

    arguments.callee._singletonInstance = this;

    var interval;
    var element  = $('.bigbanners');
    var current  = 0;
    var elements = element.find('.bigbanner-page');
    var total    = elements.size();

    elements.each(function(i) {
        var page = $(this);

        page.click(function() {
            element.find('.bigbanner-page.selected').removeClass('selected');
            $(this).addClass('selected');

            var oldBanner = element.find('.bigbanner-item.selected').css('z-index', '1');
            var newBanner = element.find('.bigbanner-item:nth-child(' + (i+1) + ')').css('z-index', '2').addClass('selected');
            newBanner.hide().fadeIn(function() {
                oldBanner.removeClass('selected').hide();
                newBanner.css('z-index', 1);
            });

            startInterval();
        });
    });

    var interval;

    var pager = function(page) {
        var index;

        if ('undefined' == typeof(page))
        {
            current++;
            if (current > total) current = 0;
            index = current;
        }
        else 
        {
            index = page;
        }
        
        $(elements[index]).click();
    }

    var startInterval = function() {
        clearInterval(interval);
        interval = setInterval(function() {
            pager();
        }, 5000);
    };

    startInterval();
};

var CartItem = function(el) {
    var id      = el.attr('data-id')/1;
    var perGood = el.attr('data-pergood')/1; 
    var qnt     = el.attr('data-qnt')/1;
    
    var qntEl   = el.find('input[data-type=qnt]');
    var priceEl = el.find('[data-type=price]');
    var delBtn  = el.find('[data-action=delete]');
    
    this.init = function() {
        var instance = this;
        Cart.items[id] = {
            'id':      id,
            'qnt':     qnt,
            'instance': instance,
            'price':   perGood * qnt,
            'perGood': perGood
        };
        
        delBtn.click(function() {
            instance.delete();
        });
        
        qntEl.keyup(function() {
            Cart.restartInterval();
        });
    };
    
    this.delete = function() {
        if (window.confirm('Удалить?'))
        {
            var elements = el.find('td').add('tr[data-desc=' + id + '] td');
            Cart.removeGood(id, Cart.items[id].qnt);
                            
            elements.fadeOut(function() {
                elements.remove();
                delete Cart.items[id];                
            });
        }
    };
    
    this.updateQnt = function() {
        var diff = Cart.items[id]['qnt'] - qntEl.val()
        
        if (diff) 
        {
            if (0 > diff)
            {
                Cart.addGood(id, -1 * diff);

            }
            else if (0 < diff)
            {
                Cart.removeGood(id, diff);
            }

            Cart.items[id]['qnt'] = qntEl.val();
            this.updatePrice(Cart.items[id]['qnt'] * perGood);
        }
    };
    
    this.updatePrice = function(price) {
        Cart.items[id]['price'] = price;
        priceEl.html(moneyFormat(price));
    };
    
    this.init();
    
};

var Cart = {
    discount: {'summ': 0, 'reviews': 0},
    element: false,
    icon:    false,
    update:  false,
    size:    0,
    sizeEl:  false,
    
    items: {},
    goodPrice: 0,
    cartPrice: 0,
    interval: false,
    
    init: function() {
        this.element   = $('#cart');
        this.icon      = $('#cart-icon');
        this.update    = this.icon.attr('data-url');
        this.size      = this.icon.attr('data-size')/1;
        this.sizeEl    = this.icon.find('[data-type=cart-size]');
        this.woDelivery   = this.element.find('[data-type=withoutGoodsText]'); 
        this.woDeliveryPrice = this.element.find('[data-type=withoutGoodsPrice]');
        this.submitBtn = this.element.find('[data-type=submit]');
        
        this.formItems = this.element.find('[data-required]');
        
        this.overallText  = this.element.find('[data-type=cartText]');
        this.discountTextElement = this.element.find('[data-type=discountText]');
        this.overallPrice = this.element.find('[data-type=cartPrice]');        

        this.deliveryType = this.element.find('[data-type=tabsType]');
        
        this.cartItems = this.element.find('[data-type=cart-goods]');
        this.initCartItems();
        
        this.element.find('.tabs-address input[name]').keyup(function() {
            Cart.restartInterval();
        });
        
        this.element.find('.tabs-item').click(function() {
            Cart.restartInterval();        
        });
        
        this.restartInterval();
    },
    
    restartInterval: function() {
        var interval;
        clearInterval(interval);
        
        interval = setInterval(function() {
            Cart.updateCart();
        }, 1000);
        Cart.updateCart();
    },
    
    updateCart: function() {
        this.cartPrice = 0;
        
        var totalGoods = 0;
        var goodPrice = 0;
        
        for (var i in Cart.items)
        {
            var item = Cart.items[i];        
            item.instance.updateQnt();

            totalGoods += item.qnt/1;
            goodPrice += item.price/1;
        }
        
        this.updateOveralls(totalGoods, goodPrice);
    },
    
    checkForm: function() {
        var disabled = true;
        
        for (var i in this.items)
        {
            disabled = false;
            break;
        }
        
        if (!disabled) 
        {
            this.element.find('.tabs-content.selected [data-required]').each(function() {
                var item = $(this);
                var name = item.attr('data-name');
                
                if (!item.attr('disabled'))
                {
                    var isDisabled = 1;

                    Cart.element.find('.tabs-content.selected :input[data-name=' + name + ']').each(function() {
                        var pattern = 'email' == name
                            ? /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/
                            : /\S+/;
                            
                        if (pattern.test($(this).val())) isDisabled *= 0;                    
                    });
                    
                    if (isDisabled) disabled = true;
                }
            });
        }
        
        if (!disabled)
        {
            this.submitBtn.removeClass('disabled');
            this.submitBtn.find('.cart-submit-btn').removeAttr('disabled');
        }
        else 
        {
            this.submitBtn.addClass('disabled');        
            this.submitBtn.find('.cart-submit-btn').attr('disabled', 'disabled');            
        }
            
    },
    
    updateOveralls: function(qnt, goodPrice) {
        this.checkForm();
    
        this.updateWODelivery(qnt, goodPrice);
            
        var delivery = $('.delivery-type[data-id=' + this.deliveryType.val() + ']');
        
        var deliveryPrice = delivery.attr('data-price');
        
        
        
        var deliveryText  = delivery.attr('data-text'); 
        
        /* BALTIC IT Fix */
        if(this.deliveryType.val() == 'courier') {
          if(goodPrice > '2500') { deliveryPrice = '0';}
        }
        if(this.deliveryType.val() == 'metro') {
          if(goodPrice > '1000') { deliveryPrice = '0';}
        }
        /* END BALTIC IT Fix */
        
        var goodsText = this.goodsText(qnt) + moneyFormat(goodPrice, false);
        var deliveryText = deliveryText + (deliveryPrice/1 ? (': ' + moneyFormat(deliveryPrice, false)) : '');
        var discountText = this.discountText(this.element.attr('data-reviews'), this.element.attr('data-summ'));
        
        var totalPrice = moneyFormat((goodPrice/1 + deliveryPrice/1 - this.discount.reviews/1) * (1 - this.discount.summ/100));
        
        this.overallText.html(goodsText + ' + ' + deliveryText + ''); 
        this.discountTextElement.html(discountText);
        this.overallPrice.html(totalPrice);
    },

    discountText: function(reviewsDiscount, summDiscount) {
        var text = [];
        if (reviewsDiscount)
        {
            this.discount.reviews = reviewsDiscount;
            text.push('&minus;' + moneyFormat(reviewsDiscount, false) + 'руб. бонусов');
        }

        if (summDiscount)
        {
            this.discount.summ = 100/summDiscount;
            text.push('скидка ' + summDiscount + '%');
        }
        
        return text.join(' и ');
    },

    goodsText: function(qnt) {
        var goods = 'предметов';

        if (1 == qnt || (11 != qnt && qnt % 10 == 1)) goods = 'предмет';
        else if (0 <= $.inArray(qnt % 10, [2, 3, 4])) goods = 'предмета';
        
        var text = qnt + ' ' + goods + ' на сумму: ';
        
        return text;
    },
    
    updateWODelivery: function(qnt, price) {
        var text = this.goodsText(qnt);
        
        this.woDelivery.html(text);
        this.woDeliveryPrice.html(moneyFormat(price));        
    },
    
    initCartItems: function() {
        this.cartItems.find('.cart-goods-item').each(function() {
            new CartItem($(this));
        });
    },
    
    addGood: function(good, qnt) {
        var qnt = 'undefined' == typeof(qnt)
            ? 1
            : qnt;
            
        var options = {
            'action': 'addGood',
            'good':   good,
            'qnt':    qnt
        };
        
        this.sync(options);
    },
    
    removeGood: function(good, qnt) {
        var qnt = 'undefined' == typeof(qnt)
            ? 1
            : qnt;
            
        var options = {
            'action': 'removeGood',
            'good':   good,
            'qnt':    qnt
        };
        
        this.sync(options);        
    },
    
    updateSize: function(size) {
        this.size = size/1;
        this.sizeEl.html(size);
    },
    
    sync: function(options) {
        var params = {};
        var func = false;
        
        if ('addGood' == options.action || 'removeGood' == options.action)
        {
            params.good   = options.good;
            params.qnt    = options.qnt;
            params.action = options.action;
            
            func = function(json) {
                if ('undefined' != typeof(json.total))
                {
                    Cart.icon.hide().fadeIn(200);
                    Cart.updateSize(json.total);
                }
            }
        }
        
        $.post(this.update, params, function(data) {
            var json = {};
            
            try 
            {
                json = $.parseJSON(data);
            }
            catch(e) 
            {
                console.error(data);
            }
            
            if ('function' == typeof(func)) func(json);
        })
    }
};

var Good = {
    element: false,
    
    init: function(el) {
        this.element = $('#good');
        this.gallery();
        this.buy();
    },
    
    buy: function() {
        this.element.find('.good-info-buy .good-info-size').each(function() {
            var size = $(this);
            var id   = size.attr('data-id');
            
            size.click(function() {
                Cart.addGood(id);
            });
        });
    },
    
    gallery: function() {
        this.element.find(".good-gallery .fancybox").fancybox({
    	    'type': 'image',
    	    'padding': 0,
    	    'margin': 50,
    	    'nextEffect': 'fade',
    	    'prevEffect': 'fade',
    	});
    }
};


var Tabs = function(el) {
    var type     = el.attr('data-type');
    var items    = el.find('[data-type=items]');
    var contents = el.find('[data-type=contents]');    
    var tabs     = this;    
    var contentsArray = [];
    
    var input    = el.find('[data-type=tabsType]');
    
    this.init = function() {

        items.find('.tabs-item').each(function(i) {
            var tab = $(this);
            var id  = tab.attr('data-id');

            if ('class' == type)
            {
                if ('undefined' == typeof(id))
                {
                    id = false;
                }
                
                contentsArray.push({
                    'id':    id,
                    'goods': el.find('[data-category=' + id + ']')
                });

                tab.click(function() {
                    if (!tab.hasClass('selected')) 
                    {
                        for (var i in contentsArray)
                        {
                            var category = contentsArray[i];
                            if (category.id == id || !id)
                            {
                                category.goods.show();
                            }
                            else
                                category.goods.hide();
                        }

                        items.find('.selected').removeClass('selected');
                        tab.addClass('selected');                        
                    }
                });
            }
            else
            {
                tab.click(function() {
                    input.val(id);
                    
                    if (!tab.hasClass('selected')) 
                    {
                        items.find('.selected').removeClass('selected');
                        tab.addClass('selected');
                        tabs.select(i);
                    }
                });
            }
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

var Event = function(el) {
    var confirm = el.find('[data-type=confirm]');
    var cancel  = el.find('[data-type=cancel]');
    var total   = el.find('[data-type=total]');
    var url     = el.attr('data-url');
    var id      = el.attr('data-id');

    confirm.click(function() {
        if (!el.hasClass('membered')) send(false);
    });

    cancel.click(function() {
        if (el.hasClass('membered')) send(true);
    });

    send = function(dir) {
        var params = {};

        params.action = dir
            ? 'cancel' 
            : 'confirm';

        $.post(url, params, function(text) {
            var json = false;

            try {
                json = $.parseJSON(text);
            } catch(e) {
                alert(text)
            };

            if ('undefined' != typeof(json.total)) {
                total.html(json.total).hide().fadeIn(200);
            }
            el[('confirm' == params.action ? 'add' : 'remove') + 'Class']('membered');
        });
    };
}

var Rating = function(el) {
    var input = el.find('[data-type=input]');

    el.find('.rating-active-item').click(function() {
        if (!el.hasClass('static'))
        {
            el.find('.rating-active-item.selected').removeClass('selected');
            $(this).addClass('selected');
            input.val($(this).attr('data-value'));
        }
    });
}

var Slidebanner = function(el) {
    var slides  = el.find('[data-type=slide]');
    var current = 0;
    var total   = slides.size();
    var width   = el.width();

    var controls = el.find('[data-type=control]');
    var interval;

    this.index = function(index) {
        if (index > total-1) index = 0;
        else if (index < 0)     index = total-1;

        return index;
    };

    this.slide = function(dir) {
        if (total < 2) return;

        next = this.index(current + (dir ? 1 : -1));

        var currentStart   = {left: 0};
        var nextAnimate    = currentStart;

        var currentAnimate = dir 
            ? {left: -1 * width}
            : {left: width};

        var nextStart      = dir
            ? {left: width}
            : {left: -1 * width};

        $(slides[next])
            .addClass('active')
            .css(nextStart)
            .animate(nextAnimate, 200);

        $(slides[current])
            .addClass('active')
            .css(currentStart)
            .animate(currentAnimate, 200, function() {
                $(this).removeClass('active');
            });

        current = next; 
    };

    var slider = this;

    var intervalFunc = function() {
        interval = setInterval(function() {
            slider.slide(true);
        }, 5000 + (1000 * Math.random()))
    };

    controls.click(function() {
        clearInterval(interval);
        slider.slide('right' == $(this).attr('data-dir'));
        intervalFunc();
    });

    intervalFunc();
};


var Calendar = function(el) {
    var slides  = el.find('[data-type=months]');
    var size    = slides.find('[data-type=month]').size();
    var current = 0;

    var left_control  = el.find('.left[data-type=control]');
    var right_control = el.find('.right[data-type=control]');
    var controls      = el.find('[data-type=control]');
    var interval;

    var checkControls = function () {
        if (current <= 0)
        {
            left_control.addClass('disabled');
        }
        else {
            left_control.removeClass('disabled');
        }

        if (current >= size-1)
        {
            right_control.addClass('disabled');
        }
        else {
            right_control.removeClass('disabled');
        }
    };

    this.slide = function(dir) {
        current += (dir ? 1 : -1);

        if (current >= size-1) current = size-1;
        else if (current <= 0) current = 0;

        checkControls();

        slides.animate({'margin-left': -current * el.width()});
    };

    var slider = this;

    controls.click(function() {
        slider.slide('right' == $(this).attr('data-dir'));
    });

    checkControls();
};

var Callback = function() {
    if ( arguments.callee._instance )
        return arguments.callee._instance;
    
    arguments.callee._instance = this;

    var handle = $('.header-callback');
    var item   = $('.header-callback-form');

    item.find('[data-type=form]').submit(function(e) {
        $.post($(this).attr('action'), $(this).serialize() + '&ajax=1', function(result) {
            var json  = false;
            var error = false;

            try {
                json = $.parseJSON(result);
            } catch(error) {
                PopupMessages.add('Произошла ошибка: ' + error);
            }

            if (0 == json.errors && !error)
            {
                PopupMessages.add('Спасибо за обращение! Мы позвоним Вам в указанное время.');
            }
            else {
                if (error)
                {
                    PopupMessages.add('Произошла ошибка: ' + error);    
                }

                if (json.error)
                {
                    PopupMessages.add('Произошла ошибка: ' + json.error);    
                }
            }
        });
        close();
        e.preventDefault();
    });

    handle.click(function() {
        init();    
    });

    var init = function() {
        handle.addClass('active');
        item.addClass('active');

        item.find('input[type=text]').first().focus().select();

        $('body').mousedown(function(e) {
            var target = $(e.target);

            if (target.closest('.header-callback-form').size() == 0
                && target.closest('.header-callback').size() == 0
                && !target.hasClass('header-callback')
                && !target.hasClass('header-callback-form'))
            {
                close();
            }
        });

        $(document).keyup(function(e) {
            if (27 == e.keyCode)
            {
                close();
            }
        });
    }

    var close = function() {

        $('body').unbind('mousedown');
        $(document).unbind('keyup');

        item.find('input[type=text]').first().val('');
        handle.removeClass('active');
        item.removeClass('active');
    }
};

var Fakeradio = function(el) {
    if ( arguments.callee._instance )
        return arguments.callee._instance;

    el.disableSelection();

    var value = false;
    var items = el.find('[data-type=item]');
    var input = el.find('[data-type=input]');

    var setValue = function(value) {
        if (value)
        {
            input.val(value);    
        }
        else
        {
            input.removeAttr('value');
        }
    }

    items.click(function() {
        var selected = $(this).hasClass('selected');
        items.removeClass('selected');

        if (selected) value = false;
        else
        {
            $(this).addClass('selected')
            value = $(this).attr('data-value');
        }

        setValue(value);
    });

    if (el.attr('data-value'))
    {
        el.find('[data-type=item][data-value=' + el.attr('data-value') + ']').click();
    }
};

var Multiple = function(el) {
    var self = this;

    checkAll = function() {
        el[(el.find('[data-type=item]').size() > 1 ? 'add': 'remove') + 'Class']('removable');
    };

    this.itemize = function(item) {
        item.find('[data-type=handle]').change(function(e) {
            self.itemize(item.clone()).insertAfter(item);

            item.find('[data-type=handle]').unbind(e);
            item.find('[data-type=handle]').removeAttr('data-sample');
            item.find('[value=0]').remove();
            item.removeClass('sample');

            checkAll();
        });

        item.find('[data-type=remove]').click(function() {
            item.fadeOut(200, function() {
                item.remove();
                checkAll();
            });
        });

        item.find(':input').each(function(i) {
            $(this).attr('data-name', el.attr('data-name') + '[]');
        });

        return item;
    };

    el.find('[data-type=item]').each(function() {
        self.itemize($(this));
    });

    checkAll();
};

var Filter = function(form) {
    form.submit(function(e) {
        form.find(':input').each(function() {
            if ($(this).attr('data-name') && !$(this).attr('data-sample'))
            {
                var name = $(this).attr('data-name');
                var appendix = '';

                if (/\[\]/img.test(name))
                {
                    name = name.replace(/\[\]/img, '');
                    appendix = '[]';
                }

                $(this).attr('name', 'filter[' + name + ']' + appendix);
            }
        });
    });
}

var Search = function(el) {
    if ( arguments.callee._instance )
        return arguments.callee._instance;

    var popup = el.find('[data-type=search-from]');
    var btn = el.find('[data-type=search-btn]');

    var close = function() {
        popup.hide();
        $('body').unbind('mousedown');
        $(document).unbind('keyup');
    };

    var open = function() {
        popup.show();

        $('body').mousedown(function(e) {
            var target = $(e.target);

            if (target.closest('.menu-item.search').size() == 0)
            {
                close();
            }
        });

        $(document).keyup(function(e) {
            if (27 == e.keyCode)
            {
                close();
            }
        });
    }

    btn.click(function() {
        if (popup.is(':visible') > 0)
        {
            close();
        }
        else {
            open();
        }
    });
};

$(function() {
    new Callback();

    $('.menu-item.search').each(function() {
        new Search($(this));
    });

    $('[data-type=filtered]').each(function() {
        new Filtered($(this));
    });

    $('[data-type=slidebanner]').each(function() {
        new Slidebanner($(this));
    });

    $('[data-type=filter]').each(function() {
        new Filter($(this));
    });

    $('[data-type=multiple]').each(function() {
        new Multiple($(this));
    });

    $('[data-type=fakeradio]').each(function() {
        new Fakeradio($(this));
    });

    $('.calendar').each(function() {
        new Calendar($(this));
    });    

    $('.rating-active').each(function() {
        new Rating($(this));
    });

    $('#event').each(function() {
        new Event($(this));
    });
    
    $('.jcalendar').each(function() {
        $(this).datepicker({
            'dateFormat': 'dd.mm.yy'
        });
    });

    $('.tabs').each(function() {
        new Tabs($(this));
    });
    
    PopupMessages.init();
    BigBanners();
    Cart.init();
    Good.init();
    Categories.init();
});

var extractNumber = function(string) {
    var string = string.replace(',', '.').replace(/[^0-9\.]/img, '');
    return string*100/100;
}

var moneyFormat = function(number, addition) {
//    number
    var addition = 'undefined' != typeof(addition) && !addition ? 0 : 1;

    function number_format (number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };            
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

    return number_format(number, 0, '', ' ') + (addition ? '.-' : '');
}
