
(function(b) {
    b.fn.fullpage = function(c) {
        function m(a) {
            if (c.autoScrolling) {
                a = window.event || a;
                a = Math.max(-1, Math.min(1, a.wheelDelta || -a.detail));
                var e;
                e = b(".section.active");
                if (!k)
                    if (e = e.find(".slides").length ? e.find(".slide.active").find(".scrollable") : e.find(".scrollable"), 0 > a)
                        if (0 < e.length)
                            if (v("bottom", e))
                                b.fn.fullpage.moveSectionDown();
                            else
                                return !0;
                        else
                            b.fn.fullpage.moveSectionDown();
                    else if (0 < e.length)
                        if (v("top", e))
                            b.fn.fullpage.moveSectionUp();
                        else
                            return !0;
                    else
                        b.fn.fullpage.moveSectionUp();
                return !1
            }
        }
        function F() {
            document.addEventListener ? (document.addEventListener("mousewheel", m, !1), document.addEventListener("DOMMouseScroll", m, !1)) : document.attachEvent("onmousewheel", m)
        }
        function n(a, e) {
            var d = {}, f, h = a.position(), h = null !== h ? h.top : null, H = G(a), l = a.data("anchor"), g = a.index(".section"), p = a.find(".slide.active");
            if (p.length) {
                f = p.data("anchor");
                var q = p.index()
            }
            p = b(".section.active").index(".section") + 1;
            a.addClass("active").siblings().removeClass("active");
            k = !0;
            "undefined" !== typeof l ? I(q, f, 
            l) : location.hash = "";
            c.autoScrolling ? (d.top = -h, f = "#superContainer") : (d.scrollTop = h, f = "html, body");
            c.css3 && c.autoScrolling ? (b.isFunction(c.onLeave) && c.onLeave.call(this, p, H), z("translate3d(0px, -" + h + "px, 0px)", !0), setTimeout(function() {
                b.isFunction(c.afterLoad) && c.afterLoad.call(this, l, g + 1);
                setTimeout(function() {
                    k = !1;
                    b.isFunction(e) && e.call(this)
                }, J)
            }, c.scrollingSpeed)) : (b.isFunction(c.onLeave) && c.onLeave.call(this, p, H), b(f).animate(d, c.scrollingSpeed, c.easing, function() {
                b.isFunction(c.afterLoad) && 
                c.afterLoad.call(this, l, g + 1);
                setTimeout(function() {
                    k = !1;
                    b.isFunction(e) && e.call(this)
                }, J)
            }));
            r = l;
            c.autoScrolling && (K(l), L(l, g))
        }
        function u(a, e) {
            var d = e.position(), f = a.find(".slidesContainer").parent(), h = e.index(), g = a.closest(".section"), l = g.index(".section"), k = g.data("anchor"), p = g.find(".fullPage-slidesNav"), q = e.data("anchor");
            if (c.onSlideLeave) {
                var m = g.find(".slide.active").index(), n;
                n = m > h ? "left" : "right";
                b.isFunction(c.onSlideLeave) && c.onSlideLeave.call(this, k, l + 1, m, n)
            }
            e.addClass("active").siblings().removeClass("active");
            "undefined" === typeof q && (q = h);
            g.hasClass("active") && (c.loopHorizontal || (g.find(".controlArrow.prev").toggle(0 != h), g.find(".controlArrow.next").toggle(!e.is(":last-child"))), I(h, q, k));
            c.css3 ? (d = "translate3d(-" + d.left + "px, 0px, 0px)", a.find(".slidesContainer").addClass("easing").css({"-webkit-transform": d,"-moz-transform": d,"-ms-transform": d,transform: d}), setTimeout(function() {
                b.isFunction(c.afterSlideLoad) && c.afterSlideLoad.call(this, k, l + 1, q, h);
                s = !1
            }, c.scrollingSpeed)) : f.animate({scrollLeft: d.left}, 
            c.scrollingSpeed, function() {
                b.isFunction(c.afterSlideLoad) && c.afterSlideLoad.call(this, k, l + 1, q, h);
                s = !1
            });
            p.find(".active").removeClass("active");
            p.find("li").eq(h).find("a").addClass("active")
        }
        function M() {
            var a = b(window).width();
            g = b(window).height();
            c.resize && S(g, a);
            b(".section").each(function() {
                parseInt(b(this).css("padding-bottom"));
                parseInt(b(this).css("padding-top"));
                if (c.scrollOverflow) {
                    var a = b(this).find(".slide");
                    a.length ? a.each(function() {
                        w(b(this))
                    }) : w(b(this))
                }
                c.verticalCentered && b(this).find(".tableCell").css("height", 
                g + "px");
                b(this).css("height", g + "px");
                a = b(this).find(".slides");
                a.length && u(a, a.find(".slide.active"))
            });
            b(".section.active").position();
            a = b(".section.active");
            a.index(".section") && n(a)
        }
        function S(a, e) {
            var c = 825, f = a;
            825 > a || 900 > e ? (900 > e && (f = e, c = 900), c = (100 * f / c).toFixed(2), b("body").css("font-size", c + "%")) : b("body").css("font-size", "100%")
        }
        function L(a, e) {
            c.navigation && (b("#fullPage-nav").find(".active").removeClass("active"), a ? b("#fullPage-nav").find('a[href="#' + a + '"]').addClass("active") : b("#fullPage-nav").find("li").eq(e).find("a").addClass("active"))
        }
        function K(a) {
            c.menu && (b(c.menu).find(".active").removeClass("active"), b(c.menu).find('[data-menuanchor="' + a + '"]').addClass("active"))
        }
        function v(a, b) {
            if ("top" === a)
                return !b.scrollTop();
            if ("bottom" === a)
                return b.scrollTop() + b.innerHeight() >= b[0].scrollHeight
        }
        function G(a) {
            var c = b(".section.active").index(".section");
            a = a.index(".section");
            return c > a ? "up" : "down"
        }
        function w(a) {
            a.css("overflow", "hidden");
            var b = a.closest(".section"), d = a.find(".scrollable");
            (d.length ? a.find(".scrollable").get(0).scrollHeight - 
            parseInt(b.css("padding-bottom")) - parseInt(b.css("padding-top")) : a.get(0).scrollHeight - parseInt(b.css("padding-bottom")) - parseInt(b.css("padding-top"))) > g ? (b = g - parseInt(b.css("padding-bottom")) - parseInt(b.css("padding-top")), d.length ? d.css("height", b + "px").parent().css("height", b + "px") : (c.verticalCentered ? a.find(".tableCell").wrapInner('<div class="scrollable" />') : a.wrapInner('<div class="scrollable" />'), a.find(".scrollable").slimScroll({height: b + "px",size: "10px",alwaysVisible: !0}))) : (a.find(".scrollable").children().first().unwrap().unwrap(), 
            a.find(".slimScrollBar").remove(), a.find(".slimScrollRail").remove());
            a.css("overflow", "")
        }
        function N(a) {
            a.addClass("table").wrapInner('<div class="tableCell" style="height:' + g + 'px;" />')
        }
        function z(a, c) {
            b("#superContainer").toggleClass("easing", c);
            b("#superContainer").css({"-webkit-transform": a,"-moz-transform": a,"-ms-transform": a,transform: a})
        }
        function A(a, c) {
            var d = isNaN(a) ? b('[data-anchor="' + a + '"]') : b(".section").eq(a - 1);
            a === r || d.hasClass("active") ? O(d, c) : n(d, function() {
                O(d, c)
            })
        }
        function O(a, b) {
            if ("undefined" != 
            typeof b) {
                var c = a.find(".slides"), f = c.find('[data-anchor="' + b + '"]');
                f.length || (f = c.find(".slide").eq(b));
                f.length && u(c, f)
            }
        }
        function T(a, b) {
            a.append('<div class="fullPage-slidesNav"><ul></ul></div>');
            var d = a.find(".fullPage-slidesNav");
            d.addClass(c.slidesNavPosition);
            for (var f = 0; f < b; f++)
                d.find("ul").append('<li><a href="#"><span></span></a></li>');
            d.css("margin-left", "-" + d.width() / 2 + "px");
            d.find("li").first().find("a").addClass("active")
        }
        function I(a, b, c) {
            var f = "";
            a ? ("undefined" !== typeof c && (f = c), 
            "undefined" === typeof b && (b = a), P = b, location.hash = f + "/" + b) : location.hash = c
        }
        function U() {
            var a = document.createElement("p"), b, c = {webkitTransform: "-webkit-transform",OTransform: "-o-transform",msTransform: "-ms-transform",MozTransform: "-moz-transform",transform: "transform"};
            document.body.insertBefore(a, null);
            for (var f in c)
                void 0 !== a.style[f] && (a.style[f] = "translate3d(1px,1px,1px)", b = window.getComputedStyle(a).getPropertyValue(c[f]));
            document.body.removeChild(a);
            return void 0 !== b && 0 < b.length && "none" !== b
        }
        c = b.extend({verticalCentered: !0,resize: !0,slidesColor: [],anchors: [],scrollingSpeed: 700,easing: "easeInQuart",menu: !1,navigation: !1,navigationPosition: "right",navigationColor: "#000",navigationTooltips: [],slidesNavigation: !1,slidesNavPosition: "bottom",controlArrowColor: "#fff",loopBottom: !1,loopTop: !1,loopHorizontal: !0,autoScrolling: !0,scrollOverflow: !1,css3: !1,paddingTop: 0,paddingBottom: 0,fixedElements: null,normalScrollElements: null,afterLoad: null,onLeave: null,afterRender: null,afterSlideLoad: null,onSlideLeave: null}, 
        c);
        var J = 700;
        b.fn.fullpage.setAutoScrolling = function(a) {
            c.autoScrolling = a;
            a = b(".section.active");
            c.autoScrolling ? (b("html, body").css({overflow: "hidden",height: "100%"}), a.length && (c.css3 ? (a = "translate3d(0px, -" + a.position().top + "px, 0px)", z(a, !1)) : b("#superContainer").css("top", "-" + a.position().top + "px"))) : (b("html, body").css({overflow: "auto",height: "auto"}), c.css3 ? z("translate3d(0px, 0px, 0px)", !1) : b("#superContainer").css("top", "0px"), b("html, body").scrollTop(a.position().top))
        };
        var s = !1, B = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|BlackBerry|Windows Phone)/), 
        g = b(window).height(), k = !1, r, P;
        F();
        c.css3 && (c.css3 = U());
        b("body").wrapInner('<div id="superContainer" />');
        if (c.navigation) {
            b("body").append('<div id="fullPage-nav"><ul></ul></div>');
            var t = b("#fullPage-nav");
            t.css("color", c.navigationColor);
            t.addClass(c.navigationPosition)
        }
        b(".section").each(function(a) {
            var e = b(this).find(".slide"), d = e.length;
            a || b(this).addClass("active");
            b(this).css("height", g + "px");
            (c.paddingTop || c.paddingBottom) && b(this).css("padding", c.paddingTop + " 0 " + c.paddingBottom + " 0");
            "undefined" !== 
            typeof c.slidesColor[a] && b(this).css("background-color", c.slidesColor[a]);
            "undefined" !== typeof c.anchors[a] && b(this).attr("data-anchor", c.anchors[a]);
            if (c.navigation) {
                var f = "";
                c.anchors.length && (f = c.anchors[a]);
                a = c.navigationTooltips[a];
                "undefined" === typeof a && (a = "");
                t.find("ul").append('<li data-tooltip="' + a + '"><a href="#' + f + '"><span></span></a></li>')
            }
            if (0 < d) {
                var f = 100 * d, h = 100 / d;
                e.wrapAll('<div class="slidesContainer" />');
                e.parent().wrap('<div class="slides" />');
                b(this).find(".slidesContainer").css("width", 
                f + "%");
                b(this).find(".slides").after('<div class="controlArrow prev"></div><div class="controlArrow next"></div>');
                b(this).find(".controlArrow.next").css("border-color", "transparent transparent transparent " + c.controlArrowColor);
                b(this).find(".controlArrow.prev").css("border-color", "transparent " + c.controlArrowColor + " transparent transparent");
                c.loopHorizontal || b(this).find(".controlArrow.prev").hide();
                c.slidesNavigation && T(b(this), d);
                e.each(function(a) {
                    a || b(this).addClass("active");
                    b(this).css("width", 
                    h + "%");
                    c.verticalCentered && N(b(this))
                })
            } else
                c.verticalCentered && N(b(this))
        }).promise().done(function() {
            b.fn.fullpage.setAutoScrolling(c.autoScrolling);
            b.isFunction(c.afterRender) && c.afterRender.call(this);
            c.fixedElements && c.css3 && b(c.fixedElements).appendTo("body");
            c.navigation && (t.css("margin-top", "-" + t.height() / 2 + "px"), t.find("li").first().find("a").addClass("active"));
            c.menu && c.css3 && b(c.menu).appendTo("body");
            if (c.scrollOverflow)
                b(window).on("load", function() {
                    b(".section").each(function() {
                        var a = 
                        b(this).find(".slide");
                        a.length ? a.each(function() {
                            w(b(this))
                        }) : w(b(this))
                    })
                });
            b(window).on("load", function() {
                var a = window.location.hash.replace("#", "").split("/"), b = a[0], a = a[1];
                b && A(b, a)
            })
        });
        var Q, C = !1;
        b(window).scroll(function(a) {
            if (!c.autoScrolling) {
                var e = b(window).scrollTop();
                a = b(".section").map(function() {
                    if (b(this).offset().top < e + 100)
                        return b(this)
                });
                a = a[a.length - 1];
                if (!a.hasClass("active")) {
                    C = !0;
                    var d = G(a);
                    b(".section.active").removeClass("active");
                    a.addClass("active");
                    var f = a.data("anchor");
                    b.isFunction(c.onLeave) && c.onLeave.call(this, a.index(".section"), d);
                    b.isFunction(c.afterLoad) && c.afterLoad.call(this, f, a.index(".section") + 1);
                    K(f);
                    L(f, 0);
                    c.anchors.length && !k && (r = f, location.hash = f);
                    clearTimeout(Q);
                    Q = setTimeout(function() {
                        C = !1
                    }, 100)
                }
            }
        });
        var D = 0, x = 0, E = 0, y = 0;
        b(document).on("touchmove", function(a) {
            if (c.autoScrolling && B) {
                a.preventDefault();
                a = a.originalEvent;
                var e = b(".section.active");
                if (!k && !s)
                    if (E = a.touches[0].pageY, y = a.touches[0].pageX, e.find(".slides").length && Math.abs(x - y) > Math.abs(D - 
                    E))
                        x > y ? e.find(".controlArrow.next").trigger("click") : x < y && e.find(".controlArrow.prev").trigger("click");
                    else if (a = e.find(".slides").length ? e.find(".slide.active").find(".scrollable") : e.find(".scrollable"), D > E)
                        if (0 < a.length)
                            if (v("bottom", a))
                                b.fn.fullpage.moveSectionDown();
                            else
                                return !0;
                        else
                            b.fn.fullpage.moveSectionDown();
                    else if (0 < a.length)
                        if (v("top", a))
                            b.fn.fullpage.moveSectionUp();
                        else
                            return !0;
                    else
                        b.fn.fullpage.moveSectionUp()
            }
        });
        b(document).on("touchstart", function(a) {
            c.autoScrolling && B && (a = 
            a.originalEvent, D = a.touches[0].pageY, x = a.touches[0].pageX)
        });
        b.fn.fullpage.moveSectionUp = function() {
            var a = b(".section.active").prev(".section");
            c.loopTop && !a.length && (a = b(".section").last());
            (0 < a.length || !a.length && c.loopTop) && n(a)
        };
        b.fn.fullpage.moveSectionDown = function() {
            var a = b(".section.active").next(".section");
            c.loopBottom && !a.length && (a = b(".section").first());
            (0 < a.length || !a.length && c.loopBottom) && n(a)
        };
        b.fn.fullpage.moveTo = function(a, c) {
            var d = "", d = isNaN(a) ? b('[data-anchor="' + a + '"]') : b(".section").eq(a - 
            1);
            "undefined" !== c ? A(a, c) : 0 < d.length && n(d)
        };
        b(window).on("hashchange", function() {
            if (!C) {
                var a = window.location.hash.replace("#", "").split("/"), b = a[0], a = a[1], c = "undefined" === typeof r, f = "undefined" === typeof r && "undefined" === typeof a;
                (b && b !== r && !c || f || "undefined" !== typeof a && !s && P != a) && A(b, a)
            }
        });
        b(document).keydown(function(a) {
            if (!k)
                switch (a.which) {
                    case 38:
                    case 33:
                        b.fn.fullpage.moveSectionUp();
                        break;
                    case 40:
                    case 34:
                        b.fn.fullpage.moveSectionDown();
                        break;
                    case 37:
                        b(".section.active").find(".controlArrow.prev").trigger("click");
                        break;
                    case 39:
                        b(".section.active").find(".controlArrow.next").trigger("click")
                }
        });
        b(document).on("click", "#fullPage-nav a", function(a) {
            a.preventDefault();
            a = b(this).parent().index();
            n(b(".section").eq(a))
        });
        b(document).on({mouseenter: function() {
                var a = b(this).data("tooltip");
                b('<div class="fullPage-tooltip ' + c.navigationPosition + '">' + a + "</div>").hide().appendTo(b(this)).fadeIn(200)
            },mouseleave: function() {
                b(this).find(".fullPage-tooltip").fadeOut().remove()
            }}, "#fullPage-nav li");
        c.normalScrollElements && 
        (b(document).on("mouseover", c.normalScrollElements, function() {
            document.addEventListener ? (document.removeEventListener("mousewheel", m, !1), document.removeEventListener("DOMMouseScroll", m, !1)) : document.detachEvent("onmousewheel", m)
        }), b(document).on("mouseout", c.normalScrollElements, function() {
            F()
        }));
        b(".section").on("click", ".controlArrow", function() {
            if (!s) {
                s = !0;
                var a = b(this).closest(".section").find(".slides"), c = a.find(".slide.active"), d = null, d = b(this).hasClass("prev") ? c.prev(".slide") : c.next(".slide");
                d.length || (d = b(this).hasClass("prev") ? c.siblings(":last") : c.siblings(":first"));
                u(a, d)
            }
        });
        b(".section").on("click", ".toSlide", function(a) {
            a.preventDefault();
            a = b(this).closest(".section").find(".slides");
            a.find(".slide.active");
            var c = null, c = a.find(".slide").eq(b(this).data("index") - 1);
            0 < c.length && u(a, c)
        });
        if (!B) {
            var R;
            b(window).resize(function() {
                clearTimeout(R);
                R = setTimeout(M, 500)
            })
        }
        b(window).bind("orientationchange", function() {
            M()
        });
        b(document).on("click", ".fullPage-slidesNav a", function(a) {
            a.preventDefault();
            a = b(this).closest(".section").find(".slides");
            var c = a.find(".slide").eq(b(this).closest("li").index());
            u(a, c)
        })
    }
})(jQuery);
