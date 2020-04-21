function convertToCurrency(amount, shorten, prefix, separator, decimals) {
    prefix    = (typeof(prefix) !== 'undefined') ? prefix : '$ ';
    separator = (typeof(separator) !== 'undefined') ? separator : ',';
    decimals  = parseInt(decimals) || 2;

    if (amount == null) {
        return null;
    }

    var output;

    if (shorten === true) {
        // replace zeros with a Letter
        if (amount > 1000000000) {
            output = (amount / 1000000000).toFixed(decimals) + 'B';
        } else if (amount > 1000000) {
            output = (amount / 1000000).toFixed(decimals) + 'M';
        } else if (amount > 1000) {
            output = (amount / 1000).toFixed(decimals) + 'k';
        } else {
            output = amount;
        }
    } else {
        // 2 decimal places
        amount = parseFloat(amount).toFixed(decimals);
        // separate thousands with commas
        output = amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, separator);
    }

    return prefix + output;
}




function stringPad(str, max) {
    str = str.toString();
    return str.length < max ? stringPad("0" + str, max) : str;
}




function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}




function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };

    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}




function scrollToElem(elem, elemOffset) {
    var offset = elemOffset || 20;

    var bodyRect = $("body")[0].getBoundingClientRect();
    var elemRect = $(elem)[0].getBoundingClientRect();
    var viewOffset = elemRect.top - bodyRect.top - offset;

    $("html, body").animate({
        scrollTop: viewOffset,
    }, 'slow');
}




// https://github.com/bgrins/bindWithDelay
$.fn.bindWithDelay = function(type, data, fn, timeout, throttle) {
    if ($.isFunction(data)) {
        throttle = timeout;
        timeout  = fn;
        fn       = data;
        data     = undefined;
    }

    // Allow delayed function to be removed with fn in unbind function
    fn.guid = fn.guid || ($.guid && $.guid++);

    // Bind each separately so that each element has its own delay
    return this.each(function() {

        var wait = null;

        function cb() {
            var e = $.extend(true, { }, arguments[0]);
            var ctx = this;
            var throttler = function() {
                wait = null;
                fn.apply(ctx, [e]);
            };

            if (!throttle) { clearTimeout(wait); wait = null; }
            if (!wait) { wait = setTimeout(throttler, timeout); }
        }

        cb.guid = fn.guid;

        $(this).bind(type, data, cb);
    });
};




function decimalPoint(p, intfloat) {
    var temp, decimalPlaces;

    // strip away thousand's separator
    p.value = p.value.replace(/,/g, "");

    if (intfloat <= 0) {
    	temp = parseInt(p.value, 10);

    	if (isNaN(temp)) {
            temp = "";
        }

    	temp = temp.toString();
    	var t =- intfloat - temp.length - 1;

    	for (var i = 0; i <= t; i++) {
    		temp = "0" + temp;
    	}

    	p.value = temp;
    } else {
    	temp = parseFloat(p.value);

        if (isNaN(temp)) {
            temp = "";
    	} else {
    		decimalPlaces = Math.pow(10,intfloat);
    		temp = Math.round(temp*decimalPlaces) / decimalPlaces;
    		temp = temp.toString();


    		if (temp.indexOf(".") > 0) {
    			temp += '00000000';
    		} else {
    			temp += '.00000000';
    		}

    	}

        if (temp.indexOf(".") > 0) {
            p.value = temp.substring(0,temp.indexOf(".") + intfloat + 1);
        } else {
            p.value = temp;
        }
    }
}
