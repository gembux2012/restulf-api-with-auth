(function ($) {

    var methods = {


        init: function (options) {

            if (!this.data['settings']) {

                this.data['settings'] = $.extend({
                    'position': 'append', //default | prepend
                    'href': '/', //default
                    'onclick': '' //default
                }, options);
            }
        },

        load: function (data) {
            this.data['settings']['el'] = ''
            this.children('.insert_from_json').remove()
            methods.prepare.call(this, data, false)
            if (this.data.settings.position == 'prepend')
                this.prepend(this.data.settings.el)
            else
                this.append(this.data.settings.el)
        },


        prepare: function (data, dropdown) {

            for (var k in data) {
                let i = data[k];
                if (typeof i === 'object') {
                    let href = data[k]['href']

                    let onclick = ''
                    if (data[k]['onclick']) {
                        onclick = (`"
                              event.preventDefault();
                              ${data[k]['onclick']}"`)
                    } else if (this.data.settings.onclick) {
                        onclick = (`"
                              event.preventDefault();
                              ${this.data.settings.onclick}"`)
                    }


                    switch (data[k]['level']) {
                        case 'item':
                            if (!dropdown) {
                                this.data['settings']['el'] += (`<li class="nav-item insert_from_json ${data[k]['active'] ? 'active' : ''}">
                                     <a class="nav-link" href=${href} onclick=${onclick}>${k}</a></li>\n`)
                            } else {
                                this.data['settings']['el'] += (`<a class="dropdown-item insert_from_json" href=${href} onclick=${onclick}>${k}</a>\n`)
                            }
                            methods.prepare.call(this, i, false)
                            break
                        case 'dropdown_item':
                            this.data['settings']['el'] += (`<li class="nav-item dropdown insert_from_json">\n
                                     <a class="nav-link dropdown-toggle" href=
                                      role="button" data-toggle="dropdown" >
                                      ${k}</a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">\n`)
                            methods.prepare.call(this, i, true);
                            this.data['settings']['el'] += '\n</div>\n</li>'
                            break

                    }

                }
            }

        },


    };

    $.fn.navFromJson = function (method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Метод с именем ' + method + ' не существует для jQuery.tooltip');
        }

    };

})(jQuery);