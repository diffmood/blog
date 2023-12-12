jQuery( function( $ ) {

	// Fix RTL numbers.
function realtime(r) {
    r.bind("DOMSubtreeModified", function(e) {
        r.unbind("DOMSubtreeModified"), jQuery(e.target).not('.xtra-done').addClass('xtra-done').codevzNumbers({
            forbiddenTag: forbiddenTag,
            numberType: numberType,
            forbiddenClass: forbiddenClass
        }, !1), realtime(r)
    })
}

function hasCommonElements(e, r) {
    return res = !1, e == [] || r == [] || e.forEach(function(e) {
        0 <= r.indexOf(e) && (res = !0)
    }), res
}

function getAllClasses(e, r) {
    var a = [];
    return jQuery(e).parentsUntil(r).andSelf().each(function() {
        this.className && a.push.apply(a, this.className.split(" "))
    }), a
}

function traverseAr(e) {
    return e.replace(/0/g, "\u0660").replace(/1/g, "\u0661").replace(/2/g, "\u0662").replace(/3/g, "\u0663").replace(/4/g, "\u0664").replace(/5/g, "\u0665").replace(/6/g, "\u0666").replace(/7/g, "\u0667").replace(/8/g, "\u0668").replace(/9/g, "\u0669").replace(/\u06F0/g, "\u0660").replace(/\u06F1/g, "\u0661").replace(/\u06F2/g, "\u0662").replace(/\u06F3/g, "\u0663").replace(/\u06F4/g, "\u0664").replace(/\u06F5/g, "\u0665").replace(/\u06F6/g, "\u0666").replace(/\u06F7/g, "\u0667").replace(/\u06F8/g, "\u0668").replace(/\u06F9/g, "\u0669")
}

function traverse(e) {
    return e.replace(/0/g, "\u06F0").replace(/1/g, "\u06F1").replace(/2/g, "\u06F2").replace(/3/g, "\u06F3").replace(/4/g, "\u06F4").replace(/5/g, "\u06F5").replace(/6/g, "\u06F6").replace(/7/g, "\u06F7").replace(/8/g, "\u06F8").replace(/9/g, "\u06F9").replace(/\u0660/g, "\u06F0").replace(/\u0661/g, "\u06F1").replace(/\u0662/g, "\u06F2").replace(/\u0663/g, "\u06F3").replace(/\u0664/g, "\u06F4").replace(/\u0665/g, "\u06F5").replace(/\u0666/g, "\u06F6").replace(/\u0667/g, "\u06F7").replace(/\u0668/g, "\u06F8").replace(/\u0669/g, "\u06F9")
}

function traverseEn(e) {
    return e.replace(/\u06F0/g, "0").replace(/\u06F1/g, "1").replace(/\u06F2/g, "2").replace(/\u06F3/g, "3").replace(/\u06F4/g, "4").replace(/\u06F5/g, "5").replace(/\u06F6/g, "6").replace(/\u06F7/g, "7").replace(/\u06F8/g, "8").replace(/\u06F9/g, "9").replace(/\u0660/g, "0").replace(/\u0661/g, "1").replace(/\u0662/g, "2").replace(/\u0663/g, "3").replace(/\u0664/g, "4").replace(/\u0665/g, "5").replace(/\u0666/g, "6").replace(/\u0667/g, "7").replace(/\u0668/g, "8").replace(/\u0669/g, "9")
}
jQuery.fn.codevzNumbers = function(e, r) {
    forbiddenTag = (e = e || {
        forbiddenTag: ["SCRIPT", "STYLE"],
        numberType: "persian",
        forbiddenClass: ["EnglishNum"]
    }).forbiddenTag || ["SCRIPT", "STYLE"], numberType = e.numberType || "persian", forbiddenClass = e.forbiddenClass || ["EnglishNum"];
    for (var a = 0; a < this.length; a++)
        for (var l = this[a], c = 0; c < l.childNodes.length; c++)
            if (className = "string" == typeof l.className ? getAllClasses(l, "body") : [], !(0 <= forbiddenTag.indexOf(l.nodeName) || hasCommonElements(forbiddenClass, className))) {
                var n = l.childNodes[c];
                if (3 == n.nodeType) {
                    var p = n.nodeValue;
                    switch (numberType.toLowerCase()) {
                        case "persian":
                            n.nodeValue = traverse(p);
                            break;
                        case "arabic":
                            n.nodeValue = traverseAr(p);
                            break;
                        default:
                            n.nodeValue = traverseEn(p)
                    }
                } else if (1 == n.nodeType) {
                    if ("OL" == n.nodeName) switch (numberType.toLowerCase()) {
                        case "persian":
                            jQuery(n).css("list-style-type", "persian");
                            break;
                        case "arabic":
                            jQuery(n).css("list-style-type", "arabic-indic");
                            break;
                        default:
                            jQuery(n).css("list-style-type", "decimal")
                    }
                    jQuery(n).codevzNumbers({
                        forbiddenTag: forbiddenTag,
                        numberType: numberType,
                        forbiddenClass: forbiddenClass
                    }, !1)
                }
            } null == r && realtime(jQuery(this))
};

	var lang 		= $( 'html' ).attr( 'lang' ),
		exclude 	= '.cz_ignore_number',
		selectors 	= 'time, .it_text, .amount, .cz_cm_ttl, .xtra-mobile-menu-text, .page-numbers, .price, .cz_grid_filters, .cz_post_date, .cz_data_date, .cz_small_post_date, .cz_data_price, .cz_wishlist_count, .cz_cart_count, .cart_list, .amount, .wc-layered-nav-rating, .woocommerce-result-count, .codevz-products-per-page option';

	if ( lang === 'ar' || lang === 'ary' ) {

		$( selectors ).not( exclude ).codevzNumbers({
			numberType: 'arabic'
		});

	} else if ( lang === 'fa-IR' ) {

		$( selectors ).not( exclude ).codevzNumbers({
			numberType: 'persian'
		});

	}

});